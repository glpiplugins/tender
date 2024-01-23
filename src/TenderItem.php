<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use MassiveAction;
use Glpi\Application\View\TemplateRenderer;

class TenderItem extends CommonDBTM   {

    static $rightname = 'networking';

    static function getIcon() {
        return "fas fa-shopping-cart";
     }

    static function getTypeName($nb = 0) {
        return __('Tender Item', 'tenderitem');
    }


   public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
          // Hier können Sie prüfen, ob der Benutzer die Rechte hat, den Tab zu sehen
          // und entsprechend den Namen zurückgeben oder false, wenn der Tab nicht angezeigt werden soll
          return __("Tender Item", "tender");
      }
      return '';
  }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
         // Hier generieren Sie den Inhalt, der im Tab angezeigt werden soll
         self::showList($item);
      }
   }

   public function showForm($ID, array $options = []) {
    global $CFG_GLPI;
    global $DB;

    $this->initForm($ID, $options);

    $iterator = $DB->request([
        'SELECT' => [
            'glpi_plugin_tender_distributions.id',
            'glpi_plugin_tender_distributions.quantity',
            'location.name as location',
            'delivery_location.name as delivery_location',
            'glpi_budgets.name as budget_name'
        ],
        'FROM' => 'glpi_plugin_tender_distributions',
        'LEFT JOIN' => [
            'glpi_locations as location' => [
                'FKEY' => [
                    'glpi_plugin_tender_distributions' => 'locations_id',
                    'location' => 'id'
                ]
            ],
            'glpi_locations as delivery_location' => [
                'FKEY' => [
                    'glpi_plugin_tender_distributions' => 'delivery_locations_id',
                    'delivery_location' => 'id'
                ]
            ],
            'glpi_budgets' => [
                'FKEY' => [
                    'glpi_plugin_tender_distributions' => 'budgets_id',
                    'glpi_budgets' => 'id'
                ]
            ]
        ],
        'WHERE' => [
            'tenderitems_id' => $ID
            ]
    ]);

    $distributions = [];
    foreach ($iterator as $item) {
        $item['itemtype'] = "GlpiPlugin\Tender\Distribution";
        $distributions[] = $item;
    }

    TemplateRenderer::getInstance()->display('@tender/tenderitemForm.html.twig', [
        'item'   => $this,
        'params' => $options,
        'is_tab' => true,
        'filters' => [],
        'nofilter' => true,
        'columns' => [
            'quantity' => __('Quantity'),
            'location' => __('Distribution'),
            'delivery_location' => __('Delivery Location'),
            'budget_name' => __('Budget'),
        ],
        'total_number' => count($distributions),
        'entries' => $distributions,
        'used' => array_column($distributions, 'id'),
        'showmassiveactions'    => true,
        'massiveactionparams' => [
            'num_displayed'    => min($_SESSION['glpilist_limit'], count($distributions)),
            'container'        => 'massGlpiPluginTenderDistribution' . mt_rand(),
            'specific_actions' => [
                // 'delete' => __('Delete permanently'),
                Distribution::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect'),
            ]
        ],
    ]);

    return true;
 }

   static function showList($tenderItem) {

    global $DB;
    global $CFG_GLPI;

    $iterator = $DB->request([
        'FROM' => 'glpi_plugin_tender_tenderitems',
        'WHERE' => [
            'tenders_id' => $tenderItem->getID()
            ]
    ]);

    $net_total = 0;
    $tax_total = 0;
    $gross_total = 0;

    $items = [];
    foreach ($iterator as $item) {
        $item['itemtype'] = "GlpiPlugin\Tender\TenderItem";
        $item['view_details'] = '<a href="/plugins/tender/front/tenderitem.form.php?id=' . $item['id'] . '">' . __('View Details'). '</a>';

        $iterator2 = $DB->request([
            'SELECT' => [
                'glpi_plugin_tender_distributions.quantity',
                'glpi_locations.name'
            ],
            'FROM' => 'glpi_plugin_tender_distributions',
            'LEFT JOIN' => [
                'glpi_locations' => [
                    'FKEY' => [
                        'glpi_plugin_tender_distributions' => 'locations_id',
                        'glpi_locations' => 'id'
                    ]
                ],
            ],
            'WHERE' => [
                'tenderitems_id' => $item['id']
                ]
        ]);

        $distributions = $item['quantity'] . '<br />';
        foreach ($iterator2 as $distribution) {
            $distributions .= '<small>(' . $distribution['quantity'] . ' - '  . $distribution['name'] . ')</small><br />';
        }

        $item['tax_rate'] = $item['tax'] . ' %';

        $item['net_total'] = $item['net_price'] * $item['quantity'];
        $item['tax'] = $item['net_total'] * ($item['tax'] == 0 ? 1 : ($item['tax'] / 100) );
        $item['gross_total'] = $item['net_total'] + $item['tax'];
        

        $net_total += $item['net_total'];
        $tax_total += $item['tax'];
        $gross_total += $item['gross_total'];

        $item['quantity'] = $distributions;
        $items[] = $item;
    }

    $suppliers = TenderSupplier::getSuppliers($tenderItem->getID());
    $catalogueitems = CatalogueItem::getCatalogueItemsBySupplier($suppliers);
      TemplateRenderer::getInstance()->display('@tender/tenderitemList.html.twig', [
          'item'   => $tenderItem,
          'suppliers' => $suppliers,
          'catalogueitems' => $catalogueitems,
          'itemtypes' => $CFG_GLPI['plugin_tender_types'],
          'footer_entries' => [
            0 => [
                'net_total' => $net_total,
                'tax' => $tax_total,
                'gross_total' => $gross_total,
            ]
          ],
          'tax_total',
          'gross_total',
          'is_tab' => true,
          'filters' => [],
          'nofilter' => true,
          'columns' => [
              'name' => __('name'),
              'quantity' => __('quantity'),
              'net_price' => __('net price'),
              'net_total' => __('net total'),
              'tax_rate' => __('tax rate'),
              'tax' => __('tax'),
              'gross_total' => __('gross total'),
              'view_details' => __('View Detail'),
          ],
          'formatters' => [
                'quantity' => 'raw_html',
                'view_details' => 'raw_html',
                'net_price' => 'float',
                'tax' => 'float',
                'net_total' => 'float',
                'gross_total' => 'float',
          ],
          'total_number' => count($items),
          'entries' => $items,
          'used' => array_column($items, 'id'),
          'showmassiveactions'    => true,
          'massiveactionparams' => [
              'num_displayed'    => min($_SESSION['glpilist_limit'], count($items)),
              'container'        => 'massGlpiPluginTenderTenderItem' . mt_rand(),
              'specific_actions' => [
                  // 'delete' => __('Delete permanently'),
                  TenderItem::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect'),
              ]
          ],
      ]);

      return true;
   }

   static function showMassiveActionsSubForm(MassiveAction $ma) {

        switch ($ma->getAction()) {
        case 'delete':
            echo Html::submit(__('Post'), array('name' => 'massiveaction'))."</span>";

            return true;
        }
        return parent::showMassiveActionsSubForm($ma);
    }

    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                        array $ids) {
        global $DB;

        switch ($ma->getAction()) {
            case 'delete' :
                $input = $ma->getInput();

                foreach ($ids as $id) {
                    Distribution::removeAllDistributions($id);
                    if ($item->getFromDB($id)
                        && $item->deleteFromDB()) {
                    Tender::calculateEstimatedNetTotal($item->fields['tenders_id']);
                    $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                    } else {
                    $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                    $ma->addMessage(__("Something went wrong"));
                    }
                }
                return;

        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    static function getTenderitems($tenders_id) {

        global $DB;

        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_tender_tenderitems',
            'WHERE' => [
                'tenders_id' => $tenders_id
                ]
        ]);

        $items = [];
        foreach ($iterator as $item) {
            $items[] = $item;
        }
        
        return $items;

    }

}
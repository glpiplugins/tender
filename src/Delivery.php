<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use MassiveAction;
use Session;
use Glpi\Application\View\TemplateRenderer;

class Delivery extends CommonDBTM   {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
        return __('Delivery', 'delivery');
    }


   public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
          // Hier können Sie prüfen, ob der Benutzer die Rechte hat, den Tab zu sehen
          // und entsprechend den Namen zurückgeben oder false, wenn der Tab nicht angezeigt werden soll
          return __("Delivery", "tender");
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
    global $DB;

    $this->initForm($ID, $options);

    $iterator = $DB->request([
        'FROM' => 'glpi_plugin_tender_deliveries',
        'WHERE' => [
            'glpi_plugin_tender_deliveries.id' => $ID
        ]
    ]);
    
    $delivery = $iterator->current();

    $iterator = $DB->request([
        'SELECT' => [
            'glpi_plugin_tender_deliveryitems.id AS id',
            'glpi_plugin_tender_distributions.id AS distributions_id',
            'glpi_plugin_tender_distributions.quantity AS quantity',
            'glpi_plugin_tender_tenderitems.name',
            'glpi_plugin_tender_tenderitems.description',
            'SUM' => [
                'glpi_plugin_tender_deliveryitems.quantity AS delivered_quantity'
            ]
        ],
        'FROM' => 'glpi_plugin_tender_distributions',
        'INNER JOIN' => [
            'glpi_plugin_tender_tenderitems' => [
                'FKEY' => [
                    'glpi_plugin_tender_tenderitems' => 'id',
                    'glpi_plugin_tender_distributions' => 'tenderitems_id'
                ]
            ],
        ],
        'LEFT JOIN' => [
            'glpi_plugin_tender_deliveryitems' => [
                'FKEY' => [
                    'glpi_plugin_tender_distributions' => 'id',
                    'glpi_plugin_tender_deliveryitems' => 'distributions_id'
                ]
            ],
            'glpi_plugin_tender_deliveries' => [
                'FKEY' => [
                    'glpi_plugin_tender_deliveries' => 'id',
                    'glpi_plugin_tender_deliveryitems' => 'deliveries_id'
                ]
            ],            
        ],
        'WHERE' => [
            'glpi_plugin_tender_deliveries.id' => $delivery['id'],
        ],
        'GROUPBY' => [
            'glpi_plugin_tender_distributions.locations_id',
            'glpi_plugin_tender_distributions.delivery_locations_id'
        ]
    ]);
    
    $deliveryitems = [];
    foreach($iterator as $item) {
        if($item['delivered_quantity'] > 0) {
            $item['itemtype'] = "GlpiPlugin\Tender\DeliveryItem";
            $deliveryitems[] = $item;
        }
    }
    TemplateRenderer::getInstance()->display('@tender/deliveryForm.html.twig', [
        'item'   => $this,
        'delivery'   => $delivery,
        'is_tab' => true,
        'filters' => [],
        'nofilter' => true,
        'columns' => [
            'name' => __('Name'),
            'description' => __('Description'),
            'quantity' => __('Quantity'),
            'delivered_quantity' => __('Delivered Quantity'),
        ],
        'formatters' => [
            'delivery_date' => 'date',
            'description' => 'raw_html'
        ],
        'total_number' => count($deliveryitems),
        'entries' => $deliveryitems,
        'showmassiveactions'    => true,
        'massiveactionparams' => [
            'num_displayed'    => min($_SESSION['glpilist_limit'], count($deliveryitems)),
            'container'        => 'massGlpiPluginTenderDeliveryItem' . mt_rand(),
            'specific_actions' => [
                // 'delete' => __('Delete permanently'),
                DeliveryItem::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect'),
            ]
        ],
    ]);

    return true;
}

   static function showList($tender = NULL) {

    global $DB;
    global $CFG_GLPI;
    
    $delivery = new Delivery();
    $delivery->initForm('');

    $iterator = $DB->request([
        'FROM' => 'glpi_plugin_tender_deliveries',
        'WHERE' => [
            'glpi_plugin_tender_deliveries.tenders_id' => $tender->getID()
            ]
    ]);

    $deliveries = [];
    foreach ($iterator as $item) {
        $item['itemtype'] = "GlpiPlugin\Tender\Delivery";
        $item['view_details'] = '<a href="/plugins/tender/front/delivery.form.php?id=' . $item['id'] . '">' . __('View Details'). '</a>';
        $deliveries[] = $item;
    }

    $iterator = $DB->request([
        'SELECT' => [
            'glpi_plugin_tender_distributions.id AS distributions_id',
            'glpi_plugin_tender_distributions.quantity AS quantity',
            'loc.name AS location_name',
            'deliv_loc.name AS delivery_location_name',
            'glpi_plugin_tender_tenderitems.name',
            'glpi_plugin_tender_tenderitems.description',
            'SUM' => [
                'glpi_plugin_tender_deliveryitems.quantity AS delivered_quantity'
            ]
        ],
        'FROM' => 'glpi_plugin_tender_distributions',
        'INNER JOIN' => [
            'glpi_locations AS loc' => [
                'FKEY' => [
                    'glpi_plugin_tender_distributions' => 'locations_id',
                    'loc' => 'id'
                ]
            ],
            'glpi_locations AS deliv_loc' => [
                'FKEY' => [
                    'glpi_plugin_tender_distributions' => 'delivery_locations_id',
                    'deliv_loc' => 'id'
                ]
            ],
            'glpi_plugin_tender_tenderitems' => [
                'FKEY' => [
                    'glpi_plugin_tender_tenderitems' => 'id',
                    'glpi_plugin_tender_distributions' => 'tenderitems_id'
                ]
            ],
        ],
        'LEFT JOIN' => [
            'glpi_plugin_tender_deliveryitems' => [
                'FKEY' => [
                    'glpi_plugin_tender_distributions' => 'id',
                    'glpi_plugin_tender_deliveryitems' => 'distributions_id'
                ]
            ]
        ],
        'GROUPBY' => [
            'glpi_plugin_tender_distributions.locations_id',
            'glpi_plugin_tender_distributions.delivery_locations_id'
        ]
    ]);
    

    $tenderitems = [];
    foreach ($iterator as $item) {
        $tenderitems[] = $item;
    }

    TemplateRenderer::getInstance()->display('@tender/deliveryList.html.twig', [
        'item' => $delivery,
        'tenderitems' => $tenderitems,
        'tenders_id' => $tender->getID(),
        'is_tab' => true,
        'filters' => [],
        'nofilter' => true,
        'columns' => [
            'delivery_reference' => __('Delivery Reference'),
            'delivery_date' => __('Delivery date'),
            'view_details' => __('View Details'),
        ],
        'formatters' => [
            'delivery_date' => 'date',
            'view_details' => 'raw_html'
        ],
        'total_number' => count($deliveries),
        'entries' => $deliveries,
        'showmassiveactions'    => true,
        'massiveactionparams' => [
            'num_displayed'    => min($_SESSION['glpilist_limit'], count($deliveries)),
            'container'        => 'massGlpiPluginTenderDelivery' . mt_rand(),
            'specific_actions' => [
                // 'delete' => __('Delete permanently'),
                Delivery::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect'),
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

                    if ($item->getFromDB($id)
                        && $item->deleteFromDB()) {
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

}
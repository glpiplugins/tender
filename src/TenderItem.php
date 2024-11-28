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
  
    $tenderItem = TenderItemModel::find($ID);

    $distributions = $tenderItem->distribution_allocation->toArray();

    TemplateRenderer::getInstance()->display('@tender/tenderitemForm.html.twig', [
        'item'   => $this,
        'params' => $options,
        'is_tab' => true,
        'filters' => [],
        'nofilter' => true,
        'columns' => [
            'quantity' => __('Quantity', 'tender'),
            'percentage' => __('Percentage', 'tender'),
            'net_price' => __('net price', 'tender'),
            'total_net_string' => __('net total', 'tender'),
            'tax_rate' => __('tax rate', 'tender'),
            'total_tax_string' => __('tax', 'tender'),
            'total_gross_string' => __('gross price', 'tender'),
            'percentage' => __('Percentage', 'tender'),
            'location_name' => __('Distribution', 'tender'),
            'delivery_location_name' => __('Delivery Location', 'tender'),
            'financial_name' => __('Financial', 'tender'),
        ],
        'footer_entries' => [
            0 => [
                'percentage' => $tenderItem->total_percentage,
                'total_net_string' => $tenderItem->total_net_string,
                'total_tax_string' => $tenderItem->total_tax_string,
                'total_gross_string' => $tenderItem->total_gross_string,
            ]
          ],
        'formatters' => [
            'percentage' => 'float'
        ],
        'measures'=> Measure::getAllMeasuresDropdown(),
        'total_number' => count($distributions),
        'entries' => $distributions,//$tenderitem->distributions->toArray(),
        'used' => $tenderItem->financialIds,
        'showmassiveactions'    => true,
        'massiveactionparams' => [
            'num_displayed'    => min($_SESSION['glpilist_limit'], count($distributions)),
            'container'        => 'massGlpiPluginTenderDistribution' . mt_rand(),
            'specific_actions' => [
                // 'delete' => __('Delete permanently'),
                Distribution::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect', 'tender'),
            ]
        ],
    ]);

    return true;
 }

   static function showList($tender) {

    global $DB;
    global $CFG_GLPI;
    $initialTender = $tender;
    $tender = TenderModel::find($tender->getID());

    $tenderItems = TenderItemModel::where('plugin_tender_tenders_id', $tender->id)
    ->get()
    ->map(function ($item) {

        $quantity = $item->quantity . '<br />';
        foreach($item->distributions as $distribution) {
            $quantity .= '<small>(' . $distribution->quantity . ' - ' . $distribution->location->name . ')</small><br />';
        }

        return [
            'id'                    => $item->id,
            'name'                  => $item->name,
            'net_price'             => $item->net_price_string,
            'tax_rate'              => $item->tax,
            'total_net'             => $item->total_net_string,
            'total_tax'             => $item->total_tax_string,
            'total_gross'           => $item->total_gross_string,
            'view_details'          => '<a href="/plugins/tender/front/tenderitem.form.php?id=' . $item['id'] . '">' . __('View Details', 'tender'). '</a>',
            'quantity'              => $quantity,
            'itemtype'              => 'GlpiPlugin\Tender\TenderItem'
        ];
        });
   
    $suppliers = Offer::getSuppliers($tender->id);
    $catalogueitems = CatalogueItem::getCatalogueItemsBySupplier($suppliers);
    $measures = Measure::getAllMeasuresDropdown();

      TemplateRenderer::getInstance()->display('@tender/tenderitemList.html.twig', [
          'item'   => $initialTender,
          'suppliers' => $suppliers,
          'catalogueitems' => $catalogueitems,
          'itemtypes' => $CFG_GLPI['plugin_tender_types'],
          'financials' => FinancialModel::orderBy('name')->pluck('name', 'id')->toArray(),
          'measures' => $measures,
          'tax_total',
          'gross_total',
          'is_tab' => true,
          'filters' => [],
          'nofilter' => true,
          'columns' => [
              'name' => __('Name', 'tender'),
              'quantity' => __('Quantity', 'tender'),
              'net_price' => __('Net Price', 'tender'),
              'total_net' => __('net total', 'tender'),
              'tax_rate' => __('tax rate', 'tender'),
              'total_tax' => __('Tax', 'tender'),
              'total_gross' => __('gross total', 'tender'),
              'view_details' => __('View Detail', 'tender'),
          ],
          'formatters' => [
                'quantity' => 'raw_html',
                'view_details' => 'raw_html'
          ],
          'footer_entries' => [
            0 => [
                'total_net' => $tender->total_net_string,
                'total_tax' => $tender->total_tax_string,
                'total_gross' => $tender->total_gross_string,
            ]
          ],
          'total_number' => count($tenderItems),
          'entries' => $tenderItems,
          'used' => $tenderItems->pluck('id'),
          'showmassiveactions'    => true,
          'massiveactionparams' => [
              'num_displayed'    => min($_SESSION['glpilist_limit'], count($tenderItems)),
              'container'        => 'massGlpiPluginTenderTenderItem' . mt_rand(),
              'specific_actions' => [
                  // 'delete' => __('Delete permanently'),
                  TenderItem::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect', 'tender'),
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
                'plugin_tender_tenders_id' => $tenders_id
                ]
        ]);

        $items = [];
        foreach ($iterator as $item) {
            $items[] = $item;
        }
        
        return $items;

    }

    static function update_tenderitem(TenderItem $item) {
        Distribution::updateDistributionPercentages($item->getID());
    }

}
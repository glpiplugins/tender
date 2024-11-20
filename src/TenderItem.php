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

    $tenderitem = TenderItemModel::find($ID)->calculateDistributions();
    // echo '<pre>';
    // print_r($tenderitem);
    // echo '</pre>';
    // $tenderItem = Distribution::with([
    //     'distributions.financial.costcenter' => function($query) use ($tenderitemsId) {
    //         $query->with(['measureitems' => function($query) use ($tenderitemsId) {
    //             $query->where('plugin_tender_measures_id', function($query) use ($tenderitemsId) {
    //                 $query->select('plugin_tender_measures_id')
    //                       ->from('glpi_plugin_tender_tenderitems')
    //                       ->where('id', $tenderitemsId)
    //                       ->limit(1);
    //             });
    //         }]);
    //     },
    //     'measure.measureitems'
    // ])->where('id', $tenderitemsId)->first();

    // $iterator = $DB->request([
    //     'SELECT' => [
    //         'glpi_plugin_tender_distributions.id',
    //         'glpi_plugin_tender_distributions.quantity',
    //         'glpi_plugin_tender_distributions.percentage',
    //         'glpi_plugin_tender_tenderitems.quantity as tenderitem_quantity',
    //         'glpi_plugin_tender_tenderitems.net_price',
    //         'glpi_plugin_tender_tenderitems.tax',
    //         'location.name as location',
    //         'delivery_location.name as delivery_location',
    //         'glpi_plugin_tender_financials.name as financial_name'
    //     ],
    //     'FROM' => 'glpi_plugin_tender_distributions',
    //     'LEFT JOIN' => [
    //         'glpi_locations as location' => [
    //             'FKEY' => [
    //                 'glpi_plugin_tender_distributions' => 'locations_id',
    //                 'location' => 'id'
    //             ]
    //         ],
    //         'glpi_locations as delivery_location' => [
    //             'FKEY' => [
    //                 'glpi_plugin_tender_distributions' => 'delivery_locations_id',
    //                 'delivery_location' => 'id'
    //             ]
    //         ],
    //         'glpi_plugin_tender_tenderitems' => [
    //             'FKEY' => [
    //                 'glpi_plugin_tender_tenderitems' => 'id',
    //                 'glpi_plugin_tender_distributions' => 'tenderitems_id'
    //             ]
    //         ],
    //         'glpi_plugin_tender_financialitems' => [
    //             'FKEY' => [
    //                 'glpi_plugin_tender_financialitems' => 'plugin_tender_tenders_id',
    //                 'glpi_plugin_tender_tenderitems' => 'tenders_id'
    //             ]
    //         ],
    //         'glpi_plugin_tender_financials' => [
    //             'FKEY' => [
    //                 'glpi_plugin_tender_distributions' => 'financials_id',
    //                 'glpi_plugin_tender_financials' => 'id'
    //             ]
    //         ],
    //     ],
    //     'WHERE' => [
    //         'tenderitems_id' => $ID
    //     ],
    //     'GROUPBY' => [
    //         'glpi_plugin_tender_distributions.id',
    //     ]
    // ]);

    // $percentage_total = 0;
    // $net_total = 0;
    // $tax_total = 0;
    // $gross_total = 0;

    // $distributions = [];
    // foreach ($iterator as $item) {
    //     $item['itemtype'] = "GlpiPlugin\Tender\Distribution";
    //     $item['tax_rate'] = $item['tax'] . ' %';
    //     $item['net_total'] = $item['net_price'] * $item['quantity'];
    //     $item['net_total'] = $item['percentage'] == 100 ? $item['net_total'] : $item['net_total'] * ($item['percentage'] / 100);
    //     $item['tax'] = ($item['tax'] == 0 ? 0 : ($item['net_total'] * $item['tax'] / 100) );
    //     $item['gross_total'] = $item['net_total'] + $item['tax'];
        
    //     $percentage_total += $item['percentage'];
    //     $net_total += $item['net_total'];
    //     $tax_total += $item['tax'];
    //     $gross_total += $item['gross_total'];
    //     $distributions[] = $item;
    // }

    $iterator = $DB->request([
        'FROM' => 'glpi_plugin_tender_financialitems',
        'LEFT JOIN' => [
            'glpi_plugin_tender_financials' => [
                'FKEY' => [
                    'glpi_plugin_tender_financialitems' => 'plugin_tender_financials_id',
                    'glpi_plugin_tender_financials' => 'id'
                ]
            ],
        ],
        'WHERE' => [
            'plugin_tender_tenders_id' => $this->fields['plugin_tender_tenders_id']
        ]
    ]);


    $financials = [];

    foreach ($iterator as $item) {
       $financials[$item['id']] = $item['name'];
    }

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
            'net_price_calculated' => __('net total', 'tender'),
            'tax_rate' => __('tax rate', 'tender'),
            'tax' => __('tax', 'tender'),
            'gross_price_calculated' => __('gross price', 'tender'),
            'percentage' => __('Percentage', 'tender'),
            'location_name' => __('Distribution', 'tender'),
            'delivery_location_name' => __('Delivery Location', 'tender'),
            'financial_name' => __('Financial', 'tender'),
        ],
        'footer_entries' => [
            0 => [
                'percentage' => $tenderitem->total_percentage,
                'net_price_calculated' => $tenderitem->total_net_price,
                'tax' => $tenderitem->total_tax,
                'gross_price_calculated' => $tenderitem->total_gross_price,
            ]
          ],
        'formatters' => [
            'net_price_calculated' => 'float',
            'tax' => 'float',
            'net_total' => 'float',
            'gross_price_calculated' => 'float',
        ],
        'measures'=> Measure::getAllMeasuresDropdown(),
        'financials' => $financials,
        'total_number' => count($tenderitem->distributions),
        'entries' => $tenderitem->distributions->toArray(),
        'used' => $tenderitem->financialIds,
        'showmassiveactions'    => true,
        'massiveactionparams' => [
            'num_displayed'    => min($_SESSION['glpilist_limit'], count($tenderitem->distributions)),
            'container'        => 'massGlpiPluginTenderDistribution' . mt_rand(),
            'specific_actions' => [
                // 'delete' => __('Delete permanently'),
                Distribution::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect', 'tender'),
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
            'plugin_tender_tenders_id' => $tenderItem->getID()
            ]
    ]);

    $net_total = 0;
    $tax_total = 0;
    $gross_total = 0;

    $items = [];
    foreach ($iterator as $item) {
        $item['itemtype'] = "GlpiPlugin\Tender\TenderItem";
        $item['view_details'] = '<a href="/plugins/tender/front/tenderitem.form.php?id=' . $item['id'] . '">' . __('View Details', 'tender'). '</a>';

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
                'plugin_tender_tenderitems_id' => $item['id']
                ]
        ]);

        $distributions = $item['quantity'] . '<br />';
        foreach ($iterator2 as $distribution) {
            $distributions .= '<small>(' . $distribution['quantity'] . ' - '  . $distribution['name'] . ')</small><br />';
        }

        $item['tax_rate'] = $item['tax'] . ' %';

        $item['net_total'] = $item['net_price'] * $item['quantity'];
        $item['tax'] = ($item['tax'] == 0 ? 0 : ($item['net_total'] * $item['tax'] / 100) );
        $item['gross_total'] = $item['net_total'] + $item['tax'];
        

        $net_total += $item['net_total'];
        $tax_total += $item['tax'];
        $gross_total += $item['gross_total'];

        $item['quantity'] = $distributions;
        $items[] = $item;
    }
   
    $suppliers = TenderSupplier::getSuppliers($tenderItem->getID());
    $catalogueitems = CatalogueItem::getCatalogueItemsBySupplier($suppliers);
    $measures = Measure::getAllMeasuresDropdown();

      TemplateRenderer::getInstance()->display('@tender/tenderitemList.html.twig', [
          'item'   => $tenderItem,
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
              'net_total' => __('net total', 'tender'),
              'tax_rate' => __('tax rate', 'tender'),
              'tax' => __('Tax', 'tender'),
              'gross_total' => __('Gross Total', 'tender'),
              'view_details' => __('View Detail', 'tender'),
          ],
          'formatters' => [
                'quantity' => 'raw_html',
                'view_details' => 'raw_html',
                'net_price' => 'float',
                'tax' => 'float',
                'net_total' => 'float',
                'gross_total' => 'float',
          ],
          'footer_entries' => [
            0 => [
                'net_total' => $net_total,
                'tax' => $tax_total,
                'gross_total' => $gross_total,
            ]
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
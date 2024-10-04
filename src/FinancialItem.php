<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Entity;
use Glpi\Application\View\TemplateRenderer;

class FinancialItem extends CommonDBTM   {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
      
        return __('Financial Item', 'Financial Items');
    }

   //  static function getIcon() {
   //      return "fas fa-credit-card";
   //   }

   public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Financial') {
          // Hier können Sie prüfen, ob der Benutzer die Rechte hat, den Tab zu sehen
          // und entsprechend den Namen zurückgeben oder false, wenn der Tab nicht angezeigt werden soll
          return __("Financial Item", "tender");
      }
      return '';
  }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Financial') {
         // Hier generieren Sie den Inhalt, der im Tab angezeigt werden soll
         self::showList($item);
      }
   }

     public function showForm($ID, array $options = []) {
        global $CFG_GLPI;

        $this->initForm($ID, $options);
        
        TemplateRenderer::getInstance()->display('@tender/financials.html.twig', [
            'item'   => $this,
            'params' => $options,
            'costcenters' => Costcenter::getAllCostcentersDropdown(),
            'accounts' => Account::getAllAccountsDropdown(),
        ]);

        return true;
     }

    static function showList($financial) {

        global $DB;
        global $CFG_GLPI;
    
        $iterator = $DB->request([
            'SELECT' => [
                'glpi_plugin_tender_financialitems' => [
                    'id',
                    'year',
                    'type',
                    'value'
                ],
                'glpi_plugin_tender_tenders' => [
                    'id as tenders_id',
                    'name',
                    'tender_subject'
                ]
            ],
            'FROM' => 'glpi_plugin_tender_financialitems',
            'LEFT JOIN' => [
                'glpi_plugin_tender_tenders' => [
                    'FKEY' => [
                        'glpi_plugin_tender_financialitems' => 'plugin_tender_tenders_id',
                        'glpi_plugin_tender_tenders' => 'id'
                    ]
                ]
            ],
            'WHERE' => [
                'plugin_tender_financials_id' => $financial->getID()
                ]
        ]);
  
        $total = 0;
        $items = [];
        foreach ($iterator as $item) {
            $item['itemtype'] = "GlpiPlugin\Tender\FinancialItem";
            $item['view_details'] = '<a href="/plugins/tender/front/tenderitem.form.php?id=' . $item['id'] . '">' . __('View Details', 'tender'). '</a>';
            $item['type_name'] = $item['type'] == 0 ? __('Expense', 'tender') : __('Income', 'tender');
            $item['tender_link'] = $item['name'] !== NULL ? '<a href="/plugins/tender/front/tender.form.php?id=' . $item['tenders_id'] . '">' .$item['tender_subject'] . ' ' . $item['name'] . '</a>' : '';
            if($item['type'] == 0) {
                $item['value'] = $item['value'] * -1;
            }
            $items[] = $item;
            $total += $item['value'];
        }
  
        TemplateRenderer::getInstance()->display('@tender/financialItemList.html.twig', [
            'item'   => $financial,
            'financialItems' => $items,
            'footer_entries' => [
              0 => [
                  'value' => $total,
              ]
            ],
            'years' => [
                '2026-01-01' => 2026,
                '2025-01-01' => 2025,
                '2024-01-01' => '2024-01-01',
                '2023-01-01' => 2023,
                '2022-01-01' => 2022,
                '2021-01-01' => 2021,
                '2020-01-01' => 2020
            ],
            'types' => [
                0 => __('Expense', 'tender'),
                1 => __('Income', 'tender'),
            ],
            'is_tab' => true,
            'filters' => [],
            'nofilter' => true,
            'columns' => [
               'year' => __('Year', 'tender'),
               'type_name' => __('Type', 'tender'),
               'value' => __('Value', 'tender'),
               'tender_link' => __('Tender', 'tender'),
               'view_details' => __('View Detail', 'tender')
           ],
           'formatters' => [
                  'view_details' => 'raw_html',
                  'tender_link' => 'raw_html',
                  'value' => 'float',
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
                  //   FinancialItem::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect'),
                ]
            ],
        ]);
  
        return true;
    }

    public function rawSearchOptions() {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '2',
           'table'              => $this::getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'searchtype'         => 'contains',
           'displaytype'        => 'text',
           'massiveaction'      => false
        ];
    
        $tab[] = [
            'id'                 => '3',
            'table'              => $this::getTable(),
            'field'              => 'value',
            'name'               => __('Value'),
            'datatype'           => 'decimal',
            'displaytype'        => 'decimal',
            'massiveaction'      => false,
            'injectable'         => true
         ];
  
        $tab[] = [
           'id'                 => '5',
           'table'              => 'glpi_entities',
           'field'              => 'completename',
           'name'               => Entity::getTypeName(1),
           'datatype'           => 'dropdown',
           'displaytype'        => 'text',
           'massiveaction'      => false,
           'injectable'         => true
        ];
  
        $tab[] = [
           'id'                 => '6',
           'table'              => $this::getTable(),
           'field'              => 'is_recursive',
           'name'               => __('Recursive'),
           'datatype'           => 'bool',
           'displaytype'        => 'bool',
           'massiveaction'      => true
        ];
  
        $tab[] = [
           'id'                 => '7',
           'table'              => $this::getTable(),
           'field'              => 'year',
           'name'               => __('Year'),
           'datatype'           => 'date',
           'displaytype'        => 'text',
           'massiveaction'      => true,
           'injectable'         => true
        ];
  
        $tab[] = [
            'id'                 => '8',
            'table'              => $this::getTable(),
            'field'              => 'type',
            'name'               => __('Type'),
            'datatype'           => 'text',
            'displaytype'        => 'text',
            'massiveaction'      => true,
            'injectable'         => true
         ];

        // $tab[] = [
        //     'id'            => '9',
        //     'table'         => 'glpi_plugin_tender_financials',
        //     'field'         => 'name',
        //     'name'          => __('Financial'),
        //     'datatype'      => 'itemlink',
        //     'itemlink_type' => 'GlpiPlugin\Tender\Financial',
        //     'massiveaction' => false,
        //     'joinparams'    => ['jointype' => 'child'],
        //     'injectable'    => true
        //  ];

        $tab[] = [
            'id'                 => '9',
            'table'              => 'glpi_plugin_tender_financials',
            'field'              => 'name',
            'itemlink_type'      => 'GlpiPlugin\Tender\Financial',
            'linkfield'          => 'plugin_tender_financials_id',
            'name'               => __('Financial Item'),
            'displaytype'        => 'dropdown',
            'relationclass'      => 'GlpiPlugin\Tender\Financial',
            'storevaluein'       => 'plugin_tender_financials_id',
            'injectable'    => true,
        ];

        $tab[] = [
            'id'            => 34,
            'table'         => 'glpi_plugin_tender_financials',
            'field'         => 'name',
            'name'          => __('Financial', 'tender'),
            'datatype'      => 'itemlink',
            'itemlink_type' => 'GlpiPlugin\Tender\Financial',
            'forcegroupby'  => false,
            'displaytype'   => 'dropdown',
            'usehaving'     => true,
            'massiveaction' => false,
            'joinparams'    => ['jointype' => 'child']
         ];

        return $tab;

    }

}
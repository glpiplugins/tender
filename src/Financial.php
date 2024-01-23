<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Entity;
use Glpi\Application\View\TemplateRenderer;

class Financial extends CommonDBTM   {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
      
        return __('Financial', 'Financials');
    }

    static function getIcon() {
        return "fas fa-credit-card";
     }

     public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
          // Hier können Sie prüfen, ob der Benutzer die Rechte hat, den Tab zu sehen
          // und entsprechend den Namen zurückgeben oder false, wenn der Tab nicht angezeigt werden soll
          return __("Financial", "tender");
      }
      return '';
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
         // Hier generieren Sie den Inhalt, der im Tab angezeigt werden soll
         self::showList($item);
      }
   }


     public function defineTabs($options = []) {
      $ong = [];
      //add main tab for current object
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('GlpiPlugin\Tender\FinancialItem', $ong, $options);
    
      return $ong;
   }

     public function showForm($ID, array $options = []) {
        global $CFG_GLPI;
        global $DB;

        $this->initForm($ID, $options);
            
        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_tender_financialitems',
            'WHERE' => [
                'plugin_tender_financials_id' => $ID
                ]
        ]);
    
        $total = 0;
        $items = [];
        foreach ($iterator as $item) {
            $value = $item['value'];
            if($item['type'] == 0) {
               $value = $item['value'] * -1;
            }
            $total += $value;
        }

        TemplateRenderer::getInstance()->display('@tender/financials.html.twig', [
            'item'   => $this,
            'params' => $options,
            'costcenters' => Costcenter::getAllCostcentersDropdown(),
            'accounts' => Account::getAllAccountsDropdown(),
            'total' => $total
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
           'massiveaction'      => false
        ];
  
        $tab[] = [
            'id'                 => '4',
            'table'              => $this::getTable(),
            'field'              => 'name',
            'name'               => __('Name'),
            'datatype'           => 'itemlink',
            'massiveaction'      => false
        ];
  
        $tab[] = [
            'id'                 => '3',
            'table'              => 'glpi_plugin_tender_costcenters',
            'field'              => 'name',
            'name'               => __('Costcenter'),
            'datatype'           => 'dropdown',
            'massiveaction'      => false
         ];

         $tab[] = [
            'id'                 => '8',
            'table'              => 'glpi_plugin_tender_accounts',
            'field'              => 'name',
            'name'               => __('Account'),
            'datatype'           => 'dropdown',
            'massiveaction'      => false
         ];
  
        $tab[] = [
           'id'                 => '5',
           'table'              => 'glpi_entities',
           'field'              => 'completename',
           'name'               => Entity::getTypeName(1),
           'datatype'           => 'dropdown',
           'massiveaction'      => false
        ];
  
        $tab[] = [
           'id'                 => '6',
           'table'              => $this::getTable(),
           'field'              => 'is_recursive',
           'name'               => __('Recursive'),
           'datatype'           => 'bool',
           'massiveaction'      => true
        ];
  
        $tab[] = [
           'id'                 => '7',
           'table'              => $this::getTable(),
           'field'              => 'language',
           'name'               => __('Language'),
           'datatype'           => 'specific',
           'searchtype'         => [
              '0'                  => 'equals'
           ],
           'massiveaction'      => true
        ];
  
        return $tab;

    }

    
   static function showList($tender) {

      global $DB;
      global $CFG_GLPI;
  
      $iterator = $DB->request([
          'FROM' => 'glpi_plugin_tender_financials'
      ]);
  

      $financials = [];

      foreach ($iterator as $item) {
         $financials[$item['id']] = $item['name'];
      }
  
      $iterator = $DB->request([
         'SELECT' => [
            'glpi_plugin_tender_financials' => 'name as name',
            'glpi_plugin_tender_costcenters' => 'name as costcenter',
            'glpi_plugin_tender_accounts' => 'name as account',
            'glpi_plugin_tender_financialitems' => 'type as type',
            'SUM' => [
               'glpi_plugin_tender_financialitems.value as value'
           ]
         ],
         'FROM' => 'glpi_plugin_tender_financialitems',
         'LEFT JOIN' => [
            'glpi_plugin_tender_financials' => [
                'FKEY' => [
                    'glpi_plugin_tender_financialitems' => 'plugin_tender_financials_id',
                    'glpi_plugin_tender_financials' => 'id'
                ]
            ],
            'glpi_plugin_tender_costcenters' => [
               'FKEY' => [
                  'glpi_plugin_tender_costcenters' => 'id',
                  'glpi_plugin_tender_financials' => 'plugin_tender_costcenters_id'
               ]
            ],
            'glpi_plugin_tender_accounts' => [
               'FKEY' => [
                  'glpi_plugin_tender_accounts' => 'id',
                  'glpi_plugin_tender_financials' => 'plugin_tender_accounts_id'
               ]
            ],
      ],
         'WHERE' => [
            'plugin_tender_tenders_id' => $tender->getID()
            ]
      ]);

      $total = 0;
      $items = [];
      foreach ($iterator as $item) {
         $items[] = $item;
         if($item['type'] == 0) {
            $value = $item['value'] * -1;
         }
         $total += $value;
      }
      
      TemplateRenderer::getInstance()->display('@tender/financialList.html.twig', [
         'item'   => $tender,
         'financials' => $financials,
         'footer_entries' => [
            0 => [
               'total' => $total
            ]
         ],
         'is_tab' => true,
         'filters' => [],
         'nofilter' => true,
         'columns' => [
               'name' => __('name'),
               'costcenter' => __('costcenter'),
               'account' => __('account'),
               'value' => __('value'),
         ],
         'formatters' => [
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
               //   TenderItem::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect'),
               ]
         ],
        ]);
  
        return true;
     }
  

}
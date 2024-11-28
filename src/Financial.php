<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Entity;
use MassiveAction;
use Html;
use Glpi\Application\View\TemplateRenderer;

class Financial extends CommonDBTM   {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
      
        return __('Financial', 'tender');
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
            
        $financial = FinancialModel::find($ID);
        
        TemplateRenderer::getInstance()->display('@tender/financials.html.twig', [
            'item'   => $this,
            'params' => $options,
            'costcenters' => Costcenter::getAllCostcentersDropdown(),
            'accounts' => Account::getAllAccountsDropdown(),
            'total_available' => MoneyHandler::formatToString($financial->total_available ?? 0)
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
            'id'                 => '3',
            'table'              => $this::getTable(),
            'field'              => 'name',
            'name'               => __('Name'),
            'datatype'           => 'itemlink',
            'displaytype'        => 'text',
            'massiveaction'      => false,
            'injectable'         => true
        ];


        $tab[] = [
         'id'                 => '4',
         'table'              => 'glpi_plugin_tender_accounts',
         'field'              => 'name',
         'datatype'           => 'relation',
         'itemlink_type'      => 'GlpiPlugin\Tender\Account',
         'linkfield'          => 'plugin_tender_accounts_id',
         'name'               => __('Account'),
         'displaytype'        => 'dropdown',
         'relationclass'      => 'GlpiPlugin\Tender\Account',
         'storevaluein'       => 'plugin_tender_accounts_id',
         'injectable'         => true
      ];
        
         $tab[] = [
            'id'                 => '8',
            'table'              => 'glpi_plugin_tender_costcenters',
            'field'              => 'name',
            'datatype'           => 'relation',
            'itemlink_type'      => 'GlpiPlugin\Tender\Costcenter',
            'linkfield'          => 'plugin_tender_costcenters_id',
            'name'               => __('Costcenter'),
            'displaytype'        => 'dropdown',
            'relationclass'      => 'GlpiPlugin\Tender\Costcenter',
            'storevaluein'       => 'plugin_tender_costcenters_id',
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
           'displaytype'        => 'text',
           'datatype'           => 'bool',
           'massiveaction'      => true
        ];
  
        $tab[] = [
           'id'                 => '7',
           'table'              => $this::getTable(),
           'field'              => 'language',
           'name'               => __('Language'),
           'datatype'           => 'specific',
           'displaytype'        => 'text',
           'searchtype'         => [
              '0'                  => 'equals'
           ],
           'massiveaction'      => true
        ];
  
        $tab[] = [
         'id'                 => '9',
         'table'              => $this::getTable(),
         'field'              => 'reference',
         'name'               => __('Reference', 'tender'),
         'datatype'           => 'text',
         'displaytype'        => 'text',
         'massiveaction'      => false,
         'injectable'         => true
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
            'glpi_plugin_tender_financials' => [
               'name as name',
               'reference as reference'
            ],
            'glpi_plugin_tender_costcenters' => 'name as costcenter',
            'glpi_plugin_tender_accounts' => 'name as account',
            'glpi_plugin_tender_financialitems' => [
               'type as type',
               'year as year'
            ],
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
            'plugin_tender_tenders_id' => $tender->getID(),
            'glpi_plugin_tender_financialitems.type' => 0
         ],
         'GROUPBY' => [
            'glpi_plugin_tender_financials.name',
            'glpi_plugin_tender_financialitems.year'
         ]
      ]);

      $total = 0;
      $items = [];
      foreach ($iterator as $item) {
         $items[] = $item;
         $total += $item['value'];
      }

      $items = FinancialItemModel::where('plugin_tender_tenders_id', $tender->getID())
         ->where('type', 0)
         ->with(['financial', 'financial.costcenter', 'financial.account'])->get();

         $items = $items->map(function($item) {
            return [
                'id'          => $item->id,
                'value'       => MoneyHandler::formatToString($item->value),
                'name'        => '<a href="/plugins/tender/front/financial.form.php?id=' . $item->financial->id . '">' . $item->financial->name . '</a>' ?? null,
                'costcenter'  => $item->financial->costCenter->name ?? null,
                'account'     => $item->financial->account->name ?? null,
                'reference'   => $item->financial->reference ?? null,
                'year'        => $item->year ?? null,
                'total'       => MoneyHandler::formatToString($item->financial->getTotalAvailableAttribute()) ?? null,
                'itemtype'    => "GlpiPlugin\Tender\FinancialItem"
            ];
        })->toArray();

      TemplateRenderer::getInstance()->display('@tender/financialList.html.twig', [
         'item'   => $tender,
         'financials' => $financials,
         'footer_entries' => [
            0 => [
               'value' => MoneyHandler::formatToString($total)
            ]
         ],
         'years' => [
            2026 => 2026,
            2025 => 2025,
            2024 => 2024,
            2023 => 2023,
            2022 => 2022,
            2021 => 2021,
            2020 => 2020
        ],
         'is_tab' => true,
         'filters' => [],
         'nofilter' => true,
         'columns' => [
               'name' => __('name'),
               'year' => __('Year', 'tender'),
               'costcenter' => __('Costcenter', 'tender'),
               'account' => __('Account', 'tender'),
               'reference' => __('Reference', 'tender'),
               'value' => __('Value', 'tender'),
               'total' => __('Total', 'tender')
         ],
         'formatters' => [
            'name'  => 'raw_html',
        ],
         'total_number' => count($items),
         'entries' => $items,
         'used' => array_column($items, 'id'),
         // 'showmassiveactions'    => true,
         'massiveactionparams' => [
               'num_displayed'    => min($_SESSION['glpilist_limit'], count($items)),
               'container'        => 'massGlpiPluginTenderTenderItem' . mt_rand(),
               'specific_actions' => [
                  // 'delete' => __('Delete permanently'),
                 FinancialItem::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect1', 'tender'),
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
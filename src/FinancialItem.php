<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Entity;
use MassiveAction;
use Html;
use Glpi\Application\View\TemplateRenderer;

class FinancialItem extends CommonDBTM   {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
      
        return __('Financial Item', 'Financial Items');
    }

   public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Financial') {
          return __("Financial Item", "tender");
      }
      return '';
  }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Financial') {
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
        
        $financialItems = FinancialItemModel::where('plugin_tender_financials_id', $financial->getID())
        ->get()
        ->map(function($item) {
            return [
                'total_available'                   => $item->financial->total_available,
                'id'                                => $item->id,
                'year'                              => $item->year,
                'type'                              => $item->type,
                'value'                             => $item->type == 0 ? MoneyHandler::formatToString($item->value * -1) : MoneyHandler::formatToString($item->value),
                'view_details'                      => '<a href="/plugins/tender/front/tenderitem.form.php?id=' . $item->id . '">' . __('View Details', 'tender'). '</a>',
                'itemtype'                          => "GlpiPlugin\Tender\FinancialItem",
                'type_name'                         => $item->type == 0 ? __('Expense', 'tender') : __('Income', 'tender'),
                'tender_link'                       => $item->tender ? '<a href="/plugins/tender/front/tender.form.php?id=' . $item->tender->id . '">' . $item->tender->tender_subject . '/' . $item->tender->name . '</a>' : '',
            ];
        });

  
        TemplateRenderer::getInstance()->display('@tender/financialItemList.html.twig', [
            'item'   => $financial,
            'financialItems' => $financialItems,
            'footer_entries' => [
              0 => [
                  'value' => MoneyHandler::formatToString($financialItems->first()['total_available'] ?? 0),
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
            ],
            'total_number' => count($financialItems),
            'entries' => $financialItems,
            'used' => $financialItems,
            'showmassiveactions'    => true,
            'massiveactionparams' => [
                'num_displayed'    => min($_SESSION['glpilist_limit'], count($financialItems)),
                'container'        => 'massGlpiPluginTenderFinancialItem' . mt_rand(),
                'specific_actions' => [
                    // 'delete' => __('Delete permanently'),
                    // FinancialItem::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect', 'tender'),
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
                    $financialItem = FinancialItemModel::find($id);
                    $hasMatchingDistribution = FinancialItemModel::where('id', $id)
                        ->whereHas('tender.distributions', function ($query) use ($financialItem) {
                            $query->where('plugin_tender_financials_id', $financialItem->plugin_tender_financials_id);
                        })->exists();

                    if (!$hasMatchingDistribution) {
                        if ($item->getFromDB($id)
                            && $item->deleteFromDB()) {
                        
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                        } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        $ma->addMessage(__("Something went wrong"));
                        }
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
<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use MassiveAction;
use Glpi\Application\View\TemplateRenderer;

class CatalogueItemSupplier extends CommonDBTM   {

    static $rightname = 'networking';

    static function getIcon() {
        return "fas fa-contact";
     }

    static function getTypeName($nb = 0) {
        return __('Catalogue Item Supplier', 'catalogueitemsupplier');
    }

    public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'GlpiPlugin\Tender\CatalogueItem') {
        return __("Supplier", "tender");
      }
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'GlpiPlugin\Tender\CatalogueItem') {
            self::showList($item);
        }
    }

    static function showList($item) {

        global $DB;

        $iterator = $DB->request([
            'SELECT' => [
                'glpi_suppliers.*',
                'glpi_plugin_tender_catalogueitemsuppliers.suppliers_reference',
                'glpi_plugin_tender_catalogueitemsuppliers.net_price',
            ],
            'FROM' => 'glpi_plugin_tender_catalogueitemsuppliers',
            'LEFT JOIN' => [
                'glpi_suppliers' => [
                    'FKEY' => [
                        'glpi_plugin_tender_catalogueitemsuppliers' => 'suppliers_id',
                        'glpi_suppliers' => 'id'
                    ]
                ]
            ],
            'WHERE' => [
                'plugin_tender_catalogueitems_id' => $item->getID()
                ]
        ]);

        $suppliers = [];
        foreach ($iterator as $supplier) {
            $supplier['name'] = '<a href="/front/supplier.form.php?id=' . $supplier['id'] . '">' . $supplier['name'] . '</a>';
            $supplier['itemtype'] = "GlpiPlugin\Tender\CatalogueItemSupplier";
            $suppliers[] = $supplier;
        }
        TemplateRenderer::getInstance()->display('@tender/catalogueitemsuppliers.html.twig', [
            'item'   => $item,
            'is_tab' => true,
            'filters' => [],
            'nofilter' => true,
            'columns' => [
                'id' => __('ID'),
                'name' => __('Name'),
                'suppliers_reference' => __('Supplier Reference'),
                'net_price' => __('Net Price'),
            ],
            'formatters' => [
                'name' => 'raw_html',
                'net_price' => 'float'
            ],
            'total_number' => count($suppliers),
            'entries' => $suppliers,
            'used' => array_column($suppliers, 'id'),
            'showmassiveactions'    => true,
            'massiveactionparams' => [
                'num_displayed'    => min($_SESSION['glpilist_limit'], count($suppliers)),
                'container'        => 'massGlpiPluginTenderCatalogueItemSupplier' . mt_rand(),
                'specific_actions' => [
                    // 'delete' => __('Delete permanently'),
                    CatalogueItemSupplier::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect'),
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

     public function rawSearchOptions() {

        $tab = parent::rawSearchOptions();

        // $tab[] = [
        //     'id'                 => '2',
        //     'table'              => $this->getTable(),
        //     'field'              => 'id',
        //     'name'               => __('ID'),
        //     'massiveaction'      => false, // implicit field is id
        //     'datatype'           => 'number'
        // ];

        $tab[] = [
            'id'                 => '3',
            'table'              => self::getTable(),
            'field'              => 'net_price',
            'name'               => __('Name'),
            'datatype'           => 'dropdown',
            'injectable'    => true,
        ];


        $tab[] = [
            'id'            => '6',
            'table'         => self::getTable(),
            'field'         => 'net_price',
            'name'          => __('Net Price'),
            'datatype'      => 'float',
            'checktype'     => 'float',
            'injectable'    => true,
        ];

        $tab[] = [
            'id'                 => '7',
            'table'              => self::getTable(),
            'field'              => 'suppliers_reference',
            'name'               => __('Suppliers Reference'),
            'datatype'           => 'dropdown',
            'injectable'    => true,
        ];

        $tab[] = [
            'id'            => 34,
            'table'         => 'glpi_suppliers',
            'field'         => 'name',
            'name'          => __('Supplier'),
            'datatype'      => 'itemlink',
            'itemlink_type' => 'Supplier',
            'forcegroupby'  => false,
            'usehaving'     => true,
            'massiveaction' => false,
            'joinparams'    => [
                'joinparams' => ['jointype' => 'child']
            ],
            'injectable'    => true,
         ];
   
         // $tab[] = [
         //    'id'            => 35,
         //    'table'         => self::getTable(),
         //    'field'         => 'date_mod',
         //    'name'          => __('Last update'),
         //    'datatype'      => 'datetime',
         //    'massiveaction' => false,
         // ];
   
         // $tab[] = [
         //    'id'            => 80,
         //    'table'         => 'glpi_entities',
         //    'field'         => 'completename',
         //    'name'          => __('Entity'),
         //    'datatype'      => 'dropdown',
         //    'injectable'    => false,
         // ];
   
         // $tab[] = [
         //    'id'            => 86,
         //    'table'         => self::getTable(),
         //    'field'         => 'is_recursive',
         //    'name'          => __('Child entities'),
         //    'datatype'      => 'bool',
         //    'checktype'     => 'text',
         //    'displaytype'   => 'dropdown',
         //    'injectable'    => true,
         //    'searchtype'    => ['equals'],
         // ];
   
         return $tab;
      }

}
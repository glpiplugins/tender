<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use CommonDropdown;
use Html;
use Entity;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;

class CatalogueItem extends CommonDropdown  {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
      
        return __('Catalogue Item', 'Catalogue Items');
    }

     public function defineTabs($options = []) {
      $ong = [];
      //add main tab for current object
      $this->addDefaultFormTab($ong);
      //add core Document tab
      $this->addStandardTab('GlpiPlugin\Tender\CatalogueItemSupplier', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      return $ong;
   }
       
     static function getIcon() {
        return "fas fa-shopping-cart";
     }

    public function showForm($ID, array $options = []) {
        global $CFG_GLPI;

        $this->initForm($ID, $options);
        
        TemplateRenderer::getInstance()->display('@tender/catalogueitems.html.twig', [
            'item'   => $this,
            'params' => $options,
            'itemtypes' => $CFG_GLPI['plugin_tender_types']
        ]);

        return true;
     }

    // public function rawSearchOptions() {
    //     $tab = parent::rawSearchOptions();

    //     $tab[] = [
    //        'id'                 => '8',
    //        'table'              => $this::getTable(),
    //        'field'              => 'id',
    //        'name'               => __('ID'),
    //        'searchtype'         => 'contains',
    //        'massiveaction'      => false
    //     ];
  
    //     $tab[] = [
    //         'id'                 => '3',
    //         'table'              => $this::getTable(),
    //         'field'              => 'name',
    //         'name'               => __('Name'),
    //         'datatype'           => 'string',
    //         'massiveaction'      => false
    //     ];
  
    //     $tab[] = [
    //        'id'                 => '4',
    //        'table'              => $this::getTable(),
    //        'field'              => 'is_recursive',
    //        'name'               => __('Recursive'),
    //        'datatype'           => 'bool',
    //        'massiveaction'      => true
    //     ];
  
    //     $tab[] = [
    //        'id'                 => '5',
    //        'table'              => $this::getTable(),
    //        'field'              => 'language',
    //        'name'               => __('Language'),
    //        'datatype'           => 'specific',
    //        'searchtype'         => [
    //           '0'                  => 'equals'
    //        ],
    //        'massiveaction'      => true
    //     ];
  
    //     return $tab;

    // }

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
            'field'              => 'name',
            'name'               => __('Name'),
            'datatype'           => 'dropdown',
            'injectable'    => true,
        ];

        $tab[] = [
            'id'                 => '4',
            'table'              => self::getTable(),
            'field'              => 'itemtype',
            'name'               => __('Itemtype'),
            'datatype'           => 'dropdown',
            'injectable'    => true,
        ];

        $tab[] = [
            'id'                 => '5',
            'table'              => self::getTable(),
            'field'              => 'itemtype',
            'name'               => __('Itemtype'),
            'datatype'           => 'dropdown',
            'injectable'    => true,
        ];

        $tab[] = [
            'id'            => '6',
            'table'         => 'glpi_manufacturers',
            'field'         => 'name',
            'name'          => __('Manufacturer'),
            'datatype'      => 'dropdown',
            'checktype'     => 'text',
            'displaytype'   => 'dropdown',
            'injectable'    => true,
        ];

        $tab[] = [
            'id'                 => '7',
            'table'              => self::getTable(),
            'field'              => 'manufacturers_reference',
            'name'               => __('Manufacturers Reference'),
            'datatype'           => 'dropdown',
            'injectable'    => true,
        ];

        // $tab[] = [
        //    'id'            => 1,
        //    'table'         => self::getTable(),
        //    'field'         => 'name',
        //    'name'          => __('Catalogue Item'),
        //    'datatype'      => 'text',
        //    'checktype'     => 'text',
        //    'displaytype'   => 'text',
        //    'injectable'    => true,
        //    'autocomplete'  => true,
        // ];
  
        // $tab[] = [
        //    'id'            => 2,
        //    'table'         => self::getTable(),
        //    'field'         => 'comment',
        //    'name'          => __('Comments'),
        //    'datatype'      => 'text',
        //    'checktype'     => 'text',
        //    'displaytype'   => 'multiline_text',
        //    'injectable'    => true,
        // ];
  
        // $tab[] = [
        //    'id'            => 3,
        //    'table'         => self::getTable(),
        //    'field'         => 'itemtype',
        //    'name'          => __('Item type'),
        //    'datatype'      => 'specific',
        //    'itemtype_list' => 'plugin_order_types',
        //    'checktype'     => 'itemtype',
        //    'searchtype'    => ['equals'],
        //    'injectable'    => true,
        //    'massiveaction' => false,
        // ];
  
        // $tab[] = [
        //    'id'            => 4,
        //    'table'         => self::getTable(),
        //    'field'         => 'models_id',
        //    'name'          => __('Model'),
        //    'checktype'     => 'text',
        //    'displaytype'   => 'reference_model',
        //    'injectable'    => true,
        //    'massiveaction' => false,
        //    'nosearch'      => true,
        //    'additionalfields' => ['itemtype'],
        // ];
  

  
        // $tab[] = [
        //    'id'            => 6,
        //    'table'         => self::getTable(),
        //    'field'         => 'types_id',
        //    'name'          => __('Type'),
        //    'checktype'     => 'text',
        //    'injectable'    => true,
        //    'massiveaction' => false,
        //    'searchtype'    => ['equals'],
        //    'nosearch'      => true,
        //    'additionalfields' => ['itemtype'],
        // ];
  
        // $tab[] = [
        //    'id'            => 7,
        //    'table'         => self::getTable(),
        //    'field'         => 'templates_id',
        //    'name'          => __('Template name'),
        //    'checktype'     => 'text',
        //    'displaytype'   => 'dropdown',
        //    'injectable'    => true,
        //    'massiveaction' => false,
        //    'nosearch'      => true,
        //    'additionalfields' => ['itemtype'],
        // ];
  
        // $tab[] = [
        //    'id'            => 8,
        //    'table'         => self::getTable(),
        //    'field'         => 'manufacturers_reference',
        //    'name'          => __('Manufacturer reference', 'order'),
        //    'autocomplete'  => true,
        // ];
  
        // $tab[] = [
        //    'id'            => 30,
        //    'table'         => self::getTable(),
        //    'field'         => 'id',
        //    'name'          => __('ID'),
        //    'injectable'    => false,
        //    'massiveaction' => false,
        // ];
  
        // $tab[] = [
        //    'id'            => 31,
        //    'table'         => self::getTable(),
        //    'field'         => 'is_active',
        //    'name'          => __('Active'),
        //    'datatype'      => 'bool',
        //    'checktype'     => 'bool',
        //    'displaytype'   => 'bool',
        //    'injectable'    => true,
        //    'searchtype'    => ['equals'],
        // ];
  
        // $tab[] = [
        //    'id'            => 32,
        //    'table'         => 'glpi_plugin_tender_catalogueitem_suppliers',
        //    'field'         => 'name',
        //    'name'          => __('Unit price tax free', 'order'),
        //    'datatype'      => 'decimal',
        //    'forcegroupby'  => true,
        //    'usehaving'     => true,
        //    'massiveaction' => false,
        //    'joinparams'    => ['jointype' => 'child'],
        // ];
  
        // $tab[] = [
        //    'id'            => 33,
        //    'table'         => 'glpi_plugin_order_references_suppliers',
        //    'field'         => 'reference_code',
        //    'name'          => __('Manufacturer\'s product reference', 'order'),
        //    'forcegroupby'  => true,
        //    'usehaving'     => true,
        //    'massiveaction' => false,
        //    'joinparams'    => ['jointype' => 'child'],
        // ];
  
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
              'beforejoin' => [
                 'table'      => 'glpi_plugin_tender_catalogueitemsuppliers',
                 'joinparams' => ['jointype' => 'child']
              ]
           ],
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
  

    static function getCatalogueItemsBySupplier($suppliers, $itemtypes = NULL) {

        global $DB;

        $catalogueitems = [];

        if (count($suppliers) > 0) {

            $query = [
                'SELECT' => ['glpi_plugin_tender_catalogueitems.id', 'glpi_plugin_tender_catalogueitems.name'],
                'DISTINCT' => true,
                'FROM' => 'glpi_plugin_tender_catalogueitems',
                'LEFT JOIN' => [
                    'glpi_plugin_tender_catalogueitemsuppliers' => [
                        'FKEY' => [
                            'glpi_plugin_tender_catalogueitems' => 'id',
                            'glpi_plugin_tender_catalogueitemsuppliers' => 'plugin_tender_catalogueitems_id'
                        ]
                    ]
                ],
                'WHERE' => [
                    'suppliers_id' => $suppliers
                ]
            ];

            if(is_array($itemtypes) && !empty($itemtypes)) {
                array_push($query['WHERE'], ['itemtype' => $itemtypes]);
            }

            $iterator = $DB->request($query);

            
            foreach ($iterator as $catalogueitem) {
                $catalogueitems[$catalogueitem['id']] = $catalogueitem['name'];
            }
        }

        return $catalogueitems;

    }

    static function getByItemtypes($suppliers) {

        global $DB;

        $catalogueitems = [];

        if (count($suppliers) > 0) {
            $iterator = $DB->request([
                'SELECT' => ['glpi_plugin_tender_catalogueitems.id', 'glpi_plugin_tender_catalogueitems.name'],
                'DISTINCT' => true,
                'FROM' => 'glpi_plugin_tender_catalogueitems',
                'LEFT JOIN' => [
                    'glpi_plugin_tender_catalogueitemsuppliers' => [
                        'FKEY' => [
                            'glpi_plugin_tender_catalogueitems' => 'id',
                            'glpi_plugin_tender_catalogueitemsuppliers' => 'plugin_tender_catalogueitems_id'
                        ]
                    ]
                ],        
                'WHERE' => [
                    'suppliers_id' => $suppliers
                    ]
            ]);

            
            foreach ($iterator as $catalogueitem) {
                $catalogueitems[$catalogueitem['id']] = $catalogueitem['name'];
            }
        }

        return $catalogueitems;

    }

    function isPrimaryType() {

        return true;
     }

}

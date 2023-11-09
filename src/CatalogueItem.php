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

    public function rawSearchOptions() {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '8',
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
            'datatype'           => 'string',
            'massiveaction'      => false
        ];
  
        $tab[] = [
           'id'                 => '4',
           'table'              => $this::getTable(),
           'field'              => 'is_recursive',
           'name'               => __('Recursive'),
           'datatype'           => 'bool',
           'massiveaction'      => true
        ];
  
        $tab[] = [
           'id'                 => '5',
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
                            'glpi_plugin_tender_catalogueitemsuppliers' => 'catalogueitems_id'
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
                            'glpi_plugin_tender_catalogueitemsuppliers' => 'catalogueitems_id'
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

}

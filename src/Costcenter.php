<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use CommonDropdown;
use Html;
use Entity;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;

class Costcenter extends CommonDropdown  {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
      
        return __('Costcenter', 'Costcenter');
    }
       
     static function getIcon() {
        return "fas fa-building";
     }

    public function showForm($ID, array $options = []) {
        global $CFG_GLPI;

        $this->initForm($ID, $options);
        
        TemplateRenderer::getInstance()->display('@tender/costcenters.html.twig', [
            'item'   => $this,
            'params' => $options,
            'itemtypes' => $CFG_GLPI['plugin_tender_types']
        ]);

        return true;
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
            'field'              => 'name',
            'name'               => __('Name'),
            'datatype'           => 'dropdown',
            'displaytype'   => 'text',
            'injectable'    => true,
        ];

 
        return $tab;
     }
  
    function isPrimaryType() {

        return true;
     }

     public static function getAllCostcentersDropdown() : array {

        global $DB;

        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_tender_costcenters'
        ]);

        $costcenters = [];

        foreach ($iterator as $costcenter) {
                $costcenters[$costcenter['id']] = $costcenter['name'];
        }

        return $costcenters;
     }

     public static function getCostcenters() {

        global $DB;

        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_tender_costcenters'
        ]);
    
    
        $items = [];
    
        foreach ($iterator as $item) {
           $items[$item['id']] = $item['name'];
        }

        return $items;
    
     }

}

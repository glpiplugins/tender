<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use CommonDropdown;
use Html;
use Entity;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;

class DocumentTemplate extends CommonDropdown  {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
      
        return __('Document Template', 'tender');
    }
       
     static function getIcon() {
        return "fas fa-file";
     }

     public function defineTabs($options = []) {
        $ong = [];
        //add main tab for current object
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('GlpiPlugin\Tender\DocumentTemplateParameter', $ong, $options);
      
        return $ong;
     }
  

    public function showForm($ID, array $options = []) {
        global $CFG_GLPI;

        $this->initForm($ID, $options);
        
        TemplateRenderer::getInstance()->display('@tender/documenttemplate.html.twig', [
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

}

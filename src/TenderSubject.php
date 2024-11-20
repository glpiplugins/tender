<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use CommonDropdown;
use Html;
use Entity;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;

class TenderSubject extends CommonDropdown  {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
      
        return __('Tender Subject', 'tender');
    }

     public function defineTabs($options = []) {
      $ong = [];
      //add main tab for current object
      $this->addDefaultFormTab($ong);
      return $ong;
   }
       
     static function getIcon() {
        return "fas fa-file-invoice";
     }

    public function showForm($ID, array $options = []) {
        global $CFG_GLPI;

        $this->initForm($ID, $options);
        
        TemplateRenderer::getInstance()->display('@tender/tendersubject.html.twig', [
            'item'   => $this,
            'params' => $options,
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
         'field'              => 'description',
         'name'               => __('Description'),
         'datatype'           => 'string',
         'massiveaction'      => false
     ];
  
        $tab[] = [
           'id'                 => '5',
           'table'              => $this::getTable(),
           'field'              => 'is_recursive',
           'name'               => __('Recursive'),
           'datatype'           => 'bool',
           'massiveaction'      => true
        ];
  
        $tab[] = [
           'id'                 => '6',
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

}

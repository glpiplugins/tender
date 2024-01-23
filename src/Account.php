<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use CommonDropdown;
use Html;
use Entity;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;

class Account extends CommonDropdown  {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
      
        return __('Accounts', 'Accounts');
    }
       
     static function getIcon() {
        return "fas fa-shopping-cart";
     }

    public function showForm($ID, array $options = []) {
        global $CFG_GLPI;

        $this->initForm($ID, $options);
        
        TemplateRenderer::getInstance()->display('@tender/accounts.html.twig', [
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

     public static function getAllAccountsDropdown() : array {

        global $DB;

        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_tender_accounts'
        ]);

        $accounts = [];

        foreach ($iterator as $account) {
                $accounts[$account['id']] = $account['name'];
        }

        return $accounts;
     }

}

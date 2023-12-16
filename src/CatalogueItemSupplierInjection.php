<?php

namespace GlpiPlugin\Tender;

use Search;
use PluginDatainjectionInjectionInterface;
use PluginDatainjectionCommonInjectionLib;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class CatalogueItemSupplierInjection extends CatalogueItemSupplier implements PluginDatainjectionInjectionInterface {


   public function __construct() {
      $this->table = getTableForItemType(get_parent_class($this));
   }


   /**
    * Returns the name of the table used to store this object parent
    *
    * @return string (table name)
   **/
   static function getTable($classname = null) {

      $parenttype = get_parent_class();
      return $parenttype::getTable();
   }


   public function isPrimaryType() {
      return true;
   }


   public function connectedTo() {
      return [
         'GlpiPlugin\Tender\CatalogueItem'
      ];
   }

   public function addOrUpdateObject($values = [], $options = []) {
      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }


   public function getOptions($primary_type = '') {
      return Search::getOptions(get_parent_class($this));
   }

//    /**
//     * @see plugins/datainjection/inc/PluginDatainjectionInjectionInterface::getOptions()
//    **/
//   function getOptions($primary_type='') {

//     $tab[110]['table']        = 'glpi_plugin_tender_catalogueitems';
//     $tab[110]['field']        = 'name';
//     $tab[110]['linkfield']    = 'name';
//     $tab[110]['name']         = __('Name');
//     $tab[110]['injectable']   = true;
//     $tab[110]['displaytype']  = 'dropdown';
//     $tab[110]['checktype']    = 'text';
//     $tab[110]['storevaluein'] = 'name';

//     $tab[111]['table']        = 'glpi_plugin_tender_catalogueitems';
//     $tab[111]['field']        = 'HostTemplate';
//     $tab[111]['linkfield']    = 'name';
//     $tab[111]['name']         = __('Name');
//     $tab[111]['injectable']   = true;
//     $tab[111]['displaytype']  = 'dropdown';
//     $tab[111]['checktype']    = 'text';
//     $tab[112]['storevaluein'] = 'plugin_nagios_object_id';

//     $tab[112]['table']        = 'glpi_plugin_nagios_objects';
//     $tab[112]['field']        = 'SupervisionTemplate';
//     $tab[112]['linkfield']    = 'otherserial';
//     $tab[112]['name']         = __('Inventory number');
//     $tab[112]['injectable']   = true;
//     $tab[112]['displaytype']  = 'dropdown';
//     $tab[112]['checktype']    = 'text';
//     $tab[112]['storevaluein'] = 'role_id';

//     return $tab;
//  }


    /**
    * @param $primary_type
    * @param $values
   **/
   function addSpecificNeededFields($primary_type, $values) {

      $fields['plugin_tender_catalogueitems_id'] = $values[$primary_type]['id'];
      $fields['itemtype'] = $primary_type;
      return $fields;
   }

    /**
    * @param $fields_toinject    array
    * @param $options            array
   **/
   function checkPresent($fields_toinject = [], $options = []) {

   return (" AND `suppliers_id` = ".$fields_toinject['GlpiPlugin\Tender\CatalogueItemSupplier']['suppliers_id']." AND `plugin_tender_catalogueitems_id` = ".$fields_toinject['GlpiPlugin\Tender\CatalogueItem']['id']);

}

}
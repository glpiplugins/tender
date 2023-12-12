<?php

namespace GlpiPlugin\Tender;

use Search;
use PluginDatainjectionInjectionInterface;
use PluginDatainjectionCommonInjectionLib;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class TenderInjection extends Tender implements PluginDatainjectionInjectionInterface {

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
      return [];
   }

   public function addOrUpdateObject($values = [], $options = []) {
      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }


   public function getOptions($primary_type = '') {
      return Search::getOptions(get_parent_class($this));
   }


}
<?php

class PluginTenderUpgradeTo1_0_1 {
   /** @var Migration */
   protected $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      global $DB;

      $this->migration = $migration;

      // Add reference field to glpi_plugin_tender_financials
      $table = 'glpi_plugin_tender_financials';
      $migration->addField($table, 'reference', 'varchar(255)');

      return $migration;
   }

}
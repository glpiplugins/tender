<?php

class PluginTenderUpgradeTo1_0_2 {
   /** @var Migration */
   protected $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      global $DB;

      $this->migration = $migration;

      // Add posting_text due_date field to glpi_plugin_tender_invoices
      $table = 'glpi_plugin_tender_invoices';
      $migration->addField($table, 'posting_text', 'varchar(255)');
      $migration->addField($table, 'due_date', 'DATE');

      // Create table glpi_plugin_tender_filetemplates
      $table = 'glpi_plugin_tender_invoices';

      return $migration;
   }

}
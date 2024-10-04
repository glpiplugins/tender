<?php

use GlpiPlugin\Tender\TenderModel;

class PluginTenderUpgradeTo1_0_3 {
   /** @var Migration */
   protected $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      global $DB;

      $this->migration = $migration;

      // Add percentage field to glpi_plugin_tender_distributions
      $table = 'glpi_plugin_tender_distributions';
      $migration->addField($table, 'percentage', 'decimal(20,4)');

      // Add plugin_tender_measures_id field to glpi_plugin_tender_tenderitems
      $table = 'glpi_plugin_tender_tenderitems';
      $migration->addField($table, 'plugin_tender_measures_id', 'int(10)');

      // Rename plugin_tender_status_id field to plugin_tender_statuses_id
      $table = 'glpi_plugin_tender_tenders';
      $migration->changeField($table, 'plugin_tender_status_id', 'plugin_tender_statuses_id', 'int(10)');

      $tenders = TenderModel::where('tender_subject', NULL)->get();
      foreach ($tenders as $tender) {
         $tender->tender_subject = $tender->id;
         $tender->save();
      }

      // Change plugin_tender_tender_subject field to unique field
      $table = 'glpi_plugin_tender_tenders';
      $migration->changeField($table, 'tender_subject', 'tender_subject', 'varchar(255) not null');
      $migration->addKey($table, ['tender_subject'], 'unicity', 'UNIQUE');

      return $migration;
   }

}
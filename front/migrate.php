<?php

define('GLPI_ROOT', '/var/www/glpi/');
include_once GLPI_ROOT . '/inc/includes.php';
require_once GLPI_ROOT . '/vendor/autoload.php';

$migration = new Migration('1.0.3');
$upgrade = new PluginTenderUpgradeTo1_0_4();
$migration = $upgrade->upgrade($migration);
$migration->executeMigration();

class PluginTenderUpgradeTo1_0_4 {
   /** @var Migration */
   protected $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
        global $DB;

        $this->migration = $migration;

      // Rename plugin_tender_tendersuppliers_id field to plugin_tender_offerss_id
      $table = 'glpi_plugin_tender_orders';
      $migration->changeField($table, 'plugin_tender_tendersuppliers_id', 'plugin_tender_offerss_id', 'int(10)');

      // Rename plugin_tender_tendersuppliers_id field to plugin_tender_offerss_id
      $table = 'glpi_plugin_tender_offeritems';
      $migration->changeField($table, 'plugin_tender_tendersuppliers_id', 'plugin_tender_offerss_id', 'int(10)');

      $table = 'glpi_plugin_tender_tendersuppliers';
      $migration->renameTable('glpi_plugin_tender_tendersuppliers', 'glpi_plugin_tender_offers');

      return $migration;
   }

   
}
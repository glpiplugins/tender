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

      // Rename tenders_id field to plugin_tender_tenders_id
      $table = 'glpi_plugin_tender_tendersuppliers';
      $migration->changeField($table, 'tenders_id', 'plugin_tender_tenders_id', 'int(10)');

      // Rename tendersuppliers_id field to plugin_tender_tendersuppliers_id
      $table = 'glpi_plugin_tender_offeritems';
      $migration->changeField($table, 'tendersuppliers_id', 'plugin_tender_tendersuppliers_id', 'int(10)');

      // Rename tenderitems_id field to plugin_tender_tenderitems_id
      $table = 'glpi_plugin_tender_offeritems';
      $migration->changeField($table, 'tenderitems_id', 'plugin_tender_tenderitems_id', 'int(10)');

      // Rename tenders_id field to plugin_tender_tenders_id
      $table = 'glpi_plugin_tender_tenderitems';
      $migration->changeField($table, 'tenders_id', 'plugin_tender_tenders_id', 'int(10)');

      // Rename tenderitems_id field to plugin_tender_tenderitems_id
      // Rename tendersuppliers_id field to plugin_tender_suppliers_id
      $table = 'glpi_plugin_tender_distributions';
      $migration->changeField($table, 'tenderitems_id', 'plugin_tender_tenderitems_id', 'int(10)');
      $migration->changeField($table, 'financials_id', 'plugin_tender_financials_id', 'int(10)');

      // Rename tenders_id field to plugin_tender_tenders_id
      // Rename tendersuppliers_id field to plugin_tender_suppliers_id
      $table = 'glpi_plugin_tender_orders';
      $migration->changeField($table, 'tenders_id', 'plugin_tender_tenders_id', 'int(10)');
      $migration->changeField($table, 'tendersuppliers_id', 'plugin_tender_suppliers_id', 'int(10)');

      // Rename tenders_id field to plugin_tender_tenders_id
      // Rename delivery_reference field to name
      $table = 'glpi_plugin_tender_deliveries';
      $migration->changeField($table, 'tenders_id', 'plugin_tender_tenders_id', 'int(10)');
      $migration->changeField($table, 'delivery_reference', 'name', 'varchar(255)');

      // Rename distributions_id field to plugin_tender_distributions_id
      // Rename deliveries_id field to plugin_tender_deliveries_id
      // Drop fields delivery_reference, delivery_date
      $table = 'glpi_plugin_tender_deliveryitems';
      $migration->changeField($table, 'distributions_id', 'plugin_tender_distributions_id', 'int(10)');
      $migration->changeField($table, 'deliveries_id', 'plugin_tender_deliveries_id', 'int(10)');
      $migration->dropField($table, 'delivery_reference');
      $migration->dropField($table, 'delivery_date');

      // Rename tenders_id field to plugin_tender_tenders_id
      // Rename invoice_reference field to name
      $table = 'glpi_plugin_tender_invoices';
      $migration->changeField($table, 'tenders_id', 'plugin_tender_tenders_id', 'int(10)');
      $migration->changeField($table, 'invoice_reference', 'name', 'int(10)');

      $tenders = TenderModel::where('tender_subject', NULL)->get();
      foreach ($tenders as $tender) {
         $tender->tender_subject = $tender->id;
         $tender->save();
      }

      if (!$DB->tableExists('glpi_plugin_tender_documenttemplates')) {
         $query = "CREATE TABLE `glpi_plugin_tender_documenttemplates` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `itemtype` VARCHAR(255) DEFAULT NULL,
            `template_path` VARCHAR(255) DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;";
         $DB->doQueryOrDie($query, "1.0.3 add table glpi_plugin_tender_documenttemplates");
     }
      if (!$DB->tableExists('glpi_plugin_tender_documenttemplate_parameters')) {
         $query = "CREATE TABLE `glpi_plugin_tender_documenttemplate_parameters` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `plugin_tender_documenttemplates_id` INT UNSIGNED DEFAULT NULL,
            `name` VARCHAR(255) DEFAULT NULL,
            `type` VARCHAR(255) DEFAULT NULL,
            `value` VARCHAR(255) DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;";
         $DB->doQueryOrDie($query, "1.0.3 add table glpi_plugin_tender_documenttemplates");
      }
      if (!$DB->tableExists('glpi_plugin_tender_measures')) {
         $query = "CREATE TABLE `glpi_plugin_tender_measures` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;";
         $DB->doQueryOrDie($query, "1.0.3 add table glpi_plugin_tender_documenttemplates");
      }
      if (!$DB->tableExists('glpi_plugin_tender_measureitems')) {
         $query = "CREATE TABLE `glpi_plugin_tender_measureitems` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `plugin_tender_measures_id` INT UNSIGNED DEFAULT NULL,
            `plugin_tender_costcenters_id` INT UNSIGNED DEFAULT NULL,
            `value` decimal(20,4) NOT NULL DEFAULT '0.0000',
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;";
         $DB->doQueryOrDie($query, "1.0.3 add table glpi_plugin_tender_documenttemplates");
      }
      return $migration;
   }

}
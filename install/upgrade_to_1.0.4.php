<?php

class PluginTenderUpgradeTo1_0_4 {
   /** @var Migration */
   protected $migration;

   /**
    * @param Migration $migration
    */
   public function upgrade(Migration $migration) {
      global $DB;

      $this->migration = $migration;

      // Change field type to int and make field comaptible with Money::class
      $table = 'glpi_plugin_tender_tenderitems';
      $migration->changeField($table, 'net_price', 'net_price_decimal', 'decimal(20,4)');
      $migration->migrationOneTable($table);
      $migration->addField($table, 'net_price', 'int signed');
      $migration->migrationOneTable($table);
      $query = "UPDATE `glpi_plugin_tender_tenderitems`
                     SET `net_price` = ROUND(net_price_decimal * 100, 0)";
      $DB->doQueryOrDie($query, "1.0.4 migrate net_price to int");

      $migration->dropField($table, 'net_price_decimal');

      // Change field type to int and make field comaptible with Money::class
      $table = 'glpi_plugin_tender_catalogueitemsuppliers';
      $migration->changeField($table, 'net_price', 'net_price_decimal', 'decimal(20,4)');
      $migration->migrationOneTable($table);
      $migration->addField($table, 'net_price', 'int signed');
      $migration->migrationOneTable($table);
      $query = "UPDATE `glpi_plugin_tender_catalogueitemsuppliers`
                     SET `net_price` = ROUND(net_price_decimal * 100, 0)";
      $DB->doQueryOrDie($query, "1.0.4 migrate net_price to int");
   
      $migration->dropField($table, 'net_price_decimal');

      // Change field type to int and make field comaptible with Money::class
      $table = 'glpi_plugin_tender_offeritems';
      $migration->changeField($table, 'net_price', 'net_price_decimal', 'decimal(20,4)');
      $migration->migrationOneTable($table);
      $migration->addField($table, 'net_price', 'int signed');
      $migration->migrationOneTable($table);
      $query = "UPDATE `glpi_plugin_tender_offeritems`
                     SET `net_price` = ROUND(net_price_decimal * 100, 0)";
      $DB->doQueryOrDie($query, "1.0.4 migrate net_price to int");

      $migration->dropField($table, 'net_price_decimal');

      // Change field type to int and make field comaptible with Money::class
      $table = 'glpi_plugin_tender_distributions';
      $migration->changeField($table, 'percentage', 'percentage_decimal', 'decimal(20,4)');
      $migration->migrationOneTable($table);
      $migration->addField($table, 'percentage', 'int signed');
      $migration->migrationOneTable($table);
      $query = "UPDATE `glpi_plugin_tender_tenderitems`
                     SET `percentage` = ROUND(percentage_decimal * 100, 0)";
      $DB->doQueryOrDie($query, "1.0.4 migrate percentage to int");

      $migration->dropField($table, 'percentage_decimal');

      // Change field type to int and make field comaptible with Money::class
      $table = 'glpi_plugin_tender_financialitems';
      $migration->changeField($table, 'value', 'value_decimal', 'decimal(20,4)');
      $migration->migrationOneTable($table);
      $migration->addField($table, 'value', 'int signed');
      $migration->migrationOneTable($table);
      $query = "UPDATE `glpi_plugin_tender_financialitems`
                     SET `value` = ROUND(value_decimal * 100, 0)";
      $DB->doQueryOrDie($query, "1.0.4 migrate value to int");

      $migration->dropField($table, 'value_decimal');

      // Rename plugin_tender_tendersuppliers_id field to plugin_tender_offerss_id
      $table = 'glpi_plugin_tender_orders';
      $migration->changeField($table, 'plugin_tender_tendersuppliers_id', 'plugin_tender_offers_id', 'int(10) unsigned');

      // Rename plugin_tender_tendersuppliers_id field to plugin_tender_offerss_id
      $table = 'glpi_plugin_tender_offeritems';
      $migration->changeField($table, 'plugin_tender_tendersuppliers_id', 'plugin_tender_offers_id', 'int(10) unsigned');

      $table = 'glpi_plugin_tender_tendersuppliers';
      $migration->renameTable('glpi_plugin_tender_tendersuppliers', 'glpi_plugin_tender_offers');

      return $migration;
   }

}
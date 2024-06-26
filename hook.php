<?php

/**
 * -------------------------------------------------------------------------
 * tender plugin for GLPI
 * Copyright (C) 2023 by the tender Development Team.
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * --------------------------------------------------------------------------
 */

use GlpiPlugin\Tender\TenderSupplier;
use GlpiPlugin\Tender\Tender;

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_tender_install()
{

    global $DB;
    $migration = new Migration(PLUGIN_TENDER_VERSION);
    if (!$DB->tableExists("glpi_plugin_tender_tenders")) {
        $DB->runFile(Plugin::getPhpDir('tender')."/install/sql/empty-1.0.2.sql");
    } else {
        require_once(__DIR__ . '/install/upgrade_to_1.0.2.php');
        $upgrade = new PluginTenderUpgradeTo1_0_2();
        $migration = $upgrade->upgrade($migration);
    }
    $migration->executeMigration();

    $directories = [
        GLPI_PLUGIN_DOC_DIR . "/tender/docx/",
    ];

    foreach ($directories as $new_directory) {
        if (!is_dir($new_directory)) {
                  @mkdir($new_directory, 0755, true)
                     or die(sprintf(
                         __('%1$s %2$s'),
                         __("Can't create folder", 'tender'),
                         $new_directory
                     ));
        }
    }

    return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_tender_uninstall()
{

    global $DB;

    // $tables = [
    //     "glpi_plugin_tender_tenders",
    //     "glpi_plugin_tender_tendersuppliers",
    //     "glpi_plugin_tender_tenderitems",
    //     "glpi_plugin_tender_distributions",
    //     "glpi_plugin_tender_catalogueitems",
    //     "glpi_plugin_tender_catalogueitemsuppliers",
    //     "glpi_plugin_tender_offeritems",
    //     "glpi_plugin_tender_orders",
    //     "glpi_plugin_tender_deliveries",
    //     "glpi_plugin_tender_deliveryitems",
    //     "glpi_plugin_tender_tendertypes",
    //     "glpi_plugin_tender_tendertypeoptions",
    //     "glpi_plugin_tender_financials",
    // ];

    // // foreach ($tables as $table) {
    // //     $DB->dropTable($table);
    // // }

    // foreach ($tables as $table) {
    //     if ($DB->tableExists($table)) {
    //        $DB->queryOrDie("DROP TABLE IF EXISTS `".$table."`", $DB->error());
    //     }
    //  }

    // $tables_glpi = ["glpi_logs"];

    // foreach ($tables_glpi as $table_glpi) {
    //     $DB->delete($table_glpi, ['itemtype' => 'PluginTender']);
    // }

    return true;
}

function plugin_tender_getDropdown() {
    return [
        'GlpiPlugin\Tender\TenderType' => __("Tender Types", "tender"),
        'GlpiPlugin\Tender\TenderStatus' => __("Tender Status", "tender"),
        'GlpiPlugin\Tender\CatalogueItem' => __("Catalogue Items", "tender"),
        'GlpiPlugin\Tender\Costcenter' => __("Costcenter", "tender"),
        'GlpiPlugin\Tender\Account' => __("Accounts", "tender"),
        'GlpiPlugin\Tender\FileTemplate' => __("File Templates", "tender"),
    ];
 }

/**
 * Load Fields classes in datainjection.
 * Called by Setup.php:44 if Datainjection is installed and active
 */
function plugin_datainjection_populate_tender()
{
    /** @var array $INJECTABLE_TYPES */
    global $INJECTABLE_TYPES;

    $INJECTABLE_TYPES['GlpiPlugin\Tender\TenderInjection'] = 'Tender';
    $INJECTABLE_TYPES['GlpiPlugin\Tender\CatalogueItemInjection'] = 'Tender';
    $INJECTABLE_TYPES['GlpiPlugin\Tender\CatalogueItemSupplierInjection'] = 'Tender';
    $INJECTABLE_TYPES['GlpiPlugin\Tender\FinancialInjection'] = 'Tender';
    $INJECTABLE_TYPES['GlpiPlugin\Tender\FinancialItemInjection'] = 'Tender';
    $INJECTABLE_TYPES['GlpiPlugin\Tender\AccountInjection'] = 'Tender';
    $INJECTABLE_TYPES['GlpiPlugin\Tender\CostcenterInjection'] = 'Tender';
}

// function plugin_tender_MassiveActions($type) {
//     $actions = [
//         TenderSupplier::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete'    => _x('button', 'delete'),
//     ];
//     // switch ($type) {
//     // //    case 'TenderSupplier' :
//     //       $myclass      = 'TenderSupplier';
//     //       $action_key   = 'delete';
//     //       $action_label = __("plugin_tender_delete", 'delete');
//     //       $actions[$myclass.MassiveAction::CLASS_ACTION_SEPARATOR.$action_key]
//     //          = $action_label;
 
//     //       break;
//     // }
//     return $actions;
//  }

function plugin_tender_upgrade_1_0_1(Migration $migration) {
    global $DB;
 
    $migration->setVersion('1.0.1');
 
    if ($DB->tableExists('glpi_plugin_tender_tenders')) {
       if ($DB->fieldExists('glpi_plugin_datainjection_profiles', 'ID')) {
          $migration->changeField('glpi_plugin_datainjection_profiles', 'ID', 'id', 'autoincrement');
          $migration->migrationOneTable('glpi_plugin_datainjection_profiles');
       }
 
        PluginDatainjectionProfile::migrateProfiles();
 
       //Drop profile table : no use anymore !
       $migration->dropTable('glpi_plugin_datainjection_profiles');
    }
 
    $migration->executeMigration();
 }

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

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_tender_install()
{

    global $DB;
    $migration = new Migration(100);
    if (!$DB->tableExists("glpi_plugin_tender_tenders")) {
        $DB->runFile(Plugin::getPhpDir('tender')."/install/sql/empty-1.0.0.sql");
    }
    $migration->executeMigration();
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
    //     "glpi_plugin_tender_tenders"
    // ];

    // foreach ($tables as $table) {
    //     $DB->dropTable($table);
    // }

    // $tables_glpi = ["glpi_logs"];

    // foreach ($tables_glpi as $table_glpi) {
    //     $DB->delete($table_glpi, ['itemtype' => 'PluginTender']);
    // }

    return true;
}

function plugin_tender_getDropdown() {
    return [
        'GlpiPlugin\Tender\TenderType' => __("Tender Types", "TenderTypes"),
        'GlpiPlugin\Tender\CatalogueItem' => __("Catalogue Items", "CatalogueItems"),
    ];
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
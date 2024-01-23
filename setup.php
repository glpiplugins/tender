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


use GlpiPlugin\Tender\Tender;
use GlpiPlugin\Tender\TenderItem;
use GlpiPlugin\Tender\Distribution;
use GlpiPlugin\Tender\Financial;
use Glpi\Plugin\Hooks;

define('PLUGIN_TENDER_VERSION', '1.0');

// Minimal GLPI version, inclusive
define("PLUGIN_TENDER_MIN_GLPI_VERSION", "10.0.0");
// Maximum GLPI version, exclusive
define("PLUGIN_TENDER_MAX_GLPI_VERSION", "10.1.99");

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_tender()
{
    global $PLUGIN_HOOKS, $CFG_GLPI, $ITEM_TYPES;

    $PLUGIN_HOOKS['csrf_compliant']['tender'] = true;

    // $PLUGIN_HOOKS['use_massive_action']['tender'] = 1;
    // $PLUGIN_HOOKS['assign_to_ticket']['tender'] = true;
    $PLUGIN_HOOKS["menu_toadd"]['tender'] = ['management' => [Tender::class, Financial::class]];

    // $PLUGIN_HOOKS['itemtype']['tender'] = ['TenderItem'];
    // $PLUGIN_HOOKS['add_tab']['tender'] = ['TenderItem' => 'plugin_tender_add_tab'];

    $TENDER_TYPES = [
        'CartridgeItem',
        'Certificate',
        'Computer',
        'ConsumableItem',
        'Contract',
        'Enclosure',
        'SoftwareLicense',
        'Monitor',
        'NetworkEquipment',
        'Pdu',
        'Peripheral',
        'Phone',
        'Printer',        
        'Rack',
        'GlpiPlugin\\\Tender\\\OtherItem',
     ];

     $CFG_GLPI['plugin_tender_types'] = $TENDER_TYPES;

     $PLUGIN_HOOKS[Hooks::ITEM_ADD]['tender']        = [Distribution::class => [Distribution::class,
     'item_add_distribution']];

    // Add Fields to Datainjection
    if (Plugin::isPluginActive('datainjection')) {
        $PLUGIN_HOOKS['plugin_datainjection_populate']['tender'] = "plugin_datainjection_populate_tender";
    }

}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_tender()
{
    return [
        'name'           => 'Tender',
        'version'        => PLUGIN_TENDER_VERSION,
        'author'         => 'Roy Brannath',
        'license'        => 'MIT',
        'homepage'       => 'https://github.com/glpiplugins/tender',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_TENDER_MIN_GLPI_VERSION,
                'max' => PLUGIN_TENDER_MAX_GLPI_VERSION,
            ]
        ]
    ];
}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_tender_check_prerequisites()
{
    return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_tender_check_config($verbose = false)
{
    if (true) { // Your configuration check
        return true;
    }

    if ($verbose) {
        echo __('Installed / not configured', 'tender');
    }
    return false;
}

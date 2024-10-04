<?php
global $CFG_GLPI;

define('GLPI_ROOT', dirname(dirname(dirname(__DIR__))));
define("GLPI_CONFIG_DIR", GLPI_ROOT . "/tests");

include GLPI_ROOT . "/inc/includes.php";
include_once GLPI_ROOT . '/tests/GLPITestCase.php';
include_once GLPI_ROOT . '/tests/DbTestCase.php';

$plugin = new \Plugin();
$plugin->checkStates(true);
$plugin->getFromDBbyDir('tender');

// if (!plugin_myplugin_check_prerequisites()) {
//     echo "\nPrerequisites are not met!";
//     die(1);
// }

if (!$plugin->isInstalled('tender')) {
    $plugin->install($plugin->getID());
}
if (!$plugin->isActivated('tender')) {
    $plugin->activate($plugin->getID());
}
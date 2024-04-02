<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
 }
 
 use Glpi\Dashboard\Dashboard;
 use Glpi\Dashboard\Item as Dashboard_Item;
 use Glpi\Dashboard\Right as Dashboard_Right;
 use Glpi\System\Diagnostic\DatabaseSchemaIntegrityChecker;
 use Ramsey\Uuid\Uuid;
 
 class PluginTenderInstall {
    protected $migration;

    private $upgradeSteps = [
        '1.0.0'    => '1.0.1'
     ];

     public function upgrade(Migration $migration, $args = []): bool {

        
     }

 }
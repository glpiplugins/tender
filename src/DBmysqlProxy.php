<?php

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

class DBmysqlProxy {

    private $dbInstance;

    public $capsule;

    public function __construct($dbInstance) {
        $this->dbInstance = $dbInstance;
        $this->setup();
    }

    private function setup() {

        $capsule = new Capsule;
        // print_r($DB);
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => 'ddev-glpi-db',
            'database' => 'db',
            'username' => 'db', //$this->dbInstance->dbuser,
            'password' =>'db', // $this->dbInstance->dbpassword,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    
        $this->capsule =  $capsule;
        
    }

    public function getCapsule() {
        return Capsule;
    }

}
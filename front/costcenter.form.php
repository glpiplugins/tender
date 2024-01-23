<?php

use GlpiPlugin\Tender\Costcenter;

include ("../../../inc/includes.php");

// Plugin::load('tender', true);

$dropdown = new Costcenter();
include (GLPI_ROOT . "/front/dropdown.common.form.php");
<?php

use GlpiPlugin\Tender\Measure;

include ("../../../inc/includes.php");

// Plugin::load('tender', true);

$dropdown = new Measure();
include (GLPI_ROOT . "/front/dropdown.common.form.php");
<?php

use GlpiPlugin\Tender\TenderStatus;

include ("../../../inc/includes.php");

// Plugin::load('tender', true);

$dropdown = new TenderStatus();
include (GLPI_ROOT . "/front/dropdown.common.form.php");
<?php

use GlpiPlugin\Tender\TenderType;

include ("../../../inc/includes.php");

Plugin::load('tender', true);

$dropdown = new TenderType();
include (GLPI_ROOT . "/front/dropdown.common.php");
<?php

use GlpiPlugin\Tender\CatalogueItem;

include ("../../../inc/includes.php");

Plugin::load('tender', true);

$dropdown = new CatalogueItem();
include (GLPI_ROOT . "/front/dropdown.common.php");
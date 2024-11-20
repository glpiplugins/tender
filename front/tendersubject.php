<?php

use GlpiPlugin\Tender\TenderSubject;

include ("../../../inc/includes.php");

Plugin::load('tender', true);

$dropdown = new TenderSubject();
include (GLPI_ROOT . "/front/dropdown.common.php");
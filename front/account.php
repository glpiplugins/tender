<?php

use GlpiPlugin\Tender\Account;

include ("../../../inc/includes.php");

Plugin::load('tender', true);

$dropdown = new Account();
include (GLPI_ROOT . "/front/dropdown.common.php");
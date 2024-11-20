<?php

use GlpiPlugin\Tender\DocumentTemplate;

include ("../../../inc/includes.php");

Plugin::load('tender', true);

$dropdown = new DocumentTemplate();
Html::header(DocumentTemplate::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "setup", "GlpiPlugin\\Tender\\DocumentTemplate", "tender");

include (GLPI_ROOT . "/front/dropdown.common.form.php");
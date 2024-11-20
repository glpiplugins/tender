<?php

use GlpiPlugin\Tender\DocumentTemplate;

include ("../../../inc/includes.php");

Plugin::load('tender', true);

$dropdown = new DocumentTemplate();
include (GLPI_ROOT . "/front/dropdown.common.php");
if (!($dropdown instanceof CommonDropdown)) {
    Html::displayErrorAndDie('');
}
if (!$dropdown->canView()) {
   // Gestion timeout session
    Session::redirectIfNotLoggedIn();
    Html::displayRightError();
}

$dropdown::displayCentralHeader(null, ['config', 'commondropdown', 'GlpiPlugin\Tender\DocumentTemplate']);


Search::show(get_class($dropdown));

Html::footer();
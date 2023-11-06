<?php

include ('../../../inc/includes.php');

$plugin = new Plugin();

Session::checkRight("networking", READ);

Html::header(PluginTenderTender::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "management", "plugintendertender", "tender");

Search::show('PluginTenderTender');

Html::footer();
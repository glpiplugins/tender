<?php

namespace GlpiPlugin\Tender;

use Plugin;
use Session;
use Html;
use Search;

include ('../../../inc/includes.php');

Session::checkRight("networking", READ); // Ändern Sie dies entsprechend den Rechten Ihres Plugins

Html::header(Financial::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "management", "GlpiPlugin\Tender\Financial", "tender");

Search::show("GlpiPlugin\Tender\Financial");

Html::footer();
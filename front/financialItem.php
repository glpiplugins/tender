<?php

namespace GlpiPlugin\Tender;

use Plugin;
use Session;
use Html;
use Search;

include ('../../../inc/includes.php');

Session::checkRight("networking", READ); // Ändern Sie dies entsprechend den Rechten Ihres Plugins

Html::header(FinancialItem::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "management", "GlpiPlugin\Tender\FinancialItem", "tender");

Search::show("GlpiPlugin\Tender\FinancialItem");

Html::footer();
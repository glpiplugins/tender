<?php

namespace GlpiPlugin\Tender;

use Plugin;
use Session;
use Html;
use Search;

include ('../../../inc/includes.php');

Session::checkRight("networking", READ); // Ändern Sie dies entsprechend den Rechten Ihres Plugins

Html::header(CatalogueItemSupplier::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "dropdown", "GlpiPlugin\Tender\CatalogueItemSupplier", "tender");

Search::show("GlpiPlugin\Tender\CatalogueItemSupplier");

Html::footer();
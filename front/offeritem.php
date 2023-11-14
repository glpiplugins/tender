<?php

namespace GlpiPlugin\Tender;

use Plugin;
use Session;
use Html;
use Search;

include ('../../../inc/includes.php');

Session::checkRight("networking", READ); // Ändern Sie dies entsprechend den Rechten Ihres Plugins

Html::header(OfferItem::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "management", "GlpiPlugin\Tender\OfferItem", "tender");

Search::show("GlpiPlugin\Tender\OfferItem");

Html::footer();
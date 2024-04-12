<?php

// Entry menu case
include ("../../../inc/includes.php");

Session::checkRight("config", UPDATE);

// To be available when plugin in not activated
Plugin::load('tender');

Html::header("TITRE", $_SERVER['PHP_SELF'], "config", "plugins");
echo __("This is the plugin config page", 'tender');
Html::footer();
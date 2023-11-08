<?php

use GlpiPlugin\Tender\TenderSupplier;

include ("../../../inc/includes.php");

if (!isset ($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset ($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isInstalled('tender') || !$plugin->isActivated('tender')) {
   Html::displayNotFoundError();
}

$object = new TenderSupplier();

if (isset($_POST['add'])) {
   if($_POST['suppliers_id'] == 0) {
      Html::back();
   }
   //Check CREATE ACL
   //$object->check(-1, CREATE, $_POST);
   //Do object creation
   $newid = $object->add($_POST);
   //Redirect to newly created object form
   Html::back();
} else if (isset($_POST['update'])) {
   //Check UPDATE ACL
   //$object->check($_POST['id'], UPDATE);
   //Do object update
   $object->update($_POST);
   //Redirect to object form
   Html::back();
} else if (isset($_POST['delete'])) {
   //Check DELETE ACL
   //$object->check($_POST['id'], DELETE);
   //Put object in dustbin
   $object->delete($_POST);
   //Redirect to objects list
   $object->redirectToList();
} else if (isset($_POST['purge'])) {
   //Check PURGE ACL
   //$object->check($_POST['id'], PURGE);
   //Do object purge
   $object->delete($_POST, 1);
   //Redirect to objects list
   Html::redirect("{$CFG_GLPI['root_doc']}/plugins/tender/front/tendersupplier.php");
} else {
    Html::header(TenderSupplier::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "management", "GlpiPlugin\Tender\TenderSupplier");
    //per default, display object
    $object->display(
            $_GET
    );

    Html::footer();
}
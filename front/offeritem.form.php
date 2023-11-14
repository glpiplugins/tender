<?php

use GlpiPlugin\Tender\TenderItem;
use GlpiPlugin\Tender\TenderSupplier;
use GlpiPlugin\Tender\OfferItem;

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

$object = new OfferItem();

if (isset($_POST['add'])) {
   $tendersupplier = TenderSupplier::getSupplier($_POST['tenders_id'], $_POST['suppliers_id']);
   $tendersupplierObj = new TenderSupplier();
   $tendersup = $_POST;
   $tendersup['id'] = $tendersupplier['id'];
   $tendersupplierObj->update($tendersup);

   $tenderitems = TenderItem::getTenderitems($_POST['tenders_id']);

   foreach ($tenderitems as $item) {
      $object = new OfferItem();
      $_POST['tendersuppliers_id'] = $tendersupplier['id'];
      $_POST['tenderitems_id'] = $item['id'];
      $_POST['net_price'] = $item['net_price'];
      $_POST['tax'] = $item['tax'];
      $newid = $object->add($_POST);
   }
   //Check CREATE ACL
   //$object->check(-1, CREATE, $_POST);
   //Do object creation

   //Redirect to newly created object form
   Html::back();
} else if (isset($_POST['update_items'])) {

   foreach ($_POST['item'] as $item) {
         $object->update($item);
   }


   Html::back();
} else if (isset($_POST['update'])) {
   $tendersupplierObj = new TenderSupplier();
   $tendersup = $_POST;
   $tendersupplierObj->update($tendersup);
   //Check UPDATE ACL
   //$object->check($_POST['id'], UPDATE);
   //Do object update
   //$object->update($_POST);
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
   Html::redirect("{$CFG_GLPI['root_doc']}/plugins/tender/front/offeritem.php");
} else {
    Html::header(OfferItem::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "dropdown", "GlpiPlugin\Tender\OfferItem");
    //per default, display object
    $object->display(
            $_GET
    );

    Html::footer();
}
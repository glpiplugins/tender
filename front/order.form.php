<?php

use GlpiPlugin\Tender\Order;
use GlpiPlugin\Tender\TenderSupplier;
use GlpiPlugin\Tender\TenderItem;
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

$object = new Order();

if (isset($_POST['add'])) {

   //Check CREATE ACL
   $offeritems = OfferItem::getOfferItems($_POST['tendersuppliers_id']);

   foreach ($offeritems as $item) {
      $tenderitem = TenderItem::getByID($item['tenderitems_id']);
      $tenderitem->update(['id' => $item['tenderitems_id'], 'net_price' => $item['net_price'], 'tax' => $item['tax'],]);
   }
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
   Html::back();
} else if (isset($_POST['purge'])) {
   //Check PURGE ACL
   //$object->check($_POST['id'], PURGE);
   //Do object purge
   $object->delete($_POST, 1);
   //Redirect to objects list
   Html::redirect("{$CFG_GLPI['root_doc']}/plugins/tender/front/order.php");
} else {
    Html::header(Order::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "management", "GlpiPlugin\Tender\Order");
    //per default, display object
    $object->display(
            $_GET
    );

    Html::footer();
}

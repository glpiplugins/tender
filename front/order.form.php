<?php

use GlpiPlugin\Tender\Order;
use GlpiPlugin\Tender\Offer;
use GlpiPlugin\Tender\TenderItemModel;
use GlpiPlugin\Tender\OfferItemModel;

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
   $offeritems = OfferItemModel::where('plugin_tender_offers_id', $_POST['plugin_tender_offers_id'])->get();

   foreach ($offeritems as $item) {
      $tenderItem = TenderItemModel::find($item->plugin_tender_tenderitems_id);
      $tenderItem->update(['id' => $item->plugin_tender_tenderitems_id, 'net_price' => $item->net_price / 100, 'tax' => $item->tax]);
   }

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

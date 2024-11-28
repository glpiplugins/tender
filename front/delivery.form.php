<?php

use GlpiPlugin\Tender\Delivery;
use GlpiPlugin\Tender\DeliveryItem;
use GlpiPlugin\Tender\TenderItemModel;

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

$object = new Delivery();

if (isset($_POST['add'])) {

   $newid = $object->add($_POST);
   //Check CREATE ACL
   foreach ($_POST['distribution'] as $distribution) {
      $deliveryitem = new DeliveryItem();
      $distribution['plugin_tender_deliveries_id'] = $newid;
      if (intval($distribution['quantity']) > 0) {
         $deliveryitem->add($distribution);
      }
   }
   foreach ($_POST['item'] as $item) {
      $tenderItem = TenderItemModel::find($item['id']);
      foreach ($tenderItem->distributions as $distribution) {
         $deliveryitem = new DeliveryItem();
         $item['plugin_tender_distributions_id'] = $distribution->id;
         $item['plugin_tender_deliveries_id'] = $newid;
         if (intval($item['quantity']) > 0) {
            $deliveryitem->add($item);
         }
      }
   }
   //$object->check(-1, CREATE, $_POST);
   //Do object creation

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
   Html::redirect("{$CFG_GLPI['root_doc']}/plugins/tender/front/delivery.php");
} else {
    Html::header(Delivery::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "management", "GlpiPlugin\Tender\Tender");
    //per default, display object
    $object->display(
            $_GET
    );

    Html::footer();
}

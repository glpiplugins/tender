<?php

use GlpiPlugin\Tender\Delivery;
use GlpiPlugin\Tender\DeliveryItem;

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

   foreach ($_POST['item'] as $item) {
      $deliveryitem = new DeliveryItem();
      $item['deliveries_id'] = $newid;
      if (intval($item['quantity']) > 0) {
         $deliveryitem->add($item);
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
    Html::header(Delivery::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "management", "GlpiPlugin\Tender\Delivery");
    //per default, display object
    $object->display(
            $_GET
    );

    Html::footer();
}

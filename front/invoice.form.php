<?php

use GlpiPlugin\Tender\Invoice;
use GlpiPlugin\Tender\InvoiceItem;

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

$object = new Invoice();

if (isset($_POST['add'])) {

   $newid = $object->add($_POST);
   //Check CREATE ACL

   foreach ($_POST['item'] as $item) {
      $invoiceitem = new InvoiceItem();
      $item['plugin_tender_invoices_id'] = $newid;
      $invoiceitem->add($item);
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
   Html::redirect("{$CFG_GLPI['root_doc']}/plugins/tender/front/invoice.php");
} else {
    Html::header(Invoice::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "management", "GlpiPlugin\Tender\Invoice");
    //per default, display object
    $object->display(
            $_GET
    );

    Html::footer();
}

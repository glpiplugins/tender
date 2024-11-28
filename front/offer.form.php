<?php

use GlpiPlugin\Tender\Offer;
use GlpiPlugin\Tender\OfferModel;
use GlpiPlugin\Tender\OfferItemModel;
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

$object = new Offer();

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

   $offer = OfferModel::where('suppliers_id', $_POST['suppliers_id'])
      ->where('plugin_tender_tenders_id', $_POST['plugin_tender_tenders_id'])->first();
   $offer->offer_date = $_POST['offer_date'];
   $offer->save();
   $tenderitems = TenderItemModel::where('plugin_tender_tenders_id', $_POST['plugin_tender_tenders_id'])->get();

   foreach ($tenderitems as $item) {
      OfferItemModel::create(
         [
            'plugin_tender_offers_id' => $offer->id,
            'plugin_tender_tenderitems_id' => $item->id,
            'net_price' => $item->net_price,
            'tax' => $item->tax
         ]
      );
   }
   //Do object update
   // $object->update($_POST);
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
   Html::redirect("{$CFG_GLPI['root_doc']}/plugins/tender/front/offer.php");
} else {
    Html::header(Offer::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "management", "GlpiPlugin\Tender\Offer");
    //per default, display object
    $object->display(
            $_GET
    );

    Html::footer();
}
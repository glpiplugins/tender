<?php

use GlpiPlugin\Tender\Distribution;
use GlpiPlugin\Tender\DistributionModel;
use GlpiPlugin\Tender\TenderItem;
use GlpiPlugin\Tender\Tender;

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

$object = new Distribution();

if (isset($_POST['add'])) {
   //Check CREATE ACL
   //$object->check(-1, CREATE, $_POST);
   //Do object creation
   foreach ($_POST['financials'] as $financial) {
      $_POST['plugin_tender_financials_id'] = $financial;
      DistributionModel::create($_POST);
   }

   // $tenderItemObj = new TenderItem();
   // $tenderItem = $tenderItemObj->getByID($_POST['plugin_tender_tenderitems_id'])->fields;
   // if($tenderItem['plugin_tender_measures_id'] == 0) {
   //    $tenderItem['quantity'] = $tenderItem['quantity'] + $_POST['quantity'];
   // }
   // $tenderItemObj->update($tenderItem);
   // $tenderItemObj->input = $_POST;
   // Distribution::addDistribution($tenderItemObj);

   
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
   $tenderItemObj = new TenderItem();
   $tenderItem = $tenderItemObj->getByID($_POST['plugin_tender_tenderitems_id'])->fields;
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
   Html::redirect("{$CFG_GLPI['root_doc']}/plugins/tender/front/distribution.php");
} else {
    Html::header(Distribution::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "management", "GlpiPlugin\Tender\Distribution");
    //per default, display object
    $object->display(
            $_GET
    );

    Html::footer();
}
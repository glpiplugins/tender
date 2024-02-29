<?php

include_once ("../../../inc/includes.php");

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Tender\CatalogueItem;

Session::checkCentralAccess();

$action = $_POST['action'] ?? $_GET["action"];

switch ($action) {

    case 'get_catalogueitems_for_itemtype':
        TemplateRenderer::getInstance()->display('@tender/tenderitem/catalogueitemDropdown.html.twig', [
            'itemtype' => $_POST['itemtype'],
            'catalogueitems' => CatalogueItem::getCatalogueItemsBySupplier($_POST['suppliers'], [$_POST['itemtype']]),
            'item' => $_POST['item'],
            'financials' => $_POST['financials'],
        ]);
        break;
    case 'get_catalogueitem_add_form':
        TemplateRenderer::getInstance()->display('@tender/tenderitem/catalogueitemAdd.html.twig', [
            'itemtypes' => $_POST['itemtypes'],
            'catalogueitems' => $_POST['catalogueitems'],
            'item' => $_POST['item'],
            'financials' => $_POST['financials']
        ]);
        break;
    case 'get_diverseitem_add_form':
        TemplateRenderer::getInstance()->display('@tender/tenderitem/diverseitemAdd.html.twig', [
            'item' => $_POST['item'],
            'financials' => $_POST['financials']
        ]);
        break;        
}
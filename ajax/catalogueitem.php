<?php

include_once ("../../../inc/includes.php");

use Glpi\Application\View\TemplateRenderer;

Session::checkCentralAccess();

$action = $_POST['action'] ?? $_GET["action"];

switch ($action) {

    case 'get_types_for_itemtype':
        TemplateRenderer::getInstance()->display('@tender/components/dropdownField.html.twig', [
            'itemtype' => $_GET['itemtype'],
            'dom_name' => $_GET['dom_name'],
            'name' => 'Type'
        ]);
    break;
    case 'get_models_for_itemtype':
        TemplateRenderer::getInstance()->display('@tender/components/dropdownField.html.twig', [
            'itemtype' => $_GET['itemtype'],
            'dom_name' => $_GET['dom_name'],
            'name' => 'Model'
        ]);
    break;
}
<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use MassiveAction;
use Glpi\Application\View\TemplateRenderer;

class TenderSupplier extends CommonDBTM   {

    static $rightname = 'networking';

    static function getIcon() {
        return "fas fa-contact";
     }

    static function getTypeName($nb = 0) {
        return __('Tender Supplier', 'tender');
    }

    public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
        return __("Supplier", "tender");
      }
      return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
            self::showList($item);
        }
    }

    static function showList($item) {

        global $DB;

        $iterator = $DB->request([
            'SELECT' => [
                'glpi_suppliers.*',
                'glpi_plugin_tender_tendersuppliers.id',
            ],
            'FROM' => 'glpi_plugin_tender_tendersuppliers',
            'LEFT JOIN' => [
                'glpi_suppliers' => [
                    'FKEY' => [
                        'glpi_plugin_tender_tendersuppliers' => 'suppliers_id',
                        'glpi_suppliers' => 'id'
                    ]
                ]
            ],
            'WHERE' => [
                'tenders_id' => $item->getID()
                ]
        ]);

        $suppliers = [];
        foreach ($iterator as $supplier) {
            $supplier['name'] = '<a href="/front/supplier.form.php?id=' . $supplier['id'] . '">' . $supplier['name'] . '</a>';
            $supplier['itemtype'] = "GlpiPlugin\Tender\TenderSupplier";
            $suppliers[] = $supplier;
        }
            
        TemplateRenderer::getInstance()->display('@tender/tendersuppliers.html.twig', [
            'item'   => $item,
            'is_tab' => true,
            'filters' => [],
            'nofilter' => true,
            'columns' => [
                'id' => __('ID'),
                'name' => __('name'),
                'address' => __('address'),
                'phone' => __('phone'),
                'website' => __('website'),
            ],
            'formatters' => [
                'name' => 'raw_html'
            ],
            'total_number' => count($suppliers),
            'entries' => $suppliers,
            'used' => array_column($suppliers, 'id'),
            'showmassiveactions'    => true,
            'massiveactionparams' => [
                'num_displayed'    => min($_SESSION['glpilist_limit'], count($suppliers)),
                'container'        => 'massGlpiPluginTenderTenderSupplier' . mt_rand(),
                'specific_actions' => [
                    // 'delete' => __('Delete permanently'),
                    TenderSupplier::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect'),
                ]
            ],
        ]);

        return true;
     }

    static function showMassiveActionsSubForm(MassiveAction $ma) {

        switch ($ma->getAction()) {
           case 'delete':
              echo Html::submit(__('Post'), array('name' => 'massiveaction'))."</span>";
  
              return true;
        }
        return parent::showMassiveActionsSubForm($ma);
    }
  
    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                        array $ids) {
        global $DB;

        switch ($ma->getAction()) {
            case 'delete' :
                $input = $ma->getInput();

                foreach ($ids as $id) {

                    if ($item->getFromDB($id)
                        && $item->deleteFromDB()) {
                    $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                    } else {
                    $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                    $ma->addMessage(__("Something went wrong"));
                    }
                }
                return;

        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }


    static function getSuppliers($tenders_id) {

        global $DB;
  
        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_tender_tendersuppliers',
            'WHERE' => [
                'tenders_id' => $tenders_id
                ]
        ]);
  
        $suppliers = [];
        foreach ($iterator as $supplier) {
            $suppliers[] = $supplier['suppliers_id'];
        }
        
        return $suppliers;
  
    }

    static function getSupplier($tenders_id, $suppliers_id) {

        global $DB;
  
        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_tender_tendersuppliers',
            'WHERE' => [
                'tenders_id' => $tenders_id,
                'suppliers_id' => $suppliers_id
                ]
        ]);
  
        return $iterator->current();
  
    }

}
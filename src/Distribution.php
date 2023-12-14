<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use MassiveAction;
use Infocom;
use Session;
use Glpi\Application\View\TemplateRenderer;

class Distribution extends CommonDBTM  {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
      
        return __('Distribution', 'Distribution');
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
                    try {
                        $object = new Distribution();
                        $object->getFromDB($id);

                        $tenderitem = $DB->request([
                            'FROM' => 'glpi_plugin_tender_tenderitems',
                            'WHERE' => [
                                'id' => $object->fields['tenderitems_id']
                                ]
                        ]);
                        $tenderitem = $tenderitem->current();

                        $tenderItemObj = new TenderItem();
                        $newQuantity = $tenderitem['quantity'] - $object->fields['quantity'];
                        if ($newQuantity > 0) {
                            $tenderItemObj->update(['id' => $tenderitem['id'], 'quantity' => ($tenderitem['quantity'] - $object->fields['quantity'])]);
                            self::removeDistribution($id);
                            $object->delete(['id' => $id]);
                        } else {
                            self::removeAllDistributions($tenderitem['id']);
                            $tenderItemObj->delete(['id' => $tenderitem['id']]);
                        }
                                               
                        
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                    } catch (Exception $e) {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        $ma->addMessage(__("Something went wrong"));
                    }
                }
                return;

        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    static function getDistributions($tenderitems_id) {
        global $DB;

        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_tender_distributions',
            'WHERE' => [
                'tenderitems_id' => $tenderitems_id
                ]
        ]);

        $distributions = [];
        foreach ($iterator as $item) {
            $distributions[] = $item;
        }

        return $distributions;

    }

    public function item_add_distribution(Distribution $item) {
        print_r($item);
        die();
    }

    static function addDistribution(int $tenderitems_id, int $quantity, int $budgets_id, int $locations_id, int $delivery_locations_id) {

        global $DB;

        $distribution = new self();
        $distribution->add([
           'tenderitems_id' => $tenderitems_id,
           'quantity' => $quantity,
           'locations_id' => $locations_id,
           'delivery_locations_id' => $delivery_locations_id,
           'budgets_id' => $budgets_id
           ]);

        
        $tenderitem = TenderItem::getByID($tenderitems_id);

        $iterator = $DB->request([
            'FROM' => 'glpi_infocoms',
            'WHERE' => [
                'items_id' => $tenderitem->fields['tenders_id'],
                'itemtype' => 'GlpiPlugin\Tender\Tender',
                'budgets_id' => $budgets_id
                ]
        ]);

        $infocom = $iterator->current();

        $value = $quantity * $tenderitem->fields['net_price'] * ($tenderitem->fields['tax'] = 0 ? 1 : ($tenderitem->fields['tax'] / 100) + 1 );

        $infocomObj = new Infocom();

        if(!$infocom) {
            print_r("yes");
            print_r($budgets_id);
            print_r("yes");

            $newid = $infocomObj->add([
                'items_id' => $tenderitem->fields['tenders_id'],
                'itemtype' => 'GlpiPlugin\Tender\Tender',
                'budgets_id' => $budgets_id,
                'value' => $value
            ]);
            print_r($newid);
            die();
        } else {
            $infocomObj->update([
                'id' => $infocom['id'],
                'value' => $infocom['value'] + $value,
            ]);
        }

    }

    static function removeAllDistributions(int $tenderitems_id) {

        global $DB;

        $distributions = $DB->request([
            'FROM' => 'glpi_plugin_tender_distributions',
            'WHERE' => [
                'tenderitems_id' => $tenderitems_id
                ]
        ]);

        $tenderitem = $DB->request([
            'FROM' => 'glpi_plugin_tender_tenderitems',
            'WHERE' => [
                'id' => $tenderitems_id
                ]
        ]);
        $tenderitem = $tenderitem->current();

        $value = 0;
        foreach($distributions as $distribution) {
            $distributionObj = new Distribution();
            $value += $distribution['quantity'] * $tenderitem['net_price'] * ($tenderitem['tax'] = 0 ? 1 : ($tenderitem['tax'] / 100) + 1 );
            $distributionObj->delete(['id' => $distribution['id']]);

            $iterator = $DB->request([
                'FROM' => 'glpi_infocoms',
                'WHERE' => [
                    'items_id' => $tenderitem['tenders_id'],
                    'itemtype' => 'GlpiPlugin\Tender\Tender',
                    'budgets_id' => $distribution['budgets_id']
                    ]
            ]);
    
            $infocom = $iterator->current();

            $newValue = $infocom['value'] - $value;
            $infocomObj = new Infocom();
            if($newValue > 0) {
                $infocomObj->update([
                    'id' => $infocom['id'],
                    'value' => $newValue,
                ]);
            } else {
                $infocomObj->delete(['id' => $infocom['id']]);
            }

        }
    }

    static function removeDistribution(int $distributions_id) {

        global $DB;

        $distributions = $DB->request([
            'SELECT' => [
                'glpi_plugin_tender_distributions.id',
                'glpi_plugin_tender_distributions.quantity',
                'glpi_plugin_tender_distributions.budgets_id',
                'glpi_plugin_tender_tenderitems.tenders_id',
                'glpi_plugin_tender_tenderitems.net_price',
                'glpi_plugin_tender_tenderitems.tax',
            ],
            'FROM' => 'glpi_plugin_tender_distributions',
            'LEFT JOIN' => [
                'glpi_plugin_tender_tenderitems' => [
                    'FKEY' => [
                        'glpi_plugin_tender_distributions' => 'tenderitems_id',
                        'glpi_plugin_tender_tenderitems' => 'id'
                    ]
                ],
            ],
            'WHERE' => [
                'glpi_plugin_tender_distributions.id' => $distributions_id
                ]
        ]);
        
        $value = 0;
        foreach($distributions as $distribution) {
            $distributionObj = new Distribution();
            $value += $distribution['quantity'] * $distribution['net_price'] * ($distribution['tax'] = 0 ? 1 : ($distribution['tax'] / 100) + 1 );
            $distributionObj->delete(['id' => $distribution['id']]);

            $iterator = $DB->request([
                'FROM' => 'glpi_infocoms',
                'WHERE' => [
                    'items_id' => $distribution['tenders_id'],
                    'itemtype' => 'GlpiPlugin\Tender\Tender',
                    'budgets_id' => $distribution['budgets_id']
                    ]
            ]);
    
            $infocom = $iterator->current();
            
            $newValue = $infocom['value'] - $value;
            $infocomObj = new Infocom();
            if($newValue > 0) {
                $infocomObj->update([
                    'id' => $infocom['id'],
                    'value' => $newValue,
                ]);
            } else {
                $infocomObj->delete(['id' => $infocom['id']]);
            }

        }
    }    
}

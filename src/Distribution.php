<?php

namespace GlpiPlugin\Tender;

use CommonGLPI;
use CommonDBTM;
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
                        DistributionModel::find($id)->delete();
                        // $object = new Distribution();
                        // $object->getFromDB($id);

                        // $tenderitem = $DB->request([
                        //     'FROM' => 'glpi_plugin_tender_tenderitems',
                        //     'WHERE' => [
                        //         'id' => $object->fields['plugin_tender_tenderitems_id']
                        //         ]
                        // ]);
                        // $tenderitem = $tenderitem->current();

                        // $tenderItemObj = new TenderItem();
                        // $newQuantity = $tenderitem['quantity'] - $object->fields['quantity'];
                        // if ($newQuantity > 0) {
                        //     $tenderItemObj->update(['id' => $tenderitem['id'], 'quantity' => ($tenderitem['quantity'] - $object->fields['quantity'])]);
                        //     self::removeDistribution($id);
                        //     $object->delete(['id' => $id]);
                        // } else {
                        //     self::removeAllDistributions($tenderitem['id']);
                        //     $tenderItemObj->delete(['id' => $tenderitem['id']]);
                        // }
                                               
                        
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                    } catch (\Exception $e) {
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
                'plugin_tender_tenderitems_id' => $tenderitems_id
                ]
        ]);

        $distributions = [];
        foreach ($iterator as $item) {
            $distributions[] = $item;
        }

        return $distributions;

    }

    // static function item_add_tenderitem(TenderItem $item) {
    //     self::addDistribution($item);
    // }

    static function pre_item_add_tenderitem(TenderItem $item) {
        if (isset($item->fields['add_catalogue'])) {
            $catalogueItem = CatalogueItem::getByID($item->fields['plugin_tender_catalogueitems_id']);
            $item->fields['name'] = $catalogueItem->fields['name'];
            $item->fields['description'] = $catalogueItem->fields['description'];
        }
    }


    static function updateDistributionPercentages(int $tenderitemsId) {

        $tenderItem = TenderItemModel::with([
            'distributions.financial.costcenter' => function($query) use ($tenderitemsId) {
                $query->with(['measureitems' => function($query) use ($tenderitemsId) {
                    $query->where('plugin_tender_measures_id', function($query) use ($tenderitemsId) {
                        $query->select('plugin_tender_measures_id')
                              ->from('glpi_plugin_tender_tenderitems')
                              ->where('id', $tenderitemsId)
                              ->limit(1);
                    });
                }]);
            },
            'measure.measureitems'
        ])->where('id', $tenderitemsId)->first();

        $measures = Measure::getMeasuresByDistribution($tenderItem);

        foreach ($measures as $measure) {
            $distribution = DistributionModel::find($measure['id']);
            $distribution->percentage = $measure['percentage'];
            $distribution->save();
        }

    }

    static function updateDistributionQuantities(int $tenderitemsId) {

        global $DB;

        $distributions = $DB->request([
            'SELECT' => [
                'glpi_plugin_tender_distributions.*',
                'glpi_plugin_tender_distributions.plugin_tender_measures_id'
            ],
            'FROM' => 'glpi_plugin_tender_distributions',
            'LEFT JOIN' => [
                'glpi_plugin_tender_tenderitems' => [
                    'FKEY' => [
                        'glpi_plugin_tender_distributions' => 'plugin_tender_tenderitems_id',
                        'glpi_plugin_tender_tenderitems' => 'id'
                    ]
                ],
            ],
            'WHERE' => [
                'plugin_tender_tenderitems_id' => $tenderitemsId
                ]
        ]);

    }

    static function removeAllDistributions(int $tenderitemsId) {

        DistributionModel::where('plugin_tender_tenderitems_id', $tenderitemsId)->delete();

        $tenderitem = TenderItemModel::find($tenderitemsId);
        $tenderitem->updateQuantity();

    }

    static function removeDistribution(int $distributions_id) {

        $distribution = DistributionModel::find($distributions_id);
        $tenderitemsId = $distribution->plugin_tender_tenderitems_id;

        $distribution->delete();

        $tenderitem = TenderItemModel::find($tenderitemsId);
        $tenderitem->updateQuantity();

    }    
}

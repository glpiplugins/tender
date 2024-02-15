<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use MassiveAction;
use Supplier;
use Glpi\Application\View\TemplateRenderer;

class DeliveryItem extends CommonDBTM   {

    static $rightname = 'networking';

    static function getIcon() {
        return "fas fa-shopping-cart";
     }

    static function getTypeName($nb = 0) {
        return __('Delivery Item', 'deliveryitem');
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

    static function removeAllDeliveryItems(int $delivery_id) {

        global $DB;

        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_tender_deliveryitems',
            'WHERE' => [
                'plugin_tender_deliveries_id' => $delivery_id
                ]
        ]);
                
        foreach ($iterator as $item) {
            $deliveryitem = new DeliveryItem();
            $deliveryitem->delete($item, 1);
        }

    }

}
<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use MassiveAction;
use Glpi\Application\View\TemplateRenderer;

class Offer extends CommonDBTM   {

    static $rightname = 'networking';

    static function getIcon() {
        return "fas fa-contact";
     }

    static function getTypeName($nb = 0) {
        return __('Offer', 'tender');
    }

    public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
            return __("Offers", "tender");
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
            self::showList($item);
        }
    }

    public function showForm($ID, array $options = []) {

        $this->initForm($ID, $options);

        $offer = OfferModel::find($ID);
            
        $offerItems = $offer->offer_items->map(function($item) {
            return [
                'id' => $item->id,
                'net_price' => $item->net_price,
                'tax' => $item->tax,
                'name' => $item->tender_item->name,
                'description' => $item->tender_item->description,
                'quantity' => $item->tender_item->quantity,
                'total_net' => MoneyHandler::formatToString($item->total_net),
                'total_tax' => MoneyHandler::formatToString($item->total_tax),
                'total_gross' => MoneyHandler::formatToString($item->total_gross)
            ];
        })->toArray();

        TemplateRenderer::getInstance()->display('@tender/offerForm.html.twig', [
            'item'       => $this,
            'offer'      => $offer,
            'offerItems' => $offerItems,
            'params'     => $options,
        ]);
        
        return true;
    }

    static function showList($tender) {

        global $DB;

        $offers = OfferModel::where('plugin_tender_tenders_id', $tender->getID())
        ->get()
        ->map(function($item) {
            $total = $item->offer_items->pluck('total_gross')->toArray();

            return [
                'id'                => $item->id,
                'supplier_name'     => '<a href="/front/supplier.form.php?id=' . $item->supplier->id . '">' . $item->supplier->name . '</a>',
                'offer_date'        => $item->offer_date,
                'total_gross'       => MoneyHandler::formatToString(MoneyHandler::sum($item->offer_items->pluck('total_gross')->toArray())->getAmount()),
                'view_offer'      => '<a href="/plugins/tender/front/offer.form.php?id=' . $item->id . '">' .  __('View Offer', 'tender') . '</a>',
                'itemtype'          => 'GlpiPlugin\Tender\TenderItem'
            ];
        });
        
        $used = SupplierModel::whereDoesntHave('offers', function ($query) use ($tender) {
            $query->where('plugin_tender_tenders_id', $tender->getID())
                  ->whereDoesntHave('offer_items');
        })->pluck('id')->toArray();


        TemplateRenderer::getInstance()->display('@tender/offer.html.twig', [
            'item'   => $tender,
            'is_tab' => true,
            'filters' => [],
            'nofilter' => true,
            'columns' => [
                'supplier_name' => __('Name', 'tender'),
                'offer_date' => __('Offer date', 'tender'),
                'total_gross' => __('Total', 'tender'),
                'view_offer' => __('Details', 'tender'),
            ],
            'formatters' => [
                'supplier_name' => 'raw_html',
                'offer_date' => 'date',
                'view_offer' => 'raw_html',
            ],
            'total_number' => count($offers),
            'entries' => $offers,
            'used' => $used,
            'showmassiveactions'    => true,
            'massiveactionparams' => [
                'num_displayed'    => min($_SESSION['glpilist_limit'], count($offers)),
                'container'        => 'massGlpiPluginTenderOffer' . mt_rand(),
                'specific_actions' => [
                    // 'delete' => __('Delete permanently'),
                    Offer::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect', 'tender'),
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
            'FROM' => 'glpi_plugin_tender_offers',
            'WHERE' => [
                'plugin_tender_tenders_id' => $tenders_id
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
            'FROM' => 'glpi_plugin_tender_offers',
            'WHERE' => [
                'plugin_tender_tenders_id' => $tenders_id,
                'suppliers_id' => $suppliers_id
                ]
        ]);
  
        return $iterator->current();
  
    }

}
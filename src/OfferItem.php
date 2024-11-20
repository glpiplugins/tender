<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use MassiveAction;
use Supplier;
use Glpi\Application\View\TemplateRenderer;

class OfferItem extends CommonDBTM   {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
        return __('Offer Item', 'offeritem');
    }

    static function getIcon() {
        return "fas fa-envelopes-bulk";
    }

    public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
            // Hier können Sie prüfen, ob der Benutzer die Rechte hat, den Tab zu sehen
            // und entsprechend den Namen zurückgeben oder false, wenn der Tab nicht angezeigt werden soll
            return __("Offers", "tender");
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
            // Hier generieren Sie den Inhalt, der im Tab angezeigt werden soll
            self::showList($item);
        }
    }

    public function showForm($ID, array $options = []) {

        $this->initForm($ID, $options);

        // Erste Abfrage in Eloquent
        $offerItem = OfferItemModel::with(['tender_supplier:id,suppliers_id,offer_date,plugin_tender_tenders_id'])
            ->where('id', $ID)
            ->first(['id', 'plugin_tender_tendersuppliers_id']);
        
        // Zweite Abfrage in Eloquent
        $offerItems = OfferItemModel::with(['tender_item:id,name,description,quantity'])
            ->where('plugin_tender_tendersuppliers_id', $this->fields['plugin_tender_tendersuppliers_id'])
            ->get(['id', 'net_price', 'tax', 'plugin_tender_tenderitems_id']);
        
        // Daten für die Darstellung vorbereiten
        $offerItemsArray = $offerItems->map(function($item) {
            return [
                'id' => $item->id,
                'net_price' => $item->net_price,
                'tax' => $item->tax,
                'name' => $item->tender_item->name,
                'description' => $item->tender_item->description,
                'quantity' => $item->tender_item->quantity,
            ];
        })->toArray();
        TemplateRenderer::getInstance()->display('@tender/offeritemForm.html.twig', [
            'item'       => $this,
            'offerItem'  => $offerItem,
            'offerItems' => $offerItemsArray,
            'params'     => $options,
        ]);
        
        return true;
    }

   static function showList($tender) {

    global $DB;
    global $CFG_GLPI;

    $iterator = self::getOfferList($tender);
    
    $items = [];
    foreach ($iterator as $item) {
        $item['supplier_name'] = '<a href="/front/supplier.form.php?id=' . $item['supplier_id'] . '">' . $item['supplier_name'] . '</a>';
        $item['view_offer'] = '<a href="/plugins/tender/front/offeritem.form.php?id=' . $item['offeritem_id'] . '">' .  __('View Offer', 'tender') . '</a>';
        $item['itemtype'] = "GlpiPlugin\Tender\OfferItem";
        $items[] = $item;
    }

    TemplateRenderer::getInstance()->display('@tender/offeritemList.html.twig', [
        'item' => $tender,
        'itemtypes' => $CFG_GLPI['plugin_tender_types'],
        'is_tab' => true,
        'filters' => [],
        'nofilter' => true,
        'columns' => [
            'supplier_name' => __('Name', 'tender'),
            'offer_date' => __('Offer date', 'tender'),
            'total_gross_price' => __('Total', 'tender'),
            'view_offer' => __('Details', 'tender'),
        ],
        'formatters' => [
            'supplier_name' => 'raw_html',
            'offer_date' => 'date',
            'total_gross_price' => 'float',
            'view_offer' => 'raw_html',
        ],
        'total_number' => count($items),
        'entries' => $items,
        'supplierIds' => SupplierModel::whereDoesntHave('tender_suppliers', function ($query) use ($tender) {
            $query->where('plugin_tender_tenders_id', $tender->getID())
                  ->whereDoesntHave('offer_items');
        })->pluck('id')->toArray(),
        // 'tender_suppliers' => SupplierModel::where('plugin_tender_tenders_id', $tender->getID())->doesntHave('offer_items')->pluck('suppliers_id')->toArray(),
        // 'used' => TenderSupplierModel::doesntHave('offer_items')->pluck('suppliers_id')->toArray(),
        'showmassiveactions'    => true,
        'massiveactionparams' => [
            'num_displayed'    => min($_SESSION['glpilist_limit'], count($items)),
            'container'        => 'massGlpiPluginTenderOfferItem' . mt_rand(),
            'specific_actions' => [
                // 'delete' => __('Delete permanently'),
                OfferItem::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect', 'tender'),
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

                $iterator = $DB->request([
                    'FROM' => 'glpi_plugin_tender_offeritems',
                    'WHERE' => [
                        'glpi_plugin_tender_offeritems.plugin_tender_tendersuppliers_id' => $ids
                    ]
                ]);
                
                foreach ($iterator as $offerItem) {
                    $object = new OfferItem();
                    $object->delete(['id' => $offerItem['id']]);                    
                }

                foreach ($ids as $id) {
                    $object = new TenderSupplier();
                    if ($object->getFromDB($id)
                        && $object->update(['id' => $id, 'offer_date' => NULL])) {
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

    // static function getUsedSuppliers($tenderItem) {

    //     // $usedSuppliers = TenderSupplierModel::where('plugin_tender_tenders_id', $tenderItem->getID())->pluck('suppliers_id')->toArray();
    //     // $unUsedSuppliers = TenderSupplierModel::where('plugin_tender_tenders_id', $tenderItem->getID())->pluck('suppliers_id')->toArray();
    //     // $tenderSuppliers = TenderSupplier::getSuppliers($tenderItem->getID());
    //     // $unUsedSuppliers = array_diff($tenderSuppliers, $offerItemSuppliers);

    //     $usedSuppliers = [];

    //     if (!empty($unUsedSuppliers)) {
    //         $crit = ['NOT' => ['id' => $unUsedSuppliers] ];
    //     } else {
    //         $crit = ['id' => $tenderSuppliers];
    //     }
        
    //     $iterator = $DB->request(
    //         'glpi_suppliers',
    //         $crit);
    //     foreach ($iterator as $supplier) {
    //         $usedSuppliers[] = $supplier['id'];
    //     }
    

    //     return $usedSuppliers;

    // }

    static function getOfferList($tender) {

        global $DB;

        $iterator = $DB->request([
            'SELECT' => [
                'glpi_suppliers.name AS supplier_name',
                'glpi_suppliers.id AS supplier_id',
                'glpi_plugin_tender_tendersuppliers.plugin_tender_tenders_id',
                'glpi_plugin_tender_tendersuppliers.id as id',
                'glpi_plugin_tender_tendersuppliers.offer_date',
                'glpi_plugin_tender_offeritems.id as offeritem_id',
                'glpi_plugin_tender_tenderitems.quantity',
                'SUM' => [
                    'glpi_plugin_tender_offeritems.net_price` * (`glpi_plugin_tender_offeritems.tax`/100+1) * `glpi_plugin_tender_tenderitems.quantity as total_gross_price'
                ]
            ],
            'FROM' => 'glpi_plugin_tender_tendersuppliers',
            'INNER JOIN' => [
                'glpi_plugin_tender_offeritems' => [
                    'FKEY' => [
                        'glpi_plugin_tender_tendersuppliers' => 'id',
                        'glpi_plugin_tender_offeritems' => 'plugin_tender_tendersuppliers_id'
                    ]
                ],
                'glpi_suppliers' => [
                    'FKEY' => [
                        'glpi_suppliers' => 'id',
                        'glpi_plugin_tender_tendersuppliers' => 'suppliers_id'
                    ]
                ],
                'glpi_plugin_tender_tenderitems' => [
                    'FKEY' => [
                        'glpi_plugin_tender_tenderitems' => 'id',
                        'glpi_plugin_tender_offeritems' => 'plugin_tender_tenderitems_id'
                    ]
                ]
            ],
            'WHERE' => [
                'glpi_plugin_tender_tendersuppliers.plugin_tender_tenders_id' => $tender->getID()
            ],
            'GROUPBY' => [
                'glpi_suppliers.name',
                'glpi_plugin_tender_tendersuppliers.plugin_tender_tenders_id'
            ]
        ]);

        return $iterator;
    }

    static function getOfferItems($tendersuppliers_id) {

        global $DB;

        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_tender_offeritems',
            'WHERE' => [
                'glpi_plugin_tender_offeritems.plugin_tender_tendersuppliers_id' => $tendersuppliers_id
            ],
        ]);

        return $iterator;
    }

}
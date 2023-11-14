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

    static function getIcon() {
        return "fas fa-shopping-cart";
     }

    static function getTypeName($nb = 0) {
        return __('Offer Item', 'offeritem');
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
        global $DB;

        $this->initForm($ID, $options);

        $iterator = $DB->request([
            'SELECT' => [
                'glpi_plugin_tender_tendersuppliers.suppliers_id AS suppliers_id',
                'glpi_plugin_tender_tendersuppliers.offer_date as offer_date',
                'glpi_plugin_tender_offeritems.id AS offeritem_id',
                'glpi_plugin_tender_tendersuppliers.tenders_id AS tenders_id',
                'glpi_plugin_tender_tendersuppliers.id AS id'
            ],
            'FROM' => 'glpi_plugin_tender_tendersuppliers',
            'INNER JOIN' => [
                'glpi_plugin_tender_offeritems' => [
                    'FKEY' => [
                        'glpi_plugin_tender_tendersuppliers' => 'id',
                        'glpi_plugin_tender_offeritems' => 'tendersuppliers_id'
                    ]
                ]
            ],
            'WHERE' => [
                'glpi_plugin_tender_offeritems.id' => $ID
            ]
        ]);
        
        $offerItem = $iterator->current();

        $iterator = $DB->request([
            'SELECT' => [
                'glpi_plugin_tender_offeritems.id as id',
                'glpi_plugin_tender_offeritems.net_price',
                'glpi_plugin_tender_offeritems.tax',
                'glpi_plugin_tender_tenderitems.name',
                'glpi_plugin_tender_tenderitems.description',
                'glpi_plugin_tender_tenderitems.quantity',
            ],
            'FROM' => 'glpi_plugin_tender_offeritems',
            'LEFT JOIN' => [
                'glpi_plugin_tender_tenderitems' => [
                    'FKEY' => [
                        'glpi_plugin_tender_tenderitems' => 'id',
                        'glpi_plugin_tender_offeritems' => 'tenderitems_id'
                    ]
                ],
            ],
            'WHERE' => [
                'tendersuppliers_id' => $this->fields['tendersuppliers_id']
            ]
        ]);
        
        $offerItems = [];
        foreach($iterator as $item) {
            $offerItems[] = $item;
        }
        TemplateRenderer::getInstance()->display('@tender/offeritemForm.html.twig', [
            'item'   => $this,
            'offerItem'   => $offerItem,
            'offerItems'   => $offerItems,
            'params' => $options,
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
        $item['view_offer'] = '<a href="/plugins/tender/front/offeritem.form.php?id=' . $item['offeritem_id'] . '">' .  __('View Offer') . '</a>';
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
            'supplier_name' => __('name'),
            'offer_date' => __('Offer date'),
            'total_gross_price' => __('Total'),
            'view_offer' => __('View Offer'),
        ],
        'formatters' => [
            'supplier_name' => 'raw_html',
            'offer_date' => 'date',
            'total_gross_price' => 'float',
            'view_offer' => 'raw_html',
        ],
        'total_number' => count($items),
        'entries' => $items,
        'used' => self::getUsedSuppliers($tender),
        'showmassiveactions'    => true,
        'massiveactionparams' => [
            'num_displayed'    => min($_SESSION['glpilist_limit'], count($items)),
            'container'        => 'massGlpiPluginTenderOfferItem' . mt_rand(),
            'specific_actions' => [
                // 'delete' => __('Delete permanently'),
                OfferItem::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect'),
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
                        'glpi_plugin_tender_offeritems.tendersuppliers_id' => $ids
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

    static function getUsedSuppliers($tenderItem) {

        global $DB;
        
        $offerItemSuppliers = [];
        $iterator = $DB->request([
            'SELECT' => [
                'glpi_plugin_tender_tendersuppliers.suppliers_id',
                'glpi_plugin_tender_offeritems.id',
            ],
            'FROM' => 'glpi_plugin_tender_offeritems',
            'LEFT JOIN' => [
                'glpi_plugin_tender_tendersuppliers' => [
                    'FKEY' => [
                        'glpi_plugin_tender_tendersuppliers' => 'id',
                        'glpi_plugin_tender_offeritems' => 'tendersuppliers_id'
                    ]
                ]
            ],
            'WHERE' => [
                'tenders_id' => $tenderItem->getID()
                ]
        ]);
        foreach ($iterator as $offerItemSupplier) {
            $offerItemSuppliers[] = $offerItemSupplier['suppliers_id'];
        }

        $tenderSuppliers = TenderSupplier::getSuppliers($tenderItem->getID());
        $unUsedSuppliers = array_diff($tenderSuppliers, $offerItemSuppliers);

        $usedSuppliers = [];

        if (!empty($unUsedSuppliers)) {
            $crit = ['NOT' => ['id' => $unUsedSuppliers] ];
        } else {
            $crit = ['id' => $tenderSuppliers];
        }
        
        $iterator = $DB->request(
            'glpi_suppliers',
            $crit);
        foreach ($iterator as $supplier) {
            $usedSuppliers[] = $supplier['id'];
        }
    

        return $usedSuppliers;

    }

    static function getOfferList($tender) {

        global $DB;

        $iterator = $DB->request([
            'SELECT' => [
                'glpi_suppliers.name AS supplier_name',
                'glpi_suppliers.id AS supplier_id',
                'glpi_plugin_tender_tendersuppliers.tenders_id',
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
                        'glpi_plugin_tender_offeritems' => 'tendersuppliers_id'
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
                        'glpi_plugin_tender_offeritems' => 'tenderitems_id'
                    ]
                ]
            ],
            'WHERE' => [
                'glpi_plugin_tender_tendersuppliers.tenders_id' => $tender->getID()
            ],
            'GROUPBY' => [
                'glpi_suppliers.name',
                'glpi_plugin_tender_tendersuppliers.tenders_id'
            ]
        ]);

        return $iterator;
    }

    static function getOfferItems($tendersuppliers_id) {

        global $DB;

        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_tender_offeritems',
            'WHERE' => [
                'glpi_plugin_tender_offeritems.tendersuppliers_id' => $tendersuppliers_id
            ],
        ]);

        return $iterator;
    }

}
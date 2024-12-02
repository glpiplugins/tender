<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use MassiveAction;
use Session;
use Glpi\Application\View\TemplateRenderer;

class Delivery extends CommonDBTM   {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
        return __('Delivery', 'tender');
    }

    static function getIcon() {
        return "fas fa-truck-ramp-box";
     }

    public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
            // Hier können Sie prüfen, ob der Benutzer die Rechte hat, den Tab zu sehen
            // und entsprechend den Namen zurückgeben oder false, wenn der Tab nicht angezeigt werden soll
            return __("Delivery", "tender");
        }
            return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
            // Hier generieren Sie den Inhalt, der im Tab angezeigt werden soll
            self::showList($item);
        }
    }

    public function defineTabs($options = []) {
        $ong = [];
        //add main tab for current object
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Document_Item', $ong, $options);
    
        return $ong;
    }

   public function showForm($ID, array $options = []) {
    global $DB;

    $this->initForm($ID, $options);
   
    $deliveryitems = DistributionModel::whereHas('delivery_items.delivery', function($query) use ($ID) {
        $query->where('id', $ID);
    })
    ->with(['tender_item'])
    ->withSum('delivery_items as delivered_quantity', 'quantity')
    ->get()
    ->filter(function($item) {
        return $item->delivered_quantity > 0;
    })
    // ->groupBy('plugin_tender_tenderitems_id')
    // ->map(function($item) {
    //     return [
    //         'id'                                => $item->delivery_items->first()->id ?? null,
    //         'plugin_tender_distributions_id'    => $item->id,
    //         'quantity'                          => $item->quantity,
    //         'name'                              => $item->tender_item->name,
    //         'location_name'                     => $item->location->name ?? null,
    //         'delivery_location_name'            => $item->delivery_location->name ?? null,
    //         'delivered_quantity'                => $item->delivered_quantity,
    //         'itemtype'                          => "GlpiPlugin\Tender\DeliveryItem",
    //     ];
    // })
    // ->toArray();
    ->groupBy('plugin_tender_tenderitems_id')
    ->map(function($item) {
        if ($item->first()->tender_item->plugin_tender_measures_id != 0) {
            $delivered_quantity = $item->sum('delivered_quantity') > 0 ? $item->first()->tender_item->quantity : 0;
        } else {
            $delivered_quantity = $item->sum('delivered_quantity');
        }
        return [
            'id'                                => $item->first()->delivery_items->first()->id ?? null,
            'plugin_tender_tenderitems_id'      => $item->first()->tender_item->id,
            'quantity'                          => $item->first()->tender_item->quantity,
            'name'                              => $item->first()->tender_item->name,
            'measure'                           => $item->first()->tender_item->measure->name ?? null,
            'plugin_tender_measures_id'         => $item->first()->tender_item->plugin_tender_measures_id,
            'delivered_quantity'                => $delivered_quantity,
            'child_entries'                     => $item->map(function($item) {
                return [
                    'delivery_location_name'    => $item->delivery_location->name,
                    'location_name'             => $item->location->name,
                    'quantity'                  => $item->quantity,
                    'delivered_quantity'        => $item->delivered_quantity
                ];
            }),
            'itemtype'                          => "GlpiPlugin\Tender\DeliveryItem",
        ];
    })
    ->toArray();

    TemplateRenderer::getInstance()->display('@tender/deliveryForm.html.twig', [
        'item'   => $this,
        'delivery'   => DeliveryModel::find($ID),
        'is_tab' => true,
        'filters' => [],
        'nofilter' => true,
        'columns' => [
            'name' => __('Name', 'tender'),
            'measure' => __('Measure', 'tender'),
            'delivery_location_name' => __('Delivery Location', 'tender'),
            'location_name' => __('Target Location', 'tender'),
            'quantity' => __('Quantity', 'tender'),
            'delivered_quantity' => __('Delivered Quantity', 'tender'),
        ],
        'formatters' => [
            'delivery_date' => 'date',
            'description' => 'raw_html'
        ],
        'total_number' => count($deliveryitems),
        'entries' => $deliveryitems,
        'showmassiveactions'    => true,
        'massiveactionparams' => [
            'num_displayed'    => min($_SESSION['glpilist_limit'], count($deliveryitems)),
            'container'        => 'massGlpiPluginTenderDeliveryItem' . mt_rand(),
            'specific_actions' => [
                // 'delete' => __('Delete permanently'),
                DeliveryItem::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect', 'tender'),
            ]
        ],
    ]);

    return true;
}

    static function showList($tender = NULL) {

        global $DB;
        global $CFG_GLPI;
        
        $delivery = new Delivery();
        $delivery->initForm('');

        $tenderId = $tender->getID();

        $deliveries = DeliveryModel::where('plugin_tender_tenders_id', $tender->getID())
        ->get()
        ->map(function($item) {
            return [
                'id'                        => $item->id,
                'name'                      => $item->name,
                'delivery_date'             => $item->delivery_date,
                'plugin_tender_tenders_id'  => $item->plugin_tender_tenders_id,
                'itemtype'                  => "GlpiPlugin\Tender\Delivery",
                'view_details'              => '<a href="/plugins/tender/front/delivery.form.php?id=' . $item->id . '">' . __('View Details', 'tender') . '</a>',
            ];
        })->toArray();

        $tenderitems = DistributionModel::with([
            'location',
            'delivery_location',
            'tender_item',
        ])
        ->whereHas('tender_item', function($query) use ($tenderId) {
            $query->where('plugin_tender_tenders_id', $tenderId);
        })
        ->withSum('delivery_items as delivered_quantity', 'quantity')
        ->get()
        ->groupBy('plugin_tender_tenderitems_id')
        ->map(function($item) {
            if ($item->first()->tender_item->plugin_tender_measures_id != 0) {
                $delivered_quantity = $item->sum('delivered_quantity') > 0 ? $item->first()->tender_item->quantity : 0;
            } else {
                $delivered_quantity = $item->sum('delivered_quantity');
            }
            return [
                'id'                        => $item->first()->tender_item->id,
                'quantity'                  => $item->first()->tender_item->quantity,
                'tenderitem_name'           => $item->first()->tender_item->name,
                'measure'                   => $item->first()->tender_item->measure,
                'plugin_tender_measures_id' => $item->first()->tender_item->plugin_tender_measures_id,
                'delivered_quantity'        => $delivered_quantity,
                'distributions'             => $item
            ];
        })
        ->toArray();

    TemplateRenderer::getInstance()->display('@tender/deliveryList.html.twig', [
        'item' => $delivery,
        'tenderitems' => $tenderitems,
        'tenders_id' => $tender->getID(),
        'is_tab' => true,
        'filters' => [],
        'nofilter' => true,
        'columns' => [
            'name' => __('Delivery Reference', 'tender'),
            'delivery_date' => __('Delivery Date', 'tender'),
            'view_details' => __('View Details', 'tender'),
        ],
        'formatters' => [
            'delivery_date' => 'date',
            'view_details' => 'raw_html'
        ],
        'total_number' => count($deliveries),
        'entries' => $deliveries,
        'showmassiveactions'    => true,
        'massiveactionparams' => [
            'num_displayed'    => min($_SESSION['glpilist_limit'], count($deliveries)),
            'container'        => 'massGlpiPluginTenderDelivery' . mt_rand(),
            'specific_actions' => [
                // 'delete' => __('Delete permanently'),
                Delivery::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect', 'tender'),
            ]
        ],
    ]);

      return true;
    }

    static function showMassiveActionsSubForm(MassiveAction $ma) {

        switch ($ma->getAction()) {
        case 'delete':
            echo Html::submit(__('Post', 'tender'), array('name' => 'massiveaction'))."</span>";

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

}
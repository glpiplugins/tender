<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use MassiveAction;
use Session;
use DateTime;
use Glpi\Application\View\TemplateRenderer;

class Invoice extends CommonDBTM   {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
        return __('Invoice', 'tender');
    }

    public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
            // Hier können Sie prüfen, ob der Benutzer die Rechte hat, den Tab zu sehen
            // und entsprechend den Namen zurückgeben oder false, wenn der Tab nicht angezeigt werden soll
            return __("Invoice", "tender");
        }
        return '';
    }

    static function getIcon() {
        return "fas fa-file-invoice-dollar";
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

        $invoice = InvoiceModel::find($ID);

        $distributions = DistributionModel::with([
            'location',
            'delivery_location',
            'financial.costcenter',
            'financial.account'
        ])
        ->whereHas('invoice_items', function($query) use ($ID) {
            $query->where('plugin_tender_invoices_id', $ID);
        })
        ->withSum('invoice_items as invoiced_quantity', 'quantity')
        ->get()
        ->groupBy('plugin_tender_tenderitems_id')
        ->map(function($item) {
            return [
                'id'                                => $item->first()->invoice_items->first()->id ?? null,
                'plugin_tender_tenderitems_id'      => $item->first()->tender_item->id,
                'quantity'                          => $item->first()->tender_item->quantity,
                'name'                              => $item->first()->tender_item->name,
                'measure'                           => $item->first()->tender_item->measure->name ?? null,
                'plugin_tender_measures_id'         => $item->first()->tender_item->plugin_tender_measures_id,
                'invoiced_quantity'                 => $item->first()->tender_item->plugin_tender_measures_id != 0 ? $item->first()->tender_item->quantity : $item->sum('invoiced_quantity'),
                'net_price'                         => MoneyHandler::formatToString($item->first()->tender_item->net_price),
                'tax'                               => $item->first()->tender_item->tax,
                'total_net'                         => MoneyHandler::formatToString($item->first()->tender_item->total_net),
                'total_tax'                         => MoneyHandler::formatToString($item->first()->tender_item->total_tax),
                'total_gross'                       => MoneyHandler::formatToString($item->first()->tender_item->total_gross),
                'child_entries'                     => $item->map(function($item) {
                    return [
                        'delivery_location_name'    => $item->delivery_location->name,
                        'location_name'             => $item->location->name,
                        'quantity'                  => $item->quantity,
                        'invoiced_quantity'         => $item->invoiced_quantity
                    ];
                }),
                'itemtype'                          => "GlpiPlugin\Tender\DeliveryItem",
            ];
        })
        ->toArray();

        TemplateRenderer::getInstance()->display('@tender/invoiceForm.html.twig', [
            'item'   => $this,
            'invoice'   => $invoice,
            'is_tab' => true,
            'filters' => [],
            'nofilter' => true,
            'columns' => [
                'name' => __('Name'),
                'measure' => __('Measure', 'tender'),
                'delivery_location_name' => __('Delivery Location', 'tender'),
                'location_name' => __('Distribution', 'tender'),
                'financial' => __('Financial', 'tender'),
                'reference' => __('Reference', 'tender'),
                'quantity' => __('Ordered Quantity', 'tender'),
                'invoiced_quantity' => __('Invoiced Quantity', 'tender'),
                'net_price' => __('Net Price', 'tender'),
                'total_net' => __('Net Total Price', 'tender'),
                'tax' => __('Tax', 'tender'),
                'total_tax' => __('Tax Total', 'tender'),
                'total_gross' => __('Gross Price', 'tender'),
            ],
            'formatters' => [
                'invoice_date'  => 'date',
                'description'   => 'raw_html',
            ],
            'footer_entries' => [
                0 => [
                    'total_net'     => MoneyHandler::formatToString($invoice->total_net),
                    'total_tax'     => MoneyHandler::formatToString($invoice->total_tax),
                    'total_gross'   => MoneyHandler::formatToString($invoice->total_gross),
                ]
              ],
            'total_number' => count($distributions),
            'entries' => $distributions,
            'showmassiveactions'    => true,
            'massiveactionparams' => [
                'num_displayed'    => min($_SESSION['glpilist_limit'], count($distributions)),
                'container'        => 'massGlpiPluginTenderDeliveryItem' . mt_rand(),
                'specific_actions' => [
                    // 'delete' => __('Delete permanently'),
                    InvoiceItem::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect', 'tender'),
                ]
            ],
        ]);

        return true;
    }

   static function showList($tender = NULL) {

    global $DB;
    global $CFG_GLPI;
    
    $invoice = new Invoice();
    $invoice->initForm('');

    $tenderId = $tender->getID();

    $invoices = InvoiceModel::where('plugin_tender_tenders_id', $tenderId)
    ->get()
    ->map(function($item) {
        return [
            'id'                        => $item->id,
            'name'                      => $item->name,
            'invoice_date'              => $item->invoice_date,
            'internal_reference'        => $item->internal_reference,
            'itemtype'                  => "GlpiPlugin\Tender\Invoice",
            'view_details'              => '<a href="/plugins/tender/front/invoice.form.php?id=' . $item->id . '">' . __('View Details', 'tender') . '</a>',
        ];
    })->toArray();

    $distributions = DistributionModel::with([
        'location',
        'delivery_location',
        'financial.costcenter',
        'financial.account'
    ])
    ->withSum('delivery_items as delivered_quantity', 'quantity')
    ->withSum('invoice_items as invoiced_quantity', 'quantity')
    ->whereHas('tender_item', function($query) use ($tenderId) {
        $query->where('plugin_tender_tenders_id', $tenderId);
    })
    ->get()
    ->groupBy('plugin_tender_tenderitems_id')
    ->map(function($item) {
        return [
            'id'                        => $item->first()->tender_item->id,
            'quantity'                  => $item->first()->tender_item->quantity,
            'tenderitem_name'           => $item->first()->tender_item->name,
            'measure'                   => $item->first()->tender_item->measure,
            'plugin_tender_measures_id' => $item->first()->tender_item->plugin_tender_measures_id,
            'delivered_quantity'        => $item->sum('delivered_quantity'),
            'invoiced_quantity'         => $item->first()->tender_item->plugin_tender_measures_id != 0 ? $item->first()->tender_item->quantity : $item->sum('invoiced_quantity'),
            'net_price'                 => $item->first()->tender_item->net_price,
            'tax'                       => $item->first()->tender_item->tax,
            'total_net'                 => MoneyHandler::formatToString($item->first()->tender_item->total_net),
            'total_tax'                 => MoneyHandler::formatToString($item->first()->tender_item->total_tax),
            'total_gross'               => MoneyHandler::formatToString($item->first()->tender_item->total_gross),
            'distributions'             => $item
        ];
    })
    ->toArray();
 
    TemplateRenderer::getInstance()->display('@tender/invoiceList.html.twig', [
        'item' => $invoice,
        'distributions' => $distributions,
        'tenders_id' => $tender->getID(),
        // 'financials' => $financials,
        'tender' => $tender->fields,
        'is_tab' => true,
        'filters' => [],
        'nofilter' => true,
        'columns' => [
            'name' => __('Invoice Reference', 'tender'),
            'internal_reference' => __('Internal Reference', 'tender'),
            'invoice_date' => __('Invoice date', 'tender'),
            'view_details' => __('View Details', 'tender'),
        ],
        'formatters' => [
            'delivery_date' => 'date',
            'view_details' => 'raw_html'
        ],
        'total_number' => count($invoices),
        'entries' => $invoices,
        'showmassiveactions'    => true,
        'massiveactionparams' => [
            'num_displayed'    => min($_SESSION['glpilist_limit'], count($invoices)),
            'container'        => 'massGlpiPluginTenderDelivery' . mt_rand(),
            'specific_actions' => [
                // 'delete' => __('Delete permanently'),
                Invoice::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect', 'tender'),
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

}
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

        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_tender_invoices',
            'WHERE' => [
                'glpi_plugin_tender_invoices.id' => $ID
            ]
        ]);
        
        $invoice = $iterator->current();

        $iterator = $DB->request([
            'SELECT' => [
                'glpi_plugin_tender_tenderitems.id AS plugin_tender_tenderitems_id',
                'glpi_plugin_tender_distributions.quantity AS quantity',
                'loc.name AS location_name',
                'deliv_loc.name AS delivery_location_name',
                'glpi_plugin_tender_tenderitems.name',
                'glpi_plugin_tender_tenderitems.description',
                'glpi_plugin_tender_tenderitems.net_price',
                'glpi_plugin_tender_tenderitems.tax',
                'glpi_plugin_tender_financials.name as financial',
                'glpi_plugin_tender_financials.reference as reference',
                'glpi_plugin_tender_costcenters.name as costcenter',
                'glpi_plugin_tender_accounts.name as account',
                new \QuerySubQuery([
                    'SELECT' => [
                        'SUM' => [
                            'glpi_plugin_tender_deliveryitems.quantity']
                        ],
                    'FROM' => 'glpi_plugin_tender_deliveryitems',
                    'WHERE' => [
                        'distributions_id' => new \QueryExpression($DB->quoteName('glpi_plugin_tender_distributions.id'))
                    ]
                    ], 'delivered_quantity'),
                new \QuerySubQuery([
                    'SELECT' => [
                        'SUM' => [
                            'glpi_plugin_tender_invoiceitems.quantity']
                        ],
                    'FROM' => 'glpi_plugin_tender_invoiceitems',
                    'WHERE' => [
                        'plugin_tender_distributions_id' => new \QueryExpression($DB->quoteName('glpi_plugin_tender_distributions.id'))
                    ]
                    ], 'invoiced_quantity')
            ],
            'FROM' => 'glpi_plugin_tender_distributions',
            'LEFT JOIN' => [
                'glpi_locations AS loc' => [
                    'FKEY' => [
                        'glpi_plugin_tender_distributions' => 'locations_id',
                        'loc' => 'id'
                    ]
                ],
                'glpi_locations AS deliv_loc' => [
                    'FKEY' => [
                        'glpi_plugin_tender_distributions' => 'delivery_locations_id',
                        'deliv_loc' => 'id'
                    ]
                ],
                'glpi_plugin_tender_tenderitems' => [
                    'FKEY' => [
                        'glpi_plugin_tender_tenderitems' => 'id',
                        'glpi_plugin_tender_distributions' => 'tenderitems_id'
                    ]
                ],
                'glpi_plugin_tender_deliveryitems' => [
                    'FKEY' => [
                        'glpi_plugin_tender_distributions' => 'id',
                        'glpi_plugin_tender_deliveryitems' => 'distributions_id'
                    ]
                ],
                'glpi_plugin_tender_invoiceitems' => [
                    'FKEY' => [
                        'glpi_plugin_tender_distributions' => 'id',
                        'glpi_plugin_tender_invoiceitems' => 'plugin_tender_distributions_id'
                    ]
                ],
                'glpi_plugin_tender_financials' => [
                    'FKEY' => [
                        'glpi_plugin_tender_financials' => 'id',
                        'glpi_plugin_tender_distributions' => 'financials_id'
                    ]
                ],
                'glpi_plugin_tender_accounts' => [
                    'FKEY' => [
                        'glpi_plugin_tender_accounts' => 'id',
                        'glpi_plugin_tender_financials' => 'plugin_tender_accounts_id'
                    ]
                ],
                'glpi_plugin_tender_costcenters' => [
                    'FKEY' => [
                        'glpi_plugin_tender_costcenters' => 'id',
                        'glpi_plugin_tender_financials' => 'plugin_tender_costcenters_id'
                    ]
                ]
            ],
            'WHERE' => [
                'glpi_plugin_tender_invoiceitems.plugin_tender_invoices_id' => $invoice['id']
            ],
            'GROUPBY' => [
                'glpi_plugin_tender_distributions.locations_id',
                'glpi_plugin_tender_distributions.delivery_locations_id'
            ]
        ]);
        
        $invoiceitems = [];
        $total = 0;
        $i = 0;
        foreach($iterator as $item) {
            if($item['invoiced_quantity'] > 0) {
                $item['itemtype'] = "GlpiPlugin\Tender\InvoiceItem";
                $price = $item['tax'] > 0 ? $item['invoiced_quantity'] * ($item['net_price'] * (($item['tax'] / 100) + 1)) : $item['invoiced_quantity'] * $item['net_price'];
                $total += $price;
                $costs['Kosten'][$i]['PSK'] = $item['costcenter'] . '.' . $item['account'];
                $costs['Kosten'][$i]['Auftrag'] = $item['reference'];
                $costs['Kosten'][$i]['Betrag'] = number_format($price, 2, ',', '.');
                $invoiceitems[] = $item;
                $i++;
            }
        }

        $totalFormatted = number_format($total, 2, ',', '.');
        $invoiceName = $invoice['name'];
        $invoicePostingText = $invoice['posting_text'];
        $invoiceDueDate = new DateTime($invoice['due_date']);
        $invoiceDueDate = $invoiceDueDate->format('d.m.Y');
        $accountDocumentString = "/plugins/tender/ajax/accountingDocument.php?sum=$totalFormatted&Haushaltsjahr=2024&Fachbereich=5&Abteilung=56&Aufwand=X&Ansatz=X&Rechnungsnummer=$invoiceName&Buchungstext=$invoicePostingText&Faelligkeit=$invoiceDueDate&" . http_build_query($costs);
        

        TemplateRenderer::getInstance()->display('@tender/invoiceForm.html.twig', [
            'item'   => $this,
            'invoice'   => $invoice,
            'is_tab' => true,
            'filters' => [],
            'nofilter' => true,
            'total' => $total,
            'accountDocumentString' => $accountDocumentString,
            'columns' => [
                'name' => __('Name'),
                'description' => __('Description'),
                'quantity' => __('Quantity'),
                'invoiced_quantity' => __('Invoiced Quantity', 'tender'),
                'delivery_location_name' => __('Delivery Location', 'tender'),
                'location_name' => __('Distribution', 'tender'),
                'financial' => __('Financial', 'tender'),
                'reference' => __('Reference', 'tender'),
            ],
            'formatters' => [
                'invoice_date' => 'date',
                'description' => 'raw_html'
            ],
            'total_number' => count($invoiceitems),
            'entries' => $invoiceitems,
            'showmassiveactions'    => true,
            'massiveactionparams' => [
                'num_displayed'    => min($_SESSION['glpilist_limit'], count($invoiceitems)),
                'container'        => 'massGlpiPluginTenderDeliveryItem' . mt_rand(),
                'specific_actions' => [
                    // 'delete' => __('Delete permanently'),
                    InvoiceItem::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect'),
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

    $iterator = $DB->request([
        'FROM' => 'glpi_plugin_tender_invoices',
        'WHERE' => [
            'glpi_plugin_tender_invoices.tenders_id' => $tender->getID()
            ]
    ]);

    $deliveries = [];
    foreach ($iterator as $item) {
        $item['itemtype'] = "GlpiPlugin\Tender\Invoice";
        $item['view_details'] = '<a href="/plugins/tender/front/invoice.form.php?id=' . $item['id'] . '">' . __('View Details'). '</a>';
        $deliveries[] = $item;
    }

    $iterator = $DB->request([
        'SELECT' => [
            'dis.id AS plugin_tender_distributions_id',
            'dis.quantity AS quantity',
            'loc.name AS location_name',
            'deliv_loc.name AS delivery_location_name',
            'glpi_plugin_tender_tenderitems.name',
            'glpi_plugin_tender_tenderitems.description',
            'glpi_plugin_tender_tenderitems.net_price',
            'glpi_plugin_tender_tenderitems.tax',
            'glpi_plugin_tender_financials.name as financial',
            'glpi_plugin_tender_financials.reference as reference',
            new \QuerySubQuery([
                'SELECT' => [
                    'SUM' => [
                        'glpi_plugin_tender_deliveryitems.quantity']
                    ],
                'FROM' => 'glpi_plugin_tender_deliveryitems',
                'WHERE' => [
                    'distributions_id' => new \QueryExpression($DB->quoteName('dis.id'))
                ]
                ], 'delivered_quantity'),
            new \QuerySubQuery([
                'SELECT' => [
                    'SUM' => [
                        'glpi_plugin_tender_invoiceitems.quantity']
                    ],
                'FROM' => 'glpi_plugin_tender_invoiceitems',
                'WHERE' => [
                    'plugin_tender_distributions_id' => new \QueryExpression($DB->quoteName('dis.id'))
                ]
                ], 'invoiced_quantity')
        ],
        'FROM' => 'glpi_plugin_tender_distributions as dis',
        'LEFT JOIN' => [
            'glpi_locations AS loc' => [
                'FKEY' => [
                    'dis' => 'locations_id',
                    'loc' => 'id'
                ]
            ],
            'glpi_locations AS deliv_loc' => [
                'FKEY' => [
                    'dis' => 'delivery_locations_id',
                    'deliv_loc' => 'id'
                ]
            ],
            'glpi_plugin_tender_tenderitems' => [
                'FKEY' => [
                    'glpi_plugin_tender_tenderitems' => 'id',
                    'dis' => 'tenderitems_id'
                ]
            ],
            'glpi_plugin_tender_deliveryitems' => [
                'FKEY' => [
                    'dis' => 'id',
                    'glpi_plugin_tender_deliveryitems' => 'distributions_id'
                ]
                ],
            'glpi_plugin_tender_invoiceitems' => [
                'FKEY' => [
                    'dis' => 'id',
                    'glpi_plugin_tender_invoiceitems' => 'plugin_tender_distributions_id'
                ]
                ],
            'glpi_plugin_tender_financials' => [
                'FKEY' => [
                    'glpi_plugin_tender_financials' => 'id',
                    'dis' => 'financials_id'
                ]
            ],
        ],
        'WHERE' => [
            'glpi_plugin_tender_tenderitems.tenders_id' => $tender->getID()
        ],
        'GROUPBY' => [
            'dis.locations_id',
            'dis.delivery_locations_id',
            'glpi_plugin_tender_tenderitems.id',
        ],
        'ORDERBY' => [
            'glpi_plugin_tender_tenderitems.id',
            'glpi_plugin_tender_financials.name',
        ]
    ]);

    $tenderitems = [];
    foreach ($iterator as $item) {
        $tenderitems[] = $item;
    }

    $iterator = $DB->request([
        'FROM' => 'glpi_plugin_tender_financialitems',
        'LEFT JOIN' => [
            'glpi_plugin_tender_financials' => [
                'FKEY' => [
                    'glpi_plugin_tender_financialitems' => 'plugin_tender_financials_id',
                    'glpi_plugin_tender_financials' => 'id'
                ]
            ],
        ],
        'WHERE' => [
            'plugin_tender_tenders_id' => $tender->getID()
        ]
    ]);


    $financials = [];

    foreach ($iterator as $item) {
       $financials[$item['id']] = $item['name'];
    }

    TemplateRenderer::getInstance()->display('@tender/invoiceList.html.twig', [
        'item' => $invoice,
        'tenderitems' => $tenderitems,
        'tenders_id' => $tender->getID(),
        'financials' => $financials,
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
        'total_number' => count($deliveries),
        'entries' => $deliveries,
        'showmassiveactions'    => true,
        'massiveactionparams' => [
            'num_displayed'    => min($_SESSION['glpilist_limit'], count($deliveries)),
            'container'        => 'massGlpiPluginTenderDelivery' . mt_rand(),
            'specific_actions' => [
                // 'delete' => __('Delete permanently'),
                Invoice::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect'),
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
<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use MassiveAction;
use Session;
use Glpi\Application\View\TemplateRenderer;

class Order extends CommonDBTM   {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
        return __('Order', 'tenderitem');
    }


   public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
          // Hier können Sie prüfen, ob der Benutzer die Rechte hat, den Tab zu sehen
          // und entsprechend den Namen zurückgeben oder false, wenn der Tab nicht angezeigt werden soll
          return __('Order', 'tender');
      }
      return '';
  }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
         // Hier generieren Sie den Inhalt, der im Tab angezeigt werden soll
         self::showOrderForm($item);
      }
   }

   static function showOrderForm($tender = NULL) {

    global $DB;
    global $CFG_GLPI;
    
    $item = new Order();

    $iterator = $DB->request([
        'SELECT' => [
            'glpi_plugin_tender_orders.*',
            'glpi_plugin_tender_offers.suppliers_id'
        ],
        'FROM' => 'glpi_plugin_tender_orders',
        'INNER JOIN' => [
            'glpi_plugin_tender_offers' => [
                'FKEY' => [
                    'glpi_plugin_tender_offers' => 'id',
                    'glpi_plugin_tender_orders' => 'plugin_tender_offers_id'
                ]
            ]
        ],
        'WHERE' => [
            'glpi_plugin_tender_orders.plugin_tender_tenders_id' => $tender->getID()
            ]
    ]);

    $order = $iterator->current();

    if ($order != NULL) {
        $item->initForm($order['id']);
    }

    $offers = OfferModel::where('plugin_tender_tenders_id', $tender->getID())
    ->get()
    ->map(function($item) {
        return [
            'id'                => $item->id,
            'supplier_name'     => '<a href="/front/supplier.form.php?id=' . $item->supplier->id . '">' . $item->supplier->name . '</a>',
            'offer_date'        => $item->offer_date,
            'total_gross'       => MoneyHandler::formatToString(MoneyHandler::sum($item->offer_items->pluck('total_gross')->toArray())->getAmount()),
            'select_offer'      => TemplateRenderer::getInstance()->render('@tender/components/orderSubmitForm.html.twig', [
                                    'offer' => $item,
                                    'csrf_token' => Session::getNewCSRFToken()
                                ])
        ];
    })->toArray();


    TemplateRenderer::getInstance()->display('@tender/order.html.twig', [
        'item' => $item,
        'order'   => $order,
        'offers' => $offers,
        'is_tab' => true,
        'filters' => [],
        'nofilter' => true,
        'columns' => [
            'supplier_name' => __('Name', 'tender'),
            'offer_date' => __('Offer date', 'tender'),
            'total_gross' => __('Total', 'tender'),
            'select_offer' => __('Select Offer', 'tender'),
        ],
        'formatters' => [
            'supplier_name' => 'raw_html',
            'offer_date' => 'date',
            'select_offer' => 'raw_html',
        ],
        'total_number' => count($offers),
        'entries' => $offers,
        'showmassiveactions'    => false,
    ]);

      return true;
   }
}
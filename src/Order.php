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
          return __("Order", "tender");
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
            'glpi_plugin_tender_tendersuppliers.suppliers_id'
        ],
        'FROM' => 'glpi_plugin_tender_orders',
        'INNER JOIN' => [
            'glpi_plugin_tender_tendersuppliers' => [
                'FKEY' => [
                    'glpi_plugin_tender_tendersuppliers' => 'id',
                    'glpi_plugin_tender_orders' => 'tendersuppliers_id'
                ]
            ]
        ],
        'WHERE' => [
            'glpi_plugin_tender_orders.tenders_id' => $tender->getID()
            ]
    ]);

    $order = $iterator->current();

    if ($order != NULL) {
        $item->initForm($order['id']);
    }

    $iterator = OfferItem::getOfferList($tender);

    $offers = [];
    foreach ($iterator as $offer) {
        $offer['supplier_name'] = '<a href="/front/supplier.form.php?id=' . $offer['supplier_id'] . '">' . $offer['supplier_name'] . '</a>';
        $offer['select_offer'] = '
            <form action="/plugins/tender/front/order.form.php" method="post">
                <button type="submit">' .  __('Select Offer') . '</button>
                <input hidden name="add" value="1" />
                <input hidden name="tendersuppliers_id" value="' . $offer['id'] .'" />
                <input hidden name="tenders_id" value="' . $offer['tenders_id'] .'" />
                <input hidden name="_glpi_csrf_token" value="' . Session::getNewCSRFToken() . '"/>
            </form>';
        $offers[] = $offer;
    }

    TemplateRenderer::getInstance()->display('@tender/order.html.twig', [
        'item' => $item,
        'order'   => $order,
        'offers' => $offers,
        'is_tab' => true,
        'filters' => [],
        'nofilter' => true,
        'columns' => [
            'supplier_name' => __('name'),
            'offer_date' => __('Offer date'),
            'total_gross_price' => __('Total'),
            'select_offer' => __('Select Offer'),
        ],
        'formatters' => [
            'supplier_name' => 'raw_html',
            'offer_date' => 'date',
            'total_gross_price' => 'float',
            'select_offer' => 'raw_html',
        ],
        'total_number' => count($offers),
        'entries' => $offers,
        'showmassiveactions'    => false,
    ]);

      return true;
   }
}
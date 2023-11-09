<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use MassiveAction;
use Glpi\Application\View\TemplateRenderer;

class TenderItem extends CommonDBTM   {

    static $rightname = 'networking';

    static function getIcon() {
        return "fas fa-shopping-cart";
     }

    static function getTypeName($nb = 0) {
        return __('Tender Item', 'tenderitem');
    }


   public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
          // Hier können Sie prüfen, ob der Benutzer die Rechte hat, den Tab zu sehen
          // und entsprechend den Namen zurückgeben oder false, wenn der Tab nicht angezeigt werden soll
          return __("Tender Item", "tender");
      }
      return '';
  }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Tender') {
         // Hier generieren Sie den Inhalt, der im Tab angezeigt werden soll
         self::showList($item);
      }
   }

   static function showList($tenderItem) {

    global $DB;
    global $CFG_GLPI;

    $iterator = $DB->request([
        'FROM' => 'glpi_plugin_tender_tenderitems',
        'WHERE' => [
            'tenders_id' => $tenderItem->getID()
            ]
    ]);

    $items = [];
    foreach ($iterator as $item) {
        $item['name'] = '<a href="/front/plugins/tender/tenderitem.form.php?id=' . $item['id'] . '">' . $item['name'] . '</a>';
        $item['itemtype'] = "GlpiPlugin\Tender\TenderItem";
        $items[] = $item;
    }
    
    $suppliers = TenderSupplier::getSuppliers($tenderItem->getID());
      TemplateRenderer::getInstance()->display('@tender/tenderitems.html.twig', [
          'item'   => $tenderItem,
          'suppliers' => $suppliers,
          'catalogueitems' => CatalogueItem::getCatalogueItemsBySupplier($suppliers),
          'itemtypes' => $CFG_GLPI['plugin_tender_types'],
          'is_tab' => true,
          'filters' => [],
          'nofilter' => true,
          'columns' => [
              'id' => __('ID'),
              'name' => __('name'),
              'quantity' => __('quantity'),
              'net_price' => __('net_price'),
              'tax' => __('tax'),
          ],
          'formatters' => [
              'name' => 'raw_html'
          ],
          'total_number' => count($items),
          'entries' => $items,
          'used' => array_column($items, 'id'),
          'showmassiveactions'    => true,
          'massiveactionparams' => [
              'num_displayed'    => min($_SESSION['glpilist_limit'], count($items)),
              'container'        => 'massGlpiPluginTenderTenderSupplier' . mt_rand(),
              'specific_actions' => [
                  // 'delete' => __('Delete permanently'),
                  TenderSupplier::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect'),
              ]
          ],
      ]);

      return true;
   }
}
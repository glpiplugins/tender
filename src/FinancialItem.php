<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Entity;
use Glpi\Application\View\TemplateRenderer;

class FinancialItem extends CommonDBTM   {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
      
        return __('Financial Item', 'Financial Items');
    }

   //  static function getIcon() {
   //      return "fas fa-credit-card";
   //   }

   public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Financial') {
          // Hier können Sie prüfen, ob der Benutzer die Rechte hat, den Tab zu sehen
          // und entsprechend den Namen zurückgeben oder false, wenn der Tab nicht angezeigt werden soll
          return __("Financial Item", "tender");
      }
      return '';
  }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Financial') {
         // Hier generieren Sie den Inhalt, der im Tab angezeigt werden soll
         self::showList($item);
      }
   }

     public function showForm($ID, array $options = []) {
        global $CFG_GLPI;

        $this->initForm($ID, $options);
        
        TemplateRenderer::getInstance()->display('@tender/financials.html.twig', [
            'item'   => $this,
            'params' => $options,
            'costcenters' => Costcenter::getAllCostcentersDropdown(),
            'accounts' => Account::getAllAccountsDropdown(),
        ]);

        return true;
     }

    static function showList($financial) {

        global $DB;
        global $CFG_GLPI;
    
        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_tender_financialitems',
            'WHERE' => [
                'plugin_tender_financials_id' => $financial->getID()
                ]
        ]);
  
        $total = 0;
        $items = [];
        foreach ($iterator as $item) {
            $item['itemtype'] = "GlpiPlugin\Tender\FinancialItem";
            $item['view_details'] = '<a href="/plugins/tender/front/tenderitem.form.php?id=' . $item['id'] . '">' . __('View Details'). '</a>';
            $item['type_name'] = $item['type'] == 0 ? __('Expense') : __('Income');
            $items[] = $item;
            if($item['type'] == 0) {
                $value = $item['value'] * -1;
            } else {
                $value = $item['value'];
            }
            $total += $value;
        }
  
        TemplateRenderer::getInstance()->display('@tender/financialItemList.html.twig', [
            'item'   => $financial,
            'financialItems' => $items,
            'footer_entries' => [
              0 => [
                  'value' => $total,
              ]
            ],
            'years' => [
                2026 => 2026,
                2025 => 2025,
                2024 => 2024,
                2023 => 2023,
                2022 => 2022,
                2021 => 2021,
                2020 => 2020
            ],
            'types' => [
                0 => __('Expense'),
                1 => __('Income'),
            ],
            'is_tab' => true,
            'filters' => [],
            'nofilter' => true,
            'columns' => [
               'year' => __('year'),
               'type_name' => __('type'),
               'value' => __('value'),
               'view_details' => __('View Detail')
           ],
           'formatters' => [
                  'view_details' => 'raw_html',
                  'value' => 'float',
            ],
            'total_number' => count($items),
            'entries' => $items,
            'used' => array_column($items, 'id'),
            'showmassiveactions'    => true,
            'massiveactionparams' => [
                'num_displayed'    => min($_SESSION['glpilist_limit'], count($items)),
                'container'        => 'massGlpiPluginTenderTenderItem' . mt_rand(),
                'specific_actions' => [
                    // 'delete' => __('Delete permanently'),
                  //   FinancialItem::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect'),
                ]
            ],
        ]);
  
        return true;
    }

}
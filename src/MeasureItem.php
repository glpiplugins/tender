<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use MassiveAction;
use Session;
use Glpi\Application\View\TemplateRenderer;

class MeasureItem extends CommonDBTM   {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
        return __('Measure Item', 'tender');
    }


   public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Measure') {
          return __("Measure Item", "tender");
      }
      return '';
  }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\Measure') {
         self::showList($item);
      }
   }

   static function showList($measure) {

    global $DB;
    global $CFG_GLPI;

    $iterator = $DB->request([
        'SELECT' => [
            'glpi_plugin_tender_costcenters.*',
            'glpi_plugin_tender_measureitems.*',
        ],
        'FROM' => 'glpi_plugin_tender_measureitems',
        'LEFT JOIN' => [
            'glpi_plugin_tender_costcenters' => [
                'FKEY' => [
                    'glpi_plugin_tender_measureitems' => 'plugin_tender_costcenters_id',
                    'glpi_plugin_tender_costcenters' => 'id'
                ]
            ],
        ],
        'WHERE' => [
            'glpi_plugin_tender_measureitems.plugin_tender_measures_id' => $measure->getID()
            ]
    ]);

    $items = [];
    $total = Measure::getTotalByMeasure($measure->getID());
    $total_percentage = 0;

    foreach ($iterator as $item) {
        $item['view_details'] = '<a href="/plugins/tender/front/measureitem.form.php?id=' . $item['id'] . '">' . __('View Details'). '</a>';
        $item['percentage'] = $item['value'] / $total * 100;
        $total_percentage += $item['percentage'];
        $items[] = $item;


    }
      
    TemplateRenderer::getInstance()->display('@tender/measureItemList.html.twig', [
        'item'   => $measure,
        'costcenters' => Costcenter::getCostcenters(),
        'is_tab' => true,
        'filters' => [],
        'nofilter' => true,
        'footer_entries' => [
            0 => [
                'value' => $total,
                'percentage' => $total_percentage
            ]
          ],
        'columns' => [
            'name' => __('name'),
            'value' => __('Value'),
            'percentage' => __('Percentage', 'tender'),
            'view_details' => __('View Detail', 'tender'),
        ],
        'formatters' => [
            'view_details' => 'raw_html',
            'value' => 'float',
            'percentage' => 'float',
        ],
        'total_number' => count($items),
        'entries' => $items,
        'used' => array_column($items, 'id'),
        'showmassiveactions'    => true,
        'massiveactionparams' => [
        'num_displayed'    => min($_SESSION['glpilist_limit'], count($items)),
        'container'        => 'massGlpiPluginTenderMeasureItem' . mt_rand(),
        'specific_actions' => [
            // 'delete' => __('Delete permanently'),
            MeasureItem::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect'),
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

    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
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
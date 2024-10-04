<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use CommonDropdown;
use Html;
use Entity;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;

class Measure extends CommonDropdown  {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
      
        return __('Measures', 'tender');
    }
       
    static function getIcon() {
        return "fas fa-chart-pie";
    }

    public function defineTabs($options = []) {
        $ong = [];
        //add main tab for current object
        $this->addDefaultFormTab($ong);
        //add core Document tab
        $this->addStandardTab('GlpiPlugin\Tender\MeasureItem', $ong, $options);

        return $ong;
    }

    public function showForm($ID, array $options = []) {
        global $CFG_GLPI;

        $this->initForm($ID, $options);
        
        TemplateRenderer::getInstance()->display('@tender/measures.html.twig', [
            'item'   => $this,
            'params' => $options,
            'itemtypes' => $CFG_GLPI['plugin_tender_types']
        ]);

        return true;
     }

     public function rawSearchOptions() {

        $tab = parent::rawSearchOptions();

        // $tab[] = [
        //     'id'                 => '2',
        //     'table'              => $this->getTable(),
        //     'field'              => 'id',
        //     'name'               => __('ID'),
        //     'massiveaction'      => false, // implicit field is id
        //     'datatype'           => 'number'
        // ];

        $tab[] = [
            'id'                 => '3',
            'table'              => self::getTable(),
            'field'              => 'name',
            'name'               => __('Name'),
            'datatype'           => 'dropdown',
            'displaytype'   => 'text',
            'injectable'    => true,
        ];

        return $tab;
     }
  
    function isPrimaryType() {

        return true;
     }

     public static function getAllMeasuresDropdown() : array {

        global $DB;

        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_tender_measures'
        ]);

        $measures = [];

        foreach ($iterator as $measure) {
                $measures[$measure['id']] = $measure['name'];
        }

        return $measures;
     }

     static function getTotalByMeasure(int $id) : int {

        global $DB;

        $iterator = $DB->request([
            'SELECT' => [
                'SUM' => [
                    'glpi_plugin_tender_measureitems.value as total'
                ]
            ],
                'FROM' => 'glpi_plugin_tender_measureitems',
                'WHERE' => [
                    'glpi_plugin_tender_measureitems.plugin_tender_measures_id' => $id
                ],
                'GROUPBY' => [
                    'glpi_plugin_tender_measureitems.plugin_tender_measures_id'
                ]
        ]);

        return $iterator->current() ? $iterator->current()['total'] : 0;
    }

    public static function getMeasuresByDistribution($tenderitem) : array {

        $measures = [];

        foreach($tenderitem->distributions as $distribution) {
            if($tenderitem->plugin_tender_measures_id == 0) {
                $percentage = 100;
            } else {
                $percentage = 100 / $tenderitem->totalMeasureValue() * $distribution->getMeasureValue();
            }
            $measures[] = [
                'id' => $distribution->id,
                'percentage' => $percentage,
                'totalNetValue' => $tenderitem->totalNetValue(),
                'distribution' => $distribution
            ];
        }
        
        return $measures;
    }

}

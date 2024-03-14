<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use Glpi\Application\View\TemplateRenderer;

class Tender extends CommonDBTM  {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
      
        return __('Tenders', 'tender');
    }

     public function defineTabs($options = []) {
      $ong = [];
      //add main tab for current object
      $this->addDefaultFormTab($ong);
      //add core Document tab
      $this->addStandardTab('GlpiPlugin\Tender\Financial', $ong, $options);
      $this->addStandardTab('GlpiPlugin\Tender\TenderSupplier', $ong, $options);
      $tendersupplier = new TenderSupplier();
      if ($tendersupplier->find(['tenders_id' => $this->fields['id']])) {
         $this->addStandardTab('GlpiPlugin\Tender\TenderItem', $ong, $options);
      }
      
      $tenderitem = new TenderItem();
      if ($tenderitem->find(['tenders_id' => $this->fields['id']])) {
         $this->addStandardTab('GlpiPlugin\Tender\OfferItem', $ong, $options);
      }

      $this->addStandardTab('GlpiPlugin\Tender\Order', $ong, $options);
      $this->addStandardTab('GlpiPlugin\Tender\Delivery', $ong, $options);
      $this->addStandardTab('GlpiPlugin\Tender\Invoice', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      return $ong;
   }
       
     static function getIcon() {
        return "fas fa-shopping-cart";
     }

   public function showForm($ID, array $options = []) {
      global $CFG_GLPI;
      global $DB;

      $this->initForm($ID, $options);

      $iterator = $DB->request([
            'FROM' => 'glpi_plugin_tender_tendertypes'
      ]);
   

      $tendertypes = [];

      foreach ($iterator as $item) {
         $tendertypes[$item['id']] = $item['name'];
      }
   
      $iterator = $DB->request([
         'FROM' => 'glpi_plugin_tender_tenderstatuses'
      ]);


      $tenderstatus = [];

      foreach ($iterator as $item) {
         $tenderstatus[$item['id']] = $item['name'];
      }

      TemplateRenderer::getInstance()->display('@tender/tender.html.twig', [
         'item'   => $this,
         'params' => $options,
         'tendertypes' => $tendertypes,
         'tenderstatus' => $tenderstatus
      ]);

      return true;
   }


    public function rawSearchOptions() {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '2',
           'table'              => $this::getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'searchtype'         => 'contains',
           'massiveaction'      => false
        ];
  
        $tab[] = [
            'id'                 => '3',
            'table'              => $this::getTable(),
            'field'              => 'name',
            'name'               => __('Name'),
            'datatype'           => 'string',
            'massiveaction'      => false,
            'injectable'    => true,
        ];
  
        $tab[] = [
           'id'                 => '4',
           'table'              => $this::getTable(),
           'field'              => 'tender_subject',
           'name'               => __('Tender Subject'),
           'datatype'           => 'string',
           'massiveaction'      => false,
           'injectable'    => true,
        ];
  
        $tab[] = [
           'id'                 => '5',
           'table'              => 'glpi_entities',
           'field'              => 'completename',
           'name'               => Entity::getTypeName(1),
           'datatype'           => 'dropdown',
           'massiveaction'      => false
        ];
  
        $tab[] = [
           'id'                 => '6',
           'table'              => $this::getTable(),
           'field'              => 'is_recursive',
           'name'               => __('Recursive'),
           'datatype'           => 'bool',
           'massiveaction'      => true
        ];
  
        $tab[] = [
           'id'                 => '7',
           'table'              => $this::getTable(),
           'field'              => 'language',
           'name'               => __('Language'),
           'datatype'           => 'specific',
           'searchtype'         => [
              '0'                  => 'equals'
           ],
           'massiveaction'      => true
        ];
  
      $tab[] = [
         'id'                 => '8',
         'table'              => 'glpi_plugin_tender_tenderstatuses',
         'field'              => 'name',
         'itemlink_type'      => 'GlpiPlugin\Tender\TenderStatus',
         'linkfield'          => 'plugin_tender_tenderstatuses_id',
         'name'               => __('Status'),
         'displaytype'        => 'dropdown',
         'relationclass'      => 'glpi_plugin_tender_tenderstatuses',
         'storevaluein'       => 'plugin_tender_tenderstatuses_id',
         'injectable'    => true,
      ]; 

      $tab[] = [
         'id'                 => '9',
         'table'              => 'glpi_plugin_tender_tendertypes',
         'field'              => 'name',
         'itemlink_type'      => 'GlpiPlugin\Tender\TenderType',
         'linkfield'          => 'plugin_tender_tendertypes_id',
         'name'               => __('Type'),
         'displaytype'        => 'dropdown',
         'relationclass'      => 'glpi_plugin_tender_tendertypes',
         'storevaluein'       => 'plugin_tender_tendertypes_id',
         'injectable'    => true,
      ]; 

      return $tab;

    }


    static function calculateEstimatedNetTotal($tenders_id) {

      global $DB;

      $tenderitems = $DB->request([
          'FROM' => 'glpi_plugin_tender_tenderitems',
          'WHERE' => [
              'tenders_id' => $tenders_id
              ]
      ]);

      $estimated_net_total = 0;
      foreach ($tenderitems as $tenderitem) {
          $estimated_net_total += $tenderitem['net_price'] * $tenderitem['quantity'];
      }

      $tender = new Tender();
      // $tender = $tender->getbyID($tenders_id);
      
      $tender->update([
          'id' => $tenders_id,
          'estimated_net_total' => $estimated_net_total
      ]);

    }



}

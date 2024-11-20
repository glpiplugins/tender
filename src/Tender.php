<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use Glpi\Application\View\TemplateRenderer;

/**
 * Class Tender
 * Represents a Tender in the GLPI Tender plugin
 */
class Tender extends CommonDBTM {

    // Define the right name for permission checks
    static $rightname = 'networking';

    /**
     * Get the type name for the Tender class
     *
     * @param int $nb Number of items (unused)
     * @return string The translated type name
     */
    static function getTypeName($nb = 0) {
        return __('Tenders', 'tender');
    }

    /**
     * Define the tabs displayed in the Tender form
     *
     * @param array $options Options array
     * @return array Array of tabs
     */
    public function defineTabs($options = []) {
        $ong = [];

        // Add the default form tab
        $this->addDefaultFormTab($ong);

        // Uncomment the following lines to add additional tabs
        $this->addStandardTab('GlpiPlugin\Tender\Financial', $ong, $options);
        // $this->addStandardTab('GlpiPlugin\Tender\TenderSupplier', $ong, $options);

        // Add the TenderItem tab
        $this->addStandardTab('GlpiPlugin\Tender\TenderItem', $ong, $options);
        
        // If TenderItems exist, add OfferItem and Order tabs
        $tenderitem = new TenderItem();
        if ($tenderitem->find(['plugin_tender_tenders_id' => $this->fields['id']])) {
            $this->addStandardTab('GlpiPlugin\Tender\OfferItem', $ong, $options);
            $this->addStandardTab('GlpiPlugin\Tender\Order', $ong, $options);
        }

        // If Orders exist, add Delivery tab
        $order = new Order();
        if ($order->find(['plugin_tender_tenders_id' => $this->fields['id']])) {
            $this->addStandardTab('GlpiPlugin\Tender\Delivery', $ong, $options);
        }

        // If Deliveries exist, add Invoice tab
        $delivery = new Delivery();
        if ($delivery->find(['plugin_tender_tenders_id' => $this->fields['id']])) {
            $this->addStandardTab('GlpiPlugin\Tender\Invoice', $ong, $options);
        }

        $invoice = new Invoice(); // Variable declared but not used
        return $ong;
    }

    /**
     * Get the icon associated with the Tender class
     *
     * @return string Icon class for FontAwesome or similar icon libraries
     */
    static function getIcon() {
        return "fas fa-shopping-cart";
    }

    /**
     * Display the form for the Tender
     *
     * @param int   $ID      ID of the Tender
     * @param array $options Options array
     * @return bool True on success
     */
    public function showForm($ID, array $options = []) {
        // Initialize the form
        $this->initForm($ID, $options);
        // Render the Tender form using a Twig template
        TemplateRenderer::getInstance()->display('@tender/tender.html.twig', [
            'item'               => $this,
            'params'             => $options,
            'tendertypes'        => TenderTypeModel::all()->pluck('name', 'id')->toArray(),
            'tenderstatus'       => TenderStatusModel::all()->pluck('name', 'id')->toArray(),
            'current_suppliers'  => TenderSupplierModel::where('plugin_tender_tenders_id', $ID)->pluck('suppliers_id')->toArray(),
        ]);

        return true;
    }

    /**
     * Define the raw search options for the Tender
     *
     * @return array Array of search options
     */
    public function rawSearchOptions() {
        $tab = parent::rawSearchOptions();

        // Search option for ID
        $tab[] = [
            'id'            => '2',
            'table'         => $this->getTable(),
            'field'         => 'id',
            'name'          => __('ID'),
            'massiveaction' => false, // Implicit field is 'id'
            'datatype'      => 'number'
        ];

        // Search option for Name
        $tab[] = [
            'id'            => '3',
            'table'         => $this::getTable(),
            'field'         => 'name',
            'name'          => __('Name', 'tender'),
            'datatype'      => 'string',
            'massiveaction' => false,
            'injectable'    => true,
        ];

        // Search option for Tender Subject
        $tab[] = [
            'id'            => '4',
            'table'         => $this::getTable(),
            'field'         => 'tender_subject',
            'name'          => __('Tender Subject', 'tender'),
            'datatype'      => 'string',
            'massiveaction' => false,
            'injectable'    => true,
        ];

        // Search option for Entity
        $tab[] = [
            'id'            => '5',
            'table'         => 'glpi_entities',
            'field'         => 'completename',
            'name'          => Entity::getTypeName(1),
            'datatype'      => 'dropdown',
            'massiveaction' => false
        ];

        // Search option for Recursive
        $tab[] = [
            'id'            => '6',
            'table'         => $this::getTable(),
            'field'         => 'is_recursive',
            'name'          => __('Recursive'),
            'datatype'      => 'bool',
            'massiveaction' => true
        ];

        // Search option for Language
        $tab[] = [
            'id'            => '7',
            'table'         => $this::getTable(),
            'field'         => 'language',
            'name'          => __('Language'),
            'datatype'      => 'specific',
            'searchtype'    => [
                '0'          => 'equals'
            ],
            'massiveaction' => true
        ];

        // Search option for Status
        $tab[] = [
            'id'             => '8',
            'table'          => 'glpi_plugin_tender_tenderstatuses',
            'field'          => 'name',
            'itemlink_type'  => 'GlpiPlugin\Tender\TenderStatus',
            'linkfield'      => 'plugin_tender_statuses_id',
            'name'           => __('Status', 'tender'),
            'displaytype'    => 'dropdown',
            'relationclass'  => 'glpi_plugin_tender_tenderstatuses',
            'storevaluein'   => 'plugin_tender_statuses_id',
            'injectable'     => true,
        ]; 

        // Search option for Type
        $tab[] = [
            'id'             => '9',
            'table'          => 'glpi_plugin_tender_tendertypes',
            'field'          => 'name',
            'itemlink_type'  => 'GlpiPlugin\Tender\TenderType',
            'linkfield'      => 'plugin_tender_tendertypes_id',
            'name'           => __('Type', 'tender'),
            'displaytype'    => 'dropdown',
            'relationclass'  => 'glpi_plugin_tender_tendertypes',
            'storevaluein'   => 'plugin_tender_tendertypes_id',
            'injectable'     => true,
        ]; 

        // Search option for Start Date
        $tab[] = [
            'id'            => '10',
            'table'         => $this::getTable(),
            'field'         => 'start_date',
            'name'          => __('Start Date', 'tender'),
            'datatype'      => 'date',
            'massiveaction' => false,
            'injectable'    => true,
        ];

        // Search option for Submission Date
        $tab[] = [
            'id'            => '11',
            'table'         => $this::getTable(),
            'field'         => 'submission_date',
            'name'          => __('Submission Date', 'tender'),
            'datatype'      => 'date',
            'massiveaction' => false,
            'injectable'    => true,
        ];

        // Search option for End Date
        $tab[] = [
            'id'            => '12',
            'table'         => $this::getTable(),
            'field'         => 'end_date',
            'name'          => __('End Date', 'tender'),
            'datatype'      => 'date',
            'massiveaction' => false,
            'injectable'    => true,
        ];

        // Search option for Suppliers
        $tab[] = [
            'id'            => '13',
            'table'         => 'glpi_suppliers',
            'field'         => 'name',
            'name'          => __('Suppliers', 'tender'),
            'datatype'      => 'itemlink',
            'right'         => 'all',
            'massiveaction' => false,
            'forcegroupby'  => true,
            // 'splititems'    => ', ', // Uncomment if you need to specify a separator
            'usehaving'     => true,
            'joinparams'    => [
                'beforejoin' => [
                    // Join with the tender-suppliers linking table
                    'table'      => 'glpi_plugin_tender_tendersuppliers',
                    'joinparams' => [
                        'jointype'  => 'child',
                        'on'        => [
                            'plugin_tender_tenders_id' => 'id' // Link tenders_id in the linking table to id in the main table
                        ]
                    ]
                ],
                'on' => [
                    'suppliers_id' => 'id' // Link suppliers_id in the linking table to id in the suppliers table
                ]
            ],
        ];

        return $tab;
    }

    public function post_addItem() {
      $this->updateSuppliers();
      parent::post_addItem();
   }

   public function post_updateItem($history = 1) {
      $this->updateSuppliers();
      parent::post_updateItem($history);
   }

   private function updateSuppliers() {
      if (isset($_POST['suppliers_id'])) {
          $suppliers_ids = $_POST['suppliers_id'];
      } else {
          $suppliers_ids = [];
      }

      $tenderSupplier = new TenderSupplier();

      // Get existing suppliers
      $existing_suppliers = $tenderSupplier->find(['plugin_tender_tenders_id' => $this->fields['id']]);

      $existing_suppliers_ids = array_column($existing_suppliers, 'suppliers_id');

      // Suppliers to add
      $suppliers_to_add = array_diff($suppliers_ids, $existing_suppliers_ids);

      // Suppliers to delete
      $suppliers_to_delete = array_diff($existing_suppliers_ids, $suppliers_ids);

      // Add new suppliers
      foreach ($suppliers_to_add as $supplier_id) {
          $tenderSupplier->add([
              'plugin_tender_tenders_id'   => $this->fields['id'],
              'suppliers_id' => $supplier_id
          ]);
      }

      // Delete removed suppliers
      foreach ($suppliers_to_delete as $supplier_id) {
          $tenderSupplier->deleteByCriteria([
              'plugin_tender_tenders_id'   => $this->fields['id'],
              'suppliers_id' => $supplier_id
          ]);
      }
  }

    /**
     * Calculate the estimated net total for a Tender
     *
     * @param int $tenders_id ID of the Tender
     */
    static function calculateEstimatedNetTotal($tenders_id) {
    
        $estimated_net_total = TenderItemModel::where('plugin_tender_tenders_id', $tenders_id)
            ->selectRaw('SUM(net_price * quantity) as total')
            ->value('total');

        TenderModel::where('id', $tenders_id)->update([
            'estimated_net_total' => $estimated_net_total
        ]);
    }
    
}

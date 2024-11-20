<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfferItemModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_offeritems';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    /**
     * Get the tender_supplier that owns the offer_item.
     */
    public function tender_supplier(): BelongsTo
    {
        return $this->belongsTo(TenderSupplierModel::class, 'plugin_tender_tendersuppliers_id', 'id');
    }

    /**
     * Get the tender_item that owns the offer_item.
     */
    public function tender_item(): BelongsTo
    {
        return $this->belongsTo(TenderItemModel::class, 'plugin_tender_tenderitems_id', 'id');
    }

}
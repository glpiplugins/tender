<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenderSupplierModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_tendersuppliers';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    /**
     * Get the supplier that owns the tenderitem.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(SupplierModel::class, 'plugin_tender_suppliers_id', 'id');
    }

    /**
     * Get the tender that owns the tenderitem.
     */
    public function tender(): BelongsTo
    {
        return $this->belongsTo(TenderModel::class, 'plugin_tender_tenders_id', 'id');
    }

    /**
     * Get the offer_items for the tender_supplier.
     */
    public function offer_items(): HasMany
    {
        return $this->hasMany(OfferItemModel::class, 'plugin_tender_tendersuppliers_id', 'id');
    }

}
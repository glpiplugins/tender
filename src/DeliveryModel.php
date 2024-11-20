<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use  Illuminate\Database\Eloquent\Relations\BelongsTo;
use  Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_deliveries';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    protected $itemtype = 'GlpiPlugin\Tender\Delivery';

    /**
     * Get the tender that owns the delivery.
     */
    public function tender(): BelongsTo
    {
        return $this->belongsTo(TenderModel::class, 'plugin_tender_tenders_id', 'id');
    }

    /**
     * Get the delivery items for the delivery.
     */
    public function delivery_items(): HasMany
    {
        return $this->hasMany(DeliveryItemModel::class, 'plugin_tender_deliveries_id', 'id');
    }

}
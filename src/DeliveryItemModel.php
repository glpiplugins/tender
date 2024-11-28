<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use  Illuminate\Database\Eloquent\Relations\BelongsTo;
use  Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryItemModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_deliveryitems';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    /**
     * Get the delivery that owns the delivery item.
     */
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(DeliveryModel::class, 'plugin_tender_deliveries_id', 'id');
    }

    /**
     * Get the distribution that owns the delivery item.
     */
    public function distribution(): BelongsTo
    {
        return $this->belongsTo(DistributionModel::class, 'plugin_tender_distributions_id', 'id');
    }

    public function getItemTypeAttribute() {
        return 'GlpiPlugin\Tender\DeliveryItem';
    }

}
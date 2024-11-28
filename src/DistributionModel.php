<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use  Illuminate\Database\Eloquent\Relations\BelongsTo;
use  Illuminate\Database\Eloquent\Relations\HasMany;

class DistributionModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_distributions';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    protected $fillable = [
        'plugin_tender_tenderitems_id',
        'quantity',
        'plugin_tender_financials_id',
        'locations_id',
        'delivery_locations_id',
        'percentage'
    ];

    protected static function boot()
    {

        parent::boot();

        static::created(function (DistributionModel $distribution) {
            $distribution->tender_item?->updateQuantity();
            $distribution->tender_item->tender->updateFinancialItemValue();
        });

        static::updated(function (DistributionModel $distribution) {
            $distribution->tender_item?->updateQuantity();
            $distribution->tender_item->tender->updateFinancialItemValue();
        });

        static::deleted(function (DistributionModel $distribution) {
            $distribution->tender_item?->updateQuantity();
            $distribution->tender_item->tender->updateFinancialItemValue();
        });
    }

    /**
     * Get the tender item that owns the distribution.
     */
    public function tender_item(): BelongsTo
    {
        return $this->belongsTo(TenderItemModel::class, 'plugin_tender_tenderitems_id', 'id');
    }

    /**
     * Get the financial that owns the distribution.
     */
    public function financial(): BelongsTo
    {
        return $this->belongsTo(FinancialModel::class, 'plugin_tender_financials_id', 'id');
    }

    /**
     * Get the location that owns the distribution.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(LocationModel::class, 'locations_id', 'id');
    }

    /**
     * Get the delivery location that owns the distribution.
     */
    public function delivery_location(): BelongsTo
    {
        return $this->belongsTo(LocationModel::class, 'delivery_locations_id', 'id');
    }

    /**
     * Get the delivery items for the distribution.
     */
    public function delivery_items(): HasMany
    {
        return $this->hasMany(DeliveryItemModel::class, 'plugin_tender_distributions_id', 'id');
    }

    /**
     * Beziehung zu Deliveries über DeliveryItems.
     */
    public function deliveries()
    {
        return $this->hasManyThrough(
            DeliveryModel::class,
            DeliveryItemModel::class,
            'plugin_tender_distributions_id',
            'id',
            'id',
            'plugin_tender_deliveries_id'
        );
    }

    /**
     * Get the invoice items for the distribution.
     */
    public function invoice_items(): HasMany
    {
        return $this->hasMany(InvoiceItemModel::class, 'plugin_tender_distributions_id', 'id');
    }

    public function getTotalNetAttribute() {
        return $this->tender_item->distribution_allocation->firstWhere('id', $this->id)['total_net'];
    }

    public function getTotalTaxAttribute() {
        return $this->tender_item->distribution_allocation->firstWhere('id', $this->id)['total_tax'];
    }

    public function getTotalGrossAttribute() {
        return $this->tender_item->distribution_allocation->firstWhere('id', $this->id)['total_gross'];
    }

    public function getMeasureValue() {

        $measures_id = $this->tender_item()->pluck('plugin_tender_measures_id');

        return $this->financial()
        ->with(['costcenter' => function ($query) use ($measures_id) {
            $query->with(['measureitems' => function ($query) use ($measures_id) {
                $query->select('plugin_tender_costcenters_id', 'plugin_tender_measures_id', 'value')
                    ->where('plugin_tender_measures_id', $measures_id);
            }]);
        }])
        ->get()
        ->pluck('costcenter.measureitems')
        ->flatten()
        ->sum('value');
    }

    public function getPercentage() {
        if ($this->plugin_tender_measures_id != 0) {
            return $this->quantity;
        } else {
            return $this->distributions()->sum('quantities');
        }
    }

}
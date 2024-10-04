<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use  Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributionModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_distributions';

    /**
     * Get the tenderitem that owns the distribution.
     */
    public function tenderitem(): BelongsTo
    {
        return $this->belongsTo(TenderItemModel::class, 'tenderitems_id', 'id');
    }

    /**
     * Get the financial that owns the distribution.
     */
    public function financial(): BelongsTo
    {
        return $this->belongsTo(FinancialModel::class, 'financials_id', 'id');
    }

    public function getMeasureValue() {

        $measures_id = $this->tenderitem()->pluck('plugin_tender_measures_id');

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

    /**
     * Get the location that owns the distribution.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(LocationModel::class, 'locations_id', 'id');
    }

    /**
     * Get the deliveryLocation that owns the distribution.
     */
    public function deliveryLocation(): BelongsTo
    {
        return $this->belongsTo(LocationModel::class, 'delivery_locations_id', 'id');
    }

    public function getPercentage() {
        if ($this->plugin_tender_measures_id != 0) {
            return $this->quantity;
        } else {
            return $this->distributions()->sum('quantities');
        }
    }

}
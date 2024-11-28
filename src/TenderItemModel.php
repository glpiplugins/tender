<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TenderItemModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_tenderitems';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    protected $fillable = [
        'plugin_tender_tenders_id',
        'entities_id',
        'name',
        'quantity',
        'net_price',
        'tax',
        'plugin_tender_catalogueitems_id',
        'plugin_tender_measures_id'
    ];

    protected $casts = [
        'net_price' => 'string',
    ];

    protected $appends = [
        'net_price_string',
        'total_net',
        'total_tax',
        'total_gross',
        'total_net_string',
        'total_tax_string',
        'total_gross_string',
        // 'distribution_allocation',
        'itemtype'
    ];

    protected $financialIds = [];
    protected $accountId = null;
    protected $costcenterIds = [];

    protected $locationsId = null;
    protected $deliveryLocationsId = null;

    protected static function boot()
    {

        parent::boot();

        static::updated(function (TenderItemModel $tenderItem) {
            $tenderItem->tender->updateFinancialItemValue();
        });

        static::deleted(function (TenderItemModel $tenderItem) {

            $tenderItem->tender->updateFinancialItemValue();
        });
    }

    /**
     * Get the tender that owns the tenderitem.
     */
    public function tender(): BelongsTo
    {
        return $this->belongsTo(TenderModel::class, 'plugin_tender_tenders_id', 'id');
    }

    /**
     * Get the measure that owns the tenderitem.
     */
    public function measure(): BelongsTo
    {
        return $this->belongsTo(MeasureModel::class, 'plugin_tender_measures_id', 'id');
    }

    /**
     * Get the distributions for the tenderitem.
     */
    public function distributions(): HasMany
    {
        $distributions = $this->hasMany(DistributionModel::class, 'plugin_tender_tenderitems_id', 'id');
        

        foreach ($distributions as $distribution) {

        }

        return $this->hasMany(DistributionModel::class, 'plugin_tender_tenderitems_id', 'id');
    }

    /**
     * Get the offer_items for the tender_item.
     */
    public function offer_items(): HasMany
    {
        return $this->hasMany(OfferItemModel::class, 'plugin_tender_tenderitems_id', 'id');
    }

    protected function netPrice(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => $value,
            set: fn (float $value) => (int) MoneyHandler::parseFromFloat($value)->getAmount()
        );
    }

    public function totalQuantities() {
        if ($this->plugin_tender_measures_id != 0) {
            return $this->quantity;
        } else {
            return $this->distributions()->sum('quantities');
        }
    }

    public function getNetPriceStringAttribute() {
        return MoneyHandler::formatToString($this->net_price);
    }

    public function getTotalNetStringAttribute() {
        return MoneyHandler::formatToString($this->total_net);
    }

    public function getTotalTaxStringAttribute() {
        return MoneyHandler::formatToString($this->total_tax);
    }

    public function getTotalGrossStringAttribute() {
        return MoneyHandler::formatToString($this->total_gross);
    }

    public function getTotalNetAttribute() {
        return MoneyHandler::multiply($this->net_price, $this->quantity);
    }

    public function getTotalTaxAttribute() {
        return $this->tax == 0 ? 0 : MoneyHandler::multiply($this->total_net, $this->tax / 100);
    }

    public function getTotalGrossAttribute() {
        return MoneyHandler::add($this->total_net, $this->total_tax);
    }

    public function getDistributionAllocationAttribute() {

            $distributions = $this->distributions->map(function($item) {
                return [
                    'id'                      => $item->id,
                    'quantity'                => $item->quantity,
                    'net_price'               => MoneyHandler::formatToString($item->tender_item->net_price),
                    'percentage'              => $item->percentage,
                    'tax_rate'                => $this->tax,
                    'financial_id'            => $item->financial->id,
                    'financial_name'          => $item->financial->name,
                    'location_name'           => $item->location->name ?? null,
                    'delivery_location_name'  => $item->delivery_location->name ?? null,
                    'itemtype'                => "GlpiPlugin\Tender\Distribution",
                ];
            });

            $percentages = $this->distributions->map(function($item) {
                return $item->percentage;
            })->toArray();

            $allocations_total_net = $this->total_net->allocate($percentages);
            $allocations_total_tax = $this->total_tax->allocate($percentages);
            $allocations_total_gross = $this->total_gross->allocate($percentages);

            $distributions = $distributions->values()->transform(function ($distribution, $index) use ($allocations_total_net, $allocations_total_tax, $allocations_total_gross) {
                $distribution['total_net'] = $allocations_total_net[$index];
                $distribution['total_net_string'] = MoneyHandler::formatToString($allocations_total_net[$index]);
                $distribution['total_tax'] = $allocations_total_tax[$index];
                $distribution['total_tax_string'] = MoneyHandler::formatToString($allocations_total_tax[$index]);
                $distribution['total_gross'] = $allocations_total_gross[$index];
                $distribution['total_gross_string'] = MoneyHandler::formatToString($allocations_total_gross[$index]);
                return $distribution;
            });
    
        return $distributions;
    }

    // public function getTotalPercentageAttribute() {
    //     return min(100, round($this->distributions->sum('percentage'), 4));
    // }

    public function getItemTypeAttribute() {
        return 'GlpiPlugin\Tender\TenderItem';
    }

    public function totalMeasureValue() {
        return $this->distributions()
        ->with(['financial.costcenter' => function ($query) {
            $query->with(['measureitems' => function ($query) {
                $query->select('plugin_tender_costcenters_id', 'plugin_tender_measures_id', 'value')
                    ->where('plugin_tender_measures_id', $this->plugin_tender_measures_id);
            }]);
        }])
        ->get()
        ->pluck('financial.costcenter.measureitems')
        ->flatten()
        ->sum('value');
    }

    public static function create(array $attributes = [])
    {
        $costcenterIds = $attributes['costcenterIds'] ?? [];
        unset($attributes['costcenterIds']);
        
        $accountId = $attributes['accountId'] ?? [];
        unset($attributes['accountId']);

        $locationsId = $attributes['locations_id'] ?? [];
        unset($attributes['locations_id']);

        $deliveryLocationsId = $attributes['delivery_locations_id'] ?? [];
        unset($attributes['delivery_locations_id']);

        $tenderItem = static::query()->create($attributes);

        $tenderItem->costcenterIds = $costcenterIds;
        $tenderItem->accountId = $accountId;
        $tenderItem->locationsId = $locationsId;
        $tenderItem->deliveryLocationsId = $deliveryLocationsId;

        $tenderItem->saveDistributions();

        return $tenderItem;
    }

    public function updateQuantity() {
        if ($this->plugin_tender_measures_id != 0) {
            return;
        }
        $this->quantity = $this->distributions->sum('quantity');
        if($this->quantity == 0) {
            $this->delete();
        } else {
            $this->save();
        }
        
    }

    public function saveDistributions()
    {

        $total = MeasureItemModel::where('plugin_tender_measures_id', $this->plugin_tender_measures_id)
        ->whereIn('plugin_tender_costcenters_id', $this->costcenterIds)->sum('value');

        $totalPercentage = 0;
        $totalCostcenterCount = count($this->costcenterIds);
        $counter = 0;
        
        foreach ($this->costcenterIds as $costcenterId) {
            $counter++;
            // Find existing financial if exits, if not create a new one
            $financial = FinancialModel::where('plugin_tender_costcenters_id', $costcenterId)
                ->where('plugin_tender_accounts_id', $this->accountId)
                ->first()
                ??
                FinancialModel::create([
                    'plugin_tender_costcenters_id'  => $costcenterId,
                    'plugin_tender_accounts_id'     => $this->accountId
                ])->first();

            // Get percentage according to measure
            if ($this->plugin_tender_measures_id != 0) {
                $value = MeasureItemModel::where('plugin_tender_measures_id', $this->plugin_tender_measures_id)
                    ->where('plugin_tender_costcenters_id', $costcenterId)->first()->value;

                $totalPercentage += $percentage = floor($value / $total * 100) ?? 100;
                
                if ($counter == $totalCostcenterCount && $totalPercentage !== 100) {
                    $percentage += 100 - $totalPercentage; 
                }
            } else {
                $percentage = 100;
            }
            
            $distribution = DistributionModel::create([
                'plugin_tender_tenderitems_id' => $this->id,
                'plugin_tender_financials_id'  => $financial->id,
                'quantity'                     => $this->quantity,
                'locations_id'                 => $this->locationsId,
                'delivery_locations_id'        => $this->deliveryLocationsId,
                'percentage'                   => $percentage,
            ]);
        }
        
        $this->tender->updateFinancialItemValue();
    }

}
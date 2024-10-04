<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderItemModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_tenderitems';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    /**
     * Get the tender that owns the tenderitem.
     */
    public function tender(): BelongsTo
    {
        return $this->belongsTo(TenderModel::class, 'tenders_id', 'id');
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
        return $this->hasMany(DistributionModel::class, 'tenderitems_id', 'id');
    }

    public function totalQuantities() {
        if ($this->plugin_tender_measures_id != 0) {
            return $this->quantity;
        } else {
            return $this->distributions()->sum('quantities');
        }
    }

    public function totalNetValue() {
        return $this->quantity * $this->net_price;
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


    public function calculateDistributions()
    {
        $this->load(['distributions.financial', 'distributions.location', 'distributions.deliveryLocation']);
        $tenderItem = $this;

        $totalNetPrice = 0;
        $totalTax = 0;
        $totalGrossPrice = 0;
        $financialIds = [];

        $this->distributions->each(function ($distribution) use ($tenderItem, &$totalNetPrice, &$totalTax, &$totalGrossPrice, &$financialIds) {
            if ($tenderItem->plugin_tender_measures_id != 0) {
                $calculatedQuantity = $tenderItem->quantity;
            } else {
                $calculatedQuantity = $distribution->quantity;
            }

            $distribution->itemtype = 'GlpiPlugin\Tender\Distribution';
            $distribution->tax_rate = $tenderItem->tax . ' %';
            $distribution->net_price = round($tenderItem->net_price, 2);
            $distribution->net_price_calculated = round($tenderItem->net_price * $calculatedQuantity * ($distribution->percentage / 100), 2);
            $distribution->tax = round($distribution->net_price_calculated * ($tenderItem->tax / 100), 2);
            $distribution->gross_price_calculated = round($distribution->net_price_calculated * (1 + $tenderItem->tax / 100), 2);

            $financial = optional($distribution->financial);
            if ($financial) {
                $financialIds[] = $financial->id;
                $distribution->financial_name = $financial->name;
            }

            $distribution->location_name = optional($distribution->location)->completename;
            $distribution->delivery_location_name = optional($distribution->deliveryLocation)->completename;

            $totalNetPrice += $distribution->net_price_calculated;
            $totalTax += $distribution->tax;
            $totalGrossPrice += $distribution->gross_price_calculated;
        });

        // Korrektur der Summenwerte, um Rundungsfehler zu vermeiden
        $expectedNetPrice = round($tenderItem->net_price * $tenderItem->quantity, 2);
        $expectedTax = round($expectedNetPrice * ($tenderItem->tax / 100), 2);
        $expectedGrossPrice = round($expectedNetPrice * (1 + $tenderItem->tax / 100), 2);

        $roundingDifferenceNetPrice = $expectedNetPrice - $totalNetPrice;
        $roundingDifferenceTax = $expectedTax - $totalTax;
        $roundingDifferenceGrossPrice = $expectedGrossPrice - $totalGrossPrice;

        if ($this->distributions->isNotEmpty()) {
            $lastDistribution = $this->distributions->last();
            $lastDistribution->net_price_calculated += $roundingDifferenceNetPrice;
            $lastDistribution->tax += $roundingDifferenceTax;
            $lastDistribution->gross_price_calculated += $roundingDifferenceGrossPrice;
        }

        $this->total_net_price = round($this->distributions->sum('net_price_calculated'), 2);
        $this->total_tax = round($this->distributions->sum('tax'), 2);
        $this->total_gross_price = round($this->distributions->sum('gross_price_calculated'), 2);
        $this->total_percentage = min(100, round($this->distributions->sum('percentage'), 4));
        $this->financialIds = $financialIds;
        return $this;
    }

    public function updateQuantity() {
        $this->quantity = $this->distributions->sum('quantity');
        $this->save();
    }
}
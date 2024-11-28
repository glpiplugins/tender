<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class TenderModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_tenders';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    protected $appends = [
        'total_net',
        'total_tax',
        'total_gross',
        'total_net_string',
        'total_tax_string',
        'total_gross_string'
    ];

    /**
     * Get the tender items for the tender.
     */
    public function tender_items(): HasMany
    {
        return $this->hasMany(TenderItemModel::class, 'plugin_tender_tenders_id', 'id');
    }

    /**
     * Get the financial items for the tender.
     */
    public function financial_items(): HasMany
    {
        return $this->hasMany(FinancialItemModel::class, 'plugin_tender_tenders_id', 'id');
    }

    /**
     * Get the distributions for the tender.
     */
    public function distributions(): hasManyThrough
    {
        return $this->hasManyThrough(
            DistributionModel::class,
            TenderItemModel::class,
            'plugin_tender_tenders_id',
            'plugin_tender_tenderitems_id',
            'id',
            'id'
        );
    }

    public function getTotalNetAttribute() {
        return MoneyHandler::sum($this->tender_items->pluck('total_net')->toArray());
    }

    public function getTotalTaxAttribute() {
        return MoneyHandler::sum($this->tender_items->pluck('total_tax')->toArray());
    }

    public function getTotalGrossAttribute() {
        return MoneyHandler::sum($this->tender_items->pluck('total_gross')->toArray());
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

    public function updateFinancialItemValue()
    {
        FinancialItemModel::where('plugin_tender_tenders_id', $this->id)->delete();

        $allocations = collect([]);
        $tender_items = $this->tender_items->each(function (TenderItemModel $item, int $key) use (&$allocations) {
            $allocations->push($item->distribution_allocation);
        });
        $test = $allocations->collapse()->groupBy('financial_id')->map(function ($group, $financialId) {
            return FinancialItemModel::updateOrCreate(
                [
                    'plugin_tender_financials_id' => $financialId,
                    'plugin_tender_tenders_id'    => $this->id,
                ],
                [
                    'value' => MoneyHandler::sum($group->pluck('total_gross')->toArray())->getAmount() / 100,
                ]
            );
        });
        // $distributions = $this->distributions;
        // $groupedDistributions = $distributions->groupBy('plugin_tender_financials_id');
        // // print('<pre>');
        // // print($this);
        // // // print($groupedDistributions);
        // // print('</pre>');
        // //     die();
        // FinancialItemModel::where('plugin_tender_tenders_id', $this->id)->delete();
        // foreach ($groupedDistributions as $financialId => $group) {

        //     $newValue = $group->sum(function ($distribution) {
        //         $tenderItem = $distribution->tender_item;

        //         if (!$tenderItem) {
        //             return 0;
        //         }

        //         // Berechnung des Brutto-Einheitspreises
        //         $unitPriceGross = $tenderItem->net_price * (1 + ($tenderItem->tax / 100));

        //         // Berechnung des Werts dieser Distribution
        //         $value = $distribution->quantity * $unitPriceGross;

        //         return $value;
        //     });

        //     // Aktualisiere oder erstelle den FinancialItem
        //     FinancialItemModel::updateOrCreate(
        //         [
        //             'plugin_tender_financials_id' => $financialId,
        //             'plugin_tender_tenders_id'    => $this->id,
        //         ],
        //         [
        //             'value' => $newValue,
        //         ]
        //     );

        // }
    }

}
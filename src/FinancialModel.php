<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_financials';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    protected $fillable = [
        'name',
        'plugin_tender_costcenters_id',
        'plugin_tender_accounts_id'
    ];

    /**
     * Get the costcenter that owns the financial.
     */
    public function costcenter(): BelongsTo
    {
        return $this->belongsTo(CostcenterModel::class, 'plugin_tender_costcenters_id', 'id');
    }

    /**
     * Get the account that owns the financial.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountModel::class, 'plugin_tender_accounts_id', 'id');
    }

    /**
     * Get the distributions for the financial.
     */
    public function distributions(): HasMany
    {
        return $this->hasMany(DistributionModel::class, 'plugin_tender_financials_id', 'id');
    }
    
    /**
     * Get the financial items for the financial.
     */
    public function financial_items(): HasMany
    {
        return $this->hasMany(FinancialItemModel::class, 'plugin_tender_financials_id', 'id');
    }

    public function getTotalNetAttribute() {
        return MoneyHandler::sum($this->tender_items->pluck('total_net')->toArray());
    }

    /**
     * Get the available total for the financial
     */
    public function getTotalAvailableAttribute()
    {
        $values = $this->financial_items->map(function ($item) {
            return $item->type == 0 ? $item->value *-1 : $item->value;
        })->toArray();
       
        return MoneyHandler::sum($values);

        return $this->financial_items->sum(function($item) {
            return $item->type == 1 ? $item->value : -$item->value;
        });
    }
}
<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Casts\Attribute;

class FinancialItemModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_financialitems';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    protected $fillable = [
        'plugin_tender_financials_id',
        'plugin_tender_tenders_id',
        'type',
        'value'
    ];

    /**
     * Get the costcenter that owns the financial item.
     */
    public function financial(): BelongsTo
    {
        return $this->belongsTo(FinancialModel::class, 'plugin_tender_financials_id', 'id');
    }

    /**
     * Get the tender that owns the financial item.
     */
    public function tender(): BelongsTo
    {
        return $this->belongsTo(TenderModel::class, 'plugin_tender_tenders_id', 'id');
    }
    
    /**
     * Get all of the distributions for the finacial.
     */
    public function distributions(): HasManyThrough
    {
        return $this->hasManyThrough(DistributionModel::class, FinancialModel::class);
    }

    /**
     * Get the costcenter.
     */
    public function costcenter(): HasOneThrough
    {
        return $this->hasOneThrough(
            CostcenterModel::class, 
            FinancialModel::class,
            'plugin_tender_costcenters_id',
            'id',
            'plugin_tender_financials_id',
            'id'
        );
    }

    /**
     * Get the account.
     */
    public function account(): HasOneThrough
    {
        return $this->hasOneThrough(
            AccountModel::class,
            FinancialModel::class,
            'plugin_tender_accounts_id',
            'id',
            'plugin_tender_financials_id',
            'id'
        );
    }

    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => $value,
            set: fn (float $value) => (int) MoneyHandler::parseFromFloat($value)->getAmount()
        );
    }

}
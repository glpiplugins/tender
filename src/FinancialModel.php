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

    /**
     * Get the costcenter that owns the financial.
     */
    public function costcenter(): BelongsTo
    {
        return $this->belongsTo(CostcenterModel::class, 'plugin_tender_costcenters_id', 'id');
    }

    /**
     * Get the distributions for the financial.
     */
    public function distributions(): HasMany
    {
        return $this->hasMany(DistributionModel::class, 'financials_id', 'id');
    }
    
}
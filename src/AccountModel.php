<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_accounts';

    /**
     * Get the financials for the account.
     */
    public function financials(): HasMany
    {
        return $this->hasMany(FinancialModel::class, 'plugin_tender_accounts_id', 'id');
    }

    /**
     * Get the measureitems for the costcenter.
     */
    public function measureitems(): HasMany
    {
        return $this->hasMany(MeasureItemModel::class, 'plugin_tender_costcenters_id', 'id');
    }

}
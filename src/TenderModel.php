<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenderModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_tenders';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    /**
     * Get the tenderitems for the tender.
     */
    public function tenderitems(): HasMany
    {
        return $this->hasMany(TenderItemModel::class, 'tenders_id', 'id');
    }

}
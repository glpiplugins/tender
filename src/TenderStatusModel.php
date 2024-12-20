<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenderStatusModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_tenderstatuses';

    /**
     * Get the tender items for the tender.
     */
    public function tender_items(): HasMany
    {
        return $this->hasMany(TenderItemModel::class, 'plugin_tender_tenders_id', 'id');
    }
}
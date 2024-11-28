<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeasureModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_measures';

    /**
     * Get the measureitems for the measure.
     */
    public function measure_items(): HasMany
    {
        return $this->hasMany(MeasureItemModel::class, 'plugin_tender_measures_id', 'id');
    }

    /**
     * Get the tenderitems for the measure.
     */
    public function tender_items(): HasMany
    {
        return $this->hasMany(TenderItemModel::class, 'plugin_tender_measures_id', 'id');
    }

}
<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use  Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeasureItemModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_measureitems';

    /**
     * Get the measure that owns the measureitem.
     */
    public function measure(): BelongsTo
    {
        return $this->belongsTo(MeasureModel::class, 'plugin_tender_measures_id', 'id');
    }

    /**
     * Get the costcenter that owns the measureitem.
     */
    public function costcenter(): BelongsTo
    {
        return $this->belongsTo(CostcenterModel::class, 'plugin_tender_costcenters_id', 'id');
    }

}
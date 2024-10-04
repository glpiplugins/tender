<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocationModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_locations';

    /**
     * Get the distributions for the location.
     */
    public function distributions(): HasMany
    {
        return $this->hasMany(DistributionsModel::class, 'locations_id', 'id');
    }

    /**
     * Get the distributions for the location.
     */
    public function distributionsDelivery(): HasMany
    {
        return $this->hasMany(DistributionsModel::class, 'delivery_locations_id', 'id');
    }

}
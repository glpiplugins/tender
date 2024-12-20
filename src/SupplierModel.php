<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_suppliers';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    /**
     * Get the offers for the supplier.
     */
    public function offers(): HasMany
    {
        return $this->hasMany(OfferModel::class, 'suppliers_id', 'id');
    }

}
<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use  Illuminate\Database\Eloquent\Relations\BelongsTo;
use  Illuminate\Database\Eloquent\Relations\HasMany;
use  Illuminate\Database\Eloquent\Relations\HasOneThrough;

class InvoiceItemModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_invoiceitems';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    protected $fillable = [
        'plugin_tender_invoices_id',
        'plugin_tender_distributions_id',
        'quantity'
    ];

    protected $appends = ['total_net', 'total_tax', 'total_gross'];

    /**
     * Get the invoice that owns the invoice item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceModel::class, 'plugin_tender_invoices_id', 'id');
    }

    /**
     * Get the distribution that owns the invoice item.
     */
    public function distribution(): BelongsTo
    {
        return $this->belongsTo(DistributionModel::class, 'plugin_tender_distributions_id', 'id');
    }

    /**
     * Accessor for tender_item via distribution.
     */
    public function getTenderItemAttribute()
    {
        return $this->distribution ? $this->distribution->tender_item : null;
    }

    public function getTotalNetAttribute() {
        return $this->quantity * $this->tender_item->net_price;
    }

    public function getTotalTaxAttribute() {
        $taxRate = $this->tender_item->tax;

        return $taxRate == 0 ? 0 : ($this->total_net) * ($taxRate / 100);
    }

    public function getTotalGrossAttribute() {
        return $this->total_net + $this->total_tax;
    }

}
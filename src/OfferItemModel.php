<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class OfferItemModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_offeritems';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    protected $fillable = [
        'net_price',
        'tax',
        'plugin_tender_offers_id',
        'plugin_tender_tenderitems_id'
    ];

    protected $appends = [
        'total_net',
        'total_tax',
        'total_gross',
        'itemtype'
    ];

    /**
     * Get the offer that owns the offer_item.
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(OfferModel::class, 'plugin_tender_offers_id', 'id');
    }

    /**
     * Get the tender_item that owns the offer_item.
     */
    public function tender_item(): BelongsTo
    {
        return $this->belongsTo(TenderItemModel::class, 'plugin_tender_tenderitems_id', 'id');
    }

    protected function netPrice(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => $value,
            set: fn (float $value) => (int) MoneyHandler::parseFromFloat($value)->getAmount()
        );
    }

    public function getTotalNetAttribute() {
        return MoneyHandler::multiply($this->net_price, $this->tender_item->quantity);
    }

    public function getTotalTaxAttribute() {
        return $this->tax == 0 ? 0 : MoneyHandler::multiply($this->total_net, $this->tax / 100);
    }

    public function getTotalGrossAttribute() {
        return MoneyHandler::add($this->total_net, $this->total_tax);
    }

    public function getItemTypeAttribute() {
        return 'GlpiPlugin\Tender\OfferItem';
    }

}
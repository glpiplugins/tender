<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class InvoiceModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_invoices';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    protected $fillable = [
        'plugin_tender_tenders_id',
        'name',
        'invoice_date',
        'internal_reference',
        'posting_text',
        'due_date'
    ];

    protected $appends = ['total_net', 'total_tax', 'total_gross', 'financials'];

    /**
     * Get the tender that owns the delivery.
     */
    public function tender(): BelongsTo
    {
        return $this->belongsTo(TenderModel::class, 'plugin_tender_tenders_id', 'id');
    }

    /**
     * Get the invoice items for the delivery.
     */
    public function invoice_items(): HasMany
    {
        return $this->hasMany(InvoiceItemModel::class, 'plugin_tender_invoices_id', 'id');
    }

    public function getTotalNetAttribute() {
        return $this->invoice_items->sum('total_net');
    }

    public function getTotalTaxAttribute() {
        return $this->invoice_items->sum('total_tax');
    }

    public function getTotalGrossAttribute() {
        return $this->invoice_items->sum('total_gross');
    }

    public function getDocumentTemplate() {
        return [
            ['name'  => 'invoice_number',   'type'  => 'string',    'value' => ''],
            ['name'  => 'invoice_date',     'type'  => 'date',      'value' => ''],
            ['name'  => 'due_date',         'type'  => 'date',      'value' => ''],
            ['name'  => 'total_gross',      'type'  => 'string',    'value' => ''],
            ['name'  => 'posting_text',     'type'  => 'string',    'value' => ''],
            ['name'  => 'current_date',     'type'  => 'date',      'value' => ''],
            ['name'  => 'financials',       'type'  => 'array',     'value' =>  []]
        ];
    }

    public function getDocumentData() {
        return [
            'invoice_number'    => $this->name,
            'invoice_date'      => $this->invoice_date,
            'due_date'          => $this->due_date,
            'total_gross'       => number_format(
                                    $this->total_gross,
                                    2, // decimals
                                    ',', // decimal_seperator
                                    '.' // thousands_separator
                                ),
            'posting_text'      => $this->posting_text,
            'current_date'      => date('Y-m-d'),
            'financials'        => $this->financials
        ];
    }

    public function getFinancialsAttribute() {

        $fmt = numfmt_create( 'de_DE', \NumberFormatter::CURRENCY );

        $this->invoice_items->loadMissing([
            'distribution.financial.costcenter',
            'distribution.financial.account'
        ]);

        return $this->invoice_items
                    ->groupBy(function($invoiceItem) {
                        return $invoiceItem->distribution->financial->id;
                    })
                    ->map(function($group, $financialId) {
                        $financial = $group->first()->distribution->financial;
                        return [
                            'plugin_tender_financials_id'   => $financialId,
                            'costcenter'                    => $financial->costcenter->name ?? null,
                            'account'                       => $financial->account->name ?? null,
                            'reference'                     => $financial->reference ?? null,
                            'total_gross'                   => number_format(
                                $group->sum('total_gross'),
                                2, // decimals
                                ',', // decimal_seperator
                                '.' // thousands_separator
                            )
                        ];
                    })
                    ->values() // Optional: Setze die Schlüssel zurück
                    ->toArray();
    }

}
<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use  Illuminate\Database\Eloquent\Relations\BelongsTo;
use  Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTemplateParameterModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_documenttemplate_parameters';

    const CREATED_AT = 'date_creation';
    const UPDATED_AT = 'date_mod';

    protected $fillable = [
        'plugin_tender_documenttemplates_id',
        'name',
        'type',
        'value'
    ];

    /**
     * Get the tender item that owns the distribution.
     */
    public function document_template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplateModel::class, 'plugin_tender_documenttemplates_id', 'id');
    }

}
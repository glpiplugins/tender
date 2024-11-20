<?php 

namespace GlpiPlugin\Tender;

// include_once ("../../inc/includes.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTemplateModel extends \Illuminate\Database\Eloquent\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glpi_plugin_tender_documenttemplates';

    /**
     * Get the parameters for the document template.
     */
    public function parameters(): HasMany
    {
        return $this->hasMany(DocumentTemplateParameterModel::class, 'plugin_tender_documenttemplates_id', 'id');
    }
    
}
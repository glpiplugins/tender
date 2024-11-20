<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Entity;
use MassiveAction;
use Html;
use Glpi\Application\View\TemplateRenderer;

class DocumentTemplateParameter extends CommonDBTM   {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
      
        return __('Document Template Parameter', 'tender');
    }

   //  static function getIcon() {
   //      return "fas fa-credit-card";
   //   }

   public function getTabNameForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\DocumentTemplate') {
          // Hier können Sie prüfen, ob der Benutzer die Rechte hat, den Tab zu sehen
          // und entsprechend den Namen zurückgeben oder false, wenn der Tab nicht angezeigt werden soll
          return __("Parameter", "tender");
      }
      return '';
  }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'GlpiPlugin\Tender\DocumentTemplate') {
         // Hier generieren Sie den Inhalt, der im Tab angezeigt werden soll
         self::showList($item);
      }
   }

     public function showForm($ID, array $options = []) {
        global $CFG_GLPI;

        $this->initForm($ID, $options);
        
        TemplateRenderer::getInstance()->display('@tender/documenttemplateparamterForm.html.twig', [
            'item'   => $this,
            'params' => $options
        ]);

        return true;
     }

    static function showList($documentTemplate) {

        global $DB;
        global $CFG_GLPI;
    
        $parameters = DocumentTemplateParameterModel::where('plugin_tender_documenttemplates_id', $documentTemplate->getID())->get()->toArray();
        
        TemplateRenderer::getInstance()->display('@tender/documenttemplateparamterList.html.twig', [
            'item'   => $documentTemplate,
            'paramters' => $parameters,
            'types' => [
                'string' => __('string', 'tender'),
                'checkbox' => __('checkbox', 'tender')
            ],
            'is_tab' => true,
            'filters' => [],
            'nofilter' => true,
            'columns' => [
               'name' => __('Name', 'tender'),
               'type' => __('Type', 'tender'),
               'value' => __('Value', 'tender'),
               'view_details' => __('View Detail', 'tender')
           ],
           'formatters' => [
                  'view_details' => 'raw_html'
            ],
            'total_number' => count($parameters),
            'entries' => $parameters,
            'used' => array_column($parameters, 'id'),
            'showmassiveactions'    => true,
            'massiveactionparams' => [
                'num_displayed'    => min($_SESSION['glpilist_limit'], count($parameters)),
                'container'        => 'massGlpiPluginTenderFinancialItem' . mt_rand(),
                'specific_actions' => [
                    // 'delete' => __('Delete permanently'),
                    DocumentTemplateParameter::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete' => __('Disconnect', 'tender'),
                ]
            ],
        ]);
  
        return true;
    }

    public function rawSearchOptions() {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '2',
           'table'              => $this::getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'searchtype'         => 'contains',
           'displaytype'        => 'text',
           'massiveaction'      => false
        ];
    
        $tab[] = [
            'id'                 => '3',
            'table'              => $this::getTable(),
            'field'              => 'type',
            'name'               => __('Type'),
            'datatype'           => 'text',
            'displaytype'        => 'text',
            'massiveaction'      => true,
            'injectable'         => true
         ];


        $tab[] = [
            'id'                 => '4',
            'table'              => $this::getTable(),
            'field'              => 'value',
            'name'               => __('Value'),
            'datatype'           => 'text',
            'displaytype'        => 'text',
            'massiveaction'      => false,
            'injectable'         => true
         ];
    
        return $tab;

    }

    static function showMassiveActionsSubForm(MassiveAction $ma) {

        switch ($ma->getAction()) {
        case 'delete':
            echo Html::submit(__('Post'), array('name' => 'massiveaction'))."</span>";
  
            return true;
        }
        return parent::showMassiveActionsSubForm($ma);
    }
  
    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                        array $ids) {
        global $DB;
  
        switch ($ma->getAction()) {
            case 'delete' :
                $input = $ma->getInput();
  
                foreach ($ids as $id) {
                 
                    if ($item->getFromDB($id)
                        && $item->deleteFromDB()) {
                    
                    $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                    } else {
                    $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                    $ma->addMessage(__("Something went wrong"));
                    }
                }
                return;
  
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

}
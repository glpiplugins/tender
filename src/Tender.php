<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use Glpi\Application\View\TemplateRenderer;

class Tender extends CommonDBTM  {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
        return __('Tender', 'tender');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if (!$withtemplate) {
            $tabs = [];
            if ($item->getType() == __CLASS__) {
                $tabs[1] = self::getTypeName(1);
            }
            return $tabs;
        }
        return '';
    }

    // public static function getMenuContent() {
    //     $menu  = [];
    //     $menu['links']['search'] = self::getSearchURL(false);
  
    //     return $menu;
    //  }
    
     static function getIcon() {
        return "fas fa-shopping-cart";
     }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == __CLASS__) {
            switch ($tabnum) {
                case 1:
                    $item->showForm($item->getID());
                    break;
            }
        }
        return true;
    }

    // public function showForm($ID, $options = []) {
    //     global $CFG_GLPI;
    
    //     // Überprüfen Sie die Berechtigungen
    //     //$this->check($ID, READ);
    
    //     // Beginnen Sie das Formular
    //     echo "<form name='form' method='post' action='" . $CFG_GLPI["root_doc"] . "/plugins/tender/front/tender.form.php'>";
    
    //     // Zeigen Sie den Formularkopf an (dies fügt den "Add" Button hinzu)
    //     $this->showFormHeader($options);
    
    //     // ... (Hier können Sie weitere Formularfelder hinzufügen)
    
    //     // Beenden Sie das Formular
    //     $this->showFormButtons($options);
    
    //     return true;
    // }
    
    public function showForm($ID, array $options = []) {
        global $CFG_GLPI;

        $this->initForm($ID, $options);
        
        TemplateRenderer::getInstance()->display('@tender/tender.html.twig', [
            'item'   => $this,
            'params' => $options,
        ]);

        return true;
     }


    function showTable() {
        // Hier können Sie Ihre Tabelle anzeigen, die mit Twig erstellt wurde.
        // Verwenden Sie die Twig-Engine, um Ihre Vorlage zu rendern.
    }

    public function defineTabs($options = []) {
        $ong = [];
  
           $this->addDefaultFormTab($ong);
           $this->addStandardTab('Ticket', $ong, $options);
  
        if (!$this->isNewID($this->fields['id'])) {
           $this->addDefaultFormTab($ong);
           $this->addStandardTab('Document_Item', $ong, $options);
           $this->addStandardTab('Notepad', $ong, $options);
           $this->addStandardTab('Log', $ong, $options);
        }
  
        return $ong;
     }

    function prepareInputForAdd($input) {
        // Hier können Sie die Eingabedaten vor dem Hinzufügen vorbereiten.
        return $input;
    }

    function prepareInputForUpdate($input) {
        // Hier können Sie die Eingabedaten vor der Aktualisierung vorbereiten.
        return $input;
    }

    public function rawSearchOptions() {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '2',
           'table'              => $this::getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'searchtype'         => 'contains',
           'massiveaction'      => false
        ];
  
        $tab[] = [
            'id'                 => '3',
            'table'              => $this::getTable(),
            'field'              => 'name',
            'name'               => __('Name'),
            'datatype'           => 'string',
            'massiveaction'      => false
        ];
  
        $tab[] = [
           'id'                 => '4',
           'table'              => $this::getTable(),
           'field'              => 'tender_subject',
           'name'               => __('Tender Subject'),
           'datatype'           => 'string',
           'massiveaction'      => false
        ];
  
        $tab[] = [
           'id'                 => '5',
           'table'              => 'glpi_entities',
           'field'              => 'completename',
           'name'               => Entity::getTypeName(1),
           'datatype'           => 'dropdown',
           'massiveaction'      => false
        ];
  
        $tab[] = [
           'id'                 => '6',
           'table'              => $this::getTable(),
           'field'              => 'is_recursive',
           'name'               => __('Recursive'),
           'datatype'           => 'bool',
           'massiveaction'      => true
        ];
  
        $tab[] = [
           'id'                 => '7',
           'table'              => $this::getTable(),
           'field'              => 'language',
           'name'               => __('Language'),
           'datatype'           => 'specific',
           'searchtype'         => [
              '0'                  => 'equals'
           ],
           'massiveaction'      => true
        ];
  
        return $tab;

    }

}

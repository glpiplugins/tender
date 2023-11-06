<?php

class PluginTenderTender extends CommonDBTM {

    static $rightname = 'plugin_tender';

    static function getTypeName($nb = 0) {
        return _n('Tender', 'Tenders', $nb, 'tender');
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

    public function showForm($ID, $options = []) {
        global $CFG_GLPI;
    
        // Überprüfen Sie die Berechtigungen
        $this->check($ID, READ);
    
        // Beginnen Sie das Formular
        echo "<form name='form' method='post' action='" . $CFG_GLPI["root_doc"] . "/plugins/tender/front/tender.form.php'>";
    
        // Zeigen Sie den Formularkopf an (dies fügt den "Add" Button hinzu)
        $this->showFormHeader($options);
    
        // ... (Hier können Sie weitere Formularfelder hinzufügen)
    
        // Beenden Sie das Formular
        $this->showFormButtons($options);
    
        return true;
    }
    

    function showTable() {
        // Hier können Sie Ihre Tabelle anzeigen, die mit Twig erstellt wurde.
        // Verwenden Sie die Twig-Engine, um Ihre Vorlage zu rendern.
    }

    function defineTabs($options = []) {
        $tabs = parent::defineTabs($options);
        $tabs['Tenders'] = self::getTypeName(2);
        return $tabs;
    }

    function prepareInputForAdd($input) {
        // Hier können Sie die Eingabedaten vor dem Hinzufügen vorbereiten.
        return $input;
    }

    function prepareInputForUpdate($input) {
        // Hier können Sie die Eingabedaten vor der Aktualisierung vorbereiten.
        return $input;
    }
}

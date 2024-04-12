<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use MassiveAction;
use Session;
use Config as GlpiConfig;
use Toolbox;
use Glpi\Application\View\TemplateRenderer;

class Config extends CommonDBTM   {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
        return __('Config', 'tender');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

        if (!$withtemplate) {
           if ($item->getType() == 'Config') {
              return __('Tender plugin');
           }
        }
        return '';
     }

    static function getIcon() {
        return "fas fa-file-invoice-dollar";
    }

    public function showFormTender($options = [])
    {
       
        global $CFG_GLPI;

        $this->getFromDB(1);
        $configValues = GlpiConfig::getConfigurationValues('plugin:tender');
        $options = [
            'target' => '/plugins/tender/front/config.form.php',
            'formOptions' => "enctype='multipart/form-data'"
        ];
        
        TemplateRenderer::getInstance()->display('@tender\config.html.twig', [
            'item'   => $this,
            'params' => $options,
            'class' => __CLASS__,
            'configValues' => $configValues
        ]);

        return true;

    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

        if ($item->getType() == 'Config') {
           $config = new self();
           $config->showFormTender();
        }
     }
}
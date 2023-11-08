<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Entity;
use Glpi\Application\View\TemplateRenderer;

class OtherItem extends CommonDBTM  {

    static $rightname = 'networking';

    static function getTypeName($nb = 0) {
      
        return __('Other Item', 'Other Item');
    }

}

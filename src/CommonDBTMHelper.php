<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use CommonDropdown;
use Html;
use Entity;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;

class CommonDBTMHelper extends CommonDBTM  {

    static function toArray($iterator) : array {
        $result = [];
        foreach ($iterator as $item) {
            $result = $item;
        }

        return $result;
    }


}
<?php

namespace GlpiPlugin\Tender;

use CommonDBTM;
use CommonGLPI;
use Html;
use Session;
use GlpiPlugin\Tender\Tender;
use Glpi\Application\View\TemplateRenderer;

class Menu extends CommonGLPI

{
    public static $rightname = 'entity';

    public static function getMenuName()
    {
        return __("Tender", "tender");
    }

    public static function getIcon()
    {
        return "fas fa-shopping-cart";
    }

    public static function getMenuContent() {
        $menu  = parent::getMenuContent();
        $menu['links']['search'] = Tender::getSearchURL(false);
  
        return $menu;
     }
  
}

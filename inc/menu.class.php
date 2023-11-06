<?php

class PluginTenderMenu extends CommonGLPI

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

    public static function getMenuContent()
    {
        if (!Session::haveRight('entity', READ)) {
            return;
        }

        $menu = [
            'title' => self::getMenuName(),
            'page'  =>  "/plugins/tender/front/tender.php",
            'icon'  => self::getIcon(),
            'content' => true
        ];

        $itemtypes = ['PluginTenderTender' => 'tender'];

        foreach ($itemtypes as $itemtype => $option) {
            $menu['options'][$option] = [
                'title' => $itemtype::getTypeName(2),
                'page'  => $itemtype::getSearchURL(false),
                'links' => [
                    'search' => $itemtype::getSearchURL(false)
                ]
            ];

            if ($itemtype::canCreate()) {
                $menu['options'][$option]['links']['add'] = $itemtype::getFormURL(false);
            }
        }
        return $menu;
    }
}

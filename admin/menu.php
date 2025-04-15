<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$module_id = "fosdev.fosreviews";

if($APPLICATION->GetGroupRight($module_id) >= "R") {
    $menu = array(
        array(
            "parent_menu" => "global_menu_services",
            "section" => $module_id,
            "sort" => 100,
            "text" => Loc::getMessage("REVIEWS_MENU_MAIN"),
            "icon" => "form_menu_icon",
            "page_icon" => "form_page_icon",
            "items_id" => "menu_{$module_id}",
            "items" => array(
                array(
                    "text" => Loc::getMessage("REVIEWS_MENU_ALL"),
                    "url" => "{$module_id}_list.php?lang=" . LANGUAGE_ID,
                    "more_url" => array(
                        "{$module_id}_edit.php"
                    ),
                    "title" => Loc::getMessage("REVIEWS_MENU_ALL_TITLE"),
                ),
                array(
                    "text" => Loc::getMessage("REVIEWS_MENU_ADD"),
                    "url" => $module_id . "_edit.php?lang=" . LANGUAGE_ID,
                    "title" => Loc::getMessage("REVIEWS_MENU_ADD_TITLE"),
                ),
            ),
        ),
    );

    return $menu;   
}

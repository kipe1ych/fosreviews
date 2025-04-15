<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentParameters = array(
    'PARAMETERS' => array(
        'CACHE_TIME' => array(
            'DEFAULT' => 3600,
        ),
        "HEADER_NAME" => array(
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("REVIEWS_LIST_HEADER_NAME"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ), 
        "DISPLAY_CUSTOMER_PHOTOS" => array(
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("REVIEWS_LIST_DISPLAY_CUSTOMER_PHOTOS"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        'PAGE_SIZE_PHOTOS' => array(
            "PARENT" => "BASE",
            'NAME' => Loc::getMessage('COUNT_PHOTOS'),
            "TYPE" => "STRING",
            'DEFAULT' => 30,
        ),
        'SHOW_RATING' => array(
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('SHOW_RATING'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),
        'LINK_AUTH' => array(
            "PARENT" => "BASE",
            'NAME' => Loc::getMessage('LINK_AUTH'),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),
        "ELEMENT_ID" => array(
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("REVIEWS_LIST_ELEMENT_ID"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),
        'NAME_BUTTON' => array(
            "PARENT" => "BASE",
            'NAME' => Loc::getMessage('NAME_BUTTON'),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),
        'PAGE_SIZE' => array(
            "PARENT" => "BASE",
            'NAME' => Loc::getMessage('COUNT'),
            "TYPE" => "STRING",
            'DEFAULT' => 10,
        ),
    ),
);


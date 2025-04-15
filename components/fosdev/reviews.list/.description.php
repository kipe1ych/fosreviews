<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = array(
    "NAME" => Loc::getMessage("REVIEWS_LIST_COMPONENT_NAME"),
    "DESCRIPTION" => Loc::getMessage("REVIEWS_LIST_COMPONENT_DESCRIPTION"),
	"ICON" => "",
	"CACHE_PATH" => "Y",
	"SORT" => 30,
    "PATH" => array(
        "ID" => "fosdev",
        "NAME" => Loc::getMessage("REVIEWS_LIST_COMPONENT_PATH_NAME"),
    ),
);

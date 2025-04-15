<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use FOS\Reviews\ReviewTable;
use FOS\Reviews\RatingTable;

Loader::includeModule('iblock');
Loader::includeModule('fileman');
Loader::includeModule('fosdev.fosreviews');

$this->arResult['ELEMENT_ID'] = (int) $arParams["ELEMENT_ID"];
$currentUri = $_SERVER['REQUEST_URI'];
$charactersToRemove = ["=", ",", ";", " ", "\t", "\r", "\n", "\013", "\014"];
$currentUri = str_replace($charactersToRemove, "", $currentUri);
setcookie('elementId'.$currentUri, $this->arResult['ELEMENT_ID'], time() + (3600*24), '/');
$this->arResult['HEADER_NAME'] = $arParams['HEADER_NAME'];
$this->arResult['DISPLAY_CUSTOMER_PHOTOS'] = $arParams['DISPLAY_CUSTOMER_PHOTOS'];
$this->arResult['PAGE_SIZE_PHOTOS'] = (int) $arParams['PAGE_SIZE_PHOTOS'];
$this->arResult['SHOW_RATING'] = $arParams['SHOW_RATING'];
$this->arResult['PAGE_SIZE'] = (int) $arParams['PAGE_SIZE'];
$this->arResult['NAME_BUTTON'] = $arParams['NAME_BUTTON'];
$this->arResult['SORT_FIELD'] = trim($arParams["SORT_FIELD"]) ?: "PUBLICATION_DATE";
$this->arResult['SORT_ORDER'] = strtoupper($arParams["SORT_ORDER"]) === "ASC" ? "ASC" : "DESC";

// Get the reviews from the database
$reviews = ReviewTable::getReviewsByProductId($this->arResult['ELEMENT_ID']);

// Assign the reviews to the component's template variables
$this->arResult["REVIEWS"] = array();
foreach($reviews as $review) {
    $data = $review;
    $rsUser = CUser::GetByID($data['USER_ID']);
    $arUser = $rsUser->Fetch();
    $data['USER_NAME'] = $arUser['NAME'];
    $data['USER_LAST_NAME'] = $arUser['LAST_NAME'];
    $data['DATE'] = ConvertTimeStamp($review['PUBLICATION_DATE'], "SHORT");
    $data['RATING'] = RatingTable::getByReviewId((int) $review['ID']);
    $this->arResult["REVIEWS"][] = $data;
}
$this->arResult["COUNT_REVIEWS"] = count($this->arResult["REVIEWS"]);

$this->arResult["IMG_LIST"] = array();
$this->arResult["COUNT_PHOTOS"] = 0;
foreach($reviews as $rev):
    $this->arResult["COUNT_PHOTOS"] += count($rev['PHOTOS']);
    foreach($rev['PHOTOS'] as $photo) {
        $this->arResult["IMG_LIST"][] = $photo['FILE_ID'];
    }
endforeach;
$this->arResult["AVERAGE"] = RatingTable::getAverageRatingByProductId($arParams["ELEMENT_ID"]);
$this->arResult["LINK_AUTH"] = $arParams["LINK_AUTH"];
$this->arResult['USER_AUTH'] = $USER->IsAuthorized();

$cases = array (2, 0, 1, 1, 1, 2);
$format = ($this->arResult["COUNT_REVIEWS"]%100 > 4 && $this->arResult["COUNT_REVIEWS"]%100 < 20) ? 2 : $cases[min($this->arResult["COUNT_REVIEWS"]%10, 5)];
$this->arResult['TEXT_REVIEW'] = $format;

// Render the component template
$this->includeComponentTemplate();
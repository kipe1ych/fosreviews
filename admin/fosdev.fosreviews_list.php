<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\AdminPageNavigation;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Loader;
use Bitrix\Main\Grid\Panel\Types;
use Bitrix\Main\Grid\Panel\Actions;
use FOS\Reviews\ReviewTable;
use FOS\Reviews\LikeTable;
use FOS\Reviews\RatingTable;

// Initialize Bitrix framework
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$moduleId = 'fosdev.fosreviews';
Loc::loadMessages(__FILE__);
Loader::includeModule($moduleId);
Loader::includeModule("iblock");

if($APPLICATION->GetGroupRight($moduleId) < "R") $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$APPLICATION->SetTitle(Loc::getMessage("REVIEWS_LIST_TITLE"));

// Load CSS and JS files
Asset::getInstance()->addCss('/bitrix/css/main/bootstrap.css');
Asset::getInstance()->addCss('/bitrix/css/main/font-awesome.css');
CJSCore::Init(array("jquery"));

// Grid and Nav
$grid_options = new Bitrix\Main\Grid\Options('fosreviews_list');

$nav_params = $grid_options->GetNavParams();
$nav = new PageNavigation('fosreviews_list');
$nav->setRecordCount(ReviewTable::getCount());
$nav->allowAllRecords(true)
    ->setPageSize($nav_params['nPageSize'])
    ->initFromUri();

// Get Total row count
$recordCount = $nav->getRecordCount();
// Get page current
$pageCurrent = $nav->getCurrentPage();
// Set all record show
if($nav->allRecordsShown()) $nav_params['nPageSize'] = $recordCount;

// Get Sort
$sort = $grid_options->GetSorting();

// Get reviews list
$listParams = array(
    'select' => array('*'),
    'count_total' => true,
    'offset' => ($pageCurrent - 1) * $nav_params['nPageSize'],
    'limit' => $nav_params['nPageSize'],
    'order' => $sort['sort'],
);

$list = ReviewTable::getList($listParams);
$reviews = [];

while($review = $list->fetch()) {
    $rating = RatingTable::getByReviewId((int) $review['ID']);
    $likes = LikeTable::getLikesCount((int) $review['ID']);
    $active = ($review['ACTIVE'])?Loc::getMessage("FOS_REVIEWS_LIST_ACTIVE_YES"):Loc::getMessage("FOS_REVIEWS_LIST_ACTIVE_NO");
    if(isset($review["PRODUCT_ID"])):
        $res = CIBlockElement::GetList(Array(), Array("ID"=>$review["PRODUCT_ID"]), false, Array("nPageSize"=>1), Array("ID", "NAME", "IBLOCK_TYPE_ID", "IBLOCK_ID"));
        while($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $productIdCol = $arFields["ID"];
            // $textproduct = '<a href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$arFields["IBLOCK_ID"].'&type='.$arFields["IBLOCK_TYPE_ID"].'&ID='.$arFields["ID"].'">'.$arFields["NAME"].'</a>';
            $textproduct = '<a href="/bitrix/admin/fosdev.fosreviews_edit.php?ID=' . $review['ID'] . '">'.$arFields["NAME"].'</a>';
        }
    endif;
    $reviews[] = [
        'data'    => [
            "ID" => $review['ID'],
            "PRODUCT_ID" => $textproduct,
            "USER_ID" => '<a href="/bitrix/admin/user_edit.php?ID=' . $review['USER_ID'] . '">' . $review['USER_ID'] . '</a>',
            "COMMENT" => '<a href="/bitrix/admin/fosdev.fosreviews_edit.php?ID=' . $review['ID'] . '">' . $review['COMMENT'] . '</a>',
            "ACTIVE" => $active,
            "RATING" => $rating['RATING'],
            "LIKES" => $likes['LIKES'],
            "DISLIKES" => $likes['DISLIKES'],
        ],
        'actions' => [
            [
                'text'    => Loc::getMessage("FOS_REVIEWS_LIST_ACTION_EDIT"),
                'onclick' => 'document.location.href="/bitrix/admin/fosdev.fosreviews_edit.php?ID='.$review['ID'].'"'
            ],
            [
                'text'    => Loc::getMessage("FOS_REVIEWS_LIST_ACTION_DELETE"),
                'onclick' => 'if(confirm("'.Loc::getMessage("FOS_REVIEWS_LIST_DELETE_CONFIRM").'")) {
                    BX.ajax.post("/bitrix/tools/fosreviews/ajax.php?action=deleteReview", {review_ids: '.$review['ID'].'}, function(result) {
                        result = JSON.parse(result);
                        if (result.success) {
                            var grid = BX.Main.gridManager.getById("fosreviews_list");
                            var reloadParams = {apply_filter: "Y", clear_nav: "N"};
                            var resPage = {};
                            resPage[grid.id] = "page-'.$pageCurrent.'";
                            grid.instance.baseUrl = BX.Grid.Utils.addUrlParams(grid.instance.baseUrl, resPage);
                            if (grid.hasOwnProperty("instance")){
                                grid.instance.reloadTable("POST", reloadParams);
                            }
                        }
                    });
                }'
            ]

        ],
    ];
}

// Render page header
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

// Render page content
$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    [
        'GRID_ID' => 'fosreviews_list',
        'COLUMNS' => [
            ['id' => 'ID', 'name' => Loc::getMessage("FOS_REVIEWS_LIST_ID"), 'sort' => 'ID', 'default' => true], 
            ['id' => 'PRODUCT_ID', 'name' => Loc::getMessage("FOS_REVIEWS_LIST_PRODUCT_ID"), 'sort' => 'PRODUCT_ID', 'default' => true], 
            ['id' => 'USER_ID', 'name' => Loc::getMessage("FOS_REVIEWS_LIST_USER_ID"), 'sort' => 'USER_ID', 'default' => true], 
            ['id' => 'COMMENT', 'name' => Loc::getMessage("FOS_REVIEWS_LIST_TEXT"), 'sort' => 'COMMENT', 'default' => true], 
            ['id' => 'ACTIVE', 'name' => Loc::getMessage("FOS_REVIEWS_LIST_ACTIVE"), 'sort' => 'ACTIVE', 'default' => true], 
            ['id' => 'RATING', 'name' => Loc::getMessage("FOS_REVIEWS_LIST_RATING"), 'sort' => 'RATING', 'default' => true], 
            ['id' => 'LIKES', 'name' => Loc::getMessage("FOS_REVIEWS_LIST_LIKES"), 'sort' => 'LIKES', 'default' => true], 
            ['id' => 'DISLIKES', 'name' => Loc::getMessage("FOS_REVIEWS_LIST_DISLIKES"), 'sort' => 'DISLIKES', 'default' => true], 
        ],
        'ROWS' => $reviews,
        'SHOW_ROW_CHECKBOXES' => true,
        'AJAX_MODE' => 'Y',
        'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
        'PAGE_SIZES' => [ 
            ['NAME' => "5", 'VALUE' => '5'], 
            ['NAME' => '10', 'VALUE' => '10'], 
            ['NAME' => '20', 'VALUE' => '20'], 
            ['NAME' => '50', 'VALUE' => '50'], 
            ['NAME' => '100', 'VALUE' => '100'] 
        ],
        'AJAX_OPTION_JUMP' => 'N',
        'ALLOW_COLUMNS_SORT'        => true,
        'ALLOW_COLUMNS_RESIZE'      => true,
        'ALLOW_HORIZONTAL_SCROLL'   => true,
        'ALLOW_SORT'                => true,
        'ALLOW_PIN_HEADER'          => true,
        'AJAX_OPTION_HISTORY'       => 'N',
        'NAV_OBJECT' => $nav, 
        'SHOW_CHECK_ALL_CHECKBOXES' => true, 
        'SHOW_ROW_ACTIONS_MENU'     => true, 
        'SHOW_GRID_SETTINGS_MENU'   => true, 
        'SHOW_NAVIGATION_PANEL'     => true, 
        'SHOW_PAGINATION'           => true, 
        'SHOW_SELECTED_COUNTER'     => true, 
        'SHOW_TOTAL_COUNTER'        => true,
        'TOTAL_ROWS_COUNT'          => $recordCount, 
        'SHOW_PAGESIZE'             => true, 
        'SHOW_ACTION_PANEL'         => true, 
        'ACTION_PANEL'              => [ 
            'GROUPS' => [ 
                'TYPE' => [ 
                    'ITEMS' => [ 
                        [ 
                            'ID'    => 'fosreviews-panel-list', 
                            'TYPE'  => 'DROPDOWN', 
                            'ITEMS' => [ 
                                ['VALUE' => '', 'NAME' => '- Выбрать -'],
                                ['VALUE' => 'delete', 'NAME' => 'Удалить'] 
                            ] 
                        ],
                        [
                            'TYPE' => Types::BUTTON,
                            'ID' => "fosreviews-delete",
                            'CLASS' => "apply",
                            'TEXT' => "Применить",
                            'ONCHANGE' => [
                                [
                                    'ACTION' => Actions::CALLBACK,
                                    'DATA' => array(
                                        array(
                                            'JS' => 'let panelBtns = document.getElementById("fosreviews-panel-list_control");
                                                    let dataValue = panelBtns.getAttribute("data-value");
                                                    if(dataValue == "delete") {
                                                        if(confirm("'.Loc::getMessage("FOS_REVIEWS_LIST_DELETE_CONFIRM_SELECTED").'")) {
                                                            var reviewIds = [];
                                                            var checkboxes = document.querySelectorAll("#fosreviews_list input.main-grid-row-checkbox:checked");
                                                            for (var i = 0; i < checkboxes.length; i++) {
                                                                reviewIds.push(checkboxes[i].value);
                                                            }

                                                            BX.ajax.post("/bitrix/tools/fosreviews/ajax.php?action=deleteReview", {review_ids: reviewIds}, function(result) {
                                                                result = JSON.parse(result);
                                                                if (result.success) {
                                                                    var grid = BX.Main.gridManager.getById("fosreviews_list");
                                                                    var reloadParams = {apply_filter: "Y", clear_nav: "N"};
                                                                    var resPage = {};
                                                                    resPage[grid.id] = "page-'.$pageCurrent.'";
                                                                    if (grid.hasOwnProperty("instance")){
                                                                        grid.instance.reloadTable("POST", reloadParams);
                                                                    }
                                                                }
                                                            });
                                                        }
                                                    }'
                                        )
                                    )
                                ]
                            ]
                        ],
                    ], 
                ] 
            ], 
        ], 
    ]
);

// Render page footer
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");

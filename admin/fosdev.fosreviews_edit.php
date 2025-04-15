<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use FOS\Reviews\ReviewTable;
use FOS\Reviews\LikeTable;
use FOS\Reviews\RatingTable;
use FOS\Reviews\PhotoTable;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$module_id = "fosdev.fosreviews";
Loc::loadMessages(__FILE__);
Loader::includeModule($module_id);
Loader::includeModule("iblock");

if($APPLICATION->GetGroupRight($moduleId) < "R") $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$id = (int)$_REQUEST["ID"];
$countphoto = 5;

if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid()) {
    if(!isset($_POST["ACTIVE"]))
        $_POST["ACTIVE"] = 0;
    else
        $_POST["ACTIVE"] = 1;

    if(!$_POST["COMMENT"]) $_POST["COMMENT"] = " ";
    $data = array(
        "ACTIVE" => $_POST["ACTIVE"],
        "USER_ID" => $_POST["USER_ID"],
        "PRODUCT_ID" => (int) $_POST["PRODUCT_ID"],
        "PUBLICATION_DATE" => strtotime($_POST["PUBLICATION_DATE"]),
        "COMMENT" => $_POST["COMMENT"],
        "RATING" => $_POST["RATING"],
        "PHOTO" => array(
            "DEL" => array(),
            "ADD" => array()
        ),
    );

    $photodel = $_POST["PHOTO_del"];
    $photoid = $_POST["PHOTO"]["ids"];
    $photoadd = $_FILES["PHOTO"];
        
    for($i=0; $i<$countphoto; $i++) {
        if($photodel["n".$i]) $data["PHOTO"]["DEL"][] = (int) $photoid[$i];
        if($photoadd["name"]["n".$i]) {
            if($photoid[$i]) $data["PHOTO"]["DEL"][] = (int) $photoid[$i];
            $data["PHOTO"]["ADD"][] = (int) CFile::SaveFile(array(
                "name" => $photoadd["name"]["n".$i],
                "size" => $photoadd["size"]["n".$i],
                "tmp_name" => $photoadd["tmp_name"]["n".$i],
                "type" => $photoadd["type"]["n".$i],
                "MODULE_ID" => $module_id,
            ), $module_id);
        }
    }
}

if (!isset($_REQUEST["ID"]) || $_REQUEST["ID"] == 0) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid()) {
        // Добавление отзыва
        $result = ReviewTable::addReview($data);
        if ($result) {
            LocalRedirect("{$module_id}_list.php?lang=" . LANGUAGE_ID);
        }
    }
    $APPLICATION->SetTitle(Loc::getMessage("REVIEWS_ADD_TITLE"));
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid()) {
        $data["ID"] = $_POST["ID"];

        $result = ReviewTable::updateReview((int) $_POST["ID"], $data);
        if ($result) {
            LocalRedirect("{$module_id}_list.php?lang=" . LANGUAGE_ID);
        }
    }
    
    $review = ReviewTable::getById($id)->fetch();
    if($review) {
        $likes = LikeTable::getLikesCount($id);
        $review["LIKES"] = $likes['LIKES'];
        $review["DISLIKES"] = $likes['DISLIKES'];
        $review["RATING"] = RatingTable::getByReviewId($id);
        $review["RATING"] = $review["RATING"]["RATING"];
        $review["PHOTO"] = array();
        $review["PHOTO"] = PhotoTable::getPhotosByReviewId($id);
    }
    
    $APPLICATION->SetTitle(Loc::getMessage("REVIEWS_EDIT_TITLE", array("#ID#" => $id)));
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$aTabs = array(
    array(
        "DIV" => "edit",
        "TAB" => Loc::getMessage("REVIEWS_EDIT_TAB"),
        "ICON" => "iblock",
        "TITLE" => Loc::getMessage("REVIEWS_EDIT_TAB_TITLE"),
    ),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($ex = $APPLICATION->GetException()) {
    CAdminMessage::ShowMessage($ex->GetString());
    $APPLICATION->ResetException();
}
?>

<form method="POST" action="<?=$APPLICATION->GetCurPage()?>?ID=<?=$id?>" enctype="multipart/form-data" name="fosreviewsform">
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="ID" value="<?=$id?>">
    <?php
        $tabControl->Begin();
        $tabControl->BeginNextTab();
    ?>
    <?if(isset($_REQUEST["ID"])):?>
    <tr>
        <td width="40%"><?=Loc::getMessage("FOS_REVIEWS_ID")?>:</td>
        <td width="60%"><?=$review["ID"]?></td>
    </tr>
    <?endif;?>
    <tr>
        <td width="40%"><?=Loc::getMessage("FOS_REVIEWS_PUBLICATION_DATE")?>:</td>
        <td width="60%">
            <?echo CalendarDate("PUBLICATION_DATE", date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), $review["PUBLICATION_DATE"]), "post_form", "20")?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?=Loc::getMessage("FOS_REVIEWS_ACTIVE")?>:</td>
        <td width="60%"><input type="checkbox" name="ACTIVE" <?=($review["ACTIVE"])?'checked':'';?> <?=(!isset($_REQUEST["ID"]))?'checked':'';?>></td>
    </tr>
    <tr>
        <td width="40%"><?=Loc::getMessage("FOS_REVIEWS_USER_ID")?>:</td>
        <td width="60%">
            <?
            echo FindUserID("USER_ID", $review["USER_ID"], "", "fosreviewsform", "10", "", " ... ", "", "");
            ?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?=Loc::getMessage("FOS_REVIEWS_PRODUCT_ID")?>:</td>
        <td width="60%">
            <?
            if(isset($review["PRODUCT_ID"])):
                $res = CIBlockElement::GetList(Array(), Array("ID"=>$review["PRODUCT_ID"]), false, Array("nPageSize"=>1), Array("ID", "NAME", "IBLOCK_TYPE_ID", "IBLOCK_ID"));
                while($ob = $res->GetNextElement()) {
                    $arFields = $ob->GetFields();
                    $productIdCol = $arFields["ID"];
                    $textproduct = '&nbsp;<a href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$arFields["IBLOCK_ID"].'&type='.$arFields["IBLOCK_TYPE_ID"].'&ID='.$arFields["ID"].'">'.$arFields["NAME"].'</a>';
                }
            endif;
            ?>
            <input name="PRODUCT_ID" id="PRODUCT_ID" value="<?=$productIdCol?>" size="5" type="text">
            <input type="button" value="..." onclick="jsUtils.OpenWindow('/bitrix/admin/iblock_element_search.php?lang=<? echo LANGUAGE_ID; ?>&IBLOCK_ID=0&n=PRODUCT_ID&k=', 900, 700);">
            <?=$textproduct?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?=Loc::getMessage("FOS_REVIEWS_EDIT_COMMENT")?>:</td>
        <td width="60%"><textarea name="COMMENT" style="width: 100%; height: 100px;"><?=$review["COMMENT"]?></textarea></td>
    </tr>
    <tr>
        <td width="40%"><?=Loc::getMessage("FOS_REVIEWS_EDIT_RATING")?>:</td>
        <td width="60%"><input type="number" name="RATING" max="5" min="1" value="<?=($review["RATING"])?$review["RATING"]:'5';?>"></td>
    </tr>
    <?if(isset($_REQUEST["ID"])):?>
    <tr>
        <td width="40%"><?=Loc::getMessage("FOS_REVIEWS_LIKES")?>:</td>
        <td width="60%"><?=$review["LIKES"]?></td>
    </tr>
    <tr>
        <td width="40%"><?=Loc::getMessage("FOS_REVIEWS_DISLIKES")?>:</td>
        <td width="60%"><?=$review["DISLIKES"]?></td>
    </tr>
    <?endif;?>
    <tr class="heading"><td colspan="2"><?=Loc::getMessage("FOS_REVIEWS_PHOTO")?>:</td></tr>
    <?for($i=0; $i<$countphoto; $i++){?>
        <tr>
            <td width="40%"></td>
            <td width="60%">
                <input type="hidden" name="PHOTO[ids][<?=$i?>]" value="<?=$review["PHOTO"][$i]?>">
                <?= CFile::InputFile("PHOTO[n".$i."]", 40, $review["PHOTO"][$i], false, 0, "IMAGE", "class=typefile", 0, "class=typeinput", "", false, false)?>
                <?if($review["PHOTO"][$i]):?>
                    <br><br><img src="<?=CFile::GetPath($review["PHOTO"][$i])?>" alt="" style="width:200px;">
                <?endif;?>
            </td>
        </tr>
    <?}?>
    <!-- add other fields here -->
    <?php
        $tabControl->EndTab();
        $tabControl->Buttons();
    ?>
    <input type="submit" name="save" value="<?=Loc::getMessage("MAIN_SAVE")?>">
    <a href="<?=$module_id?>_list.php?lang=<?=LANGUAGE_ID?>" class="adm-btn adm-btn-copy"><?=Loc::getMessage("MAIN_ADMIN_MENU_LIST")?></a>
    <?php
        $tabControl->End();
    ?>
</form>

<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");

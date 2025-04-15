<?
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;
use \FOS\Reviews\ReviewTable;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$moduleId = 'fosdev.fosreviews';
Loader::includeModule($moduleId);
Loader::includeModule('iblock');

$currentUri = $_SERVER['HTTP_REFERER'];
$currentUri = explode('/', $currentUri);
$lastcurrentUri = '';
foreach($currentUri as $key=>$val) {
    if($key == 0 || $key == 1 || $key == 2) continue;
    $lastcurrentUri .= "/" . $val;
}
$charactersToRemove = ["=", ",", ";", " ", "\t", "\r", "\n", "\013", "\014"];
$lastcurrentUri = str_replace($charactersToRemove, "", $lastcurrentUri);
$elementId = $_COOKIE['elementId'.$lastcurrentUri];

if($USER->IsAuthorized()) {
    $purch = ReviewTable::wasPurchased($USER->GetID(), $elementId);
    $purch = true;
    ?>
    <div id="modalFosRevReviews" class="fosrev-modal-mini <?=($purch)?'fosrev-modal-mini_otz':'';?>">
        <div class="fosrev-modal-mini__bg fosrev-modal-mini__close"></div>
        <form class="fosrev-modal-mini__content" id="fosrev-send" enctype="multipart/form-data" method="post">
            <div class="fosrev-modal-mini__content__close fosrev-modal-mini__close"></div>
            <?if($purch):?>
                <div class="" id="fosrev-send-replace">
                    <div class="fosrev-modal-mini__content__t"><?=Loc::getMessage("MINI_FORM_TITLE")?></div>
                    <div class="fosrev-modal-mini__content__star">
                        <div class="fosrev-modal-mini__content__star__t"><?=Loc::getMessage("MINI_FORM_TITLE_RATING")?></div>
                        <div class="fosrev-modal-mini__content__star__items">
                            <div class="fosrev-modal-mini__content__star__i" data-star="5"></div>
                            <div class="fosrev-modal-mini__content__star__i" data-star="4"></div>
                            <div class="fosrev-modal-mini__content__star__i" data-star="3"></div>
                            <div class="fosrev-modal-mini__content__star__i" data-star="2"></div>
                            <div class="fosrev-modal-mini__content__star__i" data-star="1"></div>
                        </div>
                    </div>
                    <div class="fosrev-modal-mini__content__message">
                        <div class="fosrev-modal-mini__content__message__t"><?=Loc::getMessage("MINI_FORM_ABOUT")?></div>
                        <textarea name="message" id="" class="fosrev-modal-mini__content__message__inp"></textarea>
                    </div>
                    <div class="fosrev-modal-mini__content__photos">
                        <div class="fosrev-modal-mini__content__photos__t"><?=Loc::getMessage("MINI_FORM_PHOTO")?></div>
                        <div class="fosrev-modal-mini__content__photos__content">
                            <div class="fosrev-modal-mini__content__photos__items">
                                
                            </div>
                            <label class="fosrev-modal-mini__content__photos__label fosrev-modal-mini__content__photos__label_show" for="fosrev-modal-mini__content__photos__input1">
                                <input id="fosrev-modal-mini__content__photos__input1" class="fosrev-modal-mini__content__photos__input" name="files1" type="file" accept=".jpg,.jpeg,.png,.bmp,.gif">
                            </label>
                            <label class="fosrev-modal-mini__content__photos__label" for="fosrev-modal-mini__content__photos__input2">
                                <input id="fosrev-modal-mini__content__photos__input2" class="fosrev-modal-mini__content__photos__input" name="files2" type="file" accept=".jpg,.jpeg,.png,.bmp,.gif">
                            </label>
                            <label class="fosrev-modal-mini__content__photos__label" for="fosrev-modal-mini__content__photos__input3">
                                <input id="fosrev-modal-mini__content__photos__input3" class="fosrev-modal-mini__content__photos__input" name="files3" type="file" accept=".jpg,.jpeg,.png,.bmp,.gif">
                            </label>
                            <label class="fosrev-modal-mini__content__photos__label" for="fosrev-modal-mini__content__photos__input4">
                                <input id="fosrev-modal-mini__content__photos__input4" class="fosrev-modal-mini__content__photos__input" name="files4" type="file" accept=".jpg,.jpeg,.png,.bmp,.gif">
                            </label>
                            <label class="fosrev-modal-mini__content__photos__label" for="fosrev-modal-mini__content__photos__input5">
                                <input id="fosrev-modal-mini__content__photos__input5" class="fosrev-modal-mini__content__photos__input" name="files5" type="file" accept=".jpg,.jpeg,.png,.bmp,.gif">
                            </label>
                            <div class="fosrev-modal-mini__content__photos__info"><?=Loc::getMessage("MINI_FORM_PHOTO_FORMAT")?></div>
                        </div>
                    </div>
                    <button type="button" disabled="disabled" class="fosrev-modal-mini__content__photos__but" id="fosrev-send-btn"><?=Loc::getMessage("MINI_FORM_BTN")?></button>
                    <div class="fosrev-modal-mini__content__info"><?=Loc::getMessage("MINI_FORM_INFO")?></div>
                </div>
            <?else:?>
                <div class="fosrev-modal-mini__content__t"><?=Loc::getMessage("MINI_FORM_TITLE_ERROR")?></div>
                <div class="fosrev-modal-mini__content__tx"><?=Loc::getMessage("MINI_FORM_DESC_ERROR")?></div>
            <?endif;?>
        </form>
    </div>
    <?
}

?>
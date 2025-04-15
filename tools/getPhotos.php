<?
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;
use \FOS\Reviews\ReviewTable;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$moduleId = 'fosdev.fosreviews';
Loader::includeModule($moduleId);


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
$reviews = ReviewTable::getReviewsByProductId($elementId);
$number = $_GET["photoId"];
$counter = 1;

$countPhotos = 0;
$imgList = array();
foreach($reviews as $rev):
    $countPhotos += count($rev['PHOTOS']);
    foreach($rev['PHOTOS'] as $photo) {
        $imgList[] = $photo['FILE_ID'];
    }
endforeach;
?>

<div id="modalFosRevPhotos" class="fosrev-modal">
    <div class="fosrev-modal__close"></div>
    <div class="fosrev-modal__top">
        <div class="fosrev-modal__slider">
            <?foreach($imgList as $photo):
                $img = CFile::ResizeImageGet($photo, array('width'=>450, 'height'=>600), BX_RESIZE_IMAGE_PROPORTIONAL); ?>
                <img src="<?=$img['src']?>" alt="" class="fosrev-modal__slider__i <?=($number==$counter)?'fosrev-modal__slider__i_active':'';?>" data-counter="<?=$counter?>">
            <?
            $counter++;
            endforeach;?>
            <div class="fosrev-modal__slider__prev"></div>
            <div class="fosrev-modal__slider__next"></div>
        </div>
    </div>
    <div class="fosrev-modal__bottom">
        <div class="fosrev-modal__t"><?=Loc::getMessage("FORM_PHOTOS_TITLE")?> (<?=$countPhotos?>)</div>
        <div class="fosrev-modal__list">
            <?
            $counter = 1;
            foreach($imgList as $photo):
                $img = CFile::ResizeImageGet($photo, array('width'=>219, 'height'=>293), BX_RESIZE_IMAGE_EXACT); ?>
                <img src="<?=$img['src']?>" alt="" class="fosrev-modal__list__i" data-counter="<?=$counter?>">
            <?
            $counter++;
            endforeach;?>
        </div>
    </div>
</div>
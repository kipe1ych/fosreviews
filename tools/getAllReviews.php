<?
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;
use \FOS\Reviews\ReviewTable;
use \FOS\Reviews\RatingTable;

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
$reviewsArr = array();
foreach($reviews as $review):
    $data = $review;
    $rsUser = CUser::GetByID($data['USER_ID']);
    $arUser = $rsUser->Fetch();
    $data['USER_NAME'] = $arUser['NAME'];
    $data['USER_LAST_NAME'] = $arUser['LAST_NAME'];
    $data['DATE'] = ConvertTimeStamp($review['PUBLICATION_DATE'], "SHORT");
    $data['RATING'] = RatingTable::getByReviewId((int) $review['ID']);
    $reviewsArr[] = $data;
endforeach;

$number = 1;
?>

<div id="modalAllReviews" class="fosrev-modal">
    <div class="fosrev-modal__close"></div>
    <div class="fosrev-modal__bottom fosrev-modal__bottom_style">
        <div class="fosrev-modal__t">
            <div class="fos-reviews__t"><?=Loc::getMessage("TITLE_FORM")?> <sup><?=count($reviews)?></sup></div>
        </div>
        <div class="fosrev-modal__list-reviews">
            <?foreach($reviewsArr as $review):?>
                <div class="fosrev-modal__list-reviews__i" data-id="<?=$number?>">
                    <div class="fos-review__wrap fos-review__wrap_style">
                        <div class="fos-review-header">
                            <div class="fos-review-header__info">
                                <div class="fos-review-user"><?= htmlspecialcharsbx($review['USER_NAME'] . " " . $review['USER_LAST_NAME']) ?></div>
                                <div class="fos-review-date"><?= htmlspecialcharsbx($review['DATE']) ?></div>
                            </div>
                            <div class="fos-review-rating">
                                <?php if (!empty($review['RATING'])): ?>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $review['RATING']['RATING']): ?>
                                            <span class="fos-review-rating__i fos-review-rating__i_active"></span>
                                        <?php else: ?>
                                            <span class="fos-review-rating__i"></span>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="fos-review-content fos-review-content_style"><?=$review['COMMENT']?></div>
                        <?php if (!empty($review['PHOTOS'])): ?>
                            <?php $counter = 1; ?>
                            <div class="fos-review-photos">
                                <?php foreach ($review['PHOTOS'] as $photo):
                                    $img = CFile::ResizeImageGet($photo['FILE_ID'], array('width'=>36, 'height'=>48), BX_RESIZE_IMAGE_EXACT); ?>
                                    <div class="fos-review-photos__i fos-review-photos__i_revmod" data-id="<?=$counter?>">
                                        <img src="<?= $img['src'] ?>" alt="" class="fos-review-photos__img">
                                    </div>
                                    <? $counter++; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?$number++;?>
            <?endforeach;?>
        </div>
    </div>
</div>
<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); ?>
<?
$APPLICATION->SetAdditionalCss($this->GetFolder() . "/styles.css");
$this->addExternalJS($this->GetFolder() . "/script.js");
?>

<?php if (!empty($arResult['REVIEWS'])): ?>
    <div class="fos-reviews">
        <div class="fos-reviews__t"><?=$arResult['HEADER_NAME']?> <sup><?=number_format($arResult['COUNT_REVIEWS'], 0, '.', ' ')?></sup></div>
        <?if($arResult['DISPLAY_CUSTOMER_PHOTOS'] == 'Y' && $arResult['COUNT_PHOTOS'] > 0):?>
            <div class="fos-reviews__photos-t"><?=GetMessage("PHOTOS_TITLE")?> (<?=$arResult['COUNT_PHOTOS']?>)</div>
            <div class="fos-reviews__photos-list__photos-list-main">
                <div class="fos-reviews__photos-list">
                    <div class="fos-reviews__slider-container">
                        <?
                        $counter = 1;
                        foreach($arResult['IMG_LIST'] as $photo):
                            $img = CFile::ResizeImageGet($photo, array('width'=>88, 'height'=>128), BX_RESIZE_IMAGE_EXACT); ?>
                            <div class="fos-reviews__slide">
                                <img src="<?=$img['src']?>" alt="" class="fos-reviews__photos-list__i" data-id="<?=$counter?>">
                                <?if($counter == $arResult['PAGE_SIZE_PHOTOS'] && $arResult['COUNT_PHOTOS'] > $arResult['PAGE_SIZE_PHOTOS']):?>
                                    <div class="fos-reviews__slide__bg-all" data-id="<?=$counter?>">+<?=($arResult['COUNT_PHOTOS']-$arResult['PAGE_SIZE_PHOTOS'])?></div>
                                <?endif;?>
                            </div>
                            <?
                            $counter++;
                            if($counter > $arResult['PAGE_SIZE_PHOTOS']) break;
                        endforeach;?>
                    </div>
                </div>
                <div class="fos-reviews__slider-controls">
                    <button class="fos-reviews__slider-controls__button fos-reviews__slider-controls__prev-button"></button>
                    <button class="fos-reviews__slider-controls__button fos-reviews__slider-controls__next-button"></button>
                </div>
            </div>
        <?endif;?>
        <div class="fos-reviews__line">
            <?if($arResult['SHOW_RATING'] == 'Y'):?>
                <div class="fos-reviews__rating">
                    <div class="fos-reviews__rating__num"><?=round($arResult['AVERAGE']['AVG_RATING'], 1);?></div>
                    <div class="fos-reviews__rating__stars">
                        <div class="fos-reviews__rating__stars__in" style="width: <?=$arResult['AVERAGE']['AVG_RATING']*20?>%;"></div>
                    </div>
                    <div class="fos-reviews__rating__detail-info">
                        <?=number_format($arResult['COUNT_REVIEWS'], 0, '.', ' ')?> <?=GetMessage("REVIEW_".$arResult['TEXT_REVIEW'])?>
                    </div>
                </div>
            <?endif;?>
            <?if($arResult['USER_AUTH']):?>
                <button class="fos-reviews__btn-form fos-reviews__btn-form_snd"><?=GetMessage("TITLE_TEXT_BUTTON")?></button>
            <?else:?>
                <a href="<?=$arResult['LINK_AUTH']?>" class="fos-reviews__btn-form"><?=GetMessage("TITLE_TEXT_BUTTON")?></a>
            <?endif;?>
        </div>
        <div class="fos-reviews__list">
            <div class="fos-reviews__list__slider">
                <div class="fos-reviews__list__slider__container">
                    <?php $counter = 1; ?>
                    <?php $numberReviews = 0; ?>
                    <?php foreach($arResult['REVIEWS'] as $item):
                        $numberReviews++;
                        if($numberReviews > $arResult['PAGE_SIZE']) break;
                        ?>
                        <div class="fos-review">
                            <div class="fos-review__wrap" data-id="<?=$numberReviews?>">
                                <div class="fos-review-header">
                                    <div class="fos-review-header__info">
                                        <div class="fos-review-user">
                                            <?= htmlspecialcharsbx($item['USER_NAME'] . " " . $item['USER_LAST_NAME']) ?>
                                        </div>
                                        <div class="fos-review-date">
                                            <?= htmlspecialcharsbx($item['DATE']) ?>
                                        </div>
                                    </div>
                                    <div class="fos-review-rating">
                                        <?php if (!empty($item['RATING'])): ?>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $item['RATING']['RATING']): ?>
                                                    <span class="fos-review-rating__i fos-review-rating__i_active"></span>
                                                <?php else: ?>
                                                    <span class="fos-review-rating__i"></span>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="fos-review-content">
                                    <?= htmlspecialcharsbx($item['COMMENT']) ?>
                                </div>
                                <?php if (!empty($item['PHOTOS'])): ?>
                                    <div class="fos-review-photos">
                                        <?php foreach ($item['PHOTOS'] as $photo):
                                            $img = CFile::ResizeImageGet($photo['FILE_ID'], array('width'=>36, 'height'=>48), BX_RESIZE_IMAGE_EXACT); ?>
                                            <div class="fos-review-photos__i" data-id="<?=$counter?>">
                                                <img src="<?= $img['src'] ?>" alt="" class="fos-review-photos__img">
                                            </div>
                                            <? $counter++; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="fos-reviews__list__slider-controls">
                <button class="fos-reviews__list__slider-controls__button fos-reviews__list__slider-controls__prev-button"></button>
                <button class="fos-reviews__list__slider-controls__button fos-reviews__list__slider-controls__next-button"></button>
            </div>
        </div>
        <a class="fos-reviews__btn-all" href=""><?=$arResult['NAME_BUTTON']?></a>
    </div>
<?php else: ?>
    <div class="fos-reviews">
        <div class="fos-reviews__t"><?=$arResult['HEADER_NAME']?> <sup><?=number_format($arResult['COUNT_REVIEWS'], 0, '.', ' ')?></sup></div>
        <div class="fos-reviews__desc"><?= GetMessage("FOS_REVIEWS_NO_ITEMS") ?></div>
        <div class="fos-reviews__line">
            <?if($arResult['USER_AUTH']):?>
                <button class="fos-reviews__btn-form fos-reviews__btn-form_snd"><?=GetMessage("TITLE_TEXT_BUTTON")?></button>
            <?else:?>
                <a href="<?=$arResult['LINK_AUTH']?>" class="fos-reviews__btn-form"><?=GetMessage("TITLE_TEXT_BUTTON")?></a>
            <?endif;?>
        </div>
    </div>
    
<?php endif; ?>
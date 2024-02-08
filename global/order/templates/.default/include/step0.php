<?php
/** @var array $arResult */
global $productCartIds,$priceCart;
$productCartIds = '[';
$priceCart = $arResult['ORDER']['BASKET'];

?>

<div class="order__step order-part order__step--active">
    <div class="order-part__header">Список товаров</div>
    <div class="order-part__content order-basket">
        <?$counter = 0;?>
        <? foreach ($arResult['BASKET']['ITEMS'] as $arItem): $counter++;?>
        <?$productCartIds .= "'".$arItem['PRODUCT_ID']."'";?>
        <?if($counter != count($arResult['BASKET']['ITEMS']))$productCartIds .= ",";?>
            <div class="order-basket__item ob-item itemCart-js" data-item_id="<?= $arItem['ID'] ?>">
                <a class="ob-item__img-wrap" href="<?= $arItem['DETAIL_PAGE_URL'] ?>">
                    <img class="ob-item__img" src="<?= $arItem['PREVIEW_PICTURE']['SRC'] ?>"
                         alt="<?= $arItem['NAME'] ?>">
                </a>
                <div class="ob-item__name">
                    <a class="ob-item__name-link" href="<?= $arItem['DETAIL_PAGE_URL'] ?>"><?= $arItem['NAME'] ?></a>
                </div>
                <div class="ob-item__quantity">
                    <div class="spr-quantity">
                        <input class="spr-quantity__input" type="number" name="quantity"
                               value="<?= $arItem['QUANTITY'] ?>" data-skip="true"
                               onkeypress="this.style.width = ((this.value.length + 2) * 8) + 'px';"
                               onchange="changeQuantityInCart(event)">
                        <div class="spr-quantity__arrow-wrap">
                            <span class="spr-quantity__arrow-up" onclick="changeQuantityUp(event)"></span>
                            <span class="spr-quantity__arrow-down" onclick="changeQuantityDown(event)"></span>
                        </div>
                    </div>
                </div>
                <div class="ob-item__price ob-price" style="opacity: <?= $arItem['PRICE_SUM'] == 0 ? '0' : '1' ?>">
                    <? if (intval($arItem['PRICE_DISCOUNT']) > 0): ?>
                        <span class="ob-price__value"><?= str_replace('₽', '₽', $arItem['PRICE_SUM']) ?> ₽</span>
                        <span class="ob-price__value ob-price__value--discount">
                            <?= str_replace('₽', '₽', $arItem['PRICE_BASE_SUM']) ?> ₽
                        </span>
                    <? else: ?>
                        <div class="ob-price__value">
                            <?= number_format($arItem['PRICE_SUM'], 2, '.', ' ') ?> ₽
                        </div>
                    <? endif ?>
                </div>
                <div class="ob-item__remove" onclick="deleteItemInCart(event)">
                    <span class="ob-item__remove-icon"></span>
                </div>
            </div>
        <? endforeach ?>
    </div>
</div>
<?$productCartIds .= ']';?>
<?php
/** @var array $arResult */
/** @var string $step */
?>

<div class="order-part__content order-total">
    <div class='order-part__header'>
        <div class='order-part__header-value'>Ваш заказ</div>
    </div>
    <div class="order-total__inner">
        <div class="order-total__count">
            Товаров: <?= count($arResult['BASKET']['ITEMS']) ?>
        </div>
        <div class="order-total__delivery">
            <div class="order-total__delivery-name">Стоимость доставки:</div>
            <div class="order-total__delivery-value">
                <? if (!$arResult['ORDER']['DELIVERY']): ?>
                    Бесплатно
                <? else: ?>
                    <?= number_format($arResult['ORDER']['DELIVERY'], 2, '.', ' ') . ' ₽' ?>
                <? endif ?>
            </div>
        </div>
        <div class="order-total__sum">
            <div class="order-total__sum-name">Итог:</div>
            <div class="order-total__sum-value">
                <?= number_format($arResult['ORDER']['TOTAL'], 2, '.', ' ') . ' ₽' ?>
            </div>
        </div>
        <div class="order-total__send">
            <button class="order-total__btn btn-global btn-global--order" type="button">
                Оформить заказ
            </button>
        </div>
    </div>
</div>
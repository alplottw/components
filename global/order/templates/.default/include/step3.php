<?php

/** @var array $arResult */
/** @var string $step */

?>

<div class="order__step order__step--active order__step--end">
    <div class="order-part">
        <div class="order-part__header">
            <div class="order-part__header-value">Личные данные</div>
        </div>
        <div class="order-part__content order-user">
            <div class="order-user__input order-input">
                <input type="text"
                       class="order-input__text order-input__text--personal <?= $arResult['ERRORS']['VALIDATION']['FIO'] ? 'order-input__text--error' : '' ?> valueOrder-js"
                       placeholder="ФИО" name="FIO" value="<?= $arResult['PROPS']['FIO'] ?>"
                >
            </div>
            <div class="order-user__input order-input">
                <input type="text"
                       class="order-input__text order-input__text--personal <?= $arResult['ERRORS']['VALIDATION']['EMAIL'] ? 'order-input__text--error' : '' ?> valueOrder-js"
                       placeholder="Почта" name="EMAIL" value="<?= $arResult['PROPS']['EMAIL'] ?>"
                >
            </div>
            <div class="order-user__input order-input">
                <input type="text"
                       class="order-input__text order-input__text--personal <?= $arResult['ERRORS']['VALIDATION']['PHONE'] ? 'order-input__text--error' : '' ?> valueOrder-js"
                       id="phone"
                       placeholder="Телефон" name="PHONE"
                       maxlength="18"
                       value="<?= $arResult['PROPS']['PHONE'] ? $arResult['PROPS']['PHONE'] : '+7 ' ?>"
                >
            </div>
        </div>
    </div>
    <div class="order-part">
        <? require_once $_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/total.php' ?>
    </div>
</div>

<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var string $templateFolder
 */

Asset::getInstance()->addCss('/css/popup.css');
Asset::getInstance()->addCss('/css/ob-item.css');
Asset::getInstance()->addCss('/css/order-input.css');
Asset::getInstance()->addCss('/css/fastorder.css');

Asset::getInstance()->addJs('/js/popup.js');

?>

<div class="popup popup--fastorder" >
    <div class="popup__inner" onclick="closePopup(event)">
        <div class="popup__body">
            <div class='popup__header'>Быстрый заказ
                <div class='popup__close' data-action="close"></div>
            </div>
            <form class="fastorder" style="min-width: 320px">
                <input type="hidden" name="is_ajax" value="Y">
                <input type="hidden" name="action">
               <div class="fastorder__inner">
                   <?if ($arResult['FASTORDER']['SUCCESS'] == 'Y'): ?>
                       <div class="fastorder__success-text">Благодарим за Ваш заказ!</div>
                       <div class='fastorder__success-text'>Номер заказа: <?= $arResult['ID'] ?></div>
                       <button class='btn-global btn-global--fastorder-success'
                               type='button'
                               data-action="close">
                           Закрыть
                       </button>
                       <? include $_SERVER['DOCUMENT_ROOT'] . '/include/gmAnalytics.php'?>
                   <? else: ?>
                   <? foreach ($arResult['BASKET']['ITEMS'] as $arItem): ?>
                       <div class="ob-item ob-item--fastorder" data-item_id="<?= $arItem['ID'] ?>">
                        <span class="ob-item__img-wrap" href="<?= $arItem['DETAIL_PAGE_URL'] ?>">
                            <img class="ob-item__img" src="<?= $arItem['PREVIEW_PICTURE']['SRC'] ?>"
                                 alt="<?= $arItem['NAME'] ?>">
                        </span>
                           <div class="ob-item__name">
                               <span class="ob-item__name-link" href="<?= $arItem['DETAIL_PAGE_URL'] ?>"><?= $arItem['NAME'] ?></span>
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
                       </div>
                   <? endforeach ?>
                   <div class="fastorder__info">
                       Заполните форму быстрого заказа, наши менеджеры скоро свяжутся с вами
                   </div>
                   <div class="fastorder__user">
                       <div class='order-user__input order-input'>
                           <input type='text'
                                  class="order-input__text order-input__text--personal <?= $arResult['ERRORS']['VALIDATION']['FIO'] ? 'order-input__text--error' : '' ?> valueOrder-js"
                                  placeholder='ФИО' name='FIO' value="<?= $arResult['PROPS']['FIO'] ?>"
                           >
                       </div>
                       <div class='order-user__input order-input'>
                           <input type='text'
                                  class="order-input__text order-input__text--personal <?= $arResult['ERRORS']['VALIDATION']['EMAIL'] ? 'order-input__text--error' : '' ?> valueOrder-js"
                                  placeholder='Почта' name='EMAIL' value="<?= $arResult['PROPS']['EMAIL'] ?>"
                           >
                       </div>
                       <div class='order-user__input order-input'>
                           <input type='text'
                                  class="order-input__text order-input__text--personal <?= $arResult['ERRORS']['VALIDATION']['PHONE'] ? 'order-input__text--error' : '' ?> valueOrder-js"
                                  id='phone'
                                  placeholder='Телефон' name='PHONE'
                                  maxlength='18'
                                  value="<?= $arResult['PROPS']['PHONE'] ? $arResult['PROPS']['PHONE'] : '+7 ' ?>"
                           >
                       </div>
                       <button class="btn-global btn-global--fastorder" type="button">Оформить заказ</button>
                       <div class='fastorder__policy'>
                           Нажимая кнопку «Оформить заказ», я даю согласие на обработку персональных данных в соответствии
                           с “<a href='/docs/personal.docx'>Политикой в области обработки и защиты персональных данных</a>".
                       </div>
                   </div>
                   <? endif ?>
               </div>
            </form>
        </div>
    </div>
</div>
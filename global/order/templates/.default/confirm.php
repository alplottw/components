<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Sale;

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var string $templateFolder
 * @var string $step
 */
global $priceCart,$productCartIds;

if($_GET['ORDER_ID']){
    $priceCart = 0;
    $order = Sale\Order::load($_GET['ORDER_ID']);
    #debmes($order->getSumPaid());
    $basket = $order->getBasket();
    foreach ($basket as $basketItem){
        #debmes($basketItem->getPrice(),$basketItem->getProductId());
        $priceCart += $basketItem->getPrice();
        $productCartIds[] = "'".$basketItem->getProductId()."'";
        #debmes();
    }
    $productCartIds = '['.implode(',',$productCartIds).']';
}
?>

    <div class="wrapper">
        <div class="wrapper__container">
            <div class="order__main">
                <div>Благодарим за Ваш заказ!</div>
                <div>Номер заказа: <?= $arResult['ID'] ?></div>
                <div>На Вашу электронную почту отправлено письмо с деталями заказа.</div>
                <!--            <div>Для отслеживания статуса заказа вы можете перейти в <a href="/personal/">персональный</a> раздел.</div>-->
                <? if (!$arResult['IS_PAID']): ?>
                    <div><?= reset($arResult['PAYMENT'])['TEMPLATE'] ?></div>
                <? endif; ?>
            </div>
        </div>
    </div>
<? include $_SERVER['DOCUMENT_ROOT'] . '/include/gmAnalytics.php'?>
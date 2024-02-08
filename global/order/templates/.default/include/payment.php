<?

/** @var array $arResult */


?>
<div class="order-payment">
    <div class="order-payment__inner">
        <div class="order-payment__col">
            <? foreach ($arResult['PAYMENT']['ITEMS'] as $payment): ?>
                <input
                        class="order-payment__input"
                        type="radio" name="pay"
                        id="fakePay<?= $payment['ID'] ?>"
                        value="<?= $payment['ID'] ?>"
                    <? if (intval($payment['ID']) == $arResult['PAYMENT']['SELECTED']['ID']) { ?> checked<? } ?>

                >
                <label class="order-payment__label" for="fakePay<?= $payment['ID'] ?>">
                    <span class="order-payment__name"><?= $payment['PSA_NAME'] ?></span>
                </label>
            <? endforeach ?>
        </div>
        <div class="order-payment__col topay">
            <div class="topay__inner">
                <div class="topay__header">Принимаем к оплате</div>
                <div class="topay__content">
                    <img class="topay__img" src="/img/payment/VISAE.png" alt="">
                    <img class="topay__img" src="/img/payment/VISA.png" alt="">
                    <img class="topay__img" src="/img/payment/sber_c.png" alt="">
                    <img class="topay__img" src="/img/payment/SBER.png" alt="">
                    <img class="topay__img" src="/img/payment/MIR.png" alt="">
                    <img class="topay__img" src="/img/payment/MCE.png" alt="">
                    <img class="topay__img" src="/img/payment/MC.png" alt="">
                    <img class="topay__img" src="/img/payment/MAESTRO.png" alt="">
                    <img class="topay__img" src="/img/payment/JCB.png" alt="">
                </div>
            </div>
        </div>
    </div>
</div>
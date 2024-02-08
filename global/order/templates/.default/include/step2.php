<?php

/** @var array $arResult */
/** @var string $step */

?>

<div class="order__step order__step--active">
    <div class="order-part">
        <div class='order-part__header'>
            <div class='order-part__header-value'>Выберите способ оплаты</div>
        </div>
        <? require 'payment.php' ?>
    </div>
</div>
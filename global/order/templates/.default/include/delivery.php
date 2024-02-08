<?

/** @var array $arResult */

?>

<div class="order-delivery__type-delivery type-delivery">
    <?foreach ($arResult['DELIVERY']['ITEMS'] as $delivery):?>
    <input
            class="type-delivery__input valueOrder-js"
            type="radio"
            name="delivery"
            id="delivery<?= $delivery['ID'] ?>"
            value="<?= $delivery['ID']?>"
            <?= $delivery['ID'] == $arResult['DELIVERY']['SELECTED']['ID'] ? 'checked' : '' ?>
    >
    <label class="type-delivery__label" for="delivery<?= $delivery['ID'] ?>">
        <span class="type-delivery__name"><?= $delivery['NAME'] ?></span>
        <span class="type-delivery__description"><?= $delivery['DESCRIPTION'] ?></span>
    </label>
    <? endforeach ?>
</div>
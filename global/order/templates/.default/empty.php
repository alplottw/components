<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var string $templateFolder
 * @var string $step
 */

?>

<div class="wrapper">
    <div class="wrapper__container">
        <h1 class='wrapper__header'>В корзине нет товаров</h1>
    </div>
</div>

<script>
    let step = <?= $step ?>
</script>
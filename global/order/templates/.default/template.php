<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;
/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var string $templateFolder
 */

$step = $arResult['STEP'];
//debmes($_SERVER);
//debmes($arResult['ERRORS']);
//unset($_SESSION['ORDER']);

/* TODO вынести в компонент */
if (!$_SESSION['ORDER']['geo']) {
    if ($_SESSION['ORDER']['startLat'] && $_SESSION['ORDER']['startLon']) {
        $_SESSION['ORDER']['centerCoordY'] = $_SESSION['ORDER']['startLat'];
        $_SESSION['ORDER']['centerCoordX'] = $_SESSION['ORDER']['startLon'];
        $_SESSION['ORDER']['geo'] = true;
        $centerCoord = [$_SESSION['ORDER']['centerCoordY'], $_SESSION['ORDER']['centerCoordX']];
    }
} else {
    $centerCoord = [$_SESSION['ORDER']['centerCoordY'], $_SESSION['ORDER']['centerCoordX']];
}

if (empty(reset($centerCoord)))
    $centerCoord = (\Common\Functions::getCurrentCity()['ID'] == 1) ? ['55.75370903771494', '37.61981338262558'] : ['59.89444', '30.26417'];

//sync with dadata
if (isset($arResult['PROPS']['ADDRESS'])) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/include/classes/Common/dadata.php");
    $dadata = new Dadata("221f2111893e0e8756029ed7ffcb0ae8be6231f9");
    $dadata->init();

    $arResult['daData'] = $dadata->suggest('address', ['count' => 5, 'query' => $arResult['PROPS']['ADDRESS']]);

    if (empty($arResult['daData']['suggestions'][0]['data']['geo_lat'])) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://geocode-maps.yandex.ru/1.x/?geocode=' . urlencode($arResult['PROPS']['ADDRESS']) . '&apikey=a7fc3bea-053b-4d71-aad5-09c1496d99ce&format=json');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $out = json_decode(curl_exec($curl), true);

        // если dadata не вернула координаты, то получаем их через яндекс
        if (!empty($out['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'])) {
            $coords = explode(' ', $out['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos']);
            $arResult['daData']['suggestions'][0]['data']['geo_lat'] = $coords[1];
            $arResult['daData']['suggestions'][0]['data']['geo_lon'] = $coords[0];
        }
    }
}

Asset::getInstance()->addCss('/css/order.css');
Asset::getInstance()->addCss('/css/ob-item.css');
Asset::getInstance()->addCss('/css/order-input.css');
Asset::getInstance()->addCss('/libs/jquery-ui/jquery-ui.min.css');

Asset::getInstance()->addJs('/libs/jquery-ui/jquery-ui.min.js');
Asset::getInstance()->addJs('/libs/suggestions/suggestions.min.js');
Asset::getInstance()->addJs('https://api-maps.yandex.ru/2.1/?apikey=349e486a-e569-4e65-b55d-e1a32f37e764&lang=ru_RU');

?>
<div class="wrapper">
    <div class="wrapper__container">
            <h1 class="wrapper__header">Оформление заказа</h1>
        <form class="order cartDetail-js" method="POST" autocomplete="off">
            <input type="hidden" name="action">
            <input type="hidden" name="is_ajax">
            <input type='hidden' name='startLat' id="startLat">
            <input type='hidden' name='startLon' id="startLon">
            <div class="order__main">
                <? require_once $_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/step0.php' //basket block?>
                <? require_once $_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/step1.php' //delivery block?>
                <? require_once $_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/step2.php' //payment block?>
                <? require_once $_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/step3.php' //personal & total block?>
            </div>
        </form>
        <div class="order__disclaimer">
            Нажимая кнопку «Оформить заказ», я даю согласие на обработку персональных данных в соответствии с “<a
                    href="/docs/personal.docx">Политикой в области обработки и защиты персональных данных</a>".
        </div>
    </div>
</div>

<script>

    <?if (!$_SESSION['ORDER']['geo']): ?>
    let geoSuccess = function (position) {
        let startPos = position;
        document.getElementById('startLat').value = startPos.coords.latitude;
        document.getElementById('startLon').value = startPos.coords.longitude;
        updateOrder();
    };
    let geoError = function (error) {
        console.log('Error occurred. Error code: ' + error.code);
        // error.code can be:
        //   0: unknown error
        //   1: permission denied
        //   2: position unavailable (error response from location provider)
        //   3: timed out
    };

    navigator.geolocation.getCurrentPosition(geoSuccess, geoError);
    <? endif; ?>
</script>
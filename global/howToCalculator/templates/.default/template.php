<?
$this->addExternalJS('/libs/selectize/selectize.min.js');
$this->addExternalJS('/libs/customSelect/cs.js');
$this->addExternalCSS('/libs/customSelect/cs.css');
define('REGIONS', [6 => 'sptw-1', 3 => 'sptw-2', 4 => 'sptw-3', 5 => 'sptw-4', 7 => 'sptw-5', 8 => 'sptw-6', 9 => 'sptw-7']);



/*
 * TODO ПЕРЕНЕСТИ ЛОГИКУ В КОМПОНЕНТ
*/
$arCities = $arRadiators = $arTypeWalls = $arTypeWindows = $arSections = $arTypeRadiators = [];
$rs = CIBlockSection::GetList([], ['IBLOCK_ID' => 2], false, ['ID', 'NAME', 'IBLOCK_SECTION_ID']);
while ($ob = $rs->GetNext())
{
    if (intval($ob['IBLOCK_SECTION_ID']) <= 0)
    {
        $arSections[$ob['ID']] = intval($ob['ID']);
        $arTypeRadiators[$ob['ID']] = $ob['NAME'] === 'Global Aluminium' ? 'Алюминиевые радиаторы' : ($ob['NAME'] === 'Global Bimetall' ? 'Биметалические радиаторы' : '');
    }
    else
    {
        $arSections[$ob['ID']] = intval($ob['IBLOCK_SECTION_ID']);
    }
}

$rs = CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => 2, 'ACTIVE' => 'Y', 'ACTIVE_DATE' => 'Y', '>PREVIEW_PICTURE' => '0', '>PROPERTY_CALC_VALUE'=> '0'],
    false,
    false,
    ['ID', 'IBLOCK_SECTION_ID', 'NAME', 'PROPERTY_CALC_VALUE', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL']
);
while ($ob = $rs->GetNext())
{
    $arRadiators[$ob['ID']] = [
        'name' => $ob['NAME'],
        'value' => $ob['PROPERTY_CALC_VALUE_VALUE'],
        'link' => $ob['DETAIL_PAGE_URL'],
        'img' => CFile::GetPath($ob['PREVIEW_PICTURE']),
        'type' => $arSections[$ob['IBLOCK_SECTION_ID']]
    ];
}

$edc = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity(\Bitrix\Highloadblock\HighloadBlockTable::getById(1)->fetch())->getDataClass();
$rs = $edc::getList(['order' => ['name' => 'asc'], 'select' => ['ID', 'name' => 'UF_NAME', 'value' => 'UF_VALUE', 'tempMiddle' => 'UF_MIDDLE_TEMP', 'countDay' => 'UF_CNT_DAY', 'region' => 'UF_REGION']]);
while ($ob = $rs->fetch())
{
    $arCities[$ob['ID']] = ['name' => $ob['name'], 'value' => $ob['value'], 'tempMiddle' => $ob['tempMiddle'], 'countDay' => $ob['countDay'], 'region' => REGIONS[$ob['region']]];
}

$edc = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity(\Bitrix\Highloadblock\HighloadBlockTable::getById(2)->fetch())->getDataClass();
$rs = $edc::getList(['select' => ['ID', 'name' => 'UF_NAME', 'value' => 'UF_VALUE', 'type' => 'UF_CATEGORY']]);
while ($ob = $rs->fetch())
{
    if (intval($ob['type']) === 1)
    {
        $arTypeWalls[$ob['ID']] = ['name' => $ob['name'], 'value' => $ob['value']];
    }
    else
    {
        $arTypeWindows[$ob['ID']] = ['name' => $ob['name'], 'value' => $ob['value']];
    }
}


// if($_GET['a'] === 'test'){
// 	$arSections = $arShops = $arOldShops = [];
// 	$rs = CIBlockSection::GetList([], ['IBLOCK_ID' => 8], false, ['ID', 'NAME', 'IBLOCK_SECTION_ID']);
// 	while ($ob = $rs->GetNext()) {
// 		$arSections[$ob['ID']] = ['NAME' => $ob['NAME'], 'PARENT' => $ob['IBLOCK_SECTION_ID']];
// 	}

// 	$rs = CIBlockElement::GetList([], ['IBLOCK_ID' => 8], false, false, ['ID', 'IBLOCK_SECTION_ID', 'NAME', 'PROPERTY_ADDRESS', 'PROPERTY_PHONE', 'PROPERTY_EMAIL', 'PROPERTY_WWW', 'PROPERTY_GEO']);
// 	while ($ob = $rs->GetNext()) {
// 		$citySect = $arSections[$ob['IBLOCK_SECTION_ID']];
// 		$arShops[$ob['ID']] = [
// 			'title' => trim(strtolower(html_entity_decode($ob['NAME']))),
// 			'region' => trim(strtolower(html_entity_decode($arSections[$citySect['PARENT']]['NAME']))),
// 			'city' => trim(strtolower(html_entity_decode($citySect['NAME']))),
// 			'address' => trim(strtolower(html_entity_decode($ob['PROPERTY_ADDRESS_VALUE']))),
// 			'phone' => trim(strtolower(html_entity_decode($ob['PROPERTY_PHONE_VALUE']))),
// 			'url' => trim(strtolower(html_entity_decode($ob['PROPERTY_WWW_VALUE']))),
// 			'email' => trim(strtolower(html_entity_decode($ob['PROPERTY_EMAIL_VALUE']))),
// 			// 'coords' => array_map(function($a){ return trim(strtolower($a)); }, explode(',', $ob['PROPERTY_GEO_VALUE'])),
// 			'coords' => $ob['PROPERTY_GEO_VALUE'],
// 		];
// 	}


// 	$xmlFile = simplexml_load_file($_SERVER['DOCUMENT_ROOT'].'/upload/dealers_global_2020-02-07_1501.xml');
// 	foreach ($xmlFile->{'dealer'} as $key => $dealer){
// 		$arOldShops[] = [
// 			'title' => trim(strtolower($dealer->{'title'}->__toString())),
// 			'region' => trim(strtolower($dealer->{'region'}->__toString())),
// 			'district' => trim(strtolower($dealer->{'district'}->__toString())),
// 			'city' => trim(strtolower($dealer->{'city'}->__toString())),
// 			'address' => trim(strtolower($dealer->{'address'}->__toString())),
// 			'phone' => trim(strtolower($dealer->{'phone'}->__toString())),
// 			'url' => trim(strtolower($dealer->{'url'}->__toString())),
// 			'email' => trim(strtolower($dealer->{'email'}->__toString())),
// 			// 'coords' => array_map(function($a){ return trim(strtolower($a)); }, explode(',', $dealer->{'coords'}->__toString())),
// 			'coords' => $dealer->{'coords'}->__toString(),
// 		];
// 	}

// 	$countNotFound = 0;
// 	foreach ($arOldShops as $key => $oldShop) {
// 		$isFind = false;
// 		foreach ($arShops as $key1 => $shop) {
// 			$checkFull = $oldShop['title'] === $shop['title'] && $oldShop['region'] === $shop['region'] && $oldShop['city'] === $shop['city'] && $oldShop['address'] === $shop['address'] && $oldShop['phone'] === $shop['phone'] && $oldShop['url'] === $shop['url'] && $oldShop['email'] === $shop['email'];
// 			$checkMoscow = $oldShop['title'] === $shop['title'] && $oldShop['region'] === $shop['city'] && $oldShop['address'] === $shop['address'] && $oldShop['phone'] === $shop['phone'] && $oldShop['url'] === $shop['url'] && $oldShop['email'] === $shop['email'] && $shop['city'] === 'москва';
// 			$checkPiter = $oldShop['title'] === $shop['title'] && $oldShop['region'] === $shop['city'] && $oldShop['address'] === $shop['address'] && $oldShop['phone'] === $shop['phone'] && $oldShop['url'] === $shop['url'] && $oldShop['email'] === $shop['email'] && $shop['city'] === 'санкт-петербург';

// 			if($checkFull || $checkMoscow || $checkPiter){
// 				$isFind = true;
// 				// if( !(is_array($oldShop['coords']) && count($oldShop['coords']) > 0 && is_array($shop['coords']) && count($shop['coords']) > 0
// 				// 	&& $oldShop['coords'][0] === $shop['coords'][0] && $oldShop['coords'][1] === $shop['coords'][1])
// 				// ){
// 				// 	echo '<pre>';
// 				// 	var_export([$oldShop, $shop['coords'], $key1]);
// 				// 	echo '</pre>';
// 				// }
// 				CIBlockElement::SetPropertyValuesEx($key1, 8, ['GEO' => $oldShop['coords']]);
// 				break;
// 			}
// 		}
// 		if(!$isFind){
// 			$countNotFound++;
// 			echo '<pre>';
// 			var_export($oldShop);
// 			echo '</pre>';
// 		}
// 	}

// 	echo '<pre>';
// 	var_export($countNotFound);
// 	echo '</pre>';
// }


//$arCurData = [['NAME' => 'Майкоп', 'DAY' => 148, 'TEMP' => '2,3'], ['NAME' => 'Алейск', 'DAY' => 216, 'TEMP' => '-7,8'], ['NAME' => 'Барнаул', 'DAY' => 213, 'TEMP' => '-7,5'], ['NAME' => 'Беля', 'DAY' => 223, 'TEMP' => '-2,7'], ['NAME' => 'Бийск-Зональная', 'DAY' => 213, 'TEMP' => '-7,6'], ['NAME' => 'Змеиногорск', 'DAY' => 211, 'TEMP' => '-6,7'], ['NAME' => 'Катанда', 'DAY' => 237, 'TEMP' => '-9,2'], ['NAME' => 'Кош-Агач', 'DAY' => 256, 'TEMP' => '-12,0'], ['NAME' => 'Онгудай', 'DAY' => 231, 'TEMP' => '-8,3'], ['NAME' => 'Родино', 'DAY' => 215, 'TEMP' => '-8,1'], ['NAME' => 'Рубцовск', 'DAY' => 206, 'TEMP' => '-7,9'], ['NAME' => 'Славгород', 'DAY' => 206, 'TEMP' => '-8,8'], ['NAME' => 'Тогул', 'DAY' => 225, 'TEMP' => '-7,3'], ['NAME' => 'Архара', 'DAY' => 211, 'TEMP' => '-12,7'], ['NAME' => 'Белогорск', 'DAY' => 223, 'TEMP' => '-11,9'], ['NAME' => 'Благовещенск', 'DAY' => 210, 'TEMP' => '-10,7'], ['NAME' => 'Бомнак', 'DAY' => 240, 'TEMP' => '-14,7'], ['NAME' => 'Братолюбовка', 'DAY' => 229, 'TEMP' => '-12,4'], ['NAME' => 'Бысса', 'DAY' => 236, 'TEMP' => '-13,6'], ['NAME' => 'Гош', 'DAY' => 233, 'TEMP' => '-14,0'], ['NAME' => 'Дамбуки', 'DAY' => 244, 'TEMP' => '-14,3'], ['NAME' => 'Ерофей Павлович', 'DAY' => 245, 'TEMP' => '-12,7'], ['NAME' => 'Завитинск', 'DAY' => 226, 'TEMP' => '-11,8'], ['NAME' => 'Зея', 'DAY' => 238, 'TEMP' => '-13,8'], ['NAME' => 'Норский Склад', 'DAY' => 232, 'TEMP' => '-14,3'], ['NAME' => 'Огорон', 'DAY' => 247, 'TEMP' => '-13,3'], ['NAME' => 'Поярково', 'DAY' => 222, 'TEMP' => '-11,9'], ['NAME' => 'Свободный', 'DAY' => 229, 'TEMP' => '-12,4'], ['NAME' => 'Сковородино', 'DAY' => 245, 'TEMP' => '-13,7'], ['NAME' => 'Средняя Нюкжа', 'DAY' => 262, 'TEMP' => '-16,1'], ['NAME' => 'Тыган-Уркан', 'DAY' => 245, 'TEMP' => '-12,4'], ['NAME' => 'Тында', 'DAY' => 258, 'TEMP' => '-14,7'], ['NAME' => 'Унаха', 'DAY' => 255, 'TEMP' => '-14,0'], ['NAME' => 'Усть-Нюкжа', 'DAY' => 252, 'TEMP' => '-15,1'], ['NAME' => 'Черняево', 'DAY' => 229, 'TEMP' => '-13,1'], ['NAME' => 'Шимановск', 'DAY' => 233, 'TEMP' => '-12,5'], ['NAME' => 'Экимчан', 'DAY' => 249, 'TEMP' => '-14,4'], ['NAME' => 'Архангельск', 'DAY' => 250, 'TEMP' => '-4,5'], ['NAME' => 'Борковская', 'DAY' => 277, 'TEMP' => '-6,6'], ['NAME' => 'Емецк', 'DAY' => 249, 'TEMP' => '-4,7'], ['NAME' => 'Койнас', 'DAY' => 262, 'TEMP' => '-6,2'], ['NAME' => 'Котлас', 'DAY' => 237, 'TEMP' => '-5,0'], ['NAME' => 'Мезень', 'DAY' => 268, 'TEMP' => '-5,3'], ['NAME' => 'Онега', 'DAY' => 243, 'TEMP' => '-4,0'], ['NAME' => 'Астрахань', 'DAY' => 164, 'TEMP' => '-0,8'], ['NAME' => 'Верхний Баскунчак', 'DAY' => 174, 'TEMP' => '-2,5'], ['NAME' => 'Белорецк', 'DAY' => 231, 'TEMP' => '-6,5'], ['NAME' => 'Дуван', 'DAY' => 224, 'TEMP' => '-6,0'], ['NAME' => 'Мелеуз', 'DAY' => 210, 'TEMP' => '-6,4'], ['NAME' => 'Уфа', 'DAY' => 209, 'TEMP' => '-6,0'], ['NAME' => 'Янаул', 'DAY' => 218, 'TEMP' => '-6,1'], ['NAME' => 'Белгород', 'DAY' => 191, 'TEMP' => '-1,9'], ['NAME' => 'Брянск', 'DAY' => 199, 'TEMP' => '-2,0'], ['NAME' => 'Бабушкин', 'DAY' => 250, 'TEMP' => '-5,5'], ['NAME' => 'Баргузин', 'DAY' => 240, 'TEMP' => '-11,7'], ['NAME' => 'Багдарин', 'DAY' => 261, 'TEMP' => '-13,4'], ['NAME' => 'Кяхта', 'DAY' => 229, 'TEMP' => '-8,7'], ['NAME' => 'Монды', 'DAY' => 266, 'TEMP' => '-8,1'], ['NAME' => 'Нижнеангарск', 'DAY' => 255, 'TEMP' => '-9,6'], ['NAME' => 'Сосново-Озерское', 'DAY' => 258, 'TEMP' => '-10,5'], ['NAME' => 'Уакит', 'DAY' => 274, 'TEMP' => '-12,7'], ['NAME' => 'Улан-Удэ', 'DAY' => 230, 'TEMP' => '-10,3'], ['NAME' => 'Хоринск', 'DAY' => 241, 'TEMP' => '-10,8'], ['NAME' => 'Владимир', 'DAY' => 213, 'TEMP' => '-3,5'], ['NAME' => 'Муром', 'DAY' => 214, 'TEMP' => '-4,0'], ['NAME' => 'Волгоград', 'DAY' => 176, 'TEMP' => '-2,3'], ['NAME' => 'Камышин', 'DAY' => 188, 'TEMP' => '-4,1'], ['NAME' => 'Костычевка', 'DAY' => 190, 'TEMP' => '-3,9'], ['NAME' => 'Котельниково', 'DAY' => 176, 'TEMP' => '-1,6'], ['NAME' => 'Новоаннинский', 'DAY' => 191, 'TEMP' => '-3,4'], ['NAME' => 'Эльтон', 'DAY' => 177, 'TEMP' => '-3,2'], ['NAME' => 'Бабаево', 'DAY' => 231, 'TEMP' => '-3,8'], ['NAME' => 'Вологда', 'DAY' => 228, 'TEMP' => '-4'], ['NAME' => 'Вытегра', 'DAY' => 230, 'TEMP' => '-3,4'], ['NAME' => 'Никольск', 'DAY' => 231, 'TEMP' => '-4,7'], ['NAME' => 'Тотьма', 'DAY' => 232, 'TEMP' => '-4,5'], ['NAME' => 'Воронеж', 'DAY' => 190, 'TEMP' => '-2,5'], ['NAME' => 'Дербент', 'DAY' => 138, 'TEMP' => '3,7'], ['NAME' => 'Махачкала', 'DAY' => 144, 'TEMP' => '2,7'], ['NAME' => 'Южно-Сухокумск', 'DAY' => 162, 'TEMP' => '0,8'], ['NAME' => 'Иваново', 'DAY' => 219, 'TEMP' => '-3,9'], ['NAME' => 'Кинешма', 'DAY' => 221, 'TEMP' => '-4,1'], ['NAME' => 'Алыгджер', 'DAY' => 264, 'TEMP' => '-6,4'], ['NAME' => 'Бодайбо', 'DAY' => 253, 'TEMP' => '-14,1'], ['NAME' => 'Братск', 'DAY' => 249, 'TEMP' => '-8,6'], ['NAME' => 'Верхняя Гутара', 'DAY' => 267, 'TEMP' => '-7,7'], ['NAME' => 'Дубровское', 'DAY' => 257, 'TEMP' => '-12,3'], ['NAME' => 'Ербогачен', 'DAY' => 261, 'TEMP' => '-15,3'], ['NAME' => 'Жигалово', 'DAY' => 249, 'TEMP' => '-12,3'], ['NAME' => 'Зима', 'DAY' => 239, 'TEMP' => '-9,7'], ['NAME' => 'Ика', 'DAY' => 263, 'TEMP' => '-13,8'], ['NAME' => 'Илимск', 'DAY' => 255, 'TEMP' => '-11,0'], ['NAME' => 'Иркутск', 'DAY' => 232, 'TEMP' => '-7,7'], ['NAME' => 'Ичера', 'DAY' => 254, 'TEMP' => '-12,9'], ['NAME' => 'Киренск', 'DAY' => 251, 'TEMP' => '-12,8'], ['NAME' => 'Мама', 'DAY' => 255, 'TEMP' => '-12,6'], ['NAME' => 'Марково', 'DAY' => 250, 'TEMP' => '-12,3'], ['NAME' => 'Наканно', 'DAY' => 266, 'TEMP' => '-16,9'], ['NAME' => 'Невон', 'DAY' => 253, 'TEMP' => '-11,1'], ['NAME' => 'Непа', 'DAY' => 261, 'TEMP' => '-12,9'], ['NAME' => 'Орлинга', 'DAY' => 252, 'TEMP' => '-12,0'], ['NAME' => 'Перевоз', 'DAY' => 258, 'TEMP' => '-12,6'], ['NAME' => 'Преображенка', 'DAY' => 259, 'TEMP' => '-13,3'], ['NAME' => 'Саянск', 'DAY' => 234, 'TEMP' => '-9,1'], ['NAME' => 'Слюдянка', 'DAY' => 254, 'TEMP' => '-6,4'], ['NAME' => 'Тайшет', 'DAY' => 237, 'TEMP' => '-8,1'], ['NAME' => 'Тулун', 'DAY' => 241, 'TEMP' => '-8,6'], ['NAME' => 'Усть-Ордынский - Усть-Ордынский Бурятский АО', 'DAY' => 243, 'TEMP' => '-10,9'], ['NAME' => 'Нальчик', 'DAY' => 168, 'TEMP' => '0,6'], ['NAME' => 'Калининград', 'DAY' => 188, 'TEMP' => '1,2'], ['NAME' => 'Элиста', 'DAY' => 169, 'TEMP' => '-1'], ['NAME' => 'Калуга', 'DAY' => 210, 'TEMP' => '-2,9'], ['NAME' => 'Апука - Корякский АО', 'DAY' => 286, 'TEMP' => '-5,8'], ['NAME' => 'Ича - Корякский АО', 'DAY' => 276, 'TEMP' => '-4,1'], ['NAME' => 'Ключи', 'DAY' => 251, 'TEMP' => '-6,6'], ['NAME' => 'Козыревск', 'DAY' => 256, 'TEMP' => '-7,3'], ['NAME' => 'Корф - Корякский АО', 'DAY' => 270, 'TEMP' => '-7,1'], ['NAME' => 'Лопатка, мыс', 'DAY' => 297, 'TEMP' => '-0,2'], ['NAME' => 'Мильково', 'DAY' => 256, 'TEMP' => '-8,3'], ['NAME' => 'Начики', 'DAY' => 275, 'TEMP' => '-7,4'], ['NAME' => 'о.Беринга', 'DAY' => 283, 'TEMP' => '0,4'], ['NAME' => 'Оссора - Корякский АО', 'DAY' => 272, 'TEMP' => '-6,6'], ['NAME' => 'Петропавловск', 'DAY' => 250, 'TEMP' => '-1,7'],
//['NAME' => 'Семлячики', 'DAY' => 260, 'TEMP' => '-1,5'], ['NAME' => 'Соболево', 'DAY' => 268, 'TEMP' => '-5,1'], ['NAME' => 'Кроноки', 'DAY' => 280, 'TEMP' => '-2,2'], ['NAME' => 'Ука', 'DAY' => 281, 'TEMP' => '-6,7'], ['NAME' => 'Октябрьская', 'DAY' => 281, 'TEMP' => '-3,5'], ['NAME' => 'Усть-Воямполка - Корякский АО', 'DAY' => 286, 'TEMP' => '-6,8'], ['NAME' => 'Усть-Камчатск', 'DAY' => 277, 'TEMP' => '-4,0'], ['NAME' => 'Усть-Хайрюзово', 'DAY' => 273, 'TEMP' => '-5,2'], ['NAME' => 'Черкесск', 'DAY' => 169, 'TEMP' => '0,6'], ['NAME' => 'Кемь', 'DAY' => 255, 'TEMP' => '-3,5'], ['NAME' => 'Лоухи', 'DAY' => 261, 'TEMP' => '-4,2'], ['NAME' => 'Олонец', 'DAY' => 233, 'TEMP' => '-3,2'], ['NAME' => 'Паданы', 'DAY' => 246, 'TEMP' => '-3,7'], ['NAME' => 'Петрозаводск', 'DAY' => 235, 'TEMP' => '-3,2'], ['NAME' => 'Реболы', 'DAY' => 248, 'TEMP' => '-4,2'], ['NAME' => 'Сортавала', 'DAY' => 232, 'TEMP' => '-2,5'], ['NAME' => 'Кемерово', 'DAY' => 227, 'TEMP' => '-8'], ['NAME' => 'Киселевск', 'DAY' => 227, 'TEMP' => '-7,3'], ['NAME' => 'Кондома', 'DAY' => 236, 'TEMP' => '-7,8'], ['NAME' => 'Мариинск', 'DAY' => 235, 'TEMP' => '-7,7'], ['NAME' => 'Тайга', 'DAY' => 240, 'TEMP' => '-8'], ['NAME' => 'Тисуль', 'DAY' => 231, 'TEMP' => '-7,1'], ['NAME' => 'Топки', 'DAY' => 235, 'TEMP' => '-8,2'], ['NAME' => 'Усть-Кабырза', 'DAY' => 241, 'TEMP' => '-9,0'], ['NAME' => 'Вятка', 'DAY' => 231, 'TEMP' => '-5,4'], ['NAME' => 'Нагорское', 'DAY' => 239, 'TEMP' => '-5,8'], ['NAME' => 'Савали', 'DAY' => 220, 'TEMP' => '-5,7'], ['NAME' => 'Вендинга', 'DAY' => 257, 'TEMP' => '-5,9'], ['NAME' => 'Воркута', 'DAY' => 306, 'TEMP' => '-9,1'], ['NAME' => 'Объячево', 'DAY' => 239, 'TEMP' => '-5,3'], ['NAME' => 'Петрунь', 'DAY' => 285, 'TEMP' => '-8,8'], ['NAME' => 'Печора', 'DAY' => 268, 'TEMP' => '-7,9'], ['NAME' => 'Сыктывкар', 'DAY' => 243, 'TEMP' => '-5,6'], ['NAME' => 'Троицко-Печорск', 'DAY' => 258, 'TEMP' => '-6,9'], ['NAME' => 'Усть-Уса', 'DAY' => 278, 'TEMP' => '-7,9'], ['NAME' => 'Усть-Цильма', 'DAY' => 270, 'TEMP' => '-6,9'], ['NAME' => 'Усть-Щугор', 'DAY' => 268, 'TEMP' => '-7,9'], ['NAME' => 'Ухта', 'DAY' => 261, 'TEMP' => '-6,4'], ['NAME' => 'Кострома', 'DAY' => 222, 'TEMP' => '-3,9'], ['NAME' => 'Чухлома', 'DAY' => 230, 'TEMP' => '-4,3'], ['NAME' => 'Шарья', 'DAY' => 228, 'TEMP' => '-4,7'], ['NAME' => 'Красная Поляна', 'DAY' => 155, 'TEMP' => '3,0'], ['NAME' => 'Краснодар', 'DAY' => 145, 'TEMP' => '2,5'], ['NAME' => 'Приморско-Ахтарск', 'DAY' => 159, 'TEMP' => '1,0'], ['NAME' => 'Сочи', 'DAY' => 94, 'TEMP' => '6,6'], ['NAME' => 'Тихорецк', 'DAY' => 156, 'TEMP' => '1,2'], ['NAME' => 'Агата', 'DAY' => 292, 'TEMP' => '-16,7'], ['NAME' => 'Ачинск', 'DAY' => 232, 'TEMP' => '-7'], ['NAME' => 'Байкит', 'DAY' => 266, 'TEMP' => '-14,1'], ['NAME' => 'Боготол', 'DAY' => 239, 'TEMP' => '-7,6'], ['NAME' => 'Богучаны', 'DAY' => 244, 'TEMP' => '-10,7'], ['NAME' => 'Ванавара', 'DAY' => 260, 'TEMP' => '-14'], ['NAME' => 'Вельмо', 'DAY' => 264, 'TEMP' => '-12,5'], ['NAME' => 'Верхнеимбатск', 'DAY' => 265, 'TEMP' => '-11,7'], ['NAME' => 'Волочанка', 'DAY' => 300, 'TEMP' => '-17'], ['NAME' => 'Диксон', 'DAY' => 365, 'TEMP' => '-11,5'], ['NAME' => 'Дудинка', 'DAY' => 296, 'TEMP' => '-15,2'], ['NAME' => 'Енисейск', 'DAY' => 245, 'TEMP' => '-9,6'], ['NAME' => 'Ессей', 'DAY' => 296, 'TEMP' => '-18,4'], ['NAME' => 'Игарка', 'DAY' => 292, 'TEMP' => '-16,7'], ['NAME' => 'Канск', 'DAY' => 237, 'TEMP' => '-8,8'], ['NAME' => 'Кежма', 'DAY' => 252, 'TEMP' => '-12,3'], ['NAME' => 'Ключи', 'DAY' => 240, 'TEMP' => '-7,4'], ['NAME' => 'Красноярск', 'DAY' => 233, 'TEMP' => '-6,7'], ['NAME' => 'Минусинск', 'DAY' => 221, 'TEMP' => '-7,9'], ['NAME' => 'Таимба', 'DAY' => 264, 'TEMP' => '-13,6'], ['NAME' => 'Троицкое', 'DAY' => 251, 'TEMP' => '-9,8'], ['NAME' => 'Тура', 'DAY' => 270, 'TEMP' => '-17,2'], ['NAME' => 'Туруханск', 'DAY' => 274, 'TEMP' => '-13,3'], ['NAME' => 'Хатанга', 'DAY' => 304, 'TEMP' => '-18'], ['NAME' => 'Челюскин, мыс', 'DAY' => 365, 'TEMP' => '-14,7'], ['NAME' => 'Ярцево', 'DAY' => 254, 'TEMP' => '-10,8'], ['NAME' => 'Курган', 'DAY' => 212, 'TEMP' => '-7,6'], ['NAME' => 'Курск', 'DAY' => 194, 'TEMP' => '-2,3'], ['NAME' => 'Липецк', 'DAY' => 202, 'TEMP' => '-3,4'], ['NAME' => 'Санкт-Петербург', 'DAY' => 213, 'TEMP' => '-1,3'], ['NAME' => 'Свирица', 'DAY' => 228, 'TEMP' => '-2,9'], ['NAME' => 'Тихвин', 'DAY' => 223, 'TEMP' => '-2,7'], ['NAME' => 'Аркагала', 'DAY' => 289, 'TEMP' => '-19,0'], ['NAME' => 'Брохово', 'DAY' => 278, 'TEMP' => '-9,3'], ['NAME' => 'Магадан (Нагаева, бухта)', 'DAY' => 279, 'TEMP' => '-7,5'], ['NAME' => 'Омсукчан', 'DAY' => 286, 'TEMP' => '-17,2'], ['NAME' => 'Палатка', 'DAY' => 280, 'TEMP' => '-10,7'], ['NAME' => 'Среднекан', 'DAY' => 274, 'TEMP' => '-19,3'], ['NAME' => 'Сусуман', 'DAY' => 274, 'TEMP' => '-20,8'], ['NAME' => 'Йошкар-Ола', 'DAY' => 215, 'TEMP' => '-4,9'], ['NAME' => 'Саранск', 'DAY' => 209, 'TEMP' => '-4,5'], ['NAME' => 'Дмитров', 'DAY' => 216, 'TEMP' => '-3,1'], ['NAME' => 'Кашира', 'DAY' => 212, 'TEMP' => '-3,4'], ['NAME' => 'Москва', 'DAY' => 205, 'TEMP' => '-2,2'], ['NAME' => 'Новомосковский АО', 'DAY' => 212, 'TEMP' => '-2,4'], ['NAME' => 'Троицкий АО', 'DAY' => 211, 'TEMP' => '-2,6'], ['NAME' => 'Вайда-Губа', 'DAY' => 287, 'TEMP' => '-0,8'], ['NAME' => 'Кандалакша', 'DAY' => 265, 'TEMP' => '-4,6'], ['NAME' => 'Ковдор', 'DAY' => 271, 'TEMP' => '-4,9'], ['NAME' => 'Краснощелье', 'DAY' => 279, 'TEMP' => '-5,4'], ['NAME' => 'Ловозеро', 'DAY' => 281, 'TEMP' => '-5,0'], ['NAME' => 'Мончегорск', 'DAY' => 271, 'TEMP' => '-4,5'], ['NAME' => 'Мурманск', 'DAY' => 275, 'TEMP' => '-3,4'], ['NAME' => 'Ниванкюль', 'DAY' => 271, 'TEMP' => '-4,6'], ['NAME' => 'Пулозеро', 'DAY' => 277, 'TEMP' => '-4,8'], ['NAME' => 'Пялица', 'DAY' => 298, 'TEMP' => '-2,8'], ['NAME' => 'Териберка', 'DAY' => 282, 'TEMP' => '-2,2'], ['NAME' => 'Терско-Орловский', 'DAY' => 312, 'TEMP' => '-2,5'], ['NAME' => 'Умба', 'DAY' => 263, 'TEMP' => '-4'], ['NAME' => 'Юкспор', 'DAY' => 340, 'TEMP' => '-4,5'], ['NAME' => 'Арзамас', 'DAY' => 216, 'TEMP' => '-4,7'], ['NAME' => 'Выкса', 'DAY' => 212, 'TEMP' => '-4,0'], ['NAME' => 'Нижний Новгород', 'DAY' => 215, 'TEMP' => '-4,1'], ['NAME' => 'Боровичи', 'DAY' => 220, 'TEMP' => '-2,8'], ['NAME' => 'Новгород', 'DAY' => 221, 'TEMP' => '-2,3'], ['NAME' => 'Барабинск', 'DAY' => 230, 'TEMP' => '-9'], ['NAME' => 'Болотное', 'DAY' => 228, 'TEMP' => '-7,9'], ['NAME' => 'Карасук', 'DAY' => 218, 'TEMP' => '-8,9'], ['NAME' => 'Кочки', 'DAY' => 228, 'TEMP' => '-8,9'], ['NAME' => 'Купино', 'DAY' => 215, 'TEMP' => '-8,9'], ['NAME' => 'Кыштовка', 'DAY' => 231, 'TEMP' => '-8,9'], ['NAME' => 'Новосибирск', 'DAY' => 221, 'TEMP' => '-8,1'], ['NAME' => 'Татарск', 'DAY' => 220, 'TEMP' => '-8,3'], ['NAME' => 'Чулым', 'DAY' => 230, 'TEMP' => '-8,8'], ['NAME' => 'Исиль-Куль', 'DAY' => 225, 'TEMP' => '-8,6'], ['NAME' => 'Омск', 'DAY' => 216, 'TEMP' => '-8,1'], ['NAME' => 'Тара', 'DAY' => 229, 'TEMP' => '-8,2'], ['NAME' => 'Черлак', 'DAY' => 211, 'TEMP' => '-8,7'], ['NAME' => 'Кувандык', 'DAY' => 204, 'TEMP' => '-6,9'], ['NAME' => 'Оренбург', 'DAY' => 195, 'TEMP' => '-6,1'], ['NAME' => 'Сорочинск', 'DAY' => 201, 'TEMP' => '-6,3'], ['NAME' => 'Орел', 'DAY' => 199, 'TEMP' => '-2,4'], ['NAME' => 'Земетчино', 'DAY' => 200, 'TEMP' => '-3,8'], ['NAME' => 'Пенза', 'DAY' => 200, 'TEMP' => '-4,1'], ['NAME' => 'Бисер', 'DAY' => 250, 'TEMP' => '-6,7'], ['NAME' => 'Ножовка', 'DAY' => 221, 'TEMP' => '-6,1'], ['NAME' => 'Пермь', 'DAY' => 225, 'TEMP' => '-5,5'], ['NAME' => 'Чердынь', 'DAY' => 245, 'TEMP' => '-6,7'], ['NAME' => 'Агзу', 'DAY' => 231, 'TEMP' => '-7,9'], ['NAME' => 'Анучино', 'DAY' => 203, 'TEMP' => '-8,1'], ['NAME' => 'Астраханка', 'DAY' => 202, 'TEMP' => '-6,6'], ['NAME' => 'Богополь', 'DAY' => 208, 'TEMP' => '-4,2'], ['NAME' => 'Владивосток', 'DAY' => 198, 'TEMP' => '-4,3'], ['NAME' => 'Дальнереченск', 'DAY' => 199, 'TEMP' => '-8,7'], ['NAME' => 'Кировский', 'DAY' => 201, 'TEMP' => '-8,8'], ['NAME' => 'Красный Яр', 'DAY' => 217, 'TEMP' => '-10,0'], ['NAME' => 'Маргаритово', 'DAY' => 209, 'TEMP' => '-4,1'], ['NAME' => 'Мельничное', 'DAY' => 221, 'TEMP' => '-9,6'], ['NAME' => 'Партизанск', 'DAY' => 198, 'TEMP' => '-4,5'], ['NAME' => 'Посьет', 'DAY' => 187, 'TEMP' => '-2,9'], ['NAME' => 'Преображение', 'DAY' => 202, 'TEMP' => '-1,6'], ['NAME' => 'Рудная Пристань', 'DAY' => 215, 'TEMP' => '-3,1'], ['NAME' => 'Сосуново', 'DAY' => 245, 'TEMP' => '-3,8'], ['NAME' => 'Чугуевка', 'DAY' => 211, 'TEMP' => '-8,6'], ['NAME' => 'Великие Луки', 'DAY' => 208, 'TEMP' => '-1,5'], ['NAME' => 'Псков', 'DAY' => 208, 'TEMP' => '-1,3'], ['NAME' => 'Миллерово', 'DAY' => 179, 'TEMP' => '-1,7'], ['NAME' => 'Ростов-на-Дону', 'DAY' => 166, 'TEMP' => '-0,1'], ['NAME' => 'Таганрог', 'DAY' => 165, 'TEMP' => '0'], ['NAME' => 'Рязань', 'DAY' => 208, 'TEMP' => '-3,5'], ['NAME' => 'Самара', 'DAY' => 203, 'TEMP' => '-5,2'], ['NAME' => 'Александров Гай', 'DAY' => 191, 'TEMP' => '-5,2'], ['NAME' => 'Балашов', 'DAY' => 199, 'TEMP' => '-4,2'], ['NAME' => 'Саратов', 'DAY' => 188, 'TEMP' => '-3,5'], ['NAME' => 'Александровск-Сахалинский', 'DAY' => 237, 'TEMP' => '-6,4'], ['NAME' => 'Долинск', 'DAY' => 231, 'TEMP' => '-4,0'], ['NAME' => 'Кировское', 'DAY' => 246, 'TEMP' => '-9,2'], ['NAME' => 'Корсаков', 'DAY' => 232, 'TEMP' => '-2,7'], ['NAME' => 'Курильск', 'DAY' => 223, 'TEMP' => '-0,4'], ['NAME' => 'Макаров', 'DAY' => 241, 'TEMP' => '-4,2'], ['NAME' => 'Невельск', 'DAY' => 219, 'TEMP' => '-2,1'], ['NAME' => 'Ноглики', 'DAY' => 254, 'TEMP' => '-7,2'], ['NAME' => 'Оха', 'DAY' => 266, 'TEMP' => '-7,3'], ['NAME' => 'Погиби', 'DAY' => 249, 'TEMP' => '-8,7'], ['NAME' => 'Поронайск', 'DAY' => 245, 'TEMP' => '-5,8'], ['NAME' => 'Рыбновск', 'DAY' => 255, 'TEMP' => '-8,9'], ['NAME' => 'Холмск', 'DAY' => 220, 'TEMP' => '-2,3'], ['NAME' => 'Южно-Курильск', 'DAY' => 225, 'TEMP' => '0'], ['NAME' => 'Южно-Сахалинск', 'DAY' => 227, 'TEMP' => '-4,4'], ['NAME' => 'Верхотурье', 'DAY' => 233, 'TEMP' => '-6,4'], ['NAME' => 'Екатеринбург', 'DAY' => 221, 'TEMP' => '-5,4'], ['NAME' => 'Ивдель', 'DAY' => 245, 'TEMP' => '-7,6'], ['NAME' => 'Каменск-Уральский', 'DAY' => 222, 'TEMP' => '-6,9'], ['NAME' => 'Туринск', 'DAY' => 226, 'TEMP' => '-7,7'], ['NAME' => 'Шамары', 'DAY' => 235, 'TEMP' => '-6,4'], ['NAME' => 'Владикавказ', 'DAY' => 169, 'TEMP' => '0,7'], ['NAME' => 'Вязьма', 'DAY' => 217, 'TEMP' => '-2,8'], ['NAME' => 'Смоленск', 'DAY' => 209, 'TEMP' => '-2'], ['NAME' => 'Арзгир', 'DAY' => 163, 'TEMP' => '0,1'], ['NAME' => 'Кисловодск', 'DAY' => 179, 'TEMP' => '0,4'], ['NAME' => 'Невинномысск', 'DAY' => 168, 'TEMP' => '0,1'], ['NAME' => 'Пятигорск', 'DAY' => 175, 'TEMP' => '0,2'], ['NAME' => 'Ставрополь', 'DAY' => 168, 'TEMP' => '0,5'], ['NAME' => 'Тамбов', 'DAY' => 201, 'TEMP' => '-3,7'], ['NAME' => 'Бугульма', 'DAY' => 221, 'TEMP' => '-5,8'], ['NAME' => 'Елабуга', 'DAY' => 209, 'TEMP' => '-5,2'], ['NAME' => 'Казань', 'DAY' => 208, 'TEMP' => '-4,8'], ['NAME' => 'Бежецк', 'DAY' => 222, 'TEMP' => '-3,4'], ['NAME' => 'Ржев', 'DAY' => 217, 'TEMP' => '-2,7'], ['NAME' => 'Тверь', 'DAY' => 218, 'TEMP' => '-3,0'], ['NAME' => 'Александровское', 'DAY' => 252, 'TEMP' => '-9,5'], ['NAME' => 'Колпашево', 'DAY' => 243, 'TEMP' => '-8,8'], ['NAME' => 'Средний Васюган', 'DAY' => 243, 'TEMP' => '-8,8'], ['NAME' => 'Томск', 'DAY' => 233, 'TEMP' => '-7,9'], ['NAME' => 'Усть-Озерное', 'DAY' => 249, 'TEMP' => '-9,3'], ['NAME' => 'Кызыл', 'DAY' => 225, 'TEMP' => '-15'], ['NAME' => 'Тула', 'DAY' => 207, 'TEMP' => '-3'], ['NAME' => 'Березово - Ханты-Мансийский АО', 'DAY' => 266, 'TEMP' => '-9,9'], ['NAME' => 'Демьянское', 'DAY' => 241, 'TEMP' => '-8,0'], ['NAME' => 'Кондинское - Ханты-Мансийский АО', 'DAY' => 238, 'TEMP' => '-8,6'], ['NAME' => 'Леуши', 'DAY' => 237, 'TEMP' => '-7,4'], ['NAME' => 'Марресаля', 'DAY' => 365, 'TEMP' => '-8'], ['NAME' => 'Надым', 'DAY' => 278, 'TEMP' => '-11,5'], ['NAME' => 'Октябрьское', 'DAY' => 257, 'TEMP' => '-9,1'], ['NAME' => 'Салехард', 'DAY' => 285, 'TEMP' => '-11,5'], ['NAME' => 'Сосьва', 'DAY' => 261, 'TEMP' => '-9,5'], ['NAME' => 'Сургут - Ханты-Мансийский АО', 'DAY' => 257, 'TEMP' => '-9,9'], ['NAME' => 'Тарко-Сале - Ямало-Ненецкий АО', 'DAY' => 274, 'TEMP' => '-12,6'], ['NAME' => 'Тобольск', 'DAY' => 232, 'TEMP' => '-7,9'], ['NAME' => 'Тюмень', 'DAY' => 223, 'TEMP' => '-6,9'], ['NAME' => 'Угут', 'DAY' => 251, 'TEMP' => '-9,1'], ['NAME' => 'Уренгой', 'DAY' => 286, 'TEMP' => '-13,1'], ['NAME' => 'Ямало-Ненецкий АО', 'DAY' => 286, 'TEMP' => '-13,1'], ['NAME' => 'Ханты-Мансийск', 'DAY' => 247, 'TEMP' => '-8,8'], ['NAME' => 'Глазов', 'DAY' => 231, 'TEMP' => '-6'], ['NAME' => 'Ижевск', 'DAY' => 219, 'TEMP' => '-5,6'], ['NAME' => 'Сарапул', 'DAY' => 215, 'TEMP' => '-5,6'], ['NAME' => 'Сурское', 'DAY' => 211, 'TEMP' => '-4,8'], ['NAME' => 'Ульяновск', 'DAY' => 212, 'TEMP' => '-5,4'], ['NAME' => 'Аян', 'DAY' => 278, 'TEMP' => '-7,4'], ['NAME' => 'Байдуков', 'DAY' => 255, 'TEMP' => '-9,0'], ['NAME' => 'Бикин', 'DAY' => 208, 'TEMP' => '-9,1'], ['NAME' => 'Бира', 'DAY' => 220, 'TEMP' => '-9,1'], ['NAME' => 'Биробиджан', 'DAY' => 219, 'TEMP' => '-10,4'], ['NAME' => 'Вяземский', 'DAY' => 213, 'TEMP' => '-9,3'], ['NAME' => 'Гвасюги', 'DAY' => 228, 'TEMP' => '-10,4'], ['NAME' => 'Гроссевичи', 'DAY' => 248, 'TEMP' => '-4,3'], ['NAME' => 'Де-Кастри', 'DAY' => 256, 'TEMP' => '-6,9'], ['NAME' => 'Джаорэ', 'DAY' => 252, 'TEMP' => '-7,9'], ['NAME' => 'Екатерино-Никольское', 'DAY' => 204, 'TEMP' => '-9,3'], ['NAME' => 'Комсомольск-на-Амуре', 'DAY' => 223, 'TEMP' => '-10,8'], ['NAME' => 'Нижнетамбовское', 'DAY' => 229, 'TEMP' => '-10,9'], ['NAME' => 'Нико1лаевск-на-Амуре', 'DAY' => 245, 'TEMP' => '-10,1'], ['NAME' => 'Облучье', 'DAY' => 227, 'TEMP' => '-11,5'], ['NAME' => 'Охотск', 'DAY' => 274, 'TEMP' => '-9,6'], ['NAME' => 'Им.Полины Осипенко', 'DAY' => 232, 'TEMP' => '-12,5'], ['NAME' => 'Сизиман', 'DAY' => 263, 'TEMP' => '-6,2'], ['NAME' => 'Советская Гавань', 'DAY' => 234, 'TEMP' => '-6'], ['NAME' => 'Софийский Прииск', 'DAY' => 262, 'TEMP' => '-14,7'], ['NAME' => 'Средний Ургал', 'DAY' => 238, 'TEMP' => '-13,3'], ['NAME' => 'Троицкое', 'DAY' => 217, 'TEMP' => '-9,7'], ['NAME' => 'Хабаровск', 'DAY' => 204, 'TEMP' => '-9,5'], ['NAME' => 'Чумикан', 'DAY' => 274, 'TEMP' => '-8,8'], ['NAME' => 'Энкэн', 'DAY' => 281, 'TEMP' => '-7,7'], ['NAME' => 'Абакан', 'DAY' => 223, 'TEMP' => '-7,9'], ['NAME' => 'Шира', 'DAY' => 236, 'TEMP' => '-7,7'], ['NAME' => 'Верхнеуральск', 'DAY' => 221, 'TEMP' => '-7,5'], ['NAME' => 'Нязепетровск', 'DAY' => 229, 'TEMP' => '-6,8'], ['NAME' => 'Челябинск', 'DAY' => 218, 'TEMP' => '-6,5'], ['NAME' => 'Грозный', 'DAY' => 159, 'TEMP' => '0,9'], ['NAME' => 'Агинское', 'DAY' => 238, 'TEMP' => '-10,4'], ['NAME' => 'Акша', 'DAY' => 237, 'TEMP' => '-9,6'], ['NAME' => 'Александровский Завод', 'DAY' => 250, 'TEMP' => '-12,0'], ['NAME' => 'Борзя', 'DAY' => 232, 'TEMP' => '-12,4'], ['NAME' => 'Дарасун', 'DAY' => 247, 'TEMP' => '-9,5'], ['NAME' => 'Калакан', 'DAY' => 257, 'TEMP' => '-16,3'], ['NAME' => 'Красный Чикой', 'DAY' => 240, 'TEMP' => '-11,2'], ['NAME' => 'Могоча', 'DAY' => 250, 'TEMP' => '-13,8'], ['NAME' => 'Нерчинск', 'DAY' => 233, 'TEMP' => '-14,1'], ['NAME' => 'Нерчинский Завод', 'DAY' => 233, 'TEMP' => '-12,7'], ['NAME' => 'Средний Калар', 'DAY' => 271, 'TEMP' => '-16,4'], ['NAME' => 'Тунгокочен', 'DAY' => 262, 'TEMP' => '-13,8'], ['NAME' => 'Тупик', 'DAY' => 260, 'TEMP' => '-14,8'], ['NAME' => 'Чара', 'DAY' => 263, 'TEMP' => '-15,8'], ['NAME' => 'Чита', 'DAY' => 238, 'TEMP' => '-11,3'], ['NAME' => 'Порецкое', 'DAY' => 207, 'TEMP' => '-4,5'], ['NAME' => 'Чебоксары', 'DAY' => 217, 'TEMP' => '-4,9'], ['NAME' => 'Анадырь', 'DAY' => 299, 'TEMP' => '-11,3'], ['NAME' => 'Березово', 'DAY' => 296, 'TEMP' => '-13,6'], ['NAME' => 'Марково', 'DAY' => 274, 'TEMP' => '-15,3'], ['NAME' => 'Омолон', 'DAY' => 283, 'TEMP' => '-19,8'], ['NAME' => 'Островное', 'DAY' => 278, 'TEMP' => '-19'], ['NAME' => 'Усть-Олой', 'DAY' => 278, 'TEMP' => '-19,6'], ['NAME' => 'Эньмувеем', 'DAY' => 283, 'TEMP' => '-15,3'], ['NAME' => 'Алдан', 'DAY' => 263, 'TEMP' => '-13,6'], ['NAME' => 'Аллах-Юнь', 'DAY' => 280, 'TEMP' => '-21,4'], ['NAME' => 'Амга', 'DAY' => 259, 'TEMP' => '-21,3'], ['NAME' => 'Батамай', 'DAY' => 265, 'TEMP' => '-20,8'], ['NAME' => 'Бердигястях', 'DAY' => 268, 'TEMP' => '-19,6'], ['NAME' => 'Буяга', 'DAY' => 266, 'TEMP' => '-18,2'], ['NAME' => 'Верхоянск', 'DAY' => 272, 'TEMP' => '-25'], ['NAME' => 'Вилюйск', 'DAY' => 259, 'TEMP' => '-18,8'], ['NAME' => 'Витим', 'DAY' => 255, 'TEMP' => '-13,8'], ['NAME' => 'Воронцово', 'DAY' => 297, 'TEMP' => '-19,6'], ['NAME' => 'Джалинда', 'DAY' => 296, 'TEMP' => '-19,5'], ['NAME' => 'Джарджан', 'DAY' => 283, 'TEMP' => '-19,8'], ['NAME' => 'Джикимда', 'DAY' => 256, 'TEMP' => '-16,6'], ['NAME' => 'Дружина', 'DAY' => 284, 'TEMP' => '-20,2'], ['NAME' => 'Екючю', 'DAY' => 281, 'TEMP' => '-23,0'], ['NAME' => 'Жиганск', 'DAY' => 275, 'TEMP' => '-20'], ['NAME' => 'Зырянка', 'DAY' => 265, 'TEMP' => '-20,4'], ['NAME' => 'Исить', 'DAY' => 255, 'TEMP' => '-17,9'], ['NAME' => 'Иэма', 'DAY' => 292, 'TEMP' => '-22,9'], ['NAME' => 'Крест-Хальджай', 'DAY' => 254, 'TEMP' => '-22,7'], ['NAME' => 'Кюсюр', 'DAY' => 295, 'TEMP' => '-19,7'], ['NAME' => 'Ленск', 'DAY' => 258, 'TEMP' => '-14,3'], ['NAME' => 'Нагорный', 'DAY' => 270, 'TEMP' => '-14,8'], ['NAME' => 'Нера', 'DAY' => 272, 'TEMP' => '-23,8'], ['NAME' => 'Нюрба', 'DAY' => 263, 'TEMP' => '-17,7'], ['NAME' => 'Нюя', 'DAY' => 253, 'TEMP' => '-14,2'], ['NAME' => 'Оймякон', 'DAY' => 277, 'TEMP' => '-25,4'], ['NAME' => 'Олекми1нск', 'DAY' => 253, 'TEMP' => '-15,7'], ['NAME' => 'Оленек', 'DAY' => 287, 'TEMP' => '-18,9'], ['NAME' => 'Охотский Перевоз', 'DAY' => 260, 'TEMP' => '-21,7'], ['NAME' => 'Сангар', 'DAY' => 261, 'TEMP' => '-19,6'], ['NAME' => 'Саскылах', 'DAY' => 308, 'TEMP' => '-19,3'], ['NAME' => 'Среднеколымск', 'DAY' => 277, 'TEMP' => '-19,8'], ['NAME' => 'Сунтар', 'DAY' => 257, 'TEMP' => '-16,8'], ['NAME' => 'Сухана', 'DAY' => 284, 'TEMP' => '-21,4'], ['NAME' => 'Сюльдюкар', 'DAY' => 270, 'TEMP' => '-18,0'], ['NAME' => 'Сюрен-Кюель', 'DAY' => 292, 'TEMP' => '-17,4'], ['NAME' => 'Токо', 'DAY' => 273, 'TEMP' => '-18,9'], ['NAME' => 'Томмот', 'DAY' => 262, 'TEMP' => '-17,1'], ['NAME' => 'Томпо', 'DAY' => 269, 'TEMP' => '-23,3'], ['NAME' => 'Туой-Хая', 'DAY' => 266, 'TEMP' => '-15,8'], ['NAME' => 'Тяня', 'DAY' => 262, 'TEMP' => '-15,7'], ['NAME' => 'Усть-Мая', 'DAY' => 251, 'TEMP' => '-20,8'], ['NAME' => 'Усть-Миль', 'DAY' => 259, 'TEMP' => '-18,9'], ['NAME' => 'Усть-Мома', 'DAY' => 267, 'TEMP' => '-24,1'], ['NAME' => 'Чульман', 'DAY' => 266, 'TEMP' => '-15,4'], ['NAME' => 'Чурапча', 'DAY' => 239, 'TEMP' => '-21,8'], ['NAME' => 'Шелагонцы', 'DAY' => 282, 'TEMP' => '-20,8'], ['NAME' => 'Эйк', 'DAY' => 284, 'TEMP' => '-18,5'], ['NAME' => 'Якутск', 'DAY' => 252, 'TEMP' => '-20,9'], ['NAME' => 'Варандей', 'DAY' => 323, 'TEMP' => '-7,3'], ['NAME' => 'Индига', 'DAY' => 298, 'TEMP' => '-5,6'], ['NAME' => 'Канин Нос', 'DAY' => 316, 'TEMP' => '-2,4'], ['NAME' => 'Коткино', 'DAY' => 285, 'TEMP' => '-7,1'], ['NAME' => 'Нарьян-Мар', 'DAY' => 289, 'TEMP' => '-7,5'], ['NAME' => 'Ходовариха', 'DAY' => 330, 'TEMP' => '-6,2'], ['NAME' => 'Хоседа-Хард', 'DAY' => 296, 'TEMP' => '-8,6'], ['NAME' => 'Ярославль', 'DAY' => 221, 'TEMP' => '-4'], ['NAME' => 'Ай-Петри', 'DAY' => 210, 'TEMP' => '0,7'], ['NAME' => 'Клепинино', 'DAY' => 157, 'TEMP' => '2'], ['NAME' => 'Симферополь', 'DAY' => 154, 'TEMP' => '2,6'], ['NAME' => 'Феодосия', 'DAY' => 142, 'TEMP' => '3,4'], ['NAME' => 'Ялта', 'DAY' => 126, 'TEMP' => '5,1'], ['NAME' => 'Керчь', 'DAY' => 155, 'TEMP' => '2,6'], ['NAME' => 'Севастополь', 'DAY' => 136, 'TEMP' => '4,7']];


/*\Bitrix\Main\Loader::includeModule('highloadblock');
if($_GET['a'] === '123'){

$edc = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity(\Bitrix\Highloadblock\HighloadBlockTable::getById(1)->fetch())->getDataClass();

// $arCompareDate = [];
foreach ($arCities as $id => $city) {
// $arCompareDate[$id] = ['CUR' => $city];
$isFind = false;
foreach ($arCurData as $data) {
if(strtolower($data['NAME']) === strtolower($city['name'])){
// $arCompareDate[$id]['NEW'] = $data;
$isFind = $data;
}
}
// if(!$isFind){
// 	$arCompareDate[$id] = ['CUR' => $city['name']];
// }
if($isFind){
$res = $edc::update($id, ['UF_VALUE' => str_replace(',', '.', $isFind['TEMP']), 'UF_MIDDLE_TEMP' => str_replace(',', '.', $isFind['TEMP']), 'UF_CNT_DAY' => str_replace(',', '.', $isFind['DAY'])]);
if(!$res->isSuccess()){
echo '<br><br><br><pre>'; var_export([$id, $res->getErrorMessages()]); echo '</pre>';
}
}
}

// echo '<br><br><br><pre>'; var_export($arCompareDate); echo '</pre>';

}*/

?>

<div class="s-calc-page">
    <div class="scp-wrap-content">

        <div class="s-calc-two" data-step="1"<? if ($_GET['step'] === '2') { ?> style="display: none;"<? } ?>>
            <div class="sc-items">
                <a href="#" class="sc-itm active" data-to_calc>
                    <div class="my-container">
                        <span class="sci-descr">Калькулятор расчета</span>
                        <span class="sci-hdr">для квартиры</span>
                        <span class="btn-more">рассчитать</span>
                        <span class="sci-bg lazy" data-bg="/img/bg4.jpg"></span>
                    </div>
                </a>
                <a href="#" class="sc-itm sc-red" data-to_calc>
                    <div class="my-container">
                        <span class="sci-descr">Калькулятор расчета</span>
                        <span class="sci-hdr">для частного дома</span>
                        <span class="btn-more">рассчитать</span>
                        <span class="sci-bg lazy" data-bg="/img/bg5.jpg"></span>
                    </div>
                </a>
            </div>
        </div>

        <div class="scp-content<? if ($_GET['step'] === '2') { ?> active<? } ?>" data-step="2">
            <div class="my-container">
                <span class="scp-step">2 шаг</span>
                <span class="scp-hdr">Климатические данные</span>

                <div class="sptw-top">
                    <div class="sptw-search">
                        <div class="sptw-select custom-select">
                            <select class="selectParameter-js" name="city">
                                <option value="">Выберите свой город</option>
                                <? foreach ($arCities as $id => $city) { ?>
                                    <option value="<?= $id ?>"><?= $city['name'] ?></option>
                                <? } ?>
                            </select>
                        </div>
                    </div>
                    <div class="sptw-itm">
						<span class="sptw-i-descr">Средняя температура <br>
						отопительного периода</span>
                        <span class="sptw-i-num" data-temp></span>
                        <span class="sptw-i-num-d">С°</span>
                    </div>
                    <div class="sptw-itm sptw-red">
						<span class="sptw-i-descr">Средняя температура <br>
						отопительного периода</span>
                        <span class="sptw-i-num" data-day></span>
                        <span class="sptw-i-num-d">суток</span>
                    </div>


                </div>

                <div class="sptw-map">
                    <div class="sptw-area">
                        <img class="lazy" data-src="/img/main_map.jpg" alt="map" usemap="#image-map">

                        <map name="image-map">
                            <area class="im-popup" data-window="sptw-1"
                                  coords="112,347,137,341,148,347,158,357,161,370,167,384,144,398,144,411,148,422,146,433,134,437,124,442,108,447,103,462,92,486,83,473,65,420,57,407,43,393,46,372,17,329,24,311,60,347,80,347,93,347,102,347,106,348"
                                  shape="poly">
                            <area class="im-popup" data-window="sptw-2"
                                  coords="146,228,163,231,176,235,189,245,200,254,222,269,252,291,236,295,190,292,174,305,159,328,142,341,119,345,107,330,99,311,101,289,96,274,97,259,113,247,122,234"
                                  shape="poly">
                            <area class="im-popup" data-window="sptw-3"
                                  coords="299,119,303,151,304,165,283,173,300,178,331,175,359,187,351,208,428,129,493,137,402,207,420,231,408,262,383,264,360,282,340,307,315,302,296,289,267,295,275,269,252,268,237,277,231,270,213,263,206,252,196,240,187,235,174,230,161,227,148,225,131,226,72,179,70,151,140,190,157,182,168,181,178,174,191,173,202,172,217,172,226,160,253,133,285,110"
                                  shape="poly">
                            <area class="im-popup" data-window="sptw-4"
                                  coords="236,301,248,301,261,278,267,277,263,300,292,296,312,303,337,312,318,335,303,353,285,373,269,393,285,398,268,419,270,452,232,430,215,403,200,385,171,393,169,373,164,357,151,344,158,333,167,319,180,309,183,297,204,294,213,296,222,299,229,300"
                                  shape="poly">
                            <area class="im-popup" data-window="sptw-5"
                                  coords="380,270,391,270,402,266,412,265,422,255,430,237,475,188,483,197,452,290,462,288,484,217,504,221,496,253,510,273,501,290,514,342,504,360,506,382,476,382,447,375,440,383,426,397,412,402,396,391,387,412,376,426,346,423,305,415,279,429,275,415,292,402,277,391,281,381,295,365,311,354,318,340,347,313,354,295,365,282,371,278"
                                  shape="poly">
                            <area class="im-popup" data-window="sptw-6"
                                  coords="515,327,514,308,512,283,507,250,514,243,517,217,521,209,525,202,556,194,583,172,585,84,656,173,663,233,641,260,650,345,667,364,672,416,693,422,723,396,742,401,763,447,779,452,788,474,783,527,743,540,712,552,696,553,667,542,641,547,605,520,586,554,548,541,497,555,472,534,435,515,427,497,401,461,380,450,383,423,399,406,434,401,451,382,486,387,502,390,513,379,514,359,517,351,518,339,516,334"
                                  shape="poly">
                            <area class="im-popup" data-window="sptw-7"
                                  coords="764,427,768,440,783,453,798,467,828,477,847,494,907,515,925,493,925,557,955,573,1011,469,946,379,881,414,893,332,954,288,953,222,976,221,974,184,987,205,994,260,1000,295,1061,492,1079,353,1007,191,1025,183,1026,108,992,94,1016,53,1002,30,908,38,867,141,828,157,804,166,788,115,742,140,782,198,766,200,754,210,745,222,731,202,720,193,695,213,672,205,664,251,647,270,652,324,663,349,680,387,683,414,699,412,721,391,729,388,740,391,742,396,753,408,758,418"
                                  shape="poly">
                        </map>
                    </div>
                    <div class="sptw-container">
                        <? foreach (REGIONS as $className) { ?>
                            <? $arCurCities = []; ?>
                            <? foreach ($arCities as $id => $city)
                            {
                                if ($city['region'] === $className)
                                {
                                    $arCurCities[$id] = $city;
                                }
                            } ?>
                            <div class="sptw-pp <?= $className ?>">
                                <div class="sptw-p-flex">
                                    <ul>
                                        <? $idx = 0; ?>
                                        <? foreach ($arCurCities

                                        as $id => $city)
                                        { ?>
                                        <? if ($idx == count($arCurCities) / 2){ ?>
                                    </ul>
                                    <ul>
                                        <? } ?>
                                        <li><a href="#" data-value="<?= $id ?>"
                                               data-select_city><?= $city['name'] ?></a></li>
                                        <? $idx++; ?>
                                        <? } ?>
                                    </ul>
                                </div>
                            </div>
                        <? } ?>
                    </div>
                </div>

                <div class="error-js" style="color:red;"></div>

                <a href="#" class="sptw-prev">назад</a>
                <a href="#" class="sptw-next">далее</a>
            </div>
        </div>

        <div class="scp-content scp-step-three" data-step="3">
            <div class="my-container">
                <span class="scp-step">3 шаг</span>
                <span class="scp-hdr scp-no-margin">Размеры помещения</span>
                <span class="scp-co-descr">Задайте размеры помещения, используя его ширину <br>
				и глубину или площадь</span>

                <!-- scp-three - по центру -->
                <div class="scp-sizes scp-three">
                    <div class="scps-itm">
                        <span class="scps-i-hdr">Параметры помещения</span>
                        <label class="scps-inp">
                            <input type="text" class="inputParameter-js" name="height" placeholder="Высота потолков, H">
                            <span class="scps-i-descr">м</span>
                        </label>
                        <label class="scps-inp">
                            <input type="text" class="inputParameter-js" name="width" placeholder="Ширина помещения, B">
                            <span class="scps-i-descr">м</span>
                        </label>
                        <label class="scps-inp">
                            <input type="text" class="inputParameter-js" name="length" placeholder="Длина помещения, A">
                            <span class="scps-i-descr">м</span>
                        </label>

                        <span class="scps-i-hdr">Дверь</span>
                        <div class="scps-inp scps-two-inp">
                            <div class="scps-i-inp">
                                <input type="text" class="inputParameter-js" name="door_width" placeholder="Ширина">
                                <span class="scps-i-descr">м</span>
                            </div>
                            <div class="scps-i-inp">
                                <input type="text" class="inputParameter-js" name="door_height" placeholder="Высота">
                                <span class="scps-i-descr">м</span>
                            </div>
                        </div>
                    </div>
                    <div class="scps-itm scps-three-img">
                        <img class="lazy" data-src="/img/img9.jpg" alt="img">
                    </div>
                    <div class="scps-itm">
                        <div class="scps-row">
                            <span class="scps-s-descr">Количество окон</span>
                            <div class="custom-select">
                                <select class="selectParameter-js" name="window_count">
                                    <option value="0">Выберите</option>
                                    <option value="1">Одно</option>
                                    <option value="2">Два</option>
                                    <option value="3">Три</option>
                                </select>
                            </div>
                        </div>

                        <span class="scps-i-hdr" style="display: none;" data-window_title="1">Окно 1</span>
                        <div class="scps-inp scps-two-inp" style="display: none;" data-window_block="1">
                            <div class="scps-i-inp">
                                <input type="text" class="inputParameter-js" name="window_width_1" placeholder="Ширина">
                                <span class="scps-i-descr">м</span>
                            </div>
                            <div class="scps-i-inp">
                                <input type="text" class="inputParameter-js" name="window_height_1"
                                       placeholder="Высота">
                                <span class="scps-i-descr">м</span>
                            </div>
                        </div>

                        <span class="scps-i-hdr" style="display: none;" data-window_title="2">Окно 2</span>
                        <div class="scps-inp scps-two-inp" style="display: none;" data-window_block="2">
                            <div class="scps-i-inp">
                                <input type="text" class="inputParameter-js" name="window_width_2" placeholder="Ширина">
                                <span class="scps-i-descr">м</span>
                            </div>
                            <div class="scps-i-inp">
                                <input type="text" class="inputParameter-js" name="window_height_2"
                                       placeholder="Высота">
                                <span class="scps-i-descr">м</span>
                            </div>
                        </div>

                        <span class="scps-i-hdr" style="display: none;" data-window_title="3">Окно 3</span>
                        <div class="scps-inp scps-two-inp" style="display: none;" data-window_block="3">
                            <div class="scps-i-inp">
                                <input type="text" class="inputParameter-js" name="window_width_3" placeholder="Ширина">
                                <span class="scps-i-descr">м</span>
                            </div>
                            <div class="scps-i-inp">
                                <input type="text" class="inputParameter-js" name="window_height_3"
                                       placeholder="Высота">
                                <span class="scps-i-descr">м</span>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="error-js" style="color:red;"></div>


                <a href="#" class="sptw-prev">назад</a>
                <a href="#" class="sptw-next">далее</a>
            </div>
        </div>

        <div class="scp-content scp-four-step" data-step="4">
            <div class="my-container">
                <span class="scp-step">4 шаг</span>
                <span class="scp-hdr scp-no-margin">Конструкция ограждения</span>
                <span class="scp-co-descr">Тепло в доме во многом зависит о того, <br>
				какая конструкция ограждений в помещении</span>

                <div class="scp-sizes">
                    <div class="scps-itm">
                        <div class="scps-row">
                            <span class="scps-s-descr">Стена L1</span>
                            <div class="custom-select">
                                <select class="selectParameter-js" name="wall_type_1">
                                    <option value="inside">Внутренняя</option>
                                    <option value="inside">Внутренняя</option>
                                    <option value="outside">Наружная</option>
                                </select>
                            </div>
                        </div>
                        <div class="scps-row">
                            <span class="scps-s-descr">Стена L2</span>
                            <div class="custom-select">
                                <select class="selectParameter-js" name="wall_type_2">
                                    <option value="inside">Внутренняя</option>
                                    <option value="inside">Внутренняя</option>
                                    <option value="outside">Наружная</option>
                                </select>
                            </div>
                        </div>
                        <div class="scps-row">
                            <span class="scps-s-descr">Стена L3</span>
                            <div class="custom-select">
                                <select class="selectParameter-js" name="wall_type_3">
                                    <option value="inside">Внутренняя</option>
                                    <option value="inside">Внутренняя</option>
                                    <option value="outside">Наружная</option>
                                </select>
                            </div>
                        </div>
                        <div class="scps-row">
                            <span class="scps-s-descr">Стена L4</span>
                            <div class="custom-select">
                                <select class="selectParameter-js" name="wall_type_4">
                                    <option value="inside">Внутренняя</option>
                                    <option value="inside">Внутренняя</option>
                                    <option value="outside">Наружная</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="scps-itm">
                        <div class="scps-row">
                            <span class="scps-s-descr">Дверь</span>
                            <div class="custom-select">
                                <select class="selectParameter-js" name="type_door">
                                    <option value="inside">Внутренняя</option>
                                    <option value="inside">Внутренняя</option>
                                    <option value="outside">Наружная</option>
                                </select>
                            </div>
                        </div>
                        <div class="scps-row" data-window_type="1">
                            <span class="scps-s-descr">Окно 1</span>
                            <div class="custom-select">
                                <select class="selectParameter-js" name="window_type_1">
                                    <option value="inside">Внутренняя</option>
                                    <option value="inside">Внутренняя</option>
                                    <option value="outside">Наружная</option>
                                </select>
                            </div>
                        </div>
                        <div class="scps-row" data-window_type="2">
                            <span class="scps-s-descr">Окно 2</span>
                            <div class="custom-select">
                                <select class="selectParameter-js" name="window_type_2">
                                    <option value="inside">Внутренняя</option>
                                    <option value="inside">Внутренняя</option>
                                    <option value="outside">Наружная</option>
                                </select>
                            </div>
                        </div>
                        <div class="scps-row" data-window_type="3">
                            <span class="scps-s-descr">Окно 3</span>
                            <div class="custom-select">
                                <select class="selectParameter-js" name="window_type_3">
                                    <option value="inside">Внутренняя</option>
                                    <option value="inside">Внутренняя</option>
                                    <option value="outside">Наружная</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="scps-itm">
                        <div class="scps-row">
                            <span class="scps-s-descr">Напольное перекрытие</span>
                            <div class="custom-select">
                                <select class="selectParameter-js" name="type_floor">
                                    <option value="inside">Внутренняя</option>
                                    <option value="inside">Внутренняя</option>
                                    <option value="outside">Наружная</option>
                                </select>
                            </div>
                        </div>
                        <div class="scps-row">
                            <span class="scps-s-descr">Потолочное перекрытие</span>
                            <div class="custom-select">
                                <select class="selectParameter-js" name="type_ceiling">
                                    <option value="inside">Внутренняя</option>
                                    <option value="inside">Внутренняя</option>
                                    <option value="outside">Наружная</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="scps-itm scps-four-img">
                        <img class="lazy" data-src="/img/im2.jpg" alt="img">
                    </div>
                </div>

                <div class="error-js" style="color:red;"></div>


                <a href="#" class="sptw-prev">назад</a>
                <a href="#" class="sptw-next">далее</a>
            </div>
        </div>

        <div class="scp-content scp-five-step" data-step="5">
            <div class="my-container">
                <span class="scp-step">5 шаг</span>
                <span class="scp-hdr scp-no-margin">Тип конструкции здания</span>
                <span class="scp-co-descr">Выберете тип конструкции из списка </span>

                <div class="scp-sizes">
                    <div class="scps-itm">
                        <div class="scps-row">
                            <span class="scps-s-descr">Тип наружной стены</span>
                            <div class="custom-select">
                                <select class="selectParameter-js" name="material_wall">
                                    <option value="0">Выберите</option>
                                    <? foreach ($arTypeWalls as $id => $wall) { ?>
                                        <option value="<?= $id ?>"><?= $wall['name'] ?></option>
                                    <? } ?>
                                </select>
                            </div>
                        </div>
                        <div class="scps-row">
                            <span class="scps-s-descr">Тип окна</span>
                            <div class="custom-select">
                                <select class="selectParameter-js" name="material_window">
                                    <option value="0">Выберете</option>
                                    <? foreach ($arTypeWindows as $id => $window) { ?>
                                        <option value="<?= $id ?>"><?= $window['name'] ?></option>
                                    <? } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="error-js" style="color:red;"></div>


                <a href="#" class="sptw-prev">назад</a>
                <a href="#" class="sptw-next">далее</a>
            </div>
        </div>

        <div class="scp-content scp-six-step" data-step="6">
            <div class="my-container">
                <span class="scp-step">6 шаг</span>
                <span class="scp-hdr scp-no-margin">Выбор отопительного прибора</span>
                <span class="scp-co-descr">Выбираем отопительный прибор и определяем <br>
				необходимое количество секций</span>

                <div class="scp-sizes">
                    <div class="scps-itm">
                        <span class="scps-i-hdr">Температура теплоносителя</span>
                        <div class="scps-inp scps-two-inp" style="margin-bottom: 20px;">
                            <span class="scps-i-descr" style="position: static;padding-top: 4px;">ОТ:</span>
                            <div class="scps-i-inp" style="width: calc(100% - 12px);">
                                <input type="text" class="inputParameter-js" name="temperature_enter"
                                       placeholder="На входе" value="70">
                                <span class="scps-i-descr">⁰С</span>
                            </div>
                        </div>
                        <div class="scps-inp scps-two-inp">
                            <span class="scps-i-descr" style="position: static;padding-top: 4px;">ДО:</span>
                            <div class="scps-i-inp" style="width: calc(100% - 12px);">
                                <input type="text" class="inputParameter-js" name="temperature_exit"
                                       placeholder="На выходе" value="50">
                                <span class="scps-i-descr">⁰С</span>
                            </div>
                        </div>
                        <? /*<input type="hidden" class="inputParameter-js" name="temperature_enter" placeholder="На входе" value="70">
						<input type="hidden" class="inputParameter-js" name="temperature_exit" placeholder="На выходе" value="50">*/ ?>

                        <div class="scps-row">
                            <span class="scps-s-descr">Тип отопительного прибора</span>
                            <div class="custom-select">
                                <select class="selectParameter-js" name="type_radiator">
                                    <? $isFirst = true; ?>
                                    <? foreach ($arTypeRadiators as $id => $type) { ?>
                                        <?if ($id == 232) continue ?>
                                        <? if ($isFirst) { ?>
                                            <option value="<?= $id ?>"><?= $type ?></option>
                                            <? $isFirst = false; ?>
                                        <? } ?>
                                        <option value="<?= $id ?>"><?= $type ?></option>
                                    <? } ?>
                                </select>
                            </div>
                        </div>
                        <div class="scps-row">
                            <span class="scps-s-descr">Радиатор</span>
                            <div class="custom-select">
                                <select class="selectParameter-js" name="radiator">
                                    <option value="0">Выберите</option>
                                    <? foreach ($arRadiators as $id => $radiator) { ?>
                                        <option value="<?= $id ?>"><?= $radiator['name'] ?></option>
                                    <? } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="scps-itm" style="display: none;" data-result_calc>
                        <div class="sc-block">
                            <div class="scb-top">
                                <div class="scb-t-txt">
                                    <span class="scb-t-t-1">Результат</span>
                                    <span class="scb-t-t-2">Необходимое количество секций <br>
									радиатора для вашего помещения:</span>
                                </div>
                                <div class="scb-t-num" data-count_section>12</div>
                            </div>
                            <div class="scb-content">
                                <div class="scb-c-img"><img src="" alt="img" data-picture></div>
                                <div class="scb-c-txt">
                                    <span class="scb-c-name-1" data-name></span>
                                    <span class="scb-c-name-2" data-type></span>
                                    <a href="" class="scb-c-more" data-link>Подробнее</a>
                                </div>
                            </div>

                            <a href="/calculator/" class="sptw-next sptw-next_desktop calcNew-js"
                               style="display: none;">Рассчитать заново</a>
                        </div>
                    </div>
                </div>

                <a href="#" class="sptw-prev">назад</a>
                <a href="/calculator/" class="sptw-next sptw-next_mobile calcNew-js" style="display: none;">Рассчитать
                    заново</a>
            </div>
        </div>


    </div>


    <div class="scp-steps">
        <!-- width -  в зависимости от активного шага -->
        <span class="scp-line" style="width: <?= $_GET['step'] === '2' ? '33.332' : '16.666' ?>%"></span>
        <div class="scp-stps">
            <div class="scp-stp" data-nav_step="1">1 шаг <span>Тип помещения</span></div>
            <div class="scp-stp" data-nav_step="2">2 шаг <span>Климатические данные</span></div>
            <div class="scp-stp" data-nav_step="3">3 шаг <span>Размеры помещения</span></div>
            <div class="scp-stp" data-nav_step="4">4 шаг <span>Конструкция ограждения</span></div>
            <div class="scp-stp" data-nav_step="5">5 шаг <span>Тип конструкции здания</span></div>
            <div class="scp-stp" data-nav_step="6">6 шаг <span>Выбор отопительного прибора</span></div>
        </div>
        <a class='scp-phone' href='tel:84957752005'>Возникли вопросы по расчету? Звоните 8 495 775 20 05</a>
    </div>

    <script>
        function getCities(id) {
            let elems = <?=json_encode($arCities);?>;
            if (id === 'all') {
                return elems;
            }
            if (isNaN(parseInt(id)) || parseInt(id) <= 0) {
                return undefined;
            }
            return elems[id];
        }

        function getRadiators(id) {
            let elems = <?=json_encode($arRadiators);?>;
            if (id === 'all') {
                return elems;
            }
            if (isNaN(parseInt(id)) || parseInt(id) <= 0) {
                return undefined;
            }
            return elems[id];
        }

        function getTypeWalls(id) {
            let elems = <?=json_encode($arTypeWalls);?>;
            if (id === 'all') {
                return elems;
            }
            if (isNaN(parseInt(id)) || parseInt(id) <= 0) {
                return undefined;
            }
            return elems[id];
        }

        function getTypeWindows(id) {
            let elems = <?=json_encode($arTypeWindows);?>;
            if (id === 'all') {
                return elems;
            }
            if (isNaN(parseInt(id)) || parseInt(id) <= 0) {
                return undefined;
            }
            return elems[id];
        }
    </script>

</div>
<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use Bitrix\Sale;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

/**
 * Class globalOrderComponent
 */
class globalOrderComponent extends CBitrixComponent
{
    /** @var $order Bitrix\Sale\Order */

    private $order;
    private $errors = [];
    private $deliverySystemsRestr;
    private $paySystemsRestr;
    private $validation = [];
    private $basketEmpty = false;
    private $arResponse = [
        'errors' => [],
        'html' => ''
    ];

    /**
     * globalOrderComponent constructor.
     * @param null $component
     * @throws \Bitrix\Main\LoaderException
     */
    function __construct($component = null)
    {
        parent::__construct($component);

        if (!Loader::includeModule('iblock'))
        {
            $this->errors[] = 'No iblock module';
        };

        if (!Loader::includeModule('sale'))
        {
            $this->errors[] = 'No sale module';
        };

        if (!Loader::includeModule('catalog'))
        {
            $this->errors[] = 'No catalog module';
        };
    }

    /**
     * @param $arParams
     * @return array
     */
    function onPrepareComponentParams($arParams)
    {
        if (isset($arParams['PERSON_TYPE_ID']) && intval($arParams['PERSON_TYPE_ID']) > 0)
        {
            $arParams['PERSON_TYPE_ID'] = intval($arParams['PERSON_TYPE_ID']);
        }
        else
        {
            if (intval($this->request['payer']) > 0)
            {
                $arParams['PERSON_TYPE_ID'] = intval($this->request['payer']);
            }
            else
            {
                $arParams['PERSON_TYPE_ID'] = 2;
            }
        }

        if (
            isset($arParams['IS_AJAX'])
            && ($arParams['IS_AJAX'] == 'Y' || $arParams['IS_AJAX'] == 'N')
        )
        {
            $arParams['IS_AJAX'] = $arParams['IS_AJAX'] == 'Y';
        }
        else
        {
            if (
                isset($this->request['is_ajax'])
                && ($this->request['is_ajax'] == 'Y' || $this->request['is_ajax'] == 'N')
            )
            {
                $arParams['IS_AJAX'] = $this->request['is_ajax'] == 'Y';
            }
            else
            {
                $arParams['IS_AJAX'] = false;
            }
        }
        if (isset($arParams['ACTION']) && strlen($arParams['ACTION']) > 0)
        {
            $arParams['ACTION'] = strval($arParams['ACTION']);
        }
        else
        {
            if (isset($this->request['action']) && strlen($this->request['action']) > 0)
            {
                $arParams['ACTION'] = strval($this->request['action']);
            }
            else
            {
                $arParams['ACTION'] = '';
            }
        }

        return $arParams;
    }

    protected function createVirtualOrder($fastorder = false)
    {
        global $USER;

        try
        {
            $siteId = \Bitrix\Main\Context::getCurrent()->getSite();
            if ($fastorder)
            {
                $basketItems = \Bitrix\Sale\Basket::create($siteId);
                $item = $basketItems->createItem('catalog', $this->arParams['PRODUCT_ID']);
                $item->setFields(array(
                    'QUANTITY' => 1,
                    'CURRENCY' => Bitrix\Currency\CurrencyManager::getBaseCurrency(),
                    'LID' => $siteId,
                    'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
                ));

                $basketItems->getOrderableItems();
            }
            else
            {
                $basketItems = \Bitrix\Sale\Basket::loadItemsForFUser(
                    \CSaleBasket::GetBasketUserID(),
                    $siteId
                )
                    ->getOrderableItems();

            }
            if (count($basketItems) == 0 && !$this->request->get('ORDER_ID'))
            {
                $this->basketEmpty = true;
            }

            if ($this->arParams['ACTION'] == 'saveOrder')
                $this->setOrderUser();


            $this->order = \Bitrix\Sale\Order::create($siteId, $USER->GetID());
            $this->order->setPersonTypeId(1); // физ лицо

            $this->order->setBasket($basketItems);
            $this->setOrderProps();
            $this->setOrderPayment($_SESSION['ORDER']['pay'] ? $_SESSION['ORDER']['pay'] : 0);
            $this->setOrderDelivery($_SESSION['ORDER']['delivery'] ? $_SESSION['ORDER']['delivery'] : 0);

            $this->order->doFinalAction(true);
        } catch (\Exception $e)
        {
            $this->errors[] = $e->getMessage();
        }
    }

    protected function setOrderSession()
    {
        if (!$_SESSION['ORDER'])
        {
            /*TODO ЛОГИКА ЗАПОЛНЕНИЯ ДЕФОЛТ СЕССИИ ДЛЯ ЗАКАЗА - ЗАПОЛНЕНИЕ ДАННЫХ ИЗ ПРОФИЛЯ АККАУНТА */
            $_SESSION['ORDER']['selfType'] = 'dsMap';
        }
        foreach ($this->request as $key => $value)
        {
            $_SESSION['ORDER'][$key] = $value;
        }
    }

    protected function setOrderProps()
    {

        if ($_SESSION['ORDER']['USER_DESCRIPTION'])
        {
            $this->order->setField('USER_DESCRIPTION', trim($_SESSION['ORDER']['USER_DESCRIPTION']));
        }

        foreach ($this->order->getPropertyCollection() as $prop)
        {
            /** @var \Bitrix\Sale\PropertyValue $prop */
            $value = '';
            /* ПОЛИФИЛЫ */
//            switch ($prop->getField('CODE')) {
//                case 'FIO':
//                    $value = $this->request['CONTACT_PERSON'];
//                    break;
//                default:
//            }

            if (empty($value))
            {
                foreach ($_SESSION['ORDER'] as $key => $val)
                {
                    if (strtolower($key) == strtolower($prop->getField('CODE')))
                    {
                        $value = $val;
                    }
                }
            }

            if (empty($value))
            {
                $value = $prop->getProperty()['DEFAULT_VALUE'];
            }

            if (!empty($value))
            {
                $prop->setValue($value);
            }
        }
    }

    private function updateOrderAction()
    {
        echo debmes($_SESSION['ORDER']);
    }

    private function validOrderAction()
    {
        $this->initValidation();
        $this->arResult['ERRORS'] = $this->errors;
    }

    private function saveOrderAction()
    {
        global $APPLICATION;

        try
        {
            $r = $this->order->save();
            if ($r->getErrors())
                debug_to_file($r->getErrorMessages(), 'orderError', 'orderError.log');

            $this->saveOrderInXml();
            $this->createViewResult();
            $this->arResult['GTAG_ORDER_PURCHASE'] = 'Y';
            $_SESSION['ORDER'] = $this->arResult;
            $APPLICATION->RestartBuffer();

            LocalRedirect('?ORDER_ID=' . $this->order->getId());
            $APPLICATION->FinalActions();

        } catch (Exception $e)
        {
            debug_to_file($e, 'orderError', 'orderError.log');
        }

        die();
    }


    /**
     * @return mixed|void|null
     * @throws \Bitrix\Main\ArgumentNullException
     */
    function executeComponent()
    {
        global $APPLICATION;

        $this->setOrderSession();
        $this->createVirtualOrder($this->arParams['FASTORDER']);
        $this->createViewResult();

        if (!empty($this->arParams['ACTION']))
        {
            if (is_callable([$this, $this->arParams['ACTION'] . 'Action']))
            {
                try
                {
                    call_user_func([$this, $this->arParams['ACTION'] . 'Action']);
                } catch (\Exception $e)
                {
                    $this->errors[] = $e->getMessage();
                }
            }
        }

        if ($this->arParams['IS_AJAX'])
        {
            $APPLICATION->RestartBuffer();
            ob_start();
            if ($this->basketEmpty)
            {
                $this->includeComponentTemplate('empty');
            }
            else
            {
                $this->includeComponentTemplate();
            }
            $this->arResponse['html'] = ob_get_contents();
            ob_end_clean();

            $this->arResponse['errors'] = $this->errors;

            header('Content-Type: application/json');
            echo json_encode($this->arResponse);
            $APPLICATION->FinalActions();
            die();
        }
        else
        {
            if ($this->request['ORDER_ID'])
            {
                $this->confirmOrder($this->request['ORDER_ID']);
                if ($this->arParams['FASTORDER'] == 'Y') {
                    $this->arResult['FASTORDER']['SUCCESS'] = 'Y';
                    ob_start();
                    $this->includeComponentTemplate();
                    $this->arResponse['html'] = ob_get_contents();
                    ob_end_clean();
                    $APPLICATION->RestartBuffer();
                    echo json_encode($this->arResponse);
                    die();
                } else {
                    $this->includeComponentTemplate('confirm');
                }
            }
            else
            {
                if ($this->basketEmpty)
                {
                    $this->includeComponentTemplate('empty');
                }
                else
                {
                    $this->includeComponentTemplate();
                }
            }
        }
    }

    private function createViewResult()
    {
        $this->arResult['ORDER'] = $this->createViewOrder();
        $this->arResult['PROPS'] = $this->createViewProps();
        $this->arResult['BASKET'] = $this->createViewBasket();
        $this->arResult['PAYMENT'] = $this->createViewPayment();
        $this->arResult['DELIVERY'] = $this->createViewDelivery();
        $this->arResult['ERRORS'] = $this->errors;
        $this->arResult['PARAMS'] = $this->arParams;
    }

    private function createViewBasket(): array
    {
        $basket = $this->order->getBasket();
        $basketItems = $basket->getBasketItems();

        foreach ($basketItems as $basketItem)
        {
            $item['ID'] = $basketItem->getId();
            $item['PRODUCT_ID'] = $basketItem->getProductId();
            $item['QUANTITY'] = $basketItem->getQuantity();
            $item['PRICE'] = $basketItem->getPrice();
            $item['PRICE_BASE'] = $basketItem->getBasePrice();
            $item['PRICE_DISCOUNT'] = $basketItem->getDiscountPrice();
            $item['PRICE_SUM'] = $item['PRICE'] * $item['QUANTITY'];
            $item['PRICE_BASE_SUM'] = $item['PRICE_BASE'] * $item['QUANTITY'];
            $item['PRICE_DISCOUNT_SUM'] = $item['PRICE_DISCOUNT'] * $item['QUANTITY'];
            $item['CURRENCY'] = $basketItem->getCurrency();

            $ids[] = $item['PRODUCT_ID'];
            $items[$item['PRODUCT_ID']] = $item;
        }

        $arFilter = ['IBLOCK_ID' => IBLOCK_ID_CATALOG, 'ID' => $ids];
        $arSelect = ['IBLOCK_ID', 'IBLOCK_SECTION_ID', 'ID', 'NAME', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL', 'XML_ID'];
        $dbElement = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        while ($rsElement = $dbElement->getNext())
        {
            $items[$rsElement['ID']]['NAME'] = $rsElement['NAME'];
            $items[$rsElement['ID']]['XML_ID'] = $rsElement['XML_ID'];
            $items[$rsElement['ID']]['PREVIEW_PICTURE'] = ['ID' => $rsElement['PREVIEW_PICTURE'], 'SRC' => CFile::GetPath($rsElement['PREVIEW_PICTURE'])];
            $items[$rsElement['ID']]['DETAIL_PAGE_URL'] = $rsElement['DETAIL_PAGE_URL'];

            $scRes = CIBlockSection::GetNavChain(
                $rsElement['IBLOCK_ID'],
                $rsElement['IBLOCK_SECTION_ID'],
                array('ID', 'DEPTH_LEVEL', 'NAME')
            );
            $rootSection = 0;
            while ($arGrp = $scRes->Fetch())
            {
                // определяем корневой раздел
                if ($arGrp['DEPTH_LEVEL'] == 1)
                {
                    $rootSection = $arGrp;
                }
            }
            $items[$rsElement['ID']]['SECTION_ROOT'] = $rootSection;
        }

        $arResult['ITEMS'] = $items;

        return $arResult;
    }

    private function createViewOrder(): array
    {
        $arResult['TOTAL'] = $this->order->getPrice();
        $arResult['TOTAL_DISCOUNT'] = $this->order->getDiscountPrice();
        $arResult['BASKET'] = $this->order->getBasket()->getBasePrice();
        $arResult['DELIVERY'] = $this->order->getDeliveryPrice();
        return $arResult;
    }

    private function createViewDelivery(): array
    {
        $deliverySystemId = $this->order->getDeliverySystemId();

        $arResult['ITEMS'] = $this->deliverySystemsRestr;
        $arResult['SELECTED'] = $this->deliverySystemsRestr[reset($deliverySystemId)];
        return $arResult;
    }

    private function createViewPayment(): array
    {
        $paysystemId = $this->order->getPaymentSystemId();

        $arResult['ITEMS'] = $this->paySystemsRestr;
        $arResult['SELECTED'] = $this->paySystemsRestr[reset($paysystemId)];
        return $arResult;
    }

    private function createViewProps(): array
    {
        foreach ($this->order->getPropertyCollection() as $prop)
        {
            /** @var \Bitrix\Sale\PropertyValue $prop */
            $arResult[$prop->getField('CODE')] = $prop->getValue();
        }

        return $arResult;
    }

    private function setOrderUser()
    {
        global $USER;

        if ($this->request['EMAIL'] && $this->request['EMAIL'] != '')
        {
            $rsUser = $USER->GetByLogin($this->request['EMAIL']);
            $arUser = $rsUser->Fetch();
            if ($arUser['ID'])
            {
                $USER->Authorize($arUser['ID']);
            }
            else
            {
                $USER->SimpleRegister($this->request['EMAIL']);
                $fields = array(
                    'NAME' => $this->request['CONTACT_PERSON'],
                    'LAST_NAME' => '',
                    'EMAIL' => $this->request['EMAIL'],
                    'LOGIN' => $this->request['EMAIL'],
                    'PASSWORD' => '000000',
                    'CONFIRM_PASSWORD' => '000000',
                );
                $USER->Update($USER->getID(), $fields);
            }
        } else {
            $USER->SimpleRegister(time() . '@globalfastorder.ru');
        }

        $userId = $USER->GetID() ? $USER->GetID() : CSaleUser::GetAnonymousUserID();
    }

    /**
     * @param int $paySystemId
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     */
    private function setOrderPayment($paySystemId = 0)
    {
        $paymentCollection = $this->order->getPaymentCollection();

        $payment = $paymentCollection->createItem();
        $payment->setField('SUM', $this->order->getPrice());
        $payment->setField('CURRENCY', $this->order->getCurrency());

        $this->paySystemsRestr = PaySystem\Manager::getListWithRestrictions($payment);

        if (!$paySystemId)
            $paySystemId = reset($this->paySystemsRestr)['ID'];

        $payment->setPaySystemService(
            PaySystem\Manager::getObjectById(
                intval($paySystemId)
            ));
    }

    /**
     * @param int $deliverySystemId
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\SystemException
     */
    private function setOrderDelivery($deliverySystemId = 0)
    {
        $shipmentCollection = $this->order->getShipmentCollection();

        $shipment = $shipmentCollection->createItem();
        $shipment->setField('CURRENCY', $this->order->getCurrency());

        $shipmentItemCollection = $shipment->getShipmentItemCollection();
        foreach ($this->order->getBasket()->getOrderableItems() as $item)
        {
            $shipmentItem = $shipmentItemCollection->createItem($item);
            $shipmentItem->setQuantity($item->getQuantity());
        }

        $this->deliverySystemsRestr = Delivery\Services\Manager::getRestrictedList($shipment, \Bitrix\Sale\Services\Base\RestrictionManager::MODE_CLIENT);

        if (!$deliverySystemId)
            $deliverySystemId = reset($this->deliverySystemsRestr)['ID'];

        $this->calcDeliveryPrice($shipment);

        $shipment->setBasePriceDelivery($this->deliverySystemsRestr[$deliverySystemId]['EXT']['price']);
        $shipment->setDeliveryService(
            Delivery\Services\Manager::getObjectById(
                intval($deliverySystemId)
            ));

    }

    /**
     * @param \bitrix\Sale\Shipment $shipment
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function calcDeliveryPrice(\bitrix\Sale\Shipment $shipment)
    {
        $distance = 0;
        $pickupId = 1;
        if ($_SESSION['ORDER']['distance'])
            $distance = $_SESSION['ORDER']['distance'];
        if ($_SESSION['ORDER']['pickup'])
            $pickupId = $_SESSION['ORDER']['pickup'];
        foreach ($this->deliverySystemsRestr as $delivery)
        {
            $this->deliverySystemsRestr[$delivery['ID']]['EXT'] = getDeliveryPriceDate_OrderAjax(
                $delivery['ID'],
                $this->order->getPrice(),
                $this->order->getBasket()->getWeight(),
                $delivery['ID'] == DELIVERY_SELF_PICKUP_ID ? 0 : $distance,
                $pickupId);

            $shipment->setBasePriceDelivery($this->deliverySystemsRestr[$delivery['ID']]['EXT']['price']);
            $shipment->setDeliveryService(
                Delivery\Services\Manager::getObjectById(
                    intval($delivery['ID'])
                ));
            $this->order->doFinalAction(true);
            $this->deliverySystemsRestr[$delivery['ID']]['PRICE'] = $this->order->getDeliveryPrice();
            //$this->deliverySystemsRestr[$delivery['ID']]['EXT']['soonDate']= '09.01.2023';
            $this->deliverySystemsRestr[$delivery['ID']]['DATE'] = $this->deliverySystemsRestr[$delivery['ID']]['EXT']['soonDate'];
            $this->deliverySystemsRestr[$delivery['ID']]['DESCRIPTION'] = $this->getDeliveryDescription($delivery['ID']);
            $this->deliverySystemsRestr[$delivery['ID']]['DESCRIPTION_HEADER'] = $this->getDeliveryDescriptionHeader($delivery['ID']);
            $this->deliverySystemsRestr[$delivery['ID']]['DESCRIPTION_VALUE'] = $this->getDeliveryDescriptionValue($delivery['ID']);

            if ($this->deliverySystemsRestr[$delivery['ID']]['EXT']['pickups'])
            {
                $pickups = $this->deliverySystemsRestr[$delivery['ID']]['EXT']['pickups'];
                foreach ($pickups as $id => $pickup)
                {
                    $distancePickup = $this->calcDeliveryDistance($pickup['COORD']);
                    $pickups[$id]['distancePickup'] = $distancePickup;
                }
                usort($pickups, function ($a, $b) {
                    $a = $a['distancePickup'];
                    $b = $b['distancePickup'];
                    if ($a == $b)
                    {
                        return 0;
                    }
                    return ($a < $b) ? -1 : 1;
                });
                $this->deliverySystemsRestr[$delivery['ID']]['EXT']['pickups'] = $pickups;
            }
        }
    }

    /**
     * @param $coords
     * @return float|int
     */
    private function calcDeliveryDistance($coords)
    {
        $tmp = explode(',', $coords);
        $pickupCoords = ['lat' => $tmp[0] * M_PI / 180, 'lng' => $tmp[1] * M_PI / 180];
        $tmp = [$_SESSION['ORDER']['centerCoordY'], $_SESSION['ORDER']['centerCoordX']];
        $addressCoords = ['lat' => $tmp[0] * M_PI / 180, 'lng' => $tmp[1] * M_PI / 180];
        $cl1 = cos($pickupCoords['lat']);
        $cl2 = cos($addressCoords['lat']);
        $sl1 = sin($pickupCoords['lat']);
        $sl2 = sin($addressCoords['lat']);
        $delta = $addressCoords['lng'] - $pickupCoords['lng'];
        $cdelta = cos($delta);
        $sdelta = sin($delta);

        $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
        $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;

        $ad = atan2($y, $x);
        $dist = $ad * EARTH_RADIUS;

        return number_format($dist / 1000, 2, '.', '');
    }

    /* TODO ИСПРАВИТЬ ЭТО БЕЗОБРАЗИЕ */
    /**
     * @param $deliveryId
     * @return string
     * @throws Exception
     */
    private function getDeliveryDescription($deliveryId): string
    {
        $soon = new DateTime($this->deliverySystemsRestr[$deliveryId]['DATE']);
        $str = 'с ' . $soon->format('d.m');

        if (!$this->deliverySystemsRestr[$deliveryId]['PRICE'])
        {
            $str .= ', бесплатно';
        }
        else
        {
            $str .= ', ' . number_format($this->deliverySystemsRestr[$deliveryId]['PRICE'], 2, '.', ' ') . ' ₽';
        }

        return $str;
    }

    /**
     * @param $deliveryId
     * @return string
     */
    private function getDeliveryDescriptionHeader($deliveryId): string
    {
        if ($deliveryId == DELIVERY_SELF_PICKUP_ID)
            $str = 'Самовывоз из пункта выдачи ';
        else
            $str = 'Доставка ';
        $str .= $this->deliverySystemsRestr[$deliveryId['ID']]['DATE'] . ', ';
        if (!$this->deliverySystemsRestr[$deliveryId['ID']]['PRICE'])
        {
            $str .= 'бесплатно';
        }
        else
        {
            $str .= number_format($this->deliverySystemsRestr[$deliveryId['ID']]['PRICE'], 2, '.', ' ') . ' ₽';
        }
        return $str;
    }

    /**
     * @param $deliveryId
     * @return array|mixed|string|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function getDeliveryDescriptionValue($deliveryId)
    {
        if ($deliveryId == DELIVERY_SELF_PICKUP_ID)
        {
            foreach ($this->deliverySystemsRestr[$deliveryId['ID']]['EXT']['pickups'] as $pickupId => $pickup)
            {
                if ($pickupId == $this->order->getPropertyCollection()->getItemByOrderPropertyCode('PICKUP')->getValue())
                {
                    $str = $pickup['ADDRESS'];
                    break;
                }
            }
        }
        else
        {
            $str = $this->order->getPropertyCollection()->getAddress()->getValue();
        }

        return $str;
    }

    /**
     * @throws \Bitrix\Main\ObjectNotFoundException
     */
    private function initValidation()
    {
        if ($this->order->getDeliverySystemId()[0] == DELIVERY_CITY_ID)
            $this->validation = ['street', 'house', 'DELIVERY_DATE', 'FIO', 'EMAIL', 'PHONE'];
        if ($this->order->getDeliverySystemId()[0] == DELIVERY_SELF_PICKUP_ID)
            $this->validation = ['pickup', 'DELIVERY_DATE', 'FIO', 'EMAIL', 'PHONE'];


        foreach ($this->validation as $value)
        {
            if (key_exists($value, $_SESSION['ORDER']))
            {
                switch ($value)
                {
                    case 'EMAIL':
                    {
                        if (!preg_match('/.+[@].*\..*/', $_SESSION['ORDER'][$value]))
                            $this->errors['VALIDATION'][$value] = Loc::getMessage($value . '_ERROR');
                        break;
                    }
                    case 'PHONE':
                    {
                        if (!preg_match('/\d{11}/', preg_replace('/\D/', '', $_SESSION['ORDER'][$value])))
                            $this->errors['VALIDATION'][$value] = Loc::getMessage($value . '_ERROR');
                        break;
                    }
                    default:
                    {
                        if (empty($_SESSION['ORDER'][$value]))
                        {
                            $this->errors['VALIDATION'][$value] = Loc::getMessage($value . '_ERROR');
                            break;
                        }
                    }
                }
            }
            else
            {
                $this->errors['VALIDATION'][$value] = Loc::getMessage($value . '_ERROR');
            }
        }
    }

    /**
     * @param $orderId
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectException
     */
    private function confirmOrder($orderId)
    {
        $this->arResult = [];
        $order = Sale\Order::load($orderId);

        $this->arResult['ID'] = $order->getField('ACCOUNT_NUMBER');

        if ($order->isPaid())
        {
            $this->arResult['IS_PAID'] = 'Y';
            return;
        }

        $paymentCollection = $order->getPaymentCollection();
        foreach ($paymentCollection as $payment)
        {
            $service = Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
            $context = \Bitrix\Main\Application::getInstance()->getContext();
            $this->arResult['PAYMENT'][$payment->getPaymentSystemId()]['TEMPLATE'] =
                str_replace(
                    'btn',
                    'btn btn-global btn-global--order',
                    $service->initiatePay($payment, $context->getRequest(), PaySystem\BaseServiceHandler::STRING)->getTemplate()
                );
        }
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function saveOrderInXml()
    {
        $order = $this->order;
        $arResult = $this->arResult;

        $arDataDelivery = [];
        $arDataPayment = [];

        if (!\Bitrix\Main\Loader::includeModule('highloadblock'))
        {
            return false;
        }

        $edc = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity(\Bitrix\Highloadblock\HighloadBlockTable::getById(DELIVERY_PAY_MATCH_HIBLOCK_ID)->fetch())->getDataClass();
        $rs = $edc::getList(['order' => ['ID' => 'ASC'], 'select' => ['ID', 'UF_XML_ID', 'UF_NAME', 'UF_TYPE']]);

        while ($ob = $rs->Fetch())
        {
            switch (intval($ob['UF_TYPE']))
            {
                case 48:
                    $arDataPayment[intval($ob['UF_XML_ID'])] = $ob['UF_NAME'];
                    break;
                case 49:
                    $arDataDelivery[intval($ob['UF_XML_ID'])] = $ob['UF_NAME'];
                    break;
            }
        }

        $dataProduct = getDataProductFromBasket($order->getBasket(), true);

        $storeExtId = '';
        $deliveryAddress = '';

        if ($arResult['PROPS']['PICKUP'] && $order->getDeliverySystemId()[0] == DELIVERY_SELF_PICKUP_ID)
        {
            $rs = \Bitrix\Catalog\StoreTable::getList(['filter' => ['ID' => $arResult['PROPS']['PICKUP']], 'select' => ['XML_ID', 'TITLE', 'ADDRESS', 'DESCRIPTION', 'UF_CATEGORY', 'UF_NAME_CITY']]);

            if ($ob = $rs->fetch())
            {
                $storeExtId = $ob['XML_ID'];
                $storeAddress = $ob['ADDRESS'];
                $storeDescription = $ob['DESCRIPTION'];
                $storeType = $ob['UF_CATEGORY'];
                $cityName = $ob['UF_NAME_CITY'];
            }

            if ($storeType == STORE_TYPE_PVZ_VALUE_ID)
            {
                $storeExtId = '';
                $deliveryAddress = $storeDescription;
            }
            else
            {
                $deliveryAddress = $cityName;
                $fiasId = $_SESSION['ORDER']['fias_id'];
                $fiasHome = $_SESSION['ORDER']['house'];
                $fiasLevel = $_SESSION['ORDER']['fias_level'];
                $coords = $_SESSION['ORDER']['fias_level'];
            }
        }
        else
        {
            $deliveryAddress = trim(implode(', ', [$_SESSION['ORDER']['city'], $arResult['PROPS']['ADDRESS']]), ', ');
        }

        if (!ctype_digit($arResult['PROPS']['DELIVERY_DATE']))
        {
            $arResult['PROPS']['DELIVERY_DATE'] = date('d-m-Y', strtotime('today'));
        }

        $orderDateOfCreation = date('d-m-Y');
        $orderDateOfDelivery = str_replace('.', '-', $arResult['PROPS']['DELIVERY_DATE']);
        $orderNumber = 'GLOBAL_' . date('Y') . '_' . $order->getId();
        $orderComment = htmlspecialchars(($arResult['PROPS']['PHONE'] ? $arResult['PROPS']['PHONE'] : '') . ($order->getField('USER_DESCRIPTION') ? ' | ' . $order->getField('USER_DESCRIPTION') : ''));

        $orderCodeOfTypePayment = $arDataPayment[intval($_SESSION['ORDER']['pay'])];
        $orderCodeOfTypeDelivery = $arDataDelivery[intval($_SESSION['ORDER']['delivery'])];

        $orderStoreExtId = $storeExtId;
        $orderDeliveryAddress = htmlspecialchars($deliveryAddress);
        $orderDeliveryPrice = $order->getDeliveryPrice();
        $orderCurrency = DEFAULT_CURRENCY;
        $orderContactName = htmlspecialchars($arResult['PROPS']['FIO']);
        $orderContactPhone = htmlspecialchars($arResult['PROPS']['PHONE']);
        $orderUserPhone = htmlspecialchars($arResult['PROPS']['PHONE']);
        $orderUserName = htmlspecialchars($arResult['PROPS']['FIO']);
        $orderUserEmail = htmlspecialchars($arResult['PROPS']['EMAIL']);
        $orderUserLogin = htmlspecialchars($arResult['PROPS']['EMAIL']);

        $result = '';
        $result .= '<ИнтернетЗаказ>' . PHP_EOL;
        $result .= '    <Заказ ДоговорExtID="" АдресДоставкиExtID="" Дата="' . $orderDateOfCreation . '" ДатаОтгрузки="' . $orderDateOfDelivery . '" ИнтернетНомер="' . $orderNumber . '" Комментарий="' . $orderComment . '" КодТипаОплаты="' . $orderCodeOfTypePayment . '" КодТипаДоставки="' . $orderCodeOfTypeDelivery . '" СкладОтгрузкиExtID="' . $orderStoreExtId . '" АдресДоставки="' . $orderDeliveryAddress . '" СтоимостьДоставки="' . $orderDeliveryPrice . '" Валюта_ExtID="' . $orderCurrency . '" Контакт="' . $orderContactName . '" ТелефонКонтакта="' . $orderContactPhone . '" КодФиас="' . $fiasId . '" ДомФиас="' . $fiasHome . '" УровеньФиас="' . $fiasLevel . '" КоординатыТочкиДоставки="' . $coords . '"/>' . PHP_EOL;
        $result .= '    <Покупатель ExtID="" Телефон="' . $orderUserPhone . '" Наименование="' . $orderUserName . '" EMail="' . $orderUserEmail . '" Login="' . $orderUserLogin . '"/>' . PHP_EOL;
        $result .= '    <Товары>' . PHP_EOL;

        foreach ($dataProduct as $arItem)
        {
            $result .= '        <Товар ExtID="' . $arItem['XML_ID'] . '" Количество="' . $arItem['QUANTITY'] . '" Цена="' . round($arItem['PRICE_PER_1'], 2) . '" ЕдИзм_ExtID="' . $arItem['MEASURE_EXT'] . '" Скидка="' . ($arItem['DISCOUNT'] != 0 ? round($arItem['DISCOUNT'], 2) : '') . '"/>' . PHP_EOL;
        }

        $result .= '    </Товары>' . PHP_EOL;
        $result .= '</ИнтернетЗаказ>';

        $file = '/upload/orders/order_' . date('Y') . '_' . $order->getId() . '.xml';

        if (!file_exists(Bitrix\Main\Application::getDocumentRoot() . $file))
        {
            $fp = fopen(Bitrix\Main\Application::getDocumentRoot() . $file, 'w');
            fwrite($fp, PHP_EOL);
            fclose($fp);
        }

        file_put_contents(\Bitrix\Main\Application::getDocumentRoot() . $file, $result);
        \Common\Functions::uploadOrder(\Bitrix\Main\Application::getDocumentRoot() . $file, $order->getId(), false);

        return true;
    }
}
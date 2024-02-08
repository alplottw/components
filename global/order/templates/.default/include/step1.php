<?php

/** @var array $arResult */
/** @var string $step */

?>

<div class="order__step order-part order__step--active">
    <div class="order-part__header">
        <div class="order-part__header-value">Способ получения заказа</div>
    </div>
    <div class="order-part">
        <? if ($arResult['DELIVERY']['SELECTED']['ID'] == DELIVERY_CITY_ID): ?>
            <div class="order-part__content order-delivery">
                <div class="order-delivery__content-info">
                    <? require 'delivery.php'; ?>
                    <div class="order-delivery__header">
                        Укажите адрес доставки
                    </div>
                    <input type="hidden"
                           name="distance"
                           value="<?= $_SESSION['ORDER']['distance'] ?>"
                    >
                    <input type='hidden'
                           name='fias_id'
                           value="<?= (isset($arResult['daData']) && !empty($arResult['daData']['suggestions'][0]['data']['fias_id'])) ? $arResult['daData']['suggestions'][0]['data']['fias_id'] : 0 ?>">
                    <input type='hidden'
                           name='fias_level'
                           value="<?= (isset($arResult['daData']) && !empty($arResult['daData']['suggestions'][0]['data']['fias_level'])) ? $arResult['daData']['suggestions'][0]['data']['fias_level'] : 0 ?>">
                    <input type='hidden'
                           name='coord'
                           value="<?= (isset($arResult['daData']) && !empty($arResult['daData']['suggestions'][0]['data']['geo_lat']) && !empty($arResult['daData']['suggestions'][0]['data']['geo_lon'])) ? $arResult['daData']['suggestions'][0]['data']['geo_lat'] . ',' . $arResult['daData']['suggestions'][0]['data']['geo_lon'] : 0 ?>">

                    <div class="order-delivery__input order-input order-input--hide">
                        <input class="order-input__text order-input__text--square valueOrder-js"
                               type="text"
                               id="manual_address"
                               name="ADDRESS"
                               placeholder="Новый адрес доставки"
                               data-fias=""
                               value="<?= $arResult['PROPS']['ADDRESS'] ?>"
                        >
                        <input type="hidden"
                               class="sbd-inp valueOrder-js"
                               name="city"<?= $attrReadOnly ?>
                               value="<?= $city ?>"
                        >
                        <input type="hidden"
                               class="sbd-inp valueOrder-js"
                               name="streetFiasId"
                               value="<?= $_SESSION['ORDER']['streetFiasId'] ?>"
                        >
                    </div>
                    <div class="order-delivery__address">
                        <div class="order-delivery__input order-input order-input--width-full">
                            <input class="order-input__text order-input__text--square <?= $arResult['ERRORS']['VALIDATION']['street'] ? 'order-input__text--error' : '' ?> valueOrder-js"
                                   type="text"
                                   name="street"
                                   data-measure="улица"
                                   placeholder="Улица:"
                                   value="<?= $_SESSION['ORDER']['street'] ?>"
                            >
                        </div>
                        <div class="order-delivery__input order-input order-input--width-third">
                            <input class="order-input__text order-input__text--square <?= $arResult['ERRORS']['VALIDATION']['house'] ? 'order-input__text--error' : '' ?> valueOrder-js"
                                   type="text"
                                   name="house"
                                   data-measure="дом"
                                   placeholder="Дом:"
                                   value="<?= $_SESSION['ORDER']['house'] ?>"
                            >
                        </div>
                        <div class="order-delivery__input order-input order-input--width-third">
                            <input class="order-input__text order-input__text--square valueOrder-js"
                                   type="text"
                                   name="body"
                                   data-measure="корпус"
                                   placeholder="Корпус:"
                                   value="<?= $_SESSION['ORDER']['body'] ?>"
                            >
                        </div>
                        <div class="order-delivery__input order-input order-input--width-third">
                            <input class="order-input__text order-input__text--square valueOrder-js"
                                   type="text"
                                   name="appart"
                                   data-measure="квартира"
                                   placeholder="Кв./офис:"
                                   value="<?= $_SESSION['ORDER']['appart'] ?>"
                            >
                        </div>
                        <label class="order-delivery__input order-input order-input--width-half order-input--pos-left order-input--icon order-input--icon-calendar"
                               for="ui-datepicker">
                            <? if (((
                                        date('N', strtotime($_SESSION['ORDER']['date'])) === '7'
                                        || (isset($arResult['DELIVERY']['ITEMS'][DELIVERY_CITY_ID]['notDate'])
                                            && in_array($_SESSION['ORDER']['date'], $arResult['DELIVERY']['ITEMS'][DELIVERY_CITY_ID]['notDate'])))
                                    && strtotime($arResult['DELIVERY']['ITEMS'][DELIVERY_CITY_ID]['soonDate']) < strtotime($_SESSION['ORDER']['date']))
                                || strtotime($arResult['DELIVERY']['ITEMS'][DELIVERY_CITY_ID]['EXT']['soonDate']) > strtotime($_SESSION['ORDER']['date'])
                            )
                            {
                                $_SESSION['ORDER']['date'] = $arResult['DELIVERY']['ITEMS'][DELIVERY_CITY_ID]['soonDate'];
                            }
                            $tmpValDate = $_SESSION['ORDER']['date'];
                            if ($_SESSION['ORDER']['date'][0] == 0)
                            {
                                $tmpValDate = substr($tmpValDate, 1);
                            }

                            if (in_array($tmpValDate, $arResult['DELIVERY']['ITEMS'][DELIVERY_CITY_ID]['notDate']))
                            {
                                unset($_SESSION['ORDER']['date']);
                            }
                            ?>
                            <input
                                    class="order-input__text order-input__text--square order-input__text--calendar <?= $arResult['ERRORS']['VALIDATION']['DELIVERY_DATE'] ? 'order-input__text--error' : '' ?> valueOrder-js"
                                    type="text"
                                    id="ui-datepicker"
                                    name="DELIVERY_DATE"
                                    placeholder="Дата"
                                    data-min_date="<?= $arResult['DELIVERY']['ITEMS'][DELIVERY_CITY_ID]['minQntDate'] ?>"
                                    data-not_date="<?= implode(';', $arResult['DELIVERY']['ITEMS'][DELIVERY_CITY_ID]['notDate']) ?>"
                                    value="<?= $arResult['PROPS']['DELIVERY_DATE'] ?>"
                                    readonly
                                    required
                            >
                        </label>
                    </div>
                    <div class="order-part">
                        <div class="order-part__content order-comment">
                            <div class="order-comment__input order-input">
                                <textarea
                                        class="order-input__text order-input__text--square  valueOrder-js"
                                        name="USER_DESCRIPTION"
                                        placeholder="Комментарий к заказу"
                                        rows="3"><?= $_SESSION['ORDER']['USER_DESCRIPTION'] ?>
                                </textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-delivery__content-map">
                    <div class="order-delivery__map">
                        <input type="hidden"
                               name="centerCoordX"
                               value="<?= $centerCoord[1] ?>">
                        <input type="hidden"
                               name="centerCoordY"
                               value="<?= $centerCoord[0] ?>">
                        <div class="order-delivery__map-instance"
                             id="map-order"
                             data-startx="<?= $centerCoord[1] ?>"
                             data-starty="<?= $centerCoord[0] ?>"
                             data-zoom="<?= $arResult['PROPS']['ADDRESS'] ? 15 : 11 ?>"
                             data-is_open="<?= isset($_REQUEST['coord']) ? 'Y' : 'N' ?>"
                             data-placex="<?= (isset($arResult['daData']) && !empty($arResult['daData']['suggestions'][0]['data']['geo_lat'])) ? $arResult['daData']['suggestions'][0]['data']['geo_lat'] : 0 ?>"
                             data-placey="<?= (isset($arResult['daData']) && !empty($arResult['daData']['suggestions'][0]['data']['geo_lon'])) ? $arResult['daData']['suggestions'][0]['data']['geo_lon'] : 0 ?>"
                             data-delivery="Y">
                        </div>
                    </div>
                </div>
            </div>
        <? endif ?>
        <? if ($arResult['DELIVERY']['SELECTED']['ID'] == DELIVERY_SELF_PICKUP_ID && count($arResult['DELIVERY']['ITEMS'][DELIVERY_SELF_PICKUP_ID]['EXT']['pickups']) > 0): ?>
            <div class="order-part__content order-delivery">
                <div class="order-delivery__content-info">
                    <? require 'delivery.php'; ?>
                    <div class="order-delivery__header">
                        Выберите пункт самовывоза
                    </div>
                    <div class="order-delivery__input order-input order-input--icon order-input--icon-search order-input--icon-pos-left order-input--icon-bord-none">
                        <input class="order-input__text order-input__text--bord-gray valueOrder-js"
                               type="text"
                               id="manual_address"
                               name="ADDRESS"
                               data-self="true"
                               data-skip="true"
                               placeholder="Найти по адресу"
                               value="<?= $arResult['PROPS']['ADDRESS'] ?>"
                        >
                        <div class='suggestions-wrapper'>
                            <div class='suggestions-suggestions'
                                 style='display:none;'>
                                <div class='suggestions-suggestion'
                                     data-index='0'><span class='suggestions-value'><span class='suggestions-nowrap'>г Москва</span>, <span class='suggestions-nowrap'>ул <strong>Панфил</strong>ова</span></span>
                                </div>
                            </div>
                        </div>
                        <input type="hidden"
                               class="sbd-inp valueOrder-js"
                               name="city"<?= $attrReadOnly ?>
                               value="<?= $city ?>"
                        >
                    </div>
                    <div class="order-delivery__address">
                        <label class="order-delivery__input order-input order-input--width-half order-input--pos-left order-input--icon order-input--icon-calendar"
                               for="ui-datepicker">
                            <? if (((
                                        date('N', strtotime($_SESSION['ORDER']['date'])) === '7'
                                        || (isset($arResult['DELIVERY']['ITEMS'][DELIVERY_SELF_PICKUP_ID]['EXT']['notDate'])
                                            && in_array($_SESSION['ORDER']['date'], $arResult['DELIVERY']['ITEMS'][DELIVERY_SELF_PICKUP_ID]['EXT']['notDate'])))
                                    && strtotime($arResult['DELIVERY']['ITEMS'][DELIVERY_SELF_PICKUP_ID]['EXT']['soonDate']) < strtotime($_SESSION['ORDER']['date']))
                                || strtotime($arResult['DELIVERY']['ITEMS'][DELIVERY_SELF_PICKUP_ID]['EXT']['soonDate']) > strtotime($_SESSION['ORDER']['date'])
                            )
                            {
                                $_SESSION['ORDER']['date'] = $arResult['DELIVERY']['ITEMS'][DELIVERY_SELF_PICKUP_ID]['EXT']['soonDate'];
                            }
                            $tmpValDate = $_SESSION['ORDER']['date'];
                            if ($_SESSION['ORDER']['date'][0] == 0)
                            {
                                $tmpValDate = substr($tmpValDate, 1);
                            }

                            if (in_array($tmpValDate, $arResult['DELIVERY']['ITEMS'][DELIVERY_SELF_PICKUP_ID]['EXT']['notDate']))
                            {
                                unset($_SESSION['ORDER']['date']);
                            }
                            ?>
                            <input
                                    class="order-input__text order-input__text--calendar order-input__text--square valueOrder-js <?= $arResult['ERRORS']['VALIDATION']['DELIVERY_DATE'] ? 'order-input__text--error' : '' ?>"
                                    type="text"
                                    id="ui-datepicker"
                                    name="DELIVERY_DATE"
                                    placeholder="Дата"
                                    data-min_date="<?= $arResult['DELIVERY']['ITEMS'][DELIVERY_SELF_PICKUP_ID]['EXT']['minQntDate'] ?>"
                                    data-not_date="<?= implode(';', $arResult['DELIVERY']['ITEMS'][DELIVERY_SELF_PICKUP_ID]['EXT']['notDate']) ?>"
                                    value="<?= $arResult['PROPS']['DELIVERY_DATE'] ?>"
                                    readonly
                                    required
                            >
                        </label>
                    </div>
                    <div class="order-part">
                        <div class="order-part__content order-comment">
                            <div class="order-comment__input order-input">
                                    <textarea type="text"
                                              class="order-input__text order-input__text--square  valueOrder-js"
                                              name="USER_DESCRIPTION"
                                              placeholder="Комментарий к заказу"
                                              rows="3"><?= $_SESSION['ORDER']['USER_DESCRIPTION'] ?>
                                    </textarea>
                            </div>
                            <? if ($arResult['ERRORS']['VALIDATION']['pickup']): ?>
                                <div class="order-part__error">
                                    <?= $arResult['ERRORS']['VALIDATION']['pickup'] ?>
                                </div>
                            <? endif; ?>
                        </div>
                    </div>
                </div>
                <div class="order-delivery__content-map">
                    <? if ($arResult['DELIVERY']['SELECTED']['ID'] == DELIVERY_SELF_PICKUP_ID): ?>
                        <div class=" single-select single-select--order-self">
                            <div class="single-select__inner">
                                <input class="single-select__input valueOrder-js"
                                       type="radio"
                                       name="selfType"
                                       id="dsList"
                                       value="dsList" <?= $_SESSION['ORDER']['selfType'] == DELIVERY_SELF_VIEW_TYPE_LIST ? 'checked' : '' ?>>
                                <label class="single-select__item"
                                       for="dsList">Список</label>
                                <div class="single-select__body <?= $_SESSION['ORDER']['selfType'] == DELIVERY_SELF_VIEW_TYPE_MAP ? 'single-select__body--change' : '' ?>"></div>
                                <input class="single-select__input valueOrder-js"
                                       type="radio"
                                       name="selfType"
                                       id="dsMap"
                                       value="dsMap" <?= $_SESSION['ORDER']['selfType'] == DELIVERY_SELF_VIEW_TYPE_MAP ? 'checked' : '' ?>>
                                <label class="single-select__item"
                                       for="dsMap">На карте</label>
                            </div>
                        </div>
                    <? endif ?>
                    <div class="order-part__content order-delivery <?= $_SESSION['ORDER']['selfType'] != DELIVERY_SELF_VIEW_TYPE_LIST ? 'order-part__content--hide' : '' ?>">
                        <div class="order-delivery__list pickup-list">
                            <div class="pickup-list__collection mCustomScrollbar"
                                 data-mcs-theme="dark-thin">
                                <? foreach ($arResult['DELIVERY']['ITEMS'][DELIVERY_SELF_PICKUP_ID]['EXT']['pickups'] as $pickupId => $pickup): ?>
                                    <div class="pickup-list__item">
                                        <input class="pickup-list__item-input valueOrder-js"
                                               type="radio"
                                               name="pickup"
                                            <? if ($pickup['ID'] == $arResult['PROPS']['PICKUP']) { ?> checked <? } ?>
                                               id="pickup<?= $pickup['ID'] ?>"
                                               value="<?= $pickup['ID'] ?>"
                                               data-address="<?= $pickup['ADDRESS'] ?>"
                                        >
                                        <label class="pickup-list__item-inner"
                                               for="pickup<?= $pickup['ID'] ?>">
                                            <div class="pickup-list__item-info pickup-list__item-info--header"><?= $pickup['ADDRESS'] ?></div>
                                            <div class="pickup-list__item-info">Телефон: <?= $pickup['PHONE'] ?></div>
                                            <div class="pickup-list__item-info">Время
                                                                                работы: <?= $pickup['SCHEDULE'] ?></div>
                                        </label>
                                    </div>
                                <? endforeach ?>
                            </div>
                        </div>
                    </div>
                    <div class="order-part__content <?= $_SESSION['ORDER']['selfType'] != DELIVERY_SELF_VIEW_TYPE_MAP ? 'order-part__content--hide' : '' ?>">
                        <div class="order-delivery__map">
                            <input type='hidden'
                                   name='centerCoordX'
                                   value="<?= $centerCoord[1] ?>">
                            <input type='hidden'
                                   name='centerCoordY'
                                   value="<?= $centerCoord[0] ?>">
                            <div class="order-delivery__map-instance"
                                 id="map-order"
                                 data-startx="<?= $centerCoord[1] ?>"
                                 data-starty="<?= $centerCoord[0] ?>"
                                 data-zoom="<?= $arResult['PROPS']['PICKUP'] ? 15 : 10 ?>"
                                 data-is_open="<?= isset($_REQUEST['coord']) ? 'Y' : 'N' ?>">
                                <?
                                foreach ($arResult['DELIVERY']['ITEMS'][DELIVERY_SELF_PICKUP_ID]['EXT']['pickups'] as $pickup)
                                { ?>
                                    <?
                                    $coord = explode(',', $pickup['COORD']); ?>
                                    <span data-x="<?= $coord[1] ?>"
                                          data-y="<?= $coord[0] ?>"
                                          data-category="<?= intval($pickup['CATEGORY']) ?>"
                                          data-city="<?= $pickup['CITY'] ?>"
                                          data-metro="<?= $data['METRO'] ?>"
                                          data-address="<?= $pickup['ADDRESS'] ?>"
                                          data-select="<?= $pickup['ID'] == $arResult['PROPS']['PICKUP'] ? 'Y' : 'N' ?>"
                                          data-id="<?= $pickup['ID'] ?>"></span>
                                    <?
                                    if ($pickup['ID'] == $arResult['PROPS']['PICKUP'] && $arResult['DELIVERY']['SELECTED']['ID'] == DELIVERY_SELF_PICKUP_ID)
                                    {
                                        $centerCoord = $coord;
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <? endif ?>
    </div>
</div>
var countInitMap = 0;
var focusInput = [];
var ajax = 1;
$((e) => {
    refreshDom()
});


let refreshDom = () => {


    let adaptive = initAdaptive();
    $(window).resize(() => {
        !adaptive && (adaptive = initAdaptive());
    });

    initDatepicker();
    ymaps.ready(initMap);
    initChangeSelfDelivery();
    initCustomScrollbar();
    initDadata();
    window.phone && (window.phone.oninput = ValidPhone);

    focusInput = [];
    $('.order input, .order textarea').focusin(function () {
        focusInput.push(this)
    });
    $('.order input, .order textarea').change((e) => {
        if (!$(e.target).data('skip') && ajax)
            updateOrder();
        ajax = 0;
    })

    $('.order a.order-control__btn').click((e) => {
        e.preventDefault();
        window.location.href = $(e.target).attr('href');
    });

    $('.order-total__btn').click((e) => {
        updateOrder(true, true);
    })

    let selfAddreses = [];
    $('#map-order span').each((idx, el) => {
        let addr = {
            address:$(el).data('address'),
            id:$(el).data('id')
        };
        selfAddreses.push(addr);
    })
    $('[name=ADDRESS]').on('input', (e) => {
        let $input = $(e.currentTarget);
        $suggWrap = $('.suggestions-suggestions');
        $suggWrap.html('').show();
        let result = [];
        selfAddreses.forEach((addr, idx) => {
            if (result.length < 5) {
                let startPos = addr.address.toLowerCase().indexOf($input.val().toLowerCase());
                if (startPos !== -1) {
                    let findWord = addr.address.slice(startPos, startPos + $input.val().length)
                    console.log(findWord)

                    result.push({
                        address: addr.address.replace(findWord, `<strong>${findWord}</strong>`),
                        id: addr.id
                    });
                }
            }
        })

        if (!result.length) {
            result.push({address:`Адрес не найден`});
        }

        result.forEach((addr, idx) => {
            $suggWrap.append(`
            <div class='suggestions-suggestion'
                data-id="${addr.id}"
                data-skip="true">
                <span class='suggestions-value'><span class='suggestions-nowrap'>${addr.address}</span></span>
            </div>
            `)
        })

        if ($('.suggestions-suggestion').length > 0) {
            $('.suggestions-suggestion').click((e) => {
                changeSelectPickup($(e.currentTarget).data('id'));
                $('[name=ADDRESS]').val($(e.currentTarget).text().trim());
                updateOrder();
            })
        }
    })
}

let updateOrder = (validation = false, saveOrder = false) => {
    $('[name=action]').val('updateOrder');
    if (validation)
        $('[name=action]').val('validOrder');
    $('[name=is_ajax]').val('Y');
    let data = $('.order').serialize();
    $.ajax({
        method: 'POST',
        data,
        success: (res) => {
            ajax = 1;
            focusInput = focusInput[focusInput.length - 1];

            let buffer = $(res.html);
            let $focusInput = buffer.find(`[name=${$(focusInput).attr('name')}]`);

            $focusInput.attr('autofocus', true);
            buffer.find(`[name=${$(focusInput).attr('name')}]`).replaceWith($focusInput);
            let $focusVal = $focusInput.val();
            if (saveOrder && !buffer.find('.order-input__text--error').length) {
                $('[name=action]').val('saveOrder');
                $('.order').submit();
            }
            $('.wrapper').replaceWith(buffer);
            if ($('.wrapper').find('.order-input__text--error').length) {
                $('html, body').animate({
                    scrollTop: $('.order-input__text--error').offset().top - 150
                }, 1000);
            }
            if ($(focusInput).attr('name') === 'PHONE') {
                $(`[name=${$(focusInput).attr('name')}]`).val("").val($focusVal);
            } else {
                $(`[name=${$(focusInput).attr('name')}]`).focus().val("").val($focusVal);
            }

            refreshDom();
        }
    });
}
let initAdaptive = () => {
    if (window.matchMedia('(max-width: 1024px)').matches) {
        moveOrderButtons();
        moveSingleSelect();
        return true;
    } else {
        return false;
    }
}
let moveOrderButtons = () => {
    let $orderButtons = $('.order-control');
    $orderButtons.each((idx, el) => {
        let rnd = Math.random(100000);
        $(el).data('child', rnd);

        let $orderButtonParent = $(el).parent().data('parent', rnd);
        let $clone = $orderButtonParent.clone().html('').append($(el))
        $orderButtonParent.parent().parent().append($clone);
    })
}
let moveSingleSelect = () => {
    $('.order-delivery__content-map').prepend($('.single-select'));
}
var isBackspace = !1;

function ValidPhone() {
    if (
        ((phone.onkeydown = function (e) {
            isBackspace = "Backspace" == e.key;
        }),
        phone.value.length < 2 && isBackspace && (phone.value = "+7"),
        1 != phone.value.length || "+" == phone.value[0] || isBackspace || (phone.value = "+7" + phone.value),
            !isBackspace)
    ) {
        phone.value = "+" + phone.value.replace(/\D+/g, "");
        let e = phone.value.replace(" (", "").replace(") ", "").replace(/-/g, "");
        phone.value.length > 12 && (phone.value = "+7" + CheckPhone(e.slice(2))), (phone.value = PhoneMask(phone.value));
    }
}

function PhoneMask(e) {
    let t = "";
    for (let a = 0; a < e.length; a++) (t += e[a]), 1 == a && (t += " ("), 4 == a && (t += ") "), (7 != a && 9 != a) || (t += "-");
    return t;
}

function initMap() {
    try {
        countInitMap++;
        let a = "/img/mm200.png",
            n = "/img/mm300.png",
            t = "/img/mm400.png",
            e = "/img/mm100.png";
        $("body")
            .find("#map-order")
            .each(function (r, i) {
                let s = parseFloat($(this).attr("data-startx")),
                    o = parseFloat($(this).attr("data-starty")),
                    d = parseInt($(this).attr("data-zoom")),
                    c = "Y" === $(this).attr("data-is_open");
                d > 0 || (d = 12);
                // console.log(d);
                d = 18;
                let l = [$(this).parent().find("span:first").attr("y"), $(this).parent().find("span:first").attr("x")];
                s > 0 && o > 0 && (l = [$(this).attr("data-starty"), $(this).attr("data-startx")]);
                let p = new ymaps.Map(i, {
                    center: l,
                    zoom: d,
                    behaviors: ["drag", "dblClickZoom", "multiTouch", "rightMouseButtonMagnifier"],
                    controls: ["fullscreenControl", "typeSelector", "zoomControl"]
                });
                m = [];
                $(i)
                    .find("span")
                    .each(function (r, i) {
                        let s = $(this).attr("data-id");
                        !isNaN(parseInt(s)) && parseInt(s) > 0 && (s = parseInt(s));
                        let o = $(this).attr("data-category");
                        "" !== o && (o = "<h4>" + (30 === parseInt(o) ? "Магазины/склады" : "Пункты выдачи") + "</h4>");
                        let d = $(this).attr("data-city");
                        "" !== d && (d = "<div>" + d + "</div>");
                        let c = $(this).attr("data-metro");
                        "" !== c && (c = "<div>" + c + "</div>");
                        let l = $(this).attr("data-address");
                        "" !== l && (l = "<div>" + l + "</div>");
                        let u = [d, c, l].join(" "),
                            h = "Y" === $(this).attr("data-select"),
                            f = "";
                        h && 0 != countInitMap
                            ? ((f = 30 === parseInt($(this).attr("data-category")) ? n : t), p.setCenter([$(this).attr("data-y"), $(this).attr("data-x")], 15))
                            : (f = 30 === parseInt($(this).attr("data-category")) ? a : e),
                            m.push({
                                id: s,
                                ele: String(r),
                                name: o,
                                title: u,
                                x: $(this).attr("data-x"),
                                y: $(this).attr("data-y"),
                                city: d,
                                metro: c,
                                adress: l,
                                select: h,
                                marker: f
                            });
                    });

                for (let e = 0; e < m.length; e++) {
                    const t = m[e];
                    let a = new ymaps.Placemark(
                        [t.y, t.x],
                        {
                            hintContent: t.title,
                            balloonContentHeader: t.name,
                            balloonContentBody: '<div style="text-align:left;">' + t.city + t.metro + t.adress + "</div>",
                            pickupId: t.id,
                            isSelect: t.select
                        },
                        {
                            iconLayout: "default#image",
                            iconImageHref: t.marker,
                            iconImageSize: [40, 57],
                            iconImageOffset: [-28, -28]
                        }
                    );
                    a.events.add(["click"], function (e) {
                        let t = e.originalEvent.target.properties.get("pickupId");
                        return !!e.originalEvent.target.properties.get("isSelect") || (changeSelectPickup(t, p.getCenter()), !0);
                    }),
                        p.geoObjects.add(a),
                    c && t.select && a.balloon.open();
                }

                if ((1 == countInitMap && void 0 === $(this).attr("data-delivery") , void 0 !== $(this).attr("data-delivery"))) {
                    if (((deliveryMap = p), 0 != $(this).attr("data-placex") && 0 != $(this).attr("data-placey"))) {
                        let e = $(this).attr("data-placex"),
                            t = $(this).attr("data-placey"),
                            a = new ymaps.Placemark([e, t], {}, {iconLayout: "default#image"});
                        deliveryMap.geoObjects.add(a), deliveryMap.setCenter([e, t], 16);
                    }
                    deliveryMap.events.add("click", function (e) {
                        $('select[name="exist_address"] > option').each(function () {
                            "new" == $(this).val() ? $(this).attr("selected", "selected") : $(this).attr("selected", !1), $(".select-selected").text("Новый адрес");
                        }),
                            deliveryMap.geoObjects.removeAll();
                        var t = e.get("coords");
                        let a = new ymaps.Placemark([t[0], t[1]], {}, {iconLayout: "default#image"});
                        deliveryMap.geoObjects.add(a),
                            deliveryMap.setCenter([t[0], t[1]], 16),
                            $.ajax({
                                url: "/include/ajax/post/geocoder.php",
                                type: "POST",
                                data: {lat: t[0], lng: t[1]},
                                dataType: "json",
                                success: function (e) {
                                    if (e.suggestions.length) {
                                        let t = $(".disctanceBlock-js").attr("data-city");
                                        !1 === inPoly(e.suggestions[0].data.geo_lon, e.suggestions[0].data.geo_lat, t),
                                            $("#manual_address").val(e.suggestions[0].value),
                                            $("#manual_address").blur(),
                                            $('[name=centerCoordX]').val(e.suggestions[0].data.geo_lon),
                                            $('[name=centerCoordY]').val(e.suggestions[0].data.geo_lat),
                                        null !== e.suggestions[0].data.fias_id && $('input[name="fias_id"]').val(e.suggestions[0].data.fias_id),
                                        null !== e.suggestions[0].data.street && $('input[name="street"]').val(e.suggestions[0].data.street_with_type),
                                        null !== e.suggestions[0].data.house && $('input[name="house"]').val(e.suggestions[0].data.house),
                                        null !== e.suggestions[0].data.body && $('input[name="body"]').val(e.suggestions[0].data.block),
                                        null !== e.suggestions[0].data.fias_level && $('input[name="fias_level"]').val(e.suggestions[0].data.fias_level),
                                        null !== e.suggestions[0].data.geo_lat && e.suggestions[0].data.geo_lon && $('input[name="coord"]').val(e.suggestions[0].data.geo_lat + "," + e.suggestions[0].data.geo_lon), calcMKADDistance($('[name=city]').val(), $('[name=ADDRESS]').val());
                                    } else $("#manual_address").val("Адрес не найден"), $('input[name="fias_id"]').val(0), $('input[name="house"]').val(0), $('input[name="fias_level"]').val(0), $('input[name="coord"]').val(""), calcMKADDistance($('[name=city]').val(), $('[name=ADDRESS]').val());
                                },
                                error: function (e) {
                                    console.log(e);
                                },
                            });
                    });
                }
                p.setZoom(parseInt($(this).attr("data-zoom")))
            });
    } catch (e) {
        console.log("=============="), console.log(e), console.log("==============");
    }
}

function changePayment(e = false) {
    console.log(e)
    $input = $('[name=pay]');
    $input.val($('[name=fakePay]:checked').val());
    updateOrder();
}

/* TODO по хорошему надо сделать отдельно компонент корзины */
function changeQuantityUp(e) {
    $input = $(e.target).parents('.spr-quantity').find('.spr-quantity__input');
    $input.val(Number($input.val()) + Number(1));
    $input.change();
}

function changeQuantityDown(e) {
    $input = $(e.target).parents('.spr-quantity').find('.spr-quantity__input');

    if ($input.val() == 1) return;
    $input.val(Number($input.val()) - Number(1));
    $input.change();
}

function changeQuantityInCart(e, t) {
    let a = $(e.target).parents(".itemCart-js").attr("data-item_id"),
        n = parseInt($(e.target).val()),
        r = {};
    if (n <= 0) return !1;
    if (void 0 === a || isNaN(parseInt(a)) || parseInt(a) <= 0) return !1;
    (r.action = "change"), (r.id = a), (r.qnt = n), 1 === window.location.pathname.indexOf("cart") && (r.get_detail = "Y");
    let i = [];
    for (nameProp in r) i.push(nameProp + "=" + r[nameProp]);
    let s = i.length > 0 ? "?" + i.join("&") : "";
    $.get("/include/ajax/post/basket.php" + s, function (e) {
        updateOrder();
    });
}

function deleteItemInCart(e) {
    let t = $(e.target),
        a = t.parents(".itemCart-js").attr("data-item_id"),
        n = {};
    if (void 0 === a || isNaN(parseInt(a)) || parseInt(a) <= 0) return !1;
    let r = t.parents(".itemCart-js").find("input[name=quantity]").val();
    (n.action = "delete"), (n.id = a), (n.qnt = r), 1 === window.location.pathname.indexOf("cart") && (n.get_detail = "Y");
    let i = [];
    for (nameProp in n) i.push(nameProp + "=" + n[nameProp]);
    let s = i.length > 0 ? "?" + i.join("&") : "";
    $.get("/include/ajax/post/basket.php" + s, function (e) {
        BX.onCustomEvent('OnBasketChange');
        updateOrder();
    });
}

function initDadata() {
    let token = '221f2111893e0e8756029ed7ffcb0ae8be6231f9';
    let type = 'ADDRESS';
    $('[name=street]').suggestions({
        token,
        type,
        hint: false,
        bounds: "street",
        onSelect: function (e) {
            $('[name=ADDRESS]').val(e.unrestricted_value);
            $('[name=city]').val(e.data.city);
            $('[name=streetFiasId]').val(e.data.street_fias_id);
            $('[name=house]').val('');
            $('[name=body]').val('');
            $('[name=appart]').val('');

            calcMKADDistance($('[name=city]').val(), $('[name=ADDRESS]').val());
        }
    });

    $('[name=house]').suggestions({
        token,
        type,
        hint: false,
        from_bound: {
            "value": "house"
        },
        restrict_value: true,
        constraints: {
            locations: {street_fias_id: $('[name=streetFiasId]').val()},
        },
        onSelect: function (e) {
            $('[name=ADDRESS]').val(e.unrestricted_value);
            $('[name=house]').val(e.data.house);
            $('[name=body]').val(e.data.block);

            calcMKADDistance($('[name=city]').val(), $('[name=ADDRESS]').val());
        },
    });
    /*

        $('[name=ADDRESS]').suggestions({
            token,
            type,
            hint: false,
            onSelect: function (e) {
                $('[name=centerCoordX]').val(e.data.geo_lon);
                $('[name=centerCoordY]').val(e.data.geo_lat);
                $('#manual_address').change();
            }
        });
    */

}

function initDatepicker() {
    $("#ui-datepicker").each(function (e, t) {
        let a = $(t),
            n = {},
            r = void 0 !== a.attr("data-not_date") ? a.attr("data-not_date").split(";") : [];
        void 0 !== a.attr("data-min_date") &&
        !isNaN(parseInt(a.attr("data-min_date"))) &&
        parseInt(a.attr("data-min_date")) >= 0 &&
        ((n.minDate = parseInt(a.attr("data-min_date"))), (n.maxDate = parseInt(a.attr("data-min_date")) + maxCntDayPvz)),
            (n.beforeShowDay = function (e) {
                let t = e.getMonth() + 1 > 10 ? e.getMonth() + 1 : "0" + (e.getMonth() + 1);
                return 0 === e.getDay() ? [!1, "sunday"] : -1 !== r.indexOf(e.getDate() + "." + t + "." + e.getFullYear()) ? [!1, "not_date"] : [!0, ""];
            }),
            (n.dateFormat = "dd.mm.yy"),
            (n.minDate = new Date()),
            (n.dayNames = ["Воскресение", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"]),
            (n.dayNamesShort = ["ВС", "ПН", "ВТ", "СР", "ЧТ", "ПТ", "СБ"]),
            (n.dayNamesMin = ["В", "П", "В", "С", "Ч", "П", "С"]),
            (n.monthNames = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"]),
            (n.monthNamesShort = ["Янв", "Фев", "Март", "Апр", "Май", "Июн", "Июл", "Авг", "Сент", "Окт", "Ноя", "Дек"]),
            (n.nextText = ""),
            (n.prevText = ""),
            (n.closeText = "Закрыть"),
            (n.currentText = "Сегодня"),
            (n.showOtherMonths = !0),
            (n.selectOtherMonths = !0),
            (n.constrainInput = !0),
            (n.firstDay = 1),
            $("#ui-datepicker").datepicker(n);
    });
}

function initCustomScrollbar() {
    $('.mCustomScrollbar').mCustomScrollbar({theme: "dark-thin"});
    let scrollTo = $('.pickup-list__item-input:checked').parent();
    $('.mCustomScrollbar').mCustomScrollbar("scrollTo", scrollTo || '');
}

function initChangeSelfDelivery() {

    $('.single-select__item, .single-select__body').click(() => {
        $('.single-select__body').toggleClass('single-select__body--change');
        let $prev = $('.single-select__body').parent().find('.single-select__input').first();
        let $next = $('.single-select__body').parent().find('.single-select__input').last();
        if ($prev[0].checked == true) {
            $next[0].checked = true;
        } else {
            $prev[0].checked = true;
        }
        $('.single-select__input:checked').trigger('change');
    });
}

function changeSelectPickup(e, t) {
    let a = $("[name=pickup]:checked"),
        n = $("[name=pickup][value=" + e + "]");
    console.log(a)
    console.log(n)
    $('#manual_address').val($(a).data('address'));
    void 0 !== a[0] && (a[0].checked = !1),
        (n[0].checked = !0),
        n.change();
}

function refineAddress(address) {
    address = address.toUpperCase();

    address = address.replace("ПРОСПЕКТ", "");
    address = address.replace("ПРКТ", "");
    address = address.replace("ПР-КТ", "");
    address = address.replace("ПР-Т", "");
    address = address.replace("ПРС-КТ", "");
    address = address.replace("ПРС-Т", "");
    address = address.replace("П-КТ", "");
    address = address.replace("П-Т", "");

    return address;
}

function getFullAddress(city, address, typeRoute) {
    //return (typeRoute === 'spb' ? 'Ленинградская область, г. ' : 'Московская область, г. ')+city+' '+address;
    return city + ' ' + address;
}

function calcMKADDistance(e, t) {

    let a = $(".errorDistance-js"),
        n = $(".disctanceBlock-js").attr("data-city"),
        r = "spb" === n ? "КАД" : "МКАД";
    return (
        a.text(""),
            // void 0 === window.ymaps.geocode || void 0 === window.ymaps.route || "function" != typeof window.getFullAddress
            //     ? (console.log("Не подключен требуемые скрипты"), !1)
            //             : "" === e || "" === t
            //             ? (a.text("Укажите адрес доставки"), !1)
            /* : void*/ new ymaps.geocode(refineAddress(getFullAddress(e, t, n))).then(
            function (i) {
                let s = 1e4,
                    o = 0,
                    d = 1e5,
                    c = i.geoObjects.get(0).geometry.getCoordinates(),
                    l = c[1],
                    p = c[0];
                // if (inPoly(l, p, n)) return $(".disctanceBlock-js").html('<br><span class="message message_error">Указанный адрес находится внутри ' + r + "</span>"), console.log("Точка внутри " + r), 0;
                let m = "spb" === n ? kad_points : mkad_points;
                for (let e = 0; e < m.length; e++) (o = (d = (m[e][0] - l) * (m[e][0] - l) + (m[e][1] - p) * (m[e][1] - p)) < s ? e : o), (s = d < s ? d : s);
                new ymaps.route(["spb" === n ? "г. Санкт-Петербург КАД " + o + " км" : "г. Москва МКАД " + o + " км", getFullAddress(e, t, n)]).then(
                    function (e) {
                        let t = e.getPaths().get(0).getSegments(),
                            a = t.length,
                            r = a - 1,
                            i = 0;
                        for (; r >= 0 && !(t[r].getStreet().indexOf("spb" === n ? "КАД" : "МКАД") >= 0);) r--;
                        for (let e = r + 1; e < a; e++) i += t[e].getLength();
                        if (inPoly(l, p, n)) {
                            $('[name=distance]').val(parseInt(0));
                            updateOrder();
                        } else {
                            $('[name=distance]').val(parseInt(i / 1e3));
                            updateOrder();
                        }
                    },
                    function (e) {
                        a.text("Не удалось определить расстояние до вашего адреса"), console.log("Возникла ошибка: " + e.message);
                    }
                );
            },
            function (e) {
                a.text("Не удалось определить расстояние до вашего адреса"), console.log("Возникла ошибка: " + e.message);
            }
        )
    );
}

/* массив координат каждого километра МКАД (mkad_points) */
let mkad_points = [
    [37.842663, 55.774543],
    [37.842663, 55.774543],
    [37.84269, 55.765129],
    [37.84216, 55.75589],
    [37.842232, 55.747672],
    [37.841109, 55.739098],
    [37.840112, 55.730517],
    [37.839555, 55.721782],
    [37.836968, 55.712173],
    [37.832449, 55.702566],
    [37.829557, 55.694271],
    [37.831425, 55.685214],
    [37.834695, 55.676732],
    [37.837543, 55.66763],
    [37.839295, 55.658535],
    [37.834713, 55.650881],
    [37.824948, 55.643749],
    [37.813746, 55.636433],
    [37.803083, 55.629521],
    [37.793022, 55.623666],
    [37.781614, 55.617657],
    [37.769945, 55.61114],
    [37.758428, 55.604819],
    [37.747199, 55.599077],
    [37.736949, 55.594763],
    [37.721013, 55.588135],
    [37.709416, 55.58407],
    [37.695708, 55.578971],
    [37.682709, 55.574157],
    [37.668471, 55.57209],
    [37.649948, 55.572767],
    [37.63252, 55.573749],
    [37.619243, 55.574579],
    [37.600828, 55.575648],
    [37.586814, 55.577785],
    [37.571866, 55.581383],
    [37.55761, 55.584782],
    [37.534541, 55.590027],
    [37.527732, 55.59166],
    [37.512227, 55.596173],
    [37.501959, 55.602902],
    [37.493874, 55.609685],
    [37.485682, 55.616259],
    [37.477812, 55.623066],
    [37.466709, 55.63252],
    [37.459074, 55.639568],
    [37.450135, 55.646802],
    [37.441691, 55.65434],
    [37.433292, 55.66177],
    [37.425513, 55.671509],
    [37.418497, 55.680179],
    [37.414338, 55.687995],
    [37.408076, 55.695418],
    [37.397934, 55.70247],
    [37.388978, 55.709784],
    [37.38322, 55.718354],
    [37.379681, 55.725427],
    [37.37483, 55.734978],
    [37.370131, 55.745291],
    [37.369368, 55.754978],
    [37.369062, 55.763022],
    [37.369691, 55.771408],
    [37.370086, 55.782309],
    [37.372979, 55.789537],
    [37.37862, 55.796031],
    [37.387047, 55.806252],
    [37.390523, 55.81471],
    [37.393371, 55.824147],
    [37.395176, 55.832257],
    [37.394476, 55.840831],
    [37.392949, 55.850767],
    [37.397368, 55.858756],
    [37.404564, 55.866238],
    [37.417446, 55.872996],
    [37.429672, 55.876839],
    [37.443129, 55.88101],
    [37.45955, 55.882904],
    [37.474237, 55.88513],
    [37.489634, 55.889361],
    [37.503001, 55.894737],
    [37.519072, 55.901823],
    [37.529367, 55.905654],
    [37.543749, 55.907682],
    [37.559757, 55.909418],
    [37.575423, 55.910881],
    [37.590488, 55.90913],
    [37.607035, 55.904902],
    [37.621911, 55.901152],
    [37.633014, 55.898735],
    [37.652993, 55.896458],
    [37.664905, 55.895661],
    [37.681443, 55.895106],
    [37.697513, 55.894046],
    [37.711276, 55.889997],
    [37.723681, 55.883636],
    [37.736168, 55.877359],
    [37.74437, 55.872743],
    [37.75718, 55.866137],
    [37.773646, 55.8577],
    [37.780284, 55.854234],
    [37.792322, 55.848038],
    [37.807961, 55.840007],
    [37.816127, 55.835816],
    [37.829665, 55.828718],
    [37.836914, 55.821325],
    [37.83942, 55.811538],
    [37.840166, 55.802472],
    [37.841145, 55.793925]
];

let kad_points = [
    [60.038065, 29.964285],
    [60.036515, 29.947469],
    [60.034888, 29.929862],
    [60.033252, 29.912246],
    [60.031629, 29.894657],
    [60.029998, 29.876996],
    [60.028420, 29.860081],
    [60.026757, 29.842339],
    [60.024999, 29.823636],
    [60.023394, 29.806173],
    [60.021852, 29.788889],
    [60.021415, 29.770905],
    [60.021038, 29.752562],
    [60.020080, 29.733859],
    [60.013686, 29.718686],
    [60.005185, 29.707843],
    [59.997230, 29.699750],
    [59.988698, 29.693955],
    [59.980114, 29.688467],
    [59.971528, 29.685062],
    [59.962683, 29.681559],
    [59.953790, 29.678019],
    [59.944963, 29.674543],
    [59.935867, 29.670896],
    [59.926828, 29.667356],
    [59.918359, 29.662694],
    [59.909423, 29.659146],
    [59.900146, 29.660951],
    [59.891773, 29.667877],
    [59.885548, 29.680166],
    [59.881543, 29.695438],
    [59.877660, 29.711464],
    [59.873740, 29.727660],
    [59.870181, 29.744207],
    [59.869156, 29.761706],
    [59.867846, 29.779071],
    [59.863554, 29.793992],
    [59.856695, 29.804601],
    [59.847598, 29.810323],
    [59.838833, 29.814671],
    [59.830278, 29.820510],
    [59.823249, 29.830850],
    [59.816332, 29.843696],
    [59.813378, 29.859614],
    [59.812889, 29.877068],
    [59.812410, 29.894864],
    [59.812582, 29.912525],
    [59.815378, 29.929799],
    [59.818622, 29.946553],
    [59.821775, 29.963351],
    [59.822340, 29.980698],
    [59.819477, 29.997334],
    [59.816364, 30.014277],
    [59.816138, 30.031991],
    [59.816360, 30.049518],
    [59.816323, 30.067277],
    [59.815459, 30.084947],
    [59.814550, 30.102410],
    [59.811844, 30.118787],
    [59.805771, 30.135954],
    [59.800354, 30.151692],
    [59.799933, 30.165553],
    [59.806247, 30.177519],
    [59.810921, 30.191308],
    [59.814459, 30.205905],
    [59.821164, 30.218769],
    [59.826054, 30.233924],
    [59.829785, 30.249806],
    [59.833565, 30.266057],
    [59.833271, 30.281561],
    [59.825384, 30.293015],
    [59.819106, 30.305699],
    [59.812432, 30.319309],
    [59.810084, 30.335424],
    [59.814410, 30.352295],
    [59.814944, 30.369156],
    [59.816532, 30.386134],
    [59.818409, 30.403310],
    [59.821969, 30.419983],
    [59.825986, 30.435587],
    [59.833615, 30.446744],
    [59.840962, 30.455215],
    [59.849026, 30.463884],
    [59.853098, 30.478831],
    [59.853853, 30.496178],
    [59.857120, 30.511548],
    [59.863437, 30.524214],
    [59.871441, 30.532111],
    [59.880279, 30.528427],
    [59.888979, 30.524421],
    [59.897934, 30.525328],
    [59.906819, 30.526478],
    [59.915806, 30.525831],
    [59.924713, 30.530143],
    [59.932234, 30.537330],
    [59.941470, 30.539522],
    [59.950281, 30.544408],
    [59.957723, 30.552071],
    [59.967768, 30.552332],
    [59.974387, 30.541381],
    [59.979164, 30.526020],
    [59.982113, 30.509904],
    [59.984935, 30.494372],
    [59.991160, 30.482308],
    [59.999863, 30.476586],
    [60.009341, 30.475229],
    [60.016190, 30.465024],
    [60.022283, 30.453454],
    [60.030955, 30.444812],
    [60.040649, 30.438470],
    [60.046553, 30.428131],
    [60.050327, 30.413425],
    [60.054482, 30.398333],
    [60.061452, 30.387877],
    [60.070428, 30.383206],
    [60.079289, 30.380196],
    [60.088381, 30.374501],
    [60.093474, 30.360955],
    [60.094806, 30.343725],
    [60.095291, 30.325857],
    [60.096771, 30.308188],
    [60.099126, 30.290679],
    [60.099014, 30.272974],
    [60.096009, 30.256211],
    [60.090517, 30.241568],
    [60.085734, 30.226441],
    [60.083697, 30.209292],
    [60.080779, 30.192700],
    [60.074563, 30.179773],
    [60.066895, 30.169128],
    [60.061313, 30.155528],
    [60.058107, 30.138657],
    [60.055794, 30.121392],
    [60.053548, 30.104495],
    [60.051243, 30.087085],
    [60.048921, 30.069667],
    [60.046607, 30.052284],
    [60.044203, 30.034965],
    [60.041539, 30.017852],
    [60.039220, 30.000532],
    [60.039054, 29.982018],
]

function inPoly(x, y, typeRoute) {
    let c = false; // true/false - внутри или вне полигона
    if (typeRoute === 'spb') {
        z = y;
        y = x;
        x = z;
        let j = kad_points.length - 1;
        for (let i = 0; i < kad_points.length; i++) {
            if (
                ((kad_points[i][1] <= y && y < kad_points[j][1]) || (kad_points[j][1] <= y && y < kad_points[i][1])) &&
                (x > (kad_points[j][0] - kad_points[i][0]) * (y - kad_points[i][1]) / (kad_points[j][1] - kad_points[i][1]) + kad_points[i][0])
            ) {
                c = !c
            }
            j = i;
        }
    } else {
        let j = mkad_points.length - 1;
        for (let i = 0; i < mkad_points.length; i++) {
            if (
                ((mkad_points[i][1] <= y && y < mkad_points[j][1]) || (mkad_points[j][1] <= y && y < mkad_points[i][1])) &&
                (x > (mkad_points[j][0] - mkad_points[i][0]) * (y - mkad_points[i][1]) / (mkad_points[j][1] - mkad_points[i][1]) + mkad_points[i][0])
            ) {
                c = !c
            }
            j = i;
        }
    }
    return c;
}
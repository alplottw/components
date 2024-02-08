$((e) => {
    $('body').on('click', '.btn-global--fastorder', function (e) {
        $('[name=action]').val('validOrder');
        let data;
        data = $('.fastorder').serialize();
        $.ajax({
            method: 'POST',
            data,
            success: function (res) {
                let buffer = $(res.html).find('.fastorder');

                if (!buffer.find('.order-input__text--error').length){
                    buffer.find('[name=action]').val('saveOrder');
                    $.ajax({
                        method: 'POST',
                        data: buffer.serialize(),
                        success: function (resSave) {

                            $('.fastorder').replaceWith($(JSON.parse(resSave).html).find('.fastorder'));
                            return true;
                        }
                    })
                } else {
                    $('.fastorder').replaceWith(buffer);
                }
            }
        })
    })
});

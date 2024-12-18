$(document).ready(function () {
    /* login */
    $('.contents.login .input input').bind('blur', function () {
        if ($(this).val() == '') {
            $(this).removeClass('on');
        } else {
            $(this).addClass('on');
        }
    });

    /* find */
    $('.contents.find .infoSelect input[type=radio]').on('click', function () {
        $(this).closest('.wrap').find('.input dd.info').each(function () {
            $(this).addClass('_off').prev().addClass('_off');
        });

        $(this).closest('.wrap').find('.input .' + $(this).attr('class')).removeClass('_off').prev().removeClass('_off');

        $(this).closest('.wrap').find('.input .' + $(this).attr('class') + ' select._domain').each(function () {
            if ($(this).children('option:selected').hasClass('_custom')) {
                $(this).prev()[0].readOnly = false;
            } else {
                $(this).prev()[0].readOnly = true;
            }
        });
    });
    $('.contents.find select._domain').on('change', function () {
        if ($(this).children('option:selected').hasClass('_custom')) {
            $(this).prev().val('');
            $(this).prev()[0].readOnly = false;
        } else {
            $(this).prev().val($(this).children('option:selected').text());
            $(this).prev()[0].readOnly = true;
        }
    });
});
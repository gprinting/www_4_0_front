$(document).ready(function () {
    //top ad
    $('.topAd .close').on('click', function () {
        $(this).closest('section').removeClass('on');
    });

    //lnb
    if ($('nav.lnb')[0]) {
        //$('section.lnb').css('min-height', Number($('nav.lnb').outerHeight()) + Number($('nav.lnb').css('top').replace('px', '')));
        $('section.lnb').css('min-height', "965px");
    }

    //aside member
    $('aside.member .switch, aside.member .cover').on('click', function () {
        $(this).parent().toggleClass('_folded');

    });

    $('aside.member .favorite ul').on('click', function () {
        $(this).closest('tr').find('input[type=checkbox]').click();

    });

    $('aside.member .favorite ._selectAll').on('click', function () {
        $(this).closest('section').find('input[type=checkbox]').each(function () {
            if (!this.checked) {
                this.checked = true;
            }
        });

    });

    //footer
    //email protection
    var emailProtectionDetail = $('footer .link article.emailProtection');
    $('footer .link li.emailProtection button').add(emailProtectionDetail.find('button.close')).on('click', function () {
        emailProtectionDetail.fadeToggle(300);
    });


    //folding
    $('._folding').addClass('_off');
    $('._folding ._closed').bind('click', function () {
        $(this).closest('._folding').addClass('_on');
        $(this).closest('._folding').removeClass('_off');
    });

    $('._folding ._opened').bind('click', function () {
        $(this).closest('._folding').removeClass('_on');
        $(this).closest('._folding').addClass('_off');
    });

    //주문하기
    $('header.top ._order .wrap').slideUp(0);
    $('header.top ._order').css('overflow', 'visible');

    //마우스오버
    /*
    $('header.top ._order').on('click', function () {
        var $wrap = $('header.top ._order .wrap');

	if ($wrap.css("display") === "none") {
            $('header.top ._order .wrap').stop();
            $('header.top ._order .wrap').slideDown(150);
	} else {
            $('header.top ._order .wrap').stop();
            $('header.top ._order .wrap').slideUp(150);
	}
    });
    */

    //마우스 클릭
    $('header.top ._order button._closed').bind('click', function () {
        $('header.top ._order .wrap').stop();
        $('header.top ._order .wrap').slideDown(300);
        $(this).closest('._order').addClass('_on');
    });

    $('header.top ._order button._opened').bind('click', function () {
        $('header.top ._order .wrap').stop();
        $('header.top ._order .wrap').slideUp(300);
        $(this).closest('._order').removeClass('_on');
    });

    //tip
    var tipBalloon = '<div class="_tipBalloon"></div>';
    $('._tip button').on('mouseover', function () {
        $('body').append(tipBalloon);
        $('body > ._tipBalloon').html($(this).next().html())
            .css('top', ($(this).offset().top - $('body > ._tipBalloon').height() - 12) + 'px')
            .css('left', $(this).offset().left - $('body > ._tipBalloon').width() / 2 + 'px')
    })
    $('._tip button').on('mouseout', function () {
        $('body > ._tipBalloon').remove();
    })

    //table._details
    if ($('table._details')[0]) {
        orderTable();
    }

    //general checkbox
    generalCheckbox();

    //input[readonly] prompt remove
    $('input[type=text]').on('focus click', function () {
        if ($(this).prop("readonly")) {
            return false;
        }

        readOnlyPromptBlur($(this));
    });

    //switch
    $('._switch button, ._toggle button').on('click', function () {
        if ($(this).closest('li').hasClass('_on')) {
            if ($(this).closest('._toggle').hasClass('_toggle')) {
                $(this).closest('li').removeClass('_on');
            }
            return false;
        } else {
            $(this).closest('ul').children('li._on').removeClass('_on');
            $(this).closest('li').addClass('_on');
        }
    });

    //table sorting
    tableSorting();

    //reply to email
    replyToEmail();
});

var layerPopNum = 0;

function layerPopup(code, html, data) {
    var layerPopupHTML = '<div class="popupMask ' + code + ' _num' + layerPopNum + '" style="z-index:3000;" onclick="closePopup(this);"><div class="loading"><img src="/design_template/images/common/icon_loading.gif" style="z-index:10;" alt="불러오는 중입니다."></div><div class="layerPopupWrap"><div class="layerPopup"></div></div></div>';
    $('body').append(layerPopupHTML);

    var popupMask = $('.popupMask._num' + layerPopNum++);
    var contents = popupMask.find('.layerPopup');
    var contentsWrap = popupMask.find('.layerPopupWrap');

    popupMask.fadeIn(300, function () {
        $.ajax({
            url: html,
            dataType: "html"
        }).done(function (data) {
            insertData(data);
        }).fail(function () {
            if (checkBlank(data)) {
                data = '<div><h2>오류</h2><button class="close" title="닫기"><img src="/design_template/images/common/btn_circle_x_white.png" alt="X"></button></div><div class="error"><em>오류가 있습니다.</em><br>잠시 후 다시 실행하시거나 관리자에게 문의하세요.</div>';
            }
            insertData(data);
        });
    });

    var insertData = function (data) {
        contents.html(data);

        if (popupMask.hasClass('l_full')) {
            contentsWrap.css('height', $(window).height() - 40 + 'px');
        }

        //popup position setting
        contentsWrap.css({
            //'top' : popupMask.height() > contentsWrap.height() ? (popupMask.height() - contentsWrap.height()) / 2 + 'px' : 0,
            //'left' : popupMask.width() > contentsWrap.width() ? (popupMask.width() - contentsWrap.width()) / 2 + 'px' : 0
            'margin-left': -0.5 * contentsWrap.outerWidth() + 'px',
            'margin-top': -0.5 * contentsWrap.outerHeight() + 'px'
        });

        contentsWrap.parent().css({
            'padding-left': 0.5 * contentsWrap.width() + 'px',
            'padding-top': 0.5 * contentsWrap.height() + 'px'
        });

        if (popupMask.outerHeight() > contentsWrap.height() && popupMask.outerWidth() > contentsWrap.width()) {
            //drag
            contentsWrap.draggable({
                addClasses: false,
                cursor: false,
                containment: popupMask,
                handle: 'header'
            });
        } else {
            $('body').css('overflow', 'hidden');
        }

        contentsWrap.off("click");
        contentsWrap.on("click", function (event) {
            event.stopPropagation();
        });

        popupMask.find('button.close').off('click');
        popupMask.addClass('_on')
            .find('button.close').on('click', function () {
                closePopup(popupMask);
            });

        //readonly prompt blur
        popupMask.find('input[type=text]').on('focus click', function () {
            readOnlyPromptBlur($(this));
        });

        //general checkbox
        generalCheckbox(popupMask);

        //table sorting
        tableSorting(popupMask);
    };

    return popupMask;
}

function closePopup(popupMask) {
    $(popupMask).fadeOut(300, function () {
        $(this).remove();
        $('body').css('overflow', 'auto');
    });
};

function confirmClosePopup(event, message) {
    if (confirm(message)) {
        closePopup($(event.target).closest('.popupMask'));
    }
}

function tab() {
    $('nav._tab button').on('click', function () {
        if ($(this).closest('li').hasClass('_on')) return false;

        var prevContents = $(this).closest('nav').find('._on');

        if (prevContents[0]) {
            prevContents.removeClass('_on');
            $('._tabContents.' + prevContents.children().attr('class')).removeClass('_on');
        }

        $('._tabContents.' + $(this).attr('class')).addClass('_on');
        $(this).closest('li').addClass('_on');
    });

    $('nav._tab li:first-child button').click();
}

function orderTable(target) {

    if (checkBlank(target) === true) {
        target = $('body');
    }

    //order details 여닫기
    target.find('button._showOrderDetails').on('click', function () {

        var noAdd = $(this).closest('tr').attr('no-add');
        if (!checkBlank(noAdd)) {
            return false;
        }

        //$(this).closest('table._details').find('tbody._on .wrap').slideUp(300);
        $(this).closest('table._details').find('tr._on').removeClass('_on');
        $(this).closest('tr').addClass('_on');
        //$(this).closest('tbody').find('.wrap').stop().slideDown(300);
    });
    target.find('button._hideOrderDetails').on('click', function () {

        $(this).closest('tr').removeClass('_on');
        //$(this).closest('tbody').find('.wrap').stop().slideUp(300);
    });

    //edit
    /*
    var prevValue = '';
    
    target.find('button._modify').on('click', function () {

        $(this).parent().find('button._modify').toggleClass('_on');
        $(this).parent().find('._input').toggleClass('_on');
        $(this).parent().find('._output').toggleClass('_off');
    });
    
    target.find('button._save').on('click', function () {
        var input =  $(this).parent().find('._input'),
            output = $(this).parent().find('._output');
        
        for(var iSave = 0; iSave < output.length; iSave++) {
            if ($(input[iSave]).is('input')) {
                $(output[iSave]).text($(input[iSave]).val());
            } else {
                $(output[iSave]).text($(input[iSave]).children('option:selected').text());
            }
        }
            
    });
    
    target.find('button._cancel').on('click', function () {
        var input =  $(this).parent().find('._input'),
            output = $(this).parent().find('._output');
        
        for(var iCancel = 0; iCancel < output.length; iCancel++) {
            if ($(input[iCancel]).is('input')) {
                $(input[iCancel]).val($(output[iCancel]).text());
            } else {
                $(input[iCancel]).children('option').each(function () {
                    if ($(this).text() == $(output[iCancel]).text()) {
                        this.selected = true;
                    }
                });
            }
        }
    });
    
    // to option
    target.find('select._toOption').on('change', function () {
        selectByOption(this);
    });
    
    target.find('select._toOption').each(function () {
        selectByOption(this);
    });
    
    function selectByOption (that) {
        var selectedValue = $(that).children('option:selected').text().replace(/\s/g, '').replace(/\(/g, '').replace(/\)/g, '');
        $(that).parent().find('select._byOption').each(function () {
            if($(this).hasClass('_' + selectedValue)) {
                $(this).addClass('_on');
            } else {
                $(this).removeClass('_on');
            }
        });
    }
    */
}

function readOnlyPromptBlur(that) {
    var focusableComponent = $('input, select, textarea, button');
    if (that[0].readOnly == true) {
        if ($(focusableComponent[focusableComponent.index(that) + 1])[0]) {
            $(focusableComponent[focusableComponent.index(that) + 1]).focus();
        } else {
            that.blur();
        }
    }
}

function generalCheckbox(target) {
    if (target == null || target == undefined) {
        target = $('body')
    }
    target.find('table input[type=checkbox]._general').on('click', function () {
        var general = $(this),
            individual;

        if ($(this).closest('table').hasClass('thead')) {
            individual = $(this).closest('table').next().find('input[type=checkbox]._individual').not(':disabled');
        } else {
            individual = $(this).closest('table').find('input[type=checkbox]._individual').not(':disabled');
        }

        individual.each(function () {
            this.checked = general[0].checked;
        });
    });

    target.find('table input[type=checkbox]._individual').on('click', function () {
        var general,
            unCheckedIndividual = $(this).closest('table').find('input[type=checkbox]._individual').not(':checked').not(':disabled');

        if ($(this).closest('table').parent().parent().hasClass('tableScroll')) {
            general = $(this).closest('.tableScroll').prev().find('input[type=checkbox]._general');
        } else {
            general = $(this).closest('table').find('input[type=checkbox]._general');
        }
        if (unCheckedIndividual[0]) {
            general[0].checked = false;
        } else {
            general[0].checked = true;
        }
    });
}

//table sorting
function tableSorting(target) {
    if (target == null || target == undefined) {
        target = $('body');
    }

    target.find('th._sorting').on('click', function () {
        if ($(this).hasClass('_down')) {
            $(this).removeClass('_down').addClass('_up')
        } else if ($(this).hasClass('_up')) {
            $(this).removeClass('_up');
        } else {
            $(this).closest('tr').find('._up, ._down').removeClass('_up').removeClass('_down');
            $(this).addClass('_down');
        }
    });
}

//reply to email
function replyToEmail(target) {
    if (target == null || target == undefined) {
        target = $('body');
    }

    target.find('._replyToEmail').each(function () {
        var checkbox = $(this).find('input[type=checkbox]'),
            id = $(this).find('._id'),
            domain = $(this).find('._domain'),
            preset = $(this).find('select');

        checkbox.on('click', function () {
            onCheckbox()
        });

        preset.on('change', function () {
            onDomain();
        });

        if (checkbox[0]) onCheckbox();
        onDomain();

        function onCheckbox() {
            id.attr('disabled', !checkbox[0].checked);
            domain.attr('disabled', !checkbox[0].checked);
            preset.attr('disabled', !checkbox[0].checked);
        }

        function onDomain() {
            if (preset.find('option:selected').hasClass('_custom')) {
                domain.attr('readonly', false);
                domain.val('');
            } else {
                domain.attr('readonly', true);
                domain.val(preset.find('option:selected').text());
            }
        }
    });
}
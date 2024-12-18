// 기존
    // 상품상세페이지 팁 노출
    $(function(){
        $('.icon_delivery_price').mouseenter(function(){
            $(".tip_delivery_price").show();
        });
        $('.icon_delivery_price').mouseleave(function(){
            $(".tip_delivery_price").hide();
        });

        $('.icon_delivery_scheduled').mouseenter(function(){
            $(".tip_delivery_scheduled").show();
        });
        $('.icon_delivery_scheduled').mouseleave(function(){
            $(".tip_delivery_scheduled").hide();
        });

        $('.icon_favorite_product').mouseenter(function(){
            $(".tip_favorite_product").show();
        });
        $('.icon_favorite_product').mouseleave(function(){
            $(".tip_favorite_product").hide();
        });

        $('.icon_cutting_label').mouseenter(function(){
            $(".tip_cutting_label").show();
        });
        $('.icon_cutting_label').mouseleave(function(){
            $(".tip_cutting_label").hide();
        });

        $('.icon_title_thomson').mouseenter(function(){
            $(".tip_thomson").show();
        });
        $('.icon_title_thomson').mouseleave(function(){
            $(".tip_thomson").hide();
        });
        
        $('.icon_title_thomson_type').mouseenter(function(){
            $(".tip_thomson_type").show();
        });
        $('.icon_title_thomson_type').mouseleave(function(){
            $(".tip_thomson_type").hide();
        });

        $('.icon_check_depo').mouseenter(function(){
            $(".tip_check_depo").show();
        });
        $('.icon_check_depo').mouseleave(function(){
            $(".tip_check_depo").hide();
        });

    });

    // 선입금 정보 박스
    $(function(){
        $(".myCash").mouseenter(function(){
			$(".tip_myCash").show();
		});
		$(".myCash_wrapper").mouseleave(function(){
			$(".tip_myCash").hide();
		});
    });


    // IE 버전 체크
    var ieVersionChk = function() {

        var word;
        var version = -1;

        var agent = navigator.userAgent.toLowerCase();
        var name = navigator.appName;

        // IE 구버전(10또는 그 이하)
        if (name == "Microsoft Internet Explorer") {
            word = "msie ";
        } else {
            // IE 11
            if (agent.search("trident") > -1) {
                word = "trident/.*rv:";
            // IE 12
            } else if (agent.search("edge/") > -1) {
                word = "edge/";
            }
        }

        var reg = new RegExp(word + "([0-9]{1,})(\\.{0,}[0-9]{0,1})");
        if (reg.exec(agent) != null) {
            version = RegExp.$1;
        }

        return parseInt(version);
    };

// 22 추가
$(document).ready(function () {
    /*var headerHeight = $('header.common').height() - $('header.common .major').height();
    $(window).on('scroll', function () {
        if ($(window).scrollTop() > headerHeight) {
            $('body').addClass('headerFix');
        } else {
            $('body').removeClass('headerFix');
        }
    });*/
    
    // megamenu
    $('nav.megamenu').slideUp(0);
    $('header.common button.megamenu').on('click', function (event) {
        $('body').toggleClass('megamenuOn');
        $('nav.megamenu').slideToggle(200);
        event.stopPropagation();
    });
    $( document ).ready(function() {
        var url =  window.location.pathname;
        var host = window.location.hostname;

        //도메인에 biz가 들어있으면 dprinting.co.kr로 이동 
        if(host.indexOf("biz") !== -1){
            location.href="http://dprinting.co.kr";
        }

        if(url == '/main22/main.html') {
           // $('body').toggleClass('megamenuOn');
           // $('nav.megamenu').slideToggle(200);
           // event.stopPropagation();

        }

       
    }); // 2024-11-24 PD 추가
    $('header.common button.close').on('click', function () {
        $('body').removeClass('megamenuOn');
        $('nav.megamenu').slideUp(200);
        $('body,html').animate({'scrollTop' : 0},200);
    });
    
    $('nav.megamenu').on('click', function () {
        event.stopPropagation();
    });
    
    // lnb
    if (('nav.lnb').length) {
        var pageTitle = $('header.good h3 ol').length ? $('header.good h3 ol li:last-child').text().replace(/\s/g, '') : $('header.good h3').text().replace(/\s/g, '');
        $('nav.lnb a span').each(function () {
            if ($(this).text().replace(/\s/g,'') == pageTitle) {
                $(this).closest('li').addClass('on');
            }
        });
    }
    
    // cs center product introduce thumbnail
    if ($('.csCenter.introProduct').length) {
        $('.thumbs nav button').on('click', function () {
            $(this).addClass('on').siblings().removeClass('on');
            $(this).closest('.thumbs').find('figure img').attr('src', $(this).children('img').attr('src'));
        });
        
    }
    
    // mac check
    if(navigator.platform.match('Mac') !== null) {
        $('body').addClass('macOs');
    }
});
$(window).on('load', function () {
    $('body').addClass('on');
    
    // aside
    $('aside.quick .businessHour button i.hour').css('transform', 'rotate(' + (new Date().getHours() % 12 * 30 + new Date().getMinutes() * .5) + 'deg)')
    $('aside.quick .businessHour button i.minute').css('transform', 'rotate(' + (new Date().getMinutes() * 6) + 'deg)')
    $('aside.quick button.top').on('click', function () {
        $('body,html').animate({'scrollTop' : 0},200);
    });
    
    //general checkbox
    generalCheckbox();
})

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
    var layerPopupHTML = '<div class="popupMask on _num' + layerPopNum + '"><section class="popup ' + code + '"></section></div>';
    
    $('body').append(layerPopupHTML);

    var popupMask = $('.popupMask._num' + layerPopNum++);
    var contents = popupMask.find('section.popup');

    $.ajax({
        url: html,
        dataType: "html"
    }).done(function (data) {
        contents.html(data);
        contents.find('button.close').on('click', function () {
            $(this).closest('.popupMask').remove();
        });
    }).fail(function () {
        if (checkBlank(data)) {
            data = '<header class="popup error"><h2>오류</h2><button class="close" title="닫기">닫기</button></header><article class="error"><p class="boxM error">오류가 있습니다.<p class="message">잠시 후 다시 실행하시거나 관리자에게 문의하세요.</p><hr class="end"><footer class="action"><button type="button" class="close">닫기</button></article>';
        }
        insertData(data);
        popupMask.find('button.close').on('click', function () {
            closePopup(popupMask);
        });
    });
    
    var insertData = function (data) {
        contents.html(data);
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
    $('body').on('change', 'table input[type=checkbox]._general', function () {
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
    $('body').on('change', 'table input[type=checkbox]._individual', function () {
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
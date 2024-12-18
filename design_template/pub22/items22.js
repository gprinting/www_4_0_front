var Gitems = new Object();

$(document).ready(function () {
    // review
    $('.review .owl-carousel a').each(function (i) {
        if (i % 3 == 0) {
            $('.review .owl-carousel').append('<div class="item"></div>')
        }
        $('.review .owl-carousel .item:last-child').append($(this));
    });
    
    var reviewSwiper = $('.review .owl-carousel').owlCarousel({
        loop: true,
        margin: 65,
        nav: true,
        items: 1,
        autoplay: true,
        dots: false,
        autoplayHoverPause: true,
        autoplayTimeout: 5000,
        autoplaySpeed: 300,
        onInitialized: counter,
        onTranslate: counter,
    });
    
    var counter = function (event) {
        var element = event.target,
            items = event.item.count,
            cloneNum = Math.max(2, Math.ceil(items / 2)),
            item = event.item.index + 1 - cloneNum;
        
        item = item % items == 0 ? items : item % items;
        $(event.target).next('.counter').html(item + " / " + items)
    }
    
    //view
    var photoSwiper = $('.items.view.wrap .photo .owl-carousel').owlCarousel({
        loop: false,
        nav: false,
        items: 1,
        autoplay: true,
        dots: false,
        autoplayHoverPause: true,
        autoplayTimeout: 5000,
        autoplaySpeed: 300,
    });
    
    photoSwiper.on('changed.owl.carousel', function (event) {
        var no = event.item.index - event.relatedTarget._clones.length / 2;
        if (no >= event.item.count) { no = 0; }
        $('.photo nav button:eq(' + no + ')').addClass('on')
            .siblings('.on').removeClass('on');
    });
    
    $('.items.view.wrap .photo .owl-item:not(.cloned) .item').each(function (i) {
        var $button = $('<button type="button"><img src="' + $(this).find('img').attr('src') + '"></button>');
        $button.on('click', function () {
            photoSwiper.trigger('to.owl.carousel', i);
        });
        $('.items.view.wrap .photo nav').append($button);
    });
    $('.items.view.wrap .photo nav button:eq(0)').addClass('on');
    
    $('.items.view.wrap .options dl').each(function () {
        $(this).addClass('ea' + $(this).find('dd label').length);
    });
    
    //가격
    $('.summary .ordinaryPrice .DCdetail').slideUp(0);
    $('.summary .ordinaryPrice button.foldingBtn').on('click', function () {
        $('.summary .ordinaryPrice .DCdetail').slideToggle(200);
        $(this).toggleClass('off');
    });
    
    //상세 설정
    $('._hasDetail input[type=checkbox]').on('change', function () {
        var $target = $(this).closest('._hasDetail').siblings('._detailSetting').find('li[name="' + $(this).val() + '"]');
        
        if ($(this).closest('._hasDetail').find('input:checked').length) {
            $(this).closest('._hasDetail').siblings('._detailSetting').addClass('on');
        } else {
            $(this).closest('._hasDetail').siblings('._detailSetting').removeClass('on');
        }
        
        if ($(this).prop('checked')) {
            $target.slideDown(200, Gitems.minHeight);
        } else {
            $target.slideUp(200, Gitems.minHeight);
        }
    });
    $('._detailSetting button.cancel').on('click', function () {
        var name = $(this).closest('li').attr('name');
        $(this).closest('._detailSetting').siblings('._hasDetail').find('input').each(function () {
            if ($(this).val() == name) {
                $(this).click();
            }
        });
    });
    $('._detailSetting button.plus').on('click', function () {
        $(this).siblings('input').val(Number($(this).siblings('input').val()) + 1);
    });
    $('._detailSetting button.minus').on('click', function () {
        $(this).siblings('input').val(Math.max(Number($(this).siblings('input').val()) - 1, 1));
    });
    
    Gitems.$side = $('aside.purchase');
});


$(window).on('load', function () {
    Gitems.minHeight = function () {
        $('main.items.view').css('min-height', $('main.items.view .purchase').height() + 180);
    }

    Gitems.minHeight();
    setTimeout(function () { $('main.items.view').addClass('on'); }, 100);
    
    
    $(window).on('scroll', function () {
        Gitems.mainTop = $('main.items').offset().top - 20;
        Gitems.sideTop = Math.min($('footer.common').offset().top - $(window).scrollTop() - $(window).height(), 0);

        if ($(window).scrollTop() > Gitems.mainTop && Gitems.$side.is('.init')) {
            Gitems.$side.removeClass('init');
        } else if ($(window).scrollTop() <  Gitems.mainTop && !Gitems.$side.is('.init')) {
            Gitems.$side.addClass('init');
        }

        Gitems.FnScrollY();
        Gitems.FnScrollX();


    });

    $(window).on('resize', function () {
        //Gitems.FnScrollY();
        Gitems.FnScrollX();
    });
});

Gitems.FnScrollX = function () {
    if ($(window).width() > 1330) {
        Gitems.$side.css({
            'right': ($(window).width() - 1330) / 2 + 'px'
        });
    } else {
        Gitems.$side.css({
            'right': $(window).width() - 1330 - 20 + $(window).scrollLeft() + 'px'
        });
    }
}

Gitems.FnScrollY = function () {
    if (Gitems.sideTop >= 0) {
        Gitems.sideOverflow = Gitems.$side.outerHeight() + 40 - $(window).height();
        Gitems.scrolledPosition = Gitems.scrollPosition;
        Gitems.scrollPosition = $(window).scrollTop();

        if(Gitems.scrolledPosition > Gitems.scrollPosition) { //up
            Gitems.sideMarginTop = Math.max(Gitems.sideMarginTop - Gitems.scrolledPosition + Gitems.scrollPosition, 0);
        } else if(!Gitems.$side.is('.init')) { // down
            Gitems.sideMarginTop = Math.min(Gitems.sideMarginTop + Gitems.scrollPosition - Gitems.scrolledPosition, Gitems.sideOverflow);
        }
        if ( Gitems.sideOverflow > 0) {
            Gitems.$side.css('margin-top', Gitems.sideMarginTop * -1 + 'px');
        }
    }
}

Gitems.sideMarginTop = 0;
Gitems.scrolledPosition = 0;
Gitems.scrollPosition = 0;
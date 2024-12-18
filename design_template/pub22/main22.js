$(document).ready(function () {
    // mainbanner
    if ($('.mainbanner .container .item').length > 1) {
        var $mainbannerNav = $('.mainbanner nav.paging'),
            slideSpeed = 300,
            $button = new Array();

        $('.mainbanner .container .item').each(function (i) {
            var className = $(this).attr('class').replace('item','').replace(/\s/g,'');

            $button[i] = $('<button type="button">' + Number(i + 1) + '</button>');

            $button[i].on('click', function () {
                mainbannerSwiper.trigger('to.owl.carousel', [i, slideSpeed]);
                $mainbannerNav.find('button.on').removeClass('on');
                $(this).addClass('on');
            });

            if (!$mainbannerNav.children('.' + className).length) {
                $mainbannerNav.append('<dl class="' + className + '"><dt>' + className + '</dt><dd></dd></dl>');
            }
            $mainbannerNav.find('.' + className + ' dd').append($button[i]);
        });

        $mainbannerNav.addClass('ea' + $mainbannerNav.find('dl').length);
        $mainbannerNav.find('dl:first-child dd button:first-child').addClass('on');

        var mainBannerTranslate = function (event) {
            $mainbannerNav.find('button.on').removeClass('on');

            var buttonI = event.item.index - Math.ceil($button.length/2);
            if (buttonI >= $button.length) {
                buttonI = buttonI - $button.length;
            } else if (buttonI < 0) {
                buttonI = buttonI + $button.length;
            }
            $mainbannerNav.find('button.on').removeClass('on');
            $button[buttonI].addClass('on');
        }

        var mainbannerSwiper = $('.mainbanner .owl-carousel').owlCarousel({
            loop: true,
            margin:0,
            nav: true,
            items: 1,
            autoplay: true,
            dots: false,
            autoplayHoverPause: false,
            autoplayTimeout: 3500,
            autoplaySpeed: slideSpeed,
            onTranslate: mainBannerTranslate,
        });

        var counter = function (event) {
            var element = event.target,
                items = event.item.count,
                cloneNum = Math.max(2, Math.ceil(items / 2)),
                item = event.item.index + 1 - cloneNum;

            item = item % items == 0 ? items : item % items;
            $(event.target).next('.counter').html(item + " / " + items)
        }
    }
    
    // tip
    $('.tip .owl-carousel a').each(function (i) {
        if (i % 4 == 0) {
            $('.tip .owl-carousel').append('<div class="item"></div>')
        }
        $('.tip .owl-carousel .item:last-child').append($(this));
    });
    var tipSwiper = $('.tip .owl-carousel').owlCarousel({
        loop: true,
        margin: 30,
        nav: true,
        items: 1,
        autoplay: true,
        dots: false,
        autoplayHoverPause: true,
        autoplayTimeout: 5000,
        autoplaySpeed: slideSpeed,
        onInitialized: counter,
        onTranslate: counter,
    });
    
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
        autoplaySpeed: slideSpeed,
        onInitialized: counter,
        onTranslate: counter,
    });
    
    // all goods
    $('.allgoods nav button:eq(0)').addClass('on');
    $('.allgoods .tabContents[name="' + $('.allgoods nav button:eq(0)').attr('name') + '"]').addClass('on');
    
    $('.allgoods nav button').on('mouseover focusin', function () {
        $('.allgoods nav button.on, .allgoods .tabContents.on').removeClass('on');
        $(this).addClass('on');
        $('.allgoods .tabContents[name="' + $(this).attr('name') + '"]').addClass('on');
    });

   // openPopup('popup.html');
    //openPopup2('popup2.html');
});

var openPopup = function(url) 
{
    var cookieCheck = getCookie("popupYN");
	if (cookieCheck != "N") window.open(url, '', 'width=610,height=680,left=0,top=0')
}
var openPopup2 = function(url) 
{
    var cookieCheck = getCookie("popupYN");
	if (cookieCheck != "N") window.open(url, '', 'width=610,height=480,left=620,top=0')
}
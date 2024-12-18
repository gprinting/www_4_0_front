$(document).ready(function () {
    $('.main .byStatus._toggle span.num').on('click', function () {
        $(this).prev('button').click();
    });

    var scrollTop = $(window).scrollTop(),
        scrollLeft = $(window).scrollLeft(),
        //headerMargin = $('header.top').outerHeight() + $('header.title').outerHeight() - 10,
        headerMargin = $(".div_header_wrapper").outerHeight() + 190 - 20,
        sbanner = $('.sbanner'),
        sbannerHeight = sbanner.outerHeight(),
        sbannerHiddenHeight,
        baseMargin = 70,
        //baseMargin = 30,
        bodyWidth = $('body').width(),
        bodyHeight = $(document).height(),
        footerHeight = $('footer').outerHeight(),
        bottomDistance,
        windowWidth = $(window).width(),
        windowHeight = $(window).height(),
        topForHidden = 0;

    $(window).resize(function () {
        windowWidth = $(window).width();
        windowHeight = $(window).height();
        bodyWidth = $('body').width(),
            windowScroll();
    });

    $(window).scroll(function () {
        windowScroll();
    });

    function windowScroll() {
        //상하
        scrollTop = $(window).scrollTop();
        bodyHeight = $(document).height();

        if (scrollTop > headerMargin - baseMargin) { //스크롤이 빠른견적서 상단보다 더 내려갈 경우 top 조정
            sbanner.css('top', baseMargin);
        } else {
            sbanner.css('top', headerMargin - scrollTop);
        }

        sbannerHiddenHeight = sbannerHeight - windowHeight + baseMargin; //빠른견적서보다 창이 작은 경우

        if (sbannerHiddenHeight > 0 && scrollTop > headerMargin - baseMargin) {
            if (scrollTop - (headerMargin - baseMargin) > sbannerHiddenHeight) {
                topForHidden = sbannerHiddenHeight * -1;
                sbanner.css('top', topForHidden);
            } else {
                topForHidden = sbanner.css('top').replace('px', '') - (scrollTop - (headerMargin - baseMargin));
                sbanner.css('top', topForHidden);
            }
            sbannerHiddenHeight = sbannerHiddenHeight + baseMargin;
        } else if (sbannerHiddenHeight <= 0) {
            sbannerHiddenHeight = 0;
        }

        bottomDistance = bodyHeight - (sbannerHeight + baseMargin * 2 + scrollTop + footerHeight - sbannerHiddenHeight); //하단과의 거리
        if (bottomDistance > 0) {
            sbanner.css('margin-top', 0);
        } else {
            sbanner.css('margin-top', bottomDistance);
        }

        //좌우
        if (windowWidth > bodyWidth) {
            sbanner.css('right', 0);
        } else {
            scrollLeft = $(window).scrollLeft();
            sbanner.css('right', windowWidth - bodyWidth + scrollLeft);
        }
    }
});
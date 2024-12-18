$(document).ready(function () {
    var text1 = $('header.title .location li:eq(2) span').text().replace(/\s/g, ''),
        text2 = $('header.title .location li:eq(3) span').text().replace(/\s/g, ''),
        thisText = '';

    $('nav.lnb > ul > li > a').each(function () {
        thisText = $(this).html().replace(/\s/g, '').split('<span')[0];
        if (thisText == text1) {
            $(this).closest('li').addClass('on');
            $(this).closest('li').children('ul').find('a').each(function () {
                thisText = $(this).html().replace(/\s/g, '').split('<span')[0];
                if (thisText == text2) {
                    $(this).closest('li').addClass('on');
                }
            });
        }
    });
    cscenterLnbEffect();
});

/**
 * @brief 고객센터 우상단 검색기능
 * @param txt : 인기검색어 파라미터
 */
var topSearchTxt = function (txt) {
    var searchWrd = $("#search_word").val();

    if (!checkBlank(txt)) {
        searchWrd = txt;
    }

    location.href = "/cscenter/faq.html?searchWrd=" + searchWrd;
};

/**
 * @brief 고객센터 lnb 클래스 적용
 */
var cscenterLnbEffect = function () {

    var url = location.href;
    var pageName = url.split("/");
    var pageNameLen = pageName.length;
    pageName = pageName[pageNameLen - 1];

    var className = pageName.split(".");
    className = className[0];
    if (pageName.indexOf('product_') > -1) {
        var classNameProd = pageName.split("_");
        className = classNameProd[0];
    }

    if ((pageName.indexOf('guide') > -1 || pageName.indexOf('faq') > -1) &&
        pageName.indexOf('type') > -1) {
        if (pageName.indexOf('=') > -1) {
            var classNameSub = pageName.split("=");
            className += "_";
            className += classNameSub[1];
        }
    } else if (pageName.indexOf('work_guide') > -1) {
        if (pageName.indexOf('=') > -1) {
            var classNameSub = pageName.split("=");
            className = classNameSub[1];
        }
    }

    $("#" + className).parents("li").addClass("on");
    $("#" + className).addClass("on");
};


/**
 * @brief 조건 검색
 */
var searchKeyTop = function (event) {
    if (event.keyCode != 13) {
        return false;
    }
    topSearchTxt();
}
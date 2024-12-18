$(window).on('load', function () {
    //lnb on
    var $title = $('h3 li').length > 0 ? $title = $('h3 li') : [$('h3')],
        $lnbA = $('nav.lnb>ul>li>a'),
        $lnbOn,
        $targetLi;
    $title.each(function () {
        var title = $(this).text().replace(/\s/g,'');
        $lnbA.each(function () {
            if ($(this).text().replace(/\s/g,'') == title) {
                targetLi = $(this).closest('li');
            }
        });
        $lnbA = $lnbA.next('ul').find('a');
    });
    //console.log(targetLi);
    targetLi.addClass('on');
    
    // side
    if ($('aside.csSide').length) {
        var $csAside = $('aside.csSide');
        $(window).on('scroll', function () {
            $csAside.css('margin-top', Math.max($('header.common').outerHeight() - $(window).scrollTop() + 28,0));
        });
        $csAside.find('button.folding').on('click', function () {
            $csAside.toggleClass('on');
        });
    }
    $('aside.csSide').css('margin-top', Math.max($('header.common').outerHeight() - $(window).scrollTop() + 28,0));
})

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
 * @brief 조건 검색
 */
var searchKeyTop = function (event) {
    if (event.keyCode != 13) {
        return false;
    }
    topSearchTxt();
}
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
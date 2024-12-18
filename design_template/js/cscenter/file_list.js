$(document).ready(function () {
    cndSearch(10, 1);
});

//보여줄 페이지 수
var listSize = "";

/**
 * @brief 선택조건으로 검색 클릭시
 */
var cndSearch = function (listSize, page) {

    var url = "/ajax/service/file_list/load_file_list.php";
    var blank = "<tr><td colspan=\"5\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "search_cnd": $("#search_cnd").val(),
        "search_txt": $("#search_txt").val()
    };
    var callback = function (result) {
        var rs = result.split("♪");
        if (rs[0].trim() == "") {
            $("#list").html(blank);
            $("#resultCnt").html("0");
            return false;
        }
        $("#list").html(rs[0]);
        $("#paging").html(rs[1]);
        $("#resultCnt").html(rs[2]);
    };

    data.list_num = listSize;
    data.page = page;

    showMask();
    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 보여줄 페이지 수 설정
 */
var changeListNum = function (val) {
    listSize = val;
    cndSearch(val, 1);
}

/**
 * @brief 페이지 이동
 */
var movePage = function (val) {
    cndSearch(listSize, val);
}

/**
 * @brief 조건 검색
 */
var searchKey = function (event) {
    if (event.keyCode != 13) {
        return false;
    }
    cndSearch(listSize, 1);
}

/**
 * @brief 조건 검색
 */
var searchTxt = function () {
    cndSearch(listSize, 1);
}
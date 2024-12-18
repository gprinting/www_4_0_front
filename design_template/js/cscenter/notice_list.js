$(document).ready(function () {
    //일자별 검색 datepicker 기본 셋팅
    $("#from").datepicker({
        dateFormat: 'yy-mm-dd',
        prevText: '이전 달',
        nextText: '다음 달',
        monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        dayNames: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
        showMonthAfterYear: true,
        yearSuffix: '년'
    });

    $("#to").datepicker({
        dateFormat: 'yy-mm-dd',
        prevText: '이전 달',
        nextText: '다음 달',
        monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        dayNames: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
        showMonthAfterYear: true,
        yearSuffix: '년'
    });

    cndSearch(10, 1);
});

//보여줄 페이지 수
var listSize = "";

/**
 * @brief 선택조건으로 검색 클릭시
 */
var cndSearch = function (listSize, page) {

    var url = "/ajax/cscenter/notice_list/load_notice_list.php";
    var blank = "<tr><td colspan=\"5\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "from": $("#from").val(),
        "to": $("#to").val(),
        "search_txt": $("#search_txt").val()
    };
    var callback = function (result) {
        var rs = result.split("♪");
        if ($.trim(rs[0]) == "") {
            $("#list").html(blank);
            $("#paging").html(rs[1]);
            $("#resultNum").html(rs[2]);
            return false;
        }
        $("#list").html(rs[0]);
        $("#paging").html(rs[1]);
        $("#resultNum").html(rs[2]);
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
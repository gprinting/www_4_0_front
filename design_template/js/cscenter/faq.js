$(document).ready(function () {
    cndSearch(1);
});

//보여줄 페이지 수
var listSize = "10";

/**
 * @brief 선택조건으로 검색 클릭시
 * 180509 고객센터 우상단 검색에도 반응할 수 있도록 변경
 */
var cndSearch = function (page) {

    //showMask();
    /* 추가된 부분 */
    var topSearch = $("#top_search_word").val();
    var cont = $("#cont").val();

    if (!checkBlank(topSearch)) {
        cont = topSearch;
        $("#cont").val(topSearch);
    }

    $("#top_search_word").val("");
    /* 여기까지 */

    var url = "/ajax/cscenter/faq/load_faq_list.php";
    var blank = "<tr style='text-align:center'><td colspan='2'>검색결과가 없습니다.</td></tr>";
    var data = {
        "cont": cont // 수정됨
            ,
        "type": $("#type").val()
    };
    var callback = function (result) {
        var rs = result.split("♪");
        if ($.trim(rs[0]) == "") {
            $("#faq_list").html(blank);
            $("#resultNum").html("<em>0</em>건의 검색결과가 있습니다.");
            return false;
        }
        $("#faq_list").html(rs[0]);
        $("#paging").html(rs[1]);
        $("#resultNum").html("<em>" + rs[2] + "</em>건의 검색결과가 있습니다.");
    };

    data.list_num = listSize;
    data.page = page;

    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 페이지 이동
 */
var movePage = function (val) {
    cndSearch(val);
}

/**
 * @brief 조건 검색
 */
var searchKey = function (event) {
    if (event.keyCode != 13) {
        return false;
    }
    cndSearch(1);
}

/**
 * @brief 조건 검색
 */
var searchTxt = function () {
    cndSearch(1);
}

/**
 * @brief FAQ뷰 액션
 */
var viewFAQ = function (seqno, td) {

    var url = "/proc/cscenter/faq/modi_faq_list.php";
    var data = {
        "seqno": seqno
    };
    var callback = function (result) {
        if (result != 1) {
            alert("게시물 조회에 문제가 생겼습니다.\n잠시후 다시 시도해주세요.");
            return false;
        }

        var viewTarget = $("#detail_view_" + seqno);
        var guideTarget = $("#faq_guide_" + seqno);

        if (viewTarget.attr("class") == "detail_view") {
            $(viewTarget).addClass("on");
            $(viewTarget).show();
            $(guideTarget).addClass("on");
            $(guideTarget).show();
        } else {
            $(viewTarget).removeClass("on");
            $(viewTarget).hide();
            $(guideTarget).removeClass("on");
            $(guideTarget).hide();
        }
    };

    ajaxCall(url, "html", data, callback);
}
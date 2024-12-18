$(document).ready(function () {
    //lnb 선택 효과
    mypageLnbEffect();

    //전화번호에 숫자만 입력 가능
    numKeyCheck('tel_num2');
    numKeyCheck('tel_num3');
    numKeyCheck('cell_num2');
    numKeyCheck('cell_num3');

    initSearchParam();
    ftfSearch(10, 1);
});

/**
 * @brief 선택조건으로 검색 클릭시
 */
var ftfSearch = function (listSize, page) {

    var url = "/ajax/mypage/pre_event/load_list.php";
    var blank = "<tr><td colspan=\"6\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "inq_typ": $("#inq_typ").val(),
        "title": $("#title").val(),
    };
    var callback = function (result) {
        var rs = result.split("♪");
        if (rs[0].trim() == "") {

            $("#list").html(blank);
            $("#paging").html("<li><button class='on'>1</button><li>");
            $("#resultNum").html("<em>0</em>건의 검색결과가 있습니다.");
            return false;

        } else {

            $("#list").html(rs[0]);
            $("#paging").html(rs[1]);
            $("#resultNum").html(rs[2]);
            return false;

        }

    };

    data.list_num = listSize;
    data.page = page;

    showMask();
    ajaxCall(url, "html", data, callback);
}

var listCnt = "";

/**
 * @brief 보여줄 페이지 수 설정
 */
var changeListNum = function (val) {
    listCnt = val;
    ftfSearch(listCnt, 1);
}

/**
 * @brief 페이지 이동
 */
var movePage = function (val) {

    ftfSearch(listCnt, val);
}

var initSearchParam = function () {

    if (!$("#searchParam").val())
        return false;
    var params = $("#searchParam").val().split("&");
    $.each(params, function (i, v) {
        var tmp = v.split("=");
        $("#" + tmp[0]).val(tmp[1]);
    });
};

var goList = function () {
    var url = "/mypage/pre_event.html";
    $("#frm").attr("action", url);
    $("#frm").attr("method", "post");
    $("#frm").submit();
};

/**
 * @brief 페이지 이동
 */
var preWrite = function () {
    var url = "/mypage/pre_write.html";
    $("#frm").attr("action", url);
    $("#frm").attr("method", "post");
    $("#frm").submit();

}

/**
 * @brief 페이지 이동
 */
var preView = function (seq) {
    location.href = "/mypage/pre_view.html?seq=" + seq;
}

/**
 * @brief 조건 검색
 */
var searchKey = function (event) {
    if (event.keyCode != 13) {
        return false;
    }
    ftfSearch(listCnt, 1);
}

/**
 * @brief 조건 검색
 */
var searchTxt = function () {
    ftfSearch(listCnt, 1);
}

var validation = function () {

    if ($("#title").val() == "") {
        alert("제목을 입력해주세요.");
        $("#title").focus();
        return false;
    }


    if (($("#cell_num2").val() == "" || $("#cell_num3").val() == "") &&
        ($("#tel_num2").val() == "" || $("#tel_num3").val() == "") &&
        ($("#mail").val() == "" || $("#mail2").val() == "")) {
        alert("연락처를 최소 한개는 남겨주셔야 합니다.");
        return false;
    }


    if ($("#cont").val() == "") {
        alert("내용을 입력해주세요.");
        $("#cont").focus();
        return false;
    }

    return true;
}

var regiReq = function () {
    if (!validation())
        return false;

    $("#frm").submit();
}
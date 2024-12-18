/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/03/03 왕초롱 생성
 * 2016/06/22 임종건 재개발
 *============================================================================
 *
 */

$(document).ready(function () {
    //lnb 선택 효과
    mypageLnbEffect();

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

    dateSet('0');
    claimSearch(10, 1);
});

/**
 * @brief 선택조건으로 검색 클릭시
 */
var claimSearch = function (listSize, page) {

    var colspan = "8";
    var url = "/ajax/mypage/claim_list/load_claim_list.php";
    var blank = "<tbody name=\"claim_list\"><tr><td colspan=\"" + colspan + "\">검색 된 내용이 없습니다.</td></tr></tbody>";
    var data = {
        "from": $("#from").val(),
        "to": $("#to").val(),
        "dvs": $("#dvs").val(),
        "state": $("#state").val(),
        "title": $("#title").val()
    };
    var callback = function (result) {
        var rs = result.split("♪");
        $("tbody[name='claim_list']").remove();
        //if (rs[0].trim() == "") {
        if ($.trim(rs[0]) == "") {
            $("#list").after(blank);
            $("#paging").html("<li><button class='on'>1</button><li>");
            $("#resultNum").html("<em>0</em>건의 검색결과가 있습니다.");
            return false;

        } else {
            $("#list").after(rs[0]);
            $("#paging").html(rs[1]);
            $("#resultNum").html(rs[2]);
        }
        orderTable($('body'));
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
    claimSearch(listCnt, 1);
}

/**
 * @brief 페이지 이동
 */
var movePage = function (val) {
    claimSearch(listCnt, val);
}

/**
 * @brief 페이지 이동
 */
var claimSelectMove = function () {
    var url = "/mypage/claim_select.html";
    $(location).attr('href', url);
}

/**
 * @brief 조건 검색
 */
var searchKey = function (event) {
    if (event.keyCode != 13) {
        return false;
    }
    claimSearch(listCnt, 1);
}

/**
 * @brief 조건 검색
 */
var searchTxt = function () {
    claimSearch(listCnt, 1);
}

//상세보기 펼치기
var openOrderDetail = function (idx, seqno) {

    var url = "/ajax/mypage/claim_list/load_claim_view.php";
    var data = {
        "order_common_seqno": seqno
    };
    var callback = function (result) {
        $("#detail" + idx).html(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//상세보기 접기
var closeOrderDetail = function (idx) {
    $("#detail" + idx).html("");
}
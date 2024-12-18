/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/03/08 왕초롱 생성
 * 2016/06/03 임종건 리스트 수정
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

    searchCpList(10, 1);
});

var listCnt = "10";

/**
 * @brief 선택조건으로 검색 클릭시
 */
var searchCpList = function (listSize, page) {

    listCnt = listSize;

    var url = "/ajax/mypage/benefits_coupon/load_cp_list.php";
    var blank = "<tr><td colspan=\"7\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "from": $("#from").val(),
        "to": $("#to").val(),
        "state": $("#state").val()
    };
    var callback = function (result) {
        var rs = result.split("♪");
        if (rs[0].trim() == "") {

            $("#cp_list").html(blank);
            $("#paging").html("<li><button class='on'>1</button><li>");
            $("#resultNum").html("총<em>0</em>건의 쿠폰내역이 있습니다.");

        } else {

            $("#cp_list").html(rs[0]);
            $("#paging").html(rs[1]);
            $("#resultNum").html(rs[2]);
        }
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
    listCnt = val;
    searchCpList(listCnt, 1);
}

/**
 * @brief 페이지 이동
 */
var movePage = function (val) {

    searchCpList(listCnt, val);
}

/**
 * @brief 조건 검색
 */
var searchList = function () {

    searchCpList(listCnt, 1);
}
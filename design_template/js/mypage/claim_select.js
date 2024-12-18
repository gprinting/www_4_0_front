/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/03/07 왕초롱 생성
 * 2016/06/22 임종건 재개발
 * 2016/09/02 엄준현 수정(주문상세 가져오는거 공통에서 가져오도록 수정)
 *============================================================================
 *
 */

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

    orderFinishList(10, 1);
});

var listCnt = "";

/**
 * @brief 주문완료 건 리스트
 */
var orderFinishList = function (listSize, page) {

    var colspan = "9";
    if ($("#member_dvs").val() == "기업") {
        colspan = "10";
    }

    var url = "/ajax/mypage/claim_select/load_order_list.php";
    var blank = "<tbody name=\"claim_list\"><tr><td colspan=\"" + colspan + "\">검색 된 내용이 없습니다.</td></tr></tbody>";
    var data = {
        "from": $("#from").val(),
        "to": $("#to").val(),
        "dlvr_way": $("#dlvr_way").val(),
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

/**
 * @brief 보여줄 페이지 수 설정
 */
var changeListNum = function (val) {
    listCnt = val;
    orderFinishList(listCnt, 1);
}

/**
 * @brief 페이지 이동
 */
var movePage = function (val) {
    orderFinishList(listCnt, val);
}

/**
 * @brief 클레임 요청 페이지 이동
 */
var orderClaimMove = function () {

    var selectOrder = getselectedNo();

    if (selectOrder == "") {
        alert("클레임 요청을 하려는 주문을 선택해주세요");
        return;
    }

    var url = "/mypage/claim_write.html?order_common_seqno=" + selectOrder;
    $(location).attr('href', url);

}

//체크박스 선택시 value값 가져오는 함수
var getselectedNo = function (el) {

    var selectedValue = "";

    $("input[name=claimChk]:checked").each(function () {
        selectedValue += "," + $(this).val();
    });

    if (selectedValue != "") {
        selectedValue = selectedValue.substring(1);
    }

    return selectedValue;
}

/**
 * @brief 조건 검색
 */
var searchKey = function (event) {
    if (event.keyCode != 13) {
        return false;
    }
    orderFinishList(listCnt, 1);
}

/**
 * @brief 조건 검색
 */
var searchTxt = function () {
    orderFinishList(listCnt, 1);
}

/**
 * @brief 클레임 내역리스트로 페이지 이동
 */
var goList = function () {
    var url = "/mypage/claim_list.html";
    location.href = url;
};
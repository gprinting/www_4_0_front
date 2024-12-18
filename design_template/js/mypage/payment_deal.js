/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/06/15 임종건 생성
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

    var dateObj = new Date();
    var year = dateObj.getFullYear();
    var month = dateObj.getMonth() + 1;
    var day = dateObj.getDate();

    month = month.toString();
    day = day.toString();

    if (month.length == 1) month = "0" + month;
    if (day.length == 1) day = "0" + day;

    var today = year + "-" + month + "-" + day;
    $("#from").val(today);
    $("#to").val(today);

    getPaymentList(10, 1);
});

var listCnt = "";
var startDate = "";
var endDate = "";

/**
 * @brief 결재내역 리스트
 */
var getPaymentList = function (listSize, page) {

    /*
    if (!$("#from").val()) {
        alert("시작날짜를 선택해주세요");
        return false;
    }


    if (!$("#to").val()) {
        alert("종료날짜를 선택해주세요");
        return false;
    }
    */

    startDate = $("#from").val();
    endDate = $("#to").val();

    var url = "/ajax/mypage/payment_deal/load_transactional_info.php";
    var blank = "<tr><td colspan=\"8\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "from": $("#from").val(),
        "to": $("#to").val(),
        "dvs": $("#dvs").val()
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
    getPaymentList(listCnt, 1);
}

/**
 * @brief 페이지 이동
 */
var movePage = function (val) {
    getPaymentList(listCnt, val);
}

/**
 * @brief 인쇄팝업
 */
var printLayerPopup = function () {
    if (!$("#from").val()) {
        alert("시작날짜를 선택해주세요");
        return false;
    }

    if (!$("#to").val()) {
        alert("종료날짜를 선택해주세요");
        return false;
    }

    var url = '/mypage/popup/l_dealdetails.html?from=' + startDate + '&to=' + endDate;
    var popObj = window.open(url, "_blank", "width=930,height=900,status=no");
}

/**
 * @brief 달 수 변경
 */
var setMonth = function (month) {
    $("#to").datepicker("setDate", '0');
    $("#from").datepicker("setDate", '-' + month + 'M');
};

/**
 * @brief 거래내역 엑셀로 다운로드
 */
var downloadExcel = function () {
    if (!$("#from").val()) {
        alert("시작날짜를 선택해주세요");
        return false;
    }

    if (!$("#to").val()) {
        alert("종료날짜를 선택해주세요");
        return false;
    }

    var url = "/ajax/mypage/payment_deal/make_payment_deal_excel.php";
    var data = {
        "from": startDate,
        "to": endDate,
    };
    var callback = function (result) {
        var downUrl = "/common/down_esti_excel.php?"
        downUrl += "filename=" + result;
        downUrl += "&dvs=payment";
        downUrl += "&start=" + startDate;
        downUrl += "&end=" + endDate;
        $("#file_ifr").attr("src", downUrl);
    };

    ajaxCall(url, "text", data, callback);
};
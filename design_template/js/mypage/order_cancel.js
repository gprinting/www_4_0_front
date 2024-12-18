/*
 *
 * Copyright (c) 2018 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2018/01/30 이청산 수정
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
    orderSearch(10, 1);

});

var listCnt = 0;

/**
 * @brief 선택조건으로 검색 클릭시
 */
var orderSearch = function (listSize, page) {
    var colspan = "9";
    if ($("#member_dvs").val() == "기업") {
        colspan = "10";
    }

    if ($("#im").val() == "1") {
        colspan = "4";
    }

    var url = "/ajax/mypage/order_cancel/load_cancel_list.php";
    var data = {
        "from": $("#from").val(),
        "to": $("#to").val(),
        "dlvr_way": $("#dlvr_way").val(),
        "state": $("#order_state").val(),
        "title": $("#title").val()
    };
    var callback = function (result) {
        var rs = result.split("♪");
        $("tbody[name='order_list']").remove();
        if ($("#im").val() == "1") {
            if (rs[0].trim() == "") {
                $("#list").after();
                $("#list").after(blank);
                $("#paging").html("<li><button class='on'>1</button><li>");
                $("#resultNum").html("0건의 검색결과가 있습니다.");
            } else {
                $("#list").after(rs[0]);
                $("#paging").html(rs[1]);
                $("#resultNum").html(rs[2]);
            }
        } else {
            if (rs[0].trim() == "") {
                $("#list").after();
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
            resultTableSet();
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
    orderSearch(listCnt, 1);
}

/**
 * @brief 페이지 이동
 */
var movePage = function (val) {
    orderSearch(listCnt, val);
}

/**
 * @brief 조건 검색
 */
var orderSearchKey = function (event) {
    if (event.keyCode != 13) {
        return false;
    }
    orderSearch(listCnt, 1);
}

/**
 * @brief 조건 검색
 */
var orderSearchTxt = function () {
    orderSearch(listCnt, 1);
}

var checkedPayway = function (obj) {
    $(".reorder_payway").removeClass("checked");
    $(obj).addClass("checked");
    var $radio = $(obj).prev();
    $("input[name='reorder_payway']").prop("checked", false);
    $radio.prop("checked", true);
};
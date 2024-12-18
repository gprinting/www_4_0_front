/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/02/23 왕초롱 생성
 * 2016/06/21 임종건 재개발
 * 2018/01/22 이청산 수정
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

    var from = $("#from").val();
    var to = $("#to").val();

    if (checkBlank(from) || checkBlank(to)) {
        dateSet('0');
    } else if (from === "NONE" || to === "NONE") {
        $("#from").val('');
        $("#to").val('');
    }

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

    var url = "/ajax/mypage/order_all/load_order_list.php";
    var blank = "<tbody name=\"order_list\"><tr><td colspan=\"" + colspan + "\">검색된 내용이 없습니다.</td></tr></tbody>";
    var data = {
        "from": $("#from").val(),
        "to": $("#to").val(),
        "dlvr_way": $("#dlvr_way").val(),
        "state": $("#order_state").val(),
        "title": $("#title").val(),
        "order_seqno": $("#order_seqno").val()
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

/**
 * @brief 주문 상세 팝업
 */
var showOrderDetailPop = function (order_seqno) {
    layerPopup('l_order_detail_new', 'popup/l_order_detail_new.html');
}

/**
 * @brief 배송 묶기
 */
var bindDlvr = function () {

    var chkBind = "";
    var bindLen = 0;

    $("._individual").each(function () {
        if ($(this).is(":checked") == true) {
            chkBind += $(this).val();
            chkBind += ",";
        }
    });

    bindLen = chkBind.length;
    if (bindLen < 10) {
        alert("묶을 상품을 선택해 주세요.");
        return false;
    }

    chkBind = chkBind.substr(0, bindLen - 1); // 보낼 데이터

    var url = "/proc/mypage/order_all/proc_bind_order.php";
    var data = {
        "seq_str": chkBind
    };
    var callback = function (result) {
        if (result == '3') {
            alert("받는사람 이름, 주소가 같아야 배송을 묶을 수 있습니다.");
            return false;
        } else if (result == '4') {
            alert("명함, 스티커, 복권, 자석홍보물만 배송을 묶을 수 있습니다.");
            return false;
        } else if (result == '5') {
            alert("배송묶기는 접수중인 주문부터 배송중 전까지의 주문만 묶을 수 있습니다.");
            return false;
        } else if (result == '0') {
            alert("오류입니다. 관리자에게 문의해 주세요.");
            return false;
        } else if (result == '1') {
            alert("배송을 묶었습니다.");
        }
    }

    ajaxCall(url, "html", data, callback);

}
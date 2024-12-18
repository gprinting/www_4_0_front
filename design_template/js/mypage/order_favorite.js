/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/02/26 왕초롱 생성
 *============================================================================
 *
 */

$(document).ready(function () {
    //lnb 선택 효과
    mypageLnbEffect();

    searchPrdt(10, 1);
});

var listCnt = "";
var modalMask = "";

/**
 * @brief 선택조건으로 검색 클릭시
 */
var searchPrdt = function (listSize, page) {

    var url = "/ajax/mypage/order_favorite/load_prdt_list.php";
    var blank = "<tbody name=\"prdt_list\"><tr><td colspan=\"5\">검색 된 내용이 없습니다.</td></tr></tbody>";
    var data = {
        "title": $("#title").val()
    };
    var callback = function (result) {
        var rs = result.split("♪");
        if (rs[0].trim() == "") {
            $("tbody[name='prdt_list']").remove();
            $("#list").after(blank);
            $("#paging").html("<li><button class='on'>1</button><li>");
            $("#resultNum").html("<em>0</em>건의 검색결과가 있습니다.");
            return false;

        } else {

            $("tbody[name='prdt_list']").remove();
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
    searchPrdt(listCnt, 1);
}

/**
 * @brief 페이지 이동
 */
var movePage = function (val) {

    searchPrdt(listCnt, val);
}

/**
 * @brief 조건 검색
 */
var searchKey = function (event) {
    if (event.keyCode != 13) {
        return false;
    }
    searchPrdt(listCnt, 1);
}

/**
 * @brief 조건 검색
 */
var searchTxt = function () {
    searchPrdt(listCnt, 1);
}

/**
 * @brief 선택조건으로 검색 클릭시
 */
var removeSelect = function () {

    var selectPrdt = getselectedNo();

    if (selectPrdt == "") {
        return alertReturnFalse("삭제할 목록을 선택해주세요");
    }

    if (!confirm("선택한 상품을 삭제하시겠습니까?")) {
        return false;
    }

    var url = "/proc/mypage/order_favorite/del_interest_prdt.php";
    var data = {
        "select_prdt": selectPrdt
    };
    var callback = function (result) {
        if (result.trim() == "1") {

            alert("삭제했습니다.");
            searchPrdt(listCnt, 1);

        } else {

            alert("삭제에 실패했습니다.");
        }

        $("input[name=allCheck]").prop("checked", false);
        searchPrdt();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 선택조건으로 검색 클릭시
 */
var putSelectPrdt = function () {

    var select_prdt = getselectedNo();

    if (select_prdt == "") {
        alert("장바구니에 담을 목록을 선택해주세요.");
        return false;
    }

    var url = "/proc/mypage/order_favorite/proc_interest_prdt.php";
    var data = {
        "select_prdt": select_prdt
    };
    var callback = function (result) {
        if (result.trim() == "1") {

            alert("선택한 상품을 장바구니에 담았습니다..");

        } else {

            alert("장바구니 담기에 실패했습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 전체 장바구니행
 */
var putAllPrdt = function () {

    var select_prdt = "";
    $("input[name='chk[]']").each(function () {
        select_prdt += "," + $(this).val();
    });

    select_prdt = select_prdt.substring(1);

    var url = "/proc/mypage/order_favorite/proc_interest_prdt.php";
    var data = {
        "select_prdt": select_prdt
    };
    var callback = function (result) {
        if (result.trim() == "1") {

            alert("전체 상품을 장바구니에 담았습니다..");

        } else {

            alert("장바구니 담기에 실패했습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//체크박스 선택시 value값 가져오는 함수
var getselectedNo = function (el) {

    var selectedValue = "";

    $("input[name='seq']:checked").each(function () {
        selectedValue += "," + $(this).val();
    });

    if (selectedValue != "") {
        selectedValue = selectedValue.substring(1);
    }

    return selectedValue;
}

/**
 * @brief 주문 상세정보 펼치기
 *
 * @param idx     = 행 위치
 * @param seqno   = 주문공통일련번호
 */
var openDetail = function (idx, seqno) {
    var id = "detail";

    var url = "/ajax/mypage/order_favorite/load_favorite_detail.php";
    var data = {
        "seqno": seqno
    };
    var callback = function (result) {
        var th = $("#" + id + idx);

        $("#" + id + idx).html(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 저장된 관심상품 항목으로 주문
 *
 * @param seqno = 관심상품 일련번호
 */
var goProduct = function (seqno) {
    var url = "/product/common/make_interest_prdt_def_val.php?seqno=" + seqno;
    location.replace(url);
};
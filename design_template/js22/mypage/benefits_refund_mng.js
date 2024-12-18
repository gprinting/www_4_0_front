$(document).ready(function () {
    //lnb 선택 효과
    mypageLnbEffect();
    loadRefundAcc();
    loadRefundReqList(10, 1);
});

/**
 * @brief 환불계좌 불러오기
 *
 */
var loadRefundAcc = function () {
    var url = "/ajax/mypage/benefits_virtual_mng/load_refund_info.php";
    var data = {
        // NO DATA
    };

    var callback = function (result) {
        var rs = result.split('@');
        if (rs[0] == '1') {
            $("#refund_info").html(rs[1]);
        } else {
            return false;
        }
    };

    ajaxCall(url, "html", data, callback);
};

// 환불 요청 내역 불러오기
var loadRefundReqList = function (listSize, page) {
    var refundInfo = $("#refund_info").val();
    if (refundInfo.indexOf("없습니다") > -1) {
        return false;
    }

    var url = "/ajax/mypage/benefits_virtual_mng/load_refund_req_list.php";
    var data = {
        // NO DATA
    };

    var callback = function (result) {
        var rs = result.split("♪");
        $("#list").html(rs[0]);
        $("#paging").html(rs[1]);
    };

    data.list_num = listSize;
    data.page = page;

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 페이지 이동
 */
var movePage = function (val) {
    loadRefundReqList(listCnt, val);
}

/**
 * @brief 환불계좌 등록
 *
 */
var regiRefundAcc = function () {
    var refundName = $("#refund_name").val(); // 예금주
    var refundBankName = $("#refund_bank_name").val(); // 입금 은행명
    var refundBaNum = $("#refund_ba_num").val(); // 환불 계좌번호

    refundBaNum = refundBaNum.replace(/\-/g, ''); // 하이픈 제거

    var url = "/ajax/mypage/benefits_virtual_mng/insert_refund_info.php";
    var data = {
        "refund_name": refundName,
        "refund_bank_name": refundBankName,
        "refund_ba_num": refundBaNum
    };

    var callback = function (result) {
        if (result == '0') {
            return alertReturnFalse('환불계좌 등록에 실패했습니다.');
        } else if (result == '1') {
            alert('환불계좌 등록에 성공했습니다');
            location.reload();
        }
    };

    if (checkBlank(refundName)) {
        return alertReturnFalse('예금주를 입력하세요.');
    }

    if (checkBlank(refundBankName)) {
        return alertReturnFalse('입금 은행명을 선택하세요.');
    }

    if (checkBlank(refundBaNum)) {
        return alertReturnFalse('계좌번호를 입력하세요.');
    }

    ajaxCall(url, "html", data, callback);
};

// 환불 등록
var doRefundReq = function () {
    layerPopup('l_refund_acc', 'popup/l_benefits_refund_acc.html');
}

/**
 * @brief 환불금액 요청
 *
 */
var regiRefundAmount = function () {
    var refundMax = $("#refund_max").val(); // 환불 최대 가능금액
    var refundAmount = $("#refund_amount").val(); // 환불 요청금액

    chkNumber = /^[0-9]*$/; // 숫자만 허용 정규식

    var url = "/ajax/mypage/benefits_virtual_mng/insert_refund_amount.php";
    var data = {
        "refund_max": refundMax,
        "refund_amount": refundAmount
    };

    var callback = function (result) {
        if (result == '0') {
            return alertReturnFalse('환불 요청에 실패했습니다.');
        } else if (result == '1') {
            alert('환불 요청에 성공했습니다.');
            location.reload();
        } else if (result == '2') {
            return alertReturnFalse('[경고/파라미터 변조]환불 요청에 실패했습니다.');
        } else if (result == '3') {
            return alertReturnFalse('[환불 가능금액 초과]환불 요청에 실패했습니다.');
        }
    };

    if (refundMax == '0') {
        return alertReturnFalse('환불 가능한 금액이 없습니다.');
    }

    if (checkBlank(refundAmount)) {
        return alertReturnFalse('환불요청 금액을 입력하세요.');
    }

    if (!chkNumber.test(refundAmount)) {
        return alertReturnFalse('숫자만 입력해주세요.');
    }

    ajaxCall(url, "html", data, callback);
};
$(document).ready(function () {
    //lnb 선택 효과
    mypageLnbEffect();
});

//회원탈퇴 신청
var makeWithdrawal = function (seqno) {

    if (!confirm("정말 탈퇴하시겠습니까?"))
        return false;

    showMask();
    var data = {
        "seqno": seqno,
        "reason": $("#withdraw_reason").val()
    };

    var withdraw_code = "";

    for (var i = 1; i <= 14; i++) {
        if ($("input:checkbox[id='reduce_" + i + "']").is(":checked") == true) {
            withdraw_code += "," + i;
        }
    }

    withdraw_code = withdraw_code.substring(1);
    data.withdraw_code = withdraw_code;

    delUnpaidOrder(seqno);

    $.ajax({
        type: "POST",
        data: data,
        url: "/proc/mypage/member_quit/regi_member_reduce_info.php",
        success: function (result) {

            hideMask();
            if (result == 1) {
                alert("탈퇴신청 되었습니다.");
                logout();
            } else {
                alert("탈퇴신청을 실패 하였습니다.");
            }
        },
        error: getAjaxError
    });
}

// 회원탈퇴 시 장바구니 내 or 주문대기 주문을 삭제
// 주문대기 주문삭제는 선입금 관련 로직이 생기면 수정 필요
var delUnpaidOrder = function (seqno) {
    var seq = seqno;

    searchWithdrawalCart();
};

/** 
 * @brief  장바구니 일련번호 검색
 *
 * @return 장바구니 일련번호 배열
 */
var searchWithdrawalCart = function () {

    var url = "/ajax/order/search_cart.php";
    var data = {
        // no data;
    }
    var callback = function (result) {
        delWithdrawalCart(result);
    }

    ajaxCall(url, "json", data, callback);
};

var delWithdrawalCart = function (seqArr) {

    var url = "/proc/order/delete_order.php";
    var data = {
        "seq": seqArr
    }

    var callback = function (result) {
        console.log(result);
    }

    ajaxCall(url, "text", data, callback);
};
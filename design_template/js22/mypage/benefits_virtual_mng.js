$(document).ready(function () {
    //lnb 선택 효과
    mypageLnbEffect();
    searchVirtBa();

});

/**
 * @brief 회원의 가상계좌 확인
 */
var searchVirtBa = function () {
    var url = "/ajax22/mypage/benefits_virtual_mng/load_virtual_ba.php";
    var data = {
        // no data;
    };
    var callback = function (result) {
        rs = result.split('@');
        $("#virt_ba_list").html(rs[0]);
        $("#virt_ba_change_list").html(rs[1]);

        if(rs[0].includes("등록된 가상계좌가 없습니다.") === false) {
            $("#btn_virt_ba_change").show();
        }
    }

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 가상계좌 생성(정확히는 매칭)
 */
var regiVirtAcc = function () {
    showRegiVirtAccPop();
};

/**
 * @brief 가상계좌 생성 팝업 출력
 */
var showRegiVirtAccPop = function () {
    layerPopup('l_virt_ba_regi', 'popup/l_virt_ba_regi.html');
};

/**
 * @brief 가상계좌 매칭
 */
var regiBa = function () {
    var url = "/proc/mypage/benefits_virt_ba/regi_virt_ba.php";
    var data = {
        "bank_name": $("#EP_bank_cd input:checked").val(),
        "depo_name": $("#depo_name").val()
    };

    var callback = function (result) {
        rs = result.split('@');
        if (rs[0] == '1') {
            alert("가상계좌가 등록되었습니다.");
            location.reload();
        } else {
            alert(rs[1]);
            return false;
        }
    };

    if (checkBlank($("#depo_name").val())) {
        alert("입금자명을 입력해주세요.");
        return false;
    }

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 가상계좌 입금자명 변경
 */
var modiDepoName = function () {
    var depoName = $("#modi_depo_name").val();
    var url = "/proc/mypage/benefits_virt_ba/modi_virt_ba.php";
    var data = {
        "depo_name": depoName
    };
    var callback = function (result) {
        rs = result.split('@');
        if (rs[0] == '1') {
            alert(rs[1]);
            location.reload();
        } else {
            alert(rs[1]);
            return false;
        }
    };

    if (checkBlank(depoName)) {
        return alertReturnFalse("입금자명을 입력해주세요.");
    }

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 가상계좌 입금은행 변경
 */
var modiBankType = function () {
    var bankName = $("#bank_name input:checked").val();
    var url = "/proc/mypage/benefits_virt_ba/regi_virt_ba_change.php";
    var data = {
        "bank_name": bankName
    };

    var callback = function (result) {
        rs = result.split('@');
        if (rs[0] == '1') {
            alert(rs[1]);
            location.reload();
        } else {
            alert(rs[1]);
            return false;
        }
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 가상계좌 입금은행 변경 취소
 */
var cancelChangeVirtBa = function (seqno) {
    var url = "/proc/mypage/benefits_virt_ba/cancel_virt_ba_change.php";
    var data = {
        "seqno": seqno
    };

    if (!confirm("취소 하시겠습니까?")) {
        return false;
    }

    var callback = function (result) {
        rs = result.split('@');
        if (rs[0] == '1') {
            alert(rs[1]);
            location.reload();
        } else {
            alert(rs[1]);
            return false;
        }

    };

    ajaxCall(url, "html", data, callback);
};
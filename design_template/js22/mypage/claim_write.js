/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/03/07 왕초롱 생성
 * 2016/06/22 임종건 재개발
 *============================================================================
 *
 */
/**
 * @brief 주문 클레임 선택 페이지 이동
 */
var claimSelectMove = function () {
    var url = "/mypage/claim_select.html";
    $(location).attr('href', url);
}

/**
 * @brief 클레임 리스트 페이지 이동
 */
var claimListMove = function () {
    var url = "/mypage/claim_list.html";
    $(location).attr('href', url);
}

/**
* @brief 클레임 등록
* 171204 이청산 주석처리(사용하지 않음!)
var regiClaim = function() {

    if ($("#claim_title").val() == "") {
        alert("제목을 입력해주세요.");
        $("#claim_title").focus();
        return false;
    }

    if ($("#claim_cont").val() == "") {
        alert("내용을 입력해주세요.");
        $("#claim_cont").focus();
        return false;
    }

    var formData = new FormData();
    var url = "/proc/mypage/claim_write/regi_claim.php";
    var data = formData;
    formData.append("order_seqno", $("#order_common_seqno").val());
    formData.append("claim_title", $("#claim_title").val());
    formData.append("dvs", $("#dvs").val());
    formData.append("claim_cont", $("#claim_cont").val());
    formData.append("sample_file", $("#sample_file")[0].files[0]);

    showMask();

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        dataType : "html",
        processData : false,
        contentType : false,
        success: function(result) {
            hideMask();
            if (result.trim() == 1) {
                alert("클레임을 등록 하였습니다.");
                claimListMove();
            } else {
                alert("클레임 등록에 실패하였습니다.");
            }
        },
        error    : getAjaxError   
    });
} */

/**
 * @brief 클레임 등록
 */
var regiClaim = function () {

    // validation check
    var title = $("#claim_title").val() // 클레임 제목
    var dvs = $("#dvs").val() // 클레임 유형
    var cont = $("#claim_cont").val() // 클레임 내용

    if (checkBlank(title)) {
        alert("클레임 제목을 입력해 주세요.");
        $("#claim_title").focus();
        return false;
    }

    if (checkBlank(dvs)) {
        alert("클레임 유형을 선택해 주세요.");
        return false;
    }

    if (checkBlank(cont)) {
        alert("클레임 내용을 입력해 주세요.");
        $("#claim_cont").focus();
        return false;
    }

    $("#claim_form").submit();
}
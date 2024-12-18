// 메일 팝업 출력
var mailPopupShow = function () {
    var url = '/mypage/popup/l_mail.html';
    popupMask = layerPopup('l_mail', url);

};

// 메일 보내기
var send = function () {

    var mailAddr = $("#mail_address").val();
    var membName = $("#member_name").val();
    var membCall = $("#member_call").val();
    var mailCont = $("#mail_content").val();

    var url = "/ajax/cscenter/mail/send_mail.php";
    var data = {
        "mailAddress": mailAddr,
        "memberName": membName,
        "memberCall": membCall,
        "mailContent": mailCont
    };

    var processBtn = "<button type=\"button\" class=\"btn_type_02\">신청중입니다.</button>";

    var callback = function (result) {
        if (result == "1") {
            alert("메일 전송에 성공했습니다.");
            //location.replace('/cscenter/main.html');
            location.reload();
        } else {
            alert("메일 전송에 실패했습니다. 관리자에게 문의하세요.");
        }
    };

    // validation check
    if (checkBlank(mailAddr)) {
        alert("메일주소를 입력해 주세요.");
        $("#mail_address").focus();
        return false;
    }

    if (!email_check(mailAddr)) {
        alert("올바른 메일 주소가 아닙니다.");
        $("#mail_address").focus();
        return false;
    }

    if (checkBlank(membName)) {
        alert("성함을 입력해 주세요.");
        $("#member_name").focus();
        return false;
    }

    if (checkBlank(membCall)) {
        alert("연락처를 입력해 주세요.(- 제외, 숫자만 입력 가능합니다.)");
        $("#member_call").focus();
        return false;
    }

    if (checkBlank(mailCont)) {
        alert("내용을 입력해 주세요.");
        $("#mail_content").focus();
        return false;
    }

    ajaxCall(url, "html", data, callback);
    $("#send_btn").html(processBtn);
}
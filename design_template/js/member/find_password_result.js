/**
 * @brief 비밀번호 변경
 */
var modiPw = function () {

    if (checkBlank($("#new_pw").val())) {
        alert("새로운 비밀번호를 입력 해주세요.");
        $("#new_pw").focus();
        return false;
    }

    if (checkBlank($("#confirm_pw").val())) {
        alert("새로운 비밀번호 확인을 입력 해주세요.");
        $("#confirm_pw").focus();
        return false;
    }

    if ($("#new_pw").val() != $("#confirm_pw").val()) {
        alert("비밀번호와 비밀번호 확인 의 비밀 번호가 다름니다.");
        return false;
    }

    var data = {
        "passwd": $("#new_pw").val(),
        "member_seqno": $("#member_seqno").val()
    };
    var url = "/proc/member/find_password_result/modi_password.php";
    var callback = function (result) {
        hideMask();
        if (result == 1) {
            location.href = "/member/login.html";
            alert("비밀번호를 변경하였습니다.");
        } else {
            alert("비밀번호 변경을 실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
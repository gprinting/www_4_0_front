var goPage = function () {

    if ($("input:checkbox[id='agree1']").is(":checked") == false) {
        alert("디프린팅 이용약관에 동의하셔야 됩니다.");
        return false;
    }
    if ($("input:checkbox[id='agree2']").is(":checked") == false) {
        alert("개인정보 취급방침에 동의하셔야 됩니다.");
        return false;
    }

    var f = document.frm;
    f.action = "/member/join_3.html";
    f.target = "";
    f.method = "POST";
    f.submit();
    return false;
}
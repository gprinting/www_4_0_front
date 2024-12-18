var goPage = function (dvs) {
    $("#dvs").val(dvs);
    var f = document.frm;
    f.action = "/member/join_2.html";
    f.target = "";
    f.method = "POST";
    f.submit();
    return false;
}
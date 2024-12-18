//도메인 선택
var selectDomain = function (val) {

    if (checkBlank(val)) {
        $("#email_domain").removeAttr("readonly");
    } else {
        $("#email_domain").attr("readonly", "readonly");
        $("#email_domain").val(val);
    }
}
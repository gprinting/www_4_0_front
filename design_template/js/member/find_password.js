/**
 * @brief 선택조건으로 검색 클릭시
 */
var searchPw = {
    "myInfo": function () {
        var url = "/ajax/member/find_password/load_find_password.php";
        var data = {
            "member_name": $("#member_name").val(),
            "member_id": $("#member_id").val()
        };
        var dvs = $(':input[name=infoSelect]:radio:checked').val();

        if (dvs == 1) {
            data.cell_num = $("#cell_num1").val() + "-" + $("#cell_num2").val() + "-" + $("#cell_num3").val();
            data.search_cnd = "cell_num";
        }

        if (dvs == 2) {
            data.mail = $("#email_addr").val() + "@" + $("#email_domain").val();
            data.search_cnd = "mail";
        }

        var callback = function (result) {
            if (checkBlank(result)) {
                alert("입력하신 정보와 일치한 회원이 존재하지 않습니다.");
            } else {
                location.href = "/member/find_password_result.html?seqno=" + result;
            }
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    "certi": function () {
        var url = "/ajax/member/find_password/load_certi_find_password.php";
        var data = {};

        var callback = function (result) {};

        alert("개발 준비중");
        /*
        showMask();
        ajaxCall(url, "html", data, callback);
        */
    }
};
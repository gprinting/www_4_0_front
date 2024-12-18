/**
 * @brief 선택조건으로 검색 클릭시
 */
var searchId = {
    "myInfo" : function() {
        var url = "/ajax/member/find_id/load_find_id.php";
        var data = {
    	    "member_name" : $("#name").val()
	    };
        var dvsNo = "";
        var dvs = $(':input[name=infoSelect]:radio:checked').val();

        if (dvs == 1) {
            data.cell_num = $("#cell_num1").val() + "-" + $("#cell_num2").val() + "-" +$("#cell_num3").val();
            data.search_cnd = "cell_num";
            dvsNo = 1;
        }

        if (dvs == 2) {
            data.mail = $("#email_addr").val() + "@" + $("#email_domain").val();
            data.search_cnd = "mail";
            dvsNo = 2;
        }

        var callback = function(result) {
            if (checkBlank(result)) {
                alert("입력하신 정보와 일치한 회원이 존재하지 않습니다.");
            } else {
                location.href = "/member/find_id_result.html?seqno=" + result + "&dvs=" + dvsNo;
            }
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    "certi" : function() {
        var url = "/ajax/member/find_id/load_certi_find_id.php";
        var data = {
	    };

        var callback = function(result) {
        };

        alert("개발 준비중");
        /*
        showMask();
        ajaxCall(url, "html", data, callback);
        */
    }
};

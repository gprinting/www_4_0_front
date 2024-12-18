$(document).ready(function () {
    chkMonthlyDlvrPrice();
});

var getPostcode = function () {
    new daum.Postcode({
        oncomplete: function (data) {
            var fullAddr = ''; // 최종 주소 변수
            var extraAddr = ''; // 조합형 주소 변수

            // 사용자가 선택한 주소 타입에 따라 해당 주소 값을 가져온다.
            if (data.userSelectedType === 'R') { // 사용자가 도로명 주소를 선택했을 경우
                fullAddr = data.roadAddress;

            } else { // 사용자가 지번 주소를 선택했을 경우(J)
                fullAddr = data.jibunAddress;
            }

            // 사용자가 선택한 주소가 도로명 타입일때 조합한다.
            if (data.userSelectedType === 'R') {
                //법정동명이 있을 경우 추가한다.
                if (data.bname !== '') {
                    extraAddr += data.bname;
                }
                // 건물명이 있을 경우 추가한다.
                if (data.buildingName !== '') {
                    extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                }
                // 조합형주소의 유무에 따라 양쪽에 괄호를 추가하여 최종 주소를 만든다.
                fullAddr += (extraAddr !== '' ? ' (' + extraAddr + ')' : '');
            }

            // 우편번호와 주소 정보를 해당 필드에 넣는다.
            document.getElementById('zipcode').value = data.zonecode; //5자리 새우편번호 사용
            document.getElementById('addr').value = fullAddr;

            // 커서를 상세주소 필드로 이동한다.
            document.getElementById('addr_detail').focus();

            // 배송기사 정보 검색
            //searchDlvrInfo(fullAddr);
        }
    }).open();
};

var searchDlvrInfo = function (addr) {
    var url = "/ajax22/mypage/add_dlvr/load_direct_dlvr_info.php";
    var data = {};
    var callback = function (result) {
        console.log(result);
    };

    ajaxCall(url, "json", data, callback);
};

var goPayment = function () {
    var zipcode = $("#zipcode").val();
    var chk = 0;
    var url = "/proc/mypage/add_dlvr/proc_direct_dlvr_info.php";
    var data = {
        "zipcode": zipcode,
        "addr": $("#addr").val(),
        "addr_detail": $("#addr_detail").val(),
        "add_info": $("#add_info").val(),
        "tel_num1": $("#tel_num1").val(),
        "tel_num2": $("#tel_num2").val(),
        "tel_num3": $("#tel_num3").val(),
        "cell_num1": $("#cell_num1").val(),
        "cell_num2": $("#cell_num2").val(),
        "cell_num3": $("#cell_num3").val()
    };
    var callback = function (result) {
        alert(result.msg);
        location.replace("/mypage22/delivery_address.html");
    };

    if (checkBlank(zipcode) || checkBlank($("#addr_detail").val())) {
        return alertReturnFalse("주소를 입력해주세요.");
    }

    if (!checkBlank($("#tel_num1").val()) &&
        !checkBlank($("#tel_num2").val()) &&
        !checkBlank($("#tel_num3").val())) {
        chk++;
    }

    if (!checkBlank($("#cell_num1").val()) &&
        !checkBlank($("#cell_num2").val()) &&
        !checkBlank($("#cell_num3").val())) {
        chk++;
    }

    if (chk < 1) {
        return alertReturnFalse("전화번호나 휴대전화 둘 중에 하나는 정확하게 입력해주세요.");
    }

    ajaxCall(url, "json", data, callback);
};

/*
 * @brief 월배송 총금액 확인하는 함수
 */
var chkMonthlyDlvrPrice = function () {
    var url = "/ajax22/mypage/add_dlvr/load_direct_dlvr_price.php";
    var data = {
        // NO DATA;
    };
    var callback = function (result) {
        if (checkBlank(result)) {
            return false;
        }
        $("#svc_price").html(result);
    };

    ajaxCallNoMask(url, "html", data, callback);
};
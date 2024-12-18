/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/03/29 박상용
 *============================================================================
 *
 */

$(document).ready(function () {
    //lnb 선택 효과
    mypageLnbEffect();

    registrationSearch(10, 1);
});

/**
 * @brief 선택조건으로 검색 클릭시
 */
var registrationSearch = function (listSize, page, pop) {

    var url = "/ajax/mypage/registration_list/load_registration_list.php";
    var blank = "<tr><td colspan=\"7\">등록 된 내용이 없습니다.</td></tr>";
    var data = {};
    var callback = function (result) {

        var rs = result.split("♪");
        if (rs[0].trim() == "") {
            $("#list").html(blank);
            $("#paging").html("<li><button class='on'>1</button><li>");
            $("#resultNum").html("<em>0</em>건의 검색결과가 있습니다.");
        } else {
            $("#list").html(rs[0]);
            $("#paging").html(rs[1]);
            $("#resultNum").html(rs[2]);
        }

        if (pop) {
            closePopup($(".l_businessRegistration"));
        }
    };

    data.list_num = listSize;
    data.page = page;

    showMask();
    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 다음 API 주소검색 함수
 *
 */
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
        }
    }).open({
        popupName: 'postcodePopup'
    });
};

var listCnt = "";

/**
 * @brief 페이지 이동
 */
var movePage = function (val) {
    registrationSearch(listCnt, val);
}

var goList = function () {
    var url = "/mypage/registration_list.html";
    $("#frm").attr("action", url);
    $("#frm").attr("method", "post");
    $("#frm").submit();
};

var validation = function () {

    if ($("#crn").val() == "") {
        alert("사업자등록증번호를 입력해주세요.");
        $("#crn").focus();
        return false;
    }

    var regExp = /^\d{3}-\d{2}-\d{5}$/;
    if (!regExp.test($("#crn").val())) {
        alert("사업자등록증번호를 정확히 입력해주세요.\nex) xxx-xx-xxxxx ( - 까지입력)");
        return false;
    }

    if ($("#corp_name").val() == "") {
        alert("회사명을 입력해주세요.");
        $("#corp_name").focus();
        return false;
    }


    if ($("#repre_name").val() == "") {
        alert("대표자이름을 입력해주세요.");
        $("#repre_name").focus();
        return false;
    }


    if ($("#bc").val() == "") {
        alert("업태를 입력해주세요.");
        $("#bc").focus();
        return false;
    }


    if ($("#tob").val() == "") {
        alert("종목을 입력해주세요.");
        $("#tob").focus();
        return false;
    }


    if ($("#tel2").val() == "" || $("#tel3").val() == "") {
        alert("전화번호를 입력해주세요.");
        $("#tel2").focus();
        return false;
    }


    if ($("#zipcode").val() == "" || $("#addr").val() == "" || $("#addr_detail").val() == "") {
        alert("주소를 입력해주세요.");
        $("#addr_detail").focus();
        return false;
    }


    if ($("#mng_name").val() == "") {
        alert("회계담당자 이름을 입력해주세요.");
        $("#mng_name").focus();
        return false;
    }


    if ($("#posi").val() == "") {
        alert("회계담당자 직급을 입력해주세요.");
        $("#posi").focus();
        return false;
    }


    if ($("#mail").val() == "" || $("#mail2").val() == "") {
        alert("회계담당자 이메일을 입력해주세요.");
        $("#mail").focus();
        return false;
    }

    return true;
}

var regi = function () {

    if (!validation())
        return false;

    showMask();

    var url = "/proc/mypage/registration_list/regi_registration.php";
    var data = {
        "crn": $("#crn").val(),
        "corp_name": $("#corp_name").val(),
        "repre_name": $("#repre_name").val(),
        "bc": $("#bc").val(),
        "tob": $("#tob").val(),
        "tel_num": $("#tel_num").val() + "-" + $("#tel_num2").val() + "-" + $("#tel_num3").val(),
        "zipcode": $("#zipcode").val(),
        "addr": $("#addr").val(),
        "addr_detail": $("#addr_detail").val(),
        "mng_name": $("#mng_name").val(),
        "posi": $("#posi").val(),
        "mail": $("#mail").val() + "@" + $("#mail2").val()
    };
    var callback = function (result) {
        alert(result);
        registrationSearch(10, 1, "POP");
        return false;
    };

    showMask();
    ajaxCall(url, "html", data, callback);

}

var edit = function (seq) {

    if (!validation())
        return false;

    showMask();

    var url = "/proc/mypage/registration_list/modi_registration.php";
    var data = {
        "seq": seq,
        "crn": $("#crn").val(),
        "corp_name": $("#corp_name").val(),
        "repre_name": $("#repre_name").val(),
        "bc": $("#bc").val(),
        "tob": $("#tob").val(),
        "tel_num": $("#tel_num").val() + "-" + $("#tel_num2").val() + "-" + $("#tel_num3").val(),
        "zipcode": $("#zipcode").val(),
        "addr": $("#addr").val(),
        "addr_detail": $("#addr_detail").val(),
        "mng_name": $("#mng_name").val(),
        "posi": $("#posi").val(),
        "mail": $("#mail").val() + "@" + $("#mail2").val()
    };
    var callback = function (result) {
        alert(result);
        registrationSearch(10, 1, "POP");
        return false;
    };

    showMask();
    ajaxCall(url, "html", data, callback);

}

var del = function (seq) {

    showMask();

    var url = "/proc/mypage/registration_list/del_registration.php";
    var data = {
        "seq": seq
    };
    var callback = function (result) {
        alert(result);
        registrationSearch(10, 1, "POP");
        return false;
    };

    showMask();
    ajaxCall(url, "html", data, callback);

}
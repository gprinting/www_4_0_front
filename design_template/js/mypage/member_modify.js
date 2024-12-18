$(document).ready(function () {
    //lnb 선택 효과
    mypageLnbEffect();

    //전화번호에 숫자만 입력 가능
    numKeyCheck('tel_num2');
    numKeyCheck('tel_num3');
    numKeyCheck('cel_num2');
    numKeyCheck('cel_num3');
    numKeyCheck('co_tel_num2');
    numKeyCheck('co_tel_num3');

    //이전 이메일 도메인 보여줌
    var preDomain = $("#pre_domain").val();
    if (!checkBlank(preDomain)) {
        $("#email_domain").val(preDomain);
    }

    //reply to email
    replyToEmail();

    if ($("#member_dvs").val() == "기업 회원") {
        $("#wd_yn_html").hide();
        $("#occu_html").hide();

    } else {
        $("#wd_anniv").datepicker({
            dateFormat: 'yy-mm-dd',
            prevText: '이전 달',
            nextText: '다음 달',
            monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
            monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
            dayNames: ['일', '월', '화', '수', '목', '금', '토'],
            dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
            dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
            showMonthAfterYear: true,
            yearSuffix: '년'
        });

        //결혼 유무 확인
        if ($("#pre_wd_yn").val() == "N") {
            $("#wd_anniv").attr('readonly', true);
        }

        //회원직업
        var occu1 = $("#pre_occu1").val();
        var occu2 = $("#pre_occu2").val();
        setOccu1();
        if (!checkBlank(occu1)) {
            $("#occu1").val(occu1);
        }
        setOccu2();
        if (!checkBlank(occu2)) {
            $("#occu2").val(occu2);
        }
    }


    //복수거래 업체
    var plural_deal_site_name1 = $("#pre_plural_deal_site_name1").val();
    var plural_deal_site_name2 = $("#pre_plural_deal_site_name2").val();
    $("#plural_deal_site_name1").val(plural_deal_site_name1);
    $("#plural_deal_site_name2").val(plural_deal_site_name2);

    $('.main .byStatus._toggle span.num').on('click', function () {
        $(this).prev('button').click();
    });

    $('.memberInfo .taste ._marriage input[type=radio]').on('click', function () {
        if ($(this).val() == 'N') {
            $('._marriageDate input').attr('readonly', true);
        } else {
            $('._marriageDate input').attr('readonly', false);
        }
    });

    $('.memberInfo .taste ._printBusiness input[type=radio]').on('click', function () {
        if ($(this).val() == 'Y') {
            $('._printBusinessDetail').find('select, input[type=text]').attr('disabled', false);
        } else {
            $('._printBusinessDetail').find('select, input[type=text]').attr('disabled', true);
        }
    });
});

//기업회원으로 변경
var changeMemberDvs = function () {}

/*
//사용OS 변경시 프로그램 호출
var changeOs = function(val, member_seqno) {

    var data = {
        "use_oper_sys" : val,
        "member_seqno" : member_seqno
    }
    var url = "/ajax/mypage/member_modify/load_use_pro.php";
    var callback = function(result) {
        $("#use_pro_list").html(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
*/

/**
 * @brief 비밀번호 변경
 */
var modiPw = function () {

    var exi_pw = $("#exi_pw").val();
    var new_pw = $("#new_pw").val();
    var new_pw_re = $("#new_pw_re").val();

    if (checkBlank(new_pw)) {
        alert("새로운 비밀번호를 입력 해주세요.");
        $("#new_pw").focus();
        return false;
    }

    if (new_pw.indexOf(' ') != "-1") {
        alert("비밀번호에는 공백이 들어갈 수 없습니다.");
        $("#new_pw").focus();
        return false;
    }

    if (new_pw.length < 8) {
        alert("비밀번호는 8자 이상 입력하셔야 됩니다.");
        $("#new_pw").focus();
        return false;
    }

    if (checkBlank(new_pw_re)) {
        alert("새로운 비밀번호 확인을 입력 해주세요.");
        $("#new_pw_re").focus();
        return false;
    }

    if (new_pw != new_pw_re) {
        alert("비밀번호와 비밀번호 확인 의 비밀 번호가 일치하지 않습니다.");
        return false;
    }

    var data = {
        "exi_passwd": exi_pw,
        "new_passwd": new_pw,
        "member_seqno": $("#member_seqno").val()
    };

    var url = "/proc/mypage/member_modify/modi_password.php";
    var callback = function (result) {
        hideMask();
        if (result == 1) {
            $("#exi_pw").val("");
            $("#new_pw").val("");
            $("#new_pw_re").val("");
            alert("비밀번호를 변경하였습니다.");
        } else if (result == 2) {
            alert("기존 비밀번호가 일치하지 않습니다.");
        } else {
            alert("비밀번호 변경을 실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//체크박스 value 설정
var interValue = function (id) {

    if ($("input:checkbox[id='" + id + "']").is(":checked") == true) {
        return "Y";
    } else {
        return "N";
    }
}

/**
 * @brief 상세내용 수정 (삭제 - 입력)
 */
var modiMemberDetailInfo = function () {

    //공백체크
    var arr = [
        "email_addr", "email_domain", "tel_num1", "tel_num2", "tel_num3", "cel_num1", "cel_num2", "cel_num3", "zipcode", "addr", "addr_detail"
    ];

    var msg = [
        "개인 가입정보 이메일을 입력해주세요.", "개인 가입정보 이메일을 입력해주세요.", "개인 가입정보 전화번호를 입력해주세요.", "개인 가입정보 전화번호를 입력해주세요.", "개인 가입정보 전화번호를 입력해주세요.", "개인 가입정보 휴대전화를 입력해주세요.", "개인 가입정보 휴대전화를 입력해주세요.", "개인 가입정보 휴대전화를 입력해주세요.", "개인 가입정보 우편번호를 입력해주세요.", "개인 가입정보 주소를 입력해주세요.", "개인 가입정보 주소상세를 입력해주세요."
    ];

    if ($("#member_dvs").val() === "기업회원") {
        arr.push("repre_name");
        arr.push("bc");
        arr.push("tob");
        arr.push("co_zipcode");
        arr.push("co_addr");
        arr.push("co_addr_detail");

        msg.push("사업자 등록정보 대표자를 입력해주세요.");
        msg.push("사업자 등록정보 업태를 입력해주세요.");
        msg.push("사업자 등록정보 업종을 입력해주세요.");
        msg.push("사업자 등록정보 우편번호를 입력해주세요.");
        msg.push("사업자 등록정보 주소를 입력해주세요.");
        msg.push("사업자 등록정보 주소상세를 입력해주세요.");
    }
    for (var i = 0; i < arr.length; i++) {
        if (checkBlank($.trim($("#" + arr[i]).val()))) {
            $("#" + arr[i]).focus();
            alert(msg[i]);
            return false;
        }
    }

    var data = {
        "member_seqno": $("#member_seqno").val(),
        "member_dvs": $("#member_dvs").val()
    };

    //개인 가입정보
    data.mail = $("#email_addr").val() + "@" +
        $("#email_domain").val();
    data.tel_num = $("#tel_num1").val() + "-" +
        $("#tel_num2").val() + "-" +
        $("#tel_num3").val();
    data.cell_num = $("#cel_num1").val() + "-" +
        $("#cel_num2").val() + "-" +
        $("#cel_num3").val();
    data.zipcode = $("#zipcode").val();
    data.addr = $("#addr").val();
    data.addr_detail = $("#addr_detail").val();
    data.basic_addr_yn = $('input[name="basic_addr_yn"]:checked').val();
    data.mailing_yn = $('input[name="mailing_yn"]:checked').val();
    data.sms_yn = $('input[name="sms_yn"]:checked').val();

    //사업자 등록정보
    data.repre_name = $("#repre_name").val();
    data.corp_name = $("#corp_name").val();
    data.crn = $("#crn").val();
    data.bc = $("#bc").val();
    data.tob = $("#tob").val();
    /*
    data.co_tel_num = $("#co_tel_num1").val() + "-" + 
        $("#co_tel_num2").val() + "-" + 
        $("#co_tel_num3").val();
	*/
    data.co_zipcode = $("#co_zipcode").val();
    data.co_addr = $("#co_addr").val();
    data.co_addr_detail = $("#co_addr_detail").val();

    if ($("#member_dvs").val() != "기업 회원") {

        //성향정보
        data.occu1 = $("#occu1").val();
        data.occu2 = $("#occu2").val();
        data.occu_detail = $("#occu_detail").val();

    }
    data.interest_field1 = $("#interest_field1").val();
    data.interest_field2 = $("#interest_field2").val();
    data.interest_field_detail = $("#interest_field_detail").val();

    data.inter_prdt1 = interValue("inter_prdt1");
    data.inter_prdt2 = interValue("inter_prdt2");
    data.inter_prdt3 = interValue("inter_prdt3");
    data.inter_prdt4 = interValue("inter_prdt4");
    data.inter_prdt5 = interValue("inter_prdt5");
    data.inter_prdt6 = interValue("inter_prdt6");
    data.inter_prdt7 = interValue("inter_prdt7");
    data.inter_prdt8 = interValue("inter_prdt8");
    data.inter_prdt9 = interValue("inter_prdt9");
    data.inter_prdt10 = interValue("inter_prdt10");
    data.inter_prdt11 = interValue("inter_prdt11");
    data.inter_prdt12 = interValue("inter_prdt12");

    data.inter_design1 = interValue("inter_design1");
    data.inter_design2 = interValue("inter_design2");
    data.inter_design3 = interValue("inter_design3");
    data.inter_design4 = interValue("inter_design4");
    data.inter_design5 = interValue("inter_design5");
    data.inter_design6 = interValue("inter_design6");

    data.interest_prior = $(':radio[name="interest_prior"]:checked').val(),

        data.inter_event1 = interValue("inter_event1");
    data.inter_event2 = interValue("inter_event2");
    data.inter_event3 = interValue("inter_event3");
    data.inter_event4 = interValue("inter_event4");
    data.inter_event5 = interValue("inter_event5");

    data.inter_needs1 = interValue("inter_needs1");
    data.inter_needs2 = interValue("inter_needs2");
    data.inter_needs3 = interValue("inter_needs3");
    data.inter_needs4 = interValue("inter_needs4");
    data.inter_needs5 = interValue("inter_needs5");
    data.inter_needs6 = interValue("inter_needs6");
    data.inter_needs7 = interValue("inter_needs7");
    data.inter_needs8 = interValue("inter_needs8");
    data.inter_needs9 = interValue("inter_needs9");
    data.inter_needs10 = interValue("inter_needs10");

    data.add_interest_items = $("#add_interest_items").val();
    data.design_outsource_yn = $('input[name="design_outsource_yn"]:checked').val();
    data.produce_outsource_yn = $('input[name="produce_outsource_yn"]:checked').val();
    data.plural_deal_yn = $('input[name="plural_deal_yn"]:checked').val();

    if ($('input[name="plural_deal_yn"]:checked').val() == "N") {
        data.plural_deal_site_name1 = "";
        data.plural_deal_site_detail1 = "";
        data.plural_deal_site_name2 = "";
        data.plural_deal_site_detail2 = "";
    } else {
        data.plural_deal_site_name1 = $("#plural_deal_site_name1").val();
        data.plural_deal_site_detail1 = $("#plural_deal_site_detail1").val();
        data.plural_deal_site_name2 = $("#plural_deal_site_name2").val();
        data.plural_deal_site_detail2 = $("#plural_deal_site_detail2").val();
    }

    data.memo = $("#memo").val();

    var url = "/proc/mypage/member_modify/modi_member_detail_info.php";

    var callback = function (result) {
        if (result == "1") {
            location.reload();
            alert("수정 되었습니다.");
        } else {
            alert("수정을 실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//주문담당자 리스트 호출
var getOrderMng = function () {

    var data = {
        "member_seqno": $("#member_seqno").val()
    };
    var url = "/ajax/mypage/member_modify/load_order_mng_list.php";
    var callback = function (result) {
        $("#order_mng_list").html(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//주문담당자 추가
var regiOrderMng = function () {

    //아이디 중복체크 여부
    var id_over_yn = $("#id_over_yn").val();
    if (id_over_yn != "Y") {
        alert("아이디 중복 확인을 해주세요.");
        return false;
    }

    //아이디 길이 체크
    var id = $("#member_id").val();
    if (id.length < 7) {
        alert("아이디는 7자 이상 입력하셔야 됩니다.");
        $("#pop_member_id").focus();
        return false;
    }

    //특수문자 
    var special_pattern = /[`~!@#$%^&*|\\\'\";:\/?]/gi;
    if (special_pattern.test(id) == true) {
        alert("아이디가 올바르지 않습니다.");
        $("#pop_member_id").focus();
        return false;
    }

    //비밀번호 체크
    var ps = $("#pop_passwd").val();
    if (ps.length < 8) {
        alert("비밀번호는 8자 이상 입력하셔야 됩니다.");
        $("#pop_passwd").focus();
        return false;
    }

    if (ps.indexOf(' ') != "-1") {
        alert("비밀번호에는 공백이 들어갈 수 없습니다.");
        $("#new_pw").focus();
        return false;
    }

    //비밀번호 확인
    var psr = $("#pop_passwd_re").val();
    if (ps != psr) {
        alert("비밀번호와 비밀번호 확인이 일치 하지 않습니다.");
        $("#passwd_re").focus();
        return false;
    }

    //공백체크
    var arr = new Array();
    var msg = new Array();
    arr[0] = "pop_member_name";
    arr[1] = "member_id";
    arr[2] = "pop_passwd";
    arr[3] = "pop_passwd_re";
    arr[4] = "pop_tel_num1";
    arr[5] = "pop_tel_num2";
    arr[6] = "pop_tel_num3";
    arr[7] = "pop_cel_num1";
    arr[8] = "pop_cel_num2";
    arr[9] = "pop_cel_num3";
    arr[10] = "pop_email_addr";
    arr[11] = "pop_email_domain";

    msg[0] = "회원명을 입력해주세요.";
    msg[1] = "아이디를 입력해주세요.";
    msg[2] = "비밀번호를 입력해주세요.";
    msg[3] = "비밀번호 확인을 입력해주세요.";
    msg[4] = "전화번호를 입력해주세요.";
    msg[5] = "전화번호를 입력해주세요.";
    msg[6] = "전화번호를 입력해주세요.";
    msg[7] = "휴대전화를 입력해주세요.";
    msg[8] = "휴대전화를 입력해주세요.";
    msg[9] = "휴대전화를 입력해주세요.";
    msg[10] = "이메일을 입력해주세요.";
    msg[11] = "이메일을 입력해주세요.";

    for (var i = 0; i < arr.length; i++) {
        if (checkBlank($.trim($("#" + arr[i]).val()))) {
            $("#" + arr[i]).focus();
            alert(msg[i]);
            return false;
        }
    }

    var data = {
        "member_name": $("#pop_member_name").val(),
        "member_id": $("#member_id").val(),
        "passwd": $("#pop_passwd").val(),
        "tel_num": $("#pop_tel_num1").val() + "-" +
            $("#pop_tel_num2").val() + "-" +
            $("#pop_tel_num3").val(),
        "cell_num": $("#pop_cel_num1").val() + "-" +
            $("#pop_cel_num2").val() + "-" +
            $("#pop_cel_num3").val(),
        "mail": $("#pop_email_addr").val() + "@" +
            $("#pop_email_domain").val()
    };

    var url = "/proc/mypage/member_modify/regi_order_mng.php";
    var callback = function (result) {
        if (result == "1") {
            $("#order_close").click();
            getOrderMng();
            alert("추가 되었습니다.");
        } else {
            alert("추가를 실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//주문담당자 수정
var modiOrderMng = function (seqno) {

    //비밀번호 체크
    var ps = $("#pop_passwd").val();
    if (ps.length < 8) {
        alert("비밀번호는 8자 이상 입력하셔야 됩니다.");
        $("#pop_passwd").focus();
        return false;
    }

    //비밀번호 확인
    var psr = $("#pop_passwd_re").val();
    if (ps != psr) {
        alert("비밀번호와 비밀번호 확인이 일치 하지 않습니다.");
        $("#passwd_re").focus();
        return false;
    }

    //공백체크
    var arr = new Array();
    var msg = new Array();
    arr[0] = "pop_member_name";
    arr[1] = "pop_posi";
    arr[2] = "pop_passwd";
    arr[3] = "pop_passwd_re";
    arr[4] = "pop_tel_num1";
    arr[5] = "pop_tel_num2";
    arr[6] = "pop_tel_num3";
    arr[7] = "pop_cel_num1";
    arr[8] = "pop_cel_num2";
    arr[9] = "pop_cel_num3";
    arr[10] = "pop_email_addr";
    arr[11] = "pop_email_domain";

    msg[0] = "회원명을 입력해주세요.";
    msg[1] = "직급을 입력해주세요.";
    msg[2] = "비밀번호를 입력해주세요.";
    msg[3] = "비밀번호 확인을 입력해주세요.";
    msg[4] = "전화번호를 입력해주세요.";
    msg[5] = "전화번호를 입력해주세요.";
    msg[6] = "전화번호를 입력해주세요.";
    msg[7] = "휴대전화를 입력해주세요.";
    msg[8] = "휴대전화를 입력해주세요.";
    msg[9] = "휴대전화를 입력해주세요.";
    msg[10] = "이메일을 입력해주세요.";
    msg[11] = "이메일을 입력해주세요.";

    for (var i = 0; i < arr.length; i++) {
        if (checkBlank($.trim($("#" + arr[i]).val()))) {
            $("#" + arr[i]).focus();
            alert(msg[i]);
            return false;
        }
    }

    var data = {
        "member_seqno": seqno,
        "member_name": $("#pop_member_name").val(),
        "posi": $("#pop_posi").val(),
        "passwd": $("#pop_passwd").val(),
        "tel_num": $("#pop_tel_num1").val() + "-" +
            $("#pop_tel_num2").val() + "-" +
            $("#pop_tel_num3").val(),
        "cell_num": $("#pop_cel_num1").val() + "-" +
            $("#pop_cel_num2").val() + "-" +
            $("#pop_cel_num3").val(),
        "mail": $("#pop_email_addr").val() + "@" +
            $("#pop_email_domain").val()
    };

    var url = "/proc/mypage/member_modify/modi_order_mng.php";
    var callback = function (result) {
        if (result == "1") {
            $("#order_close").click();
            getOrderMng();
            alert("수정 되었습니다.");
        } else {
            alert("수정을 실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//주문담당자 삭제
var delOrderMng = function (seqno) {

    var data = {
        "member_seqno": seqno
    };

    var url = "/proc/mypage/member_modify/del_order_mng.php";
    var callback = function (result) {
        if (result == "1") {
            $("#order_close").click();
            getOrderMng();
            alert("삭제 되었습니다.");
        } else {
            alert("삭제를 실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//회계담당자 리스트 호출
var getAccMng = function () {

    var data = {
        "member_seqno": $("#member_seqno").val()
    };
    var url = "/ajax/mypage/member_modify/load_acc_mng_list.php";
    var callback = function (result) {
        $("#accting_mng_list").html(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//회계담당자 추가
var regiAccMng = function () {

    //공백체크
    var arr = new Array();
    var msg = new Array();
    arr[0] = "acc_name";
    arr[1] = "acc_posi";
    arr[2] = "acc_tel_num1";
    arr[3] = "acc_tel_num2";
    arr[4] = "acc_tel_num3";
    arr[5] = "acc_cel_num1";
    arr[6] = "acc_cel_num2";
    arr[7] = "acc_cel_num3";
    arr[8] = "acc_email_addr";
    arr[9] = "acc_email_domain";

    msg[0] = "회원명을 입력해주세요.";
    msg[1] = "직급을 입력해주세요.";
    msg[2] = "전화번호를 입력해주세요.";
    msg[3] = "전화번호를 입력해주세요.";
    msg[4] = "전화번호를 입력해주세요.";
    msg[5] = "휴대전화를 입력해주세요.";
    msg[6] = "휴대전화를 입력해주세요.";
    msg[7] = "휴대전화를 입력해주세요.";
    msg[8] = "이메일을 입력해주세요.";
    msg[9] = "이메일을 입력해주세요.";

    for (var i = 0; i < arr.length; i++) {
        if (checkBlank($.trim($("#" + arr[i]).val()))) {
            $("#" + arr[i]).focus();
            alert(msg[i]);
            return false;
        }
    }

    var data = {
        "name": $("#acc_name").val(),
        "posi": $("#acc_posi").val(),
        "tel_num": $("#acc_tel_num1").val() + "-" +
            $("#acc_tel_num2").val() + "-" +
            $("#acc_tel_num3").val(),
        "cell_num": $("#acc_cel_num1").val() + "-" +
            $("#acc_cel_num2").val() + "-" +
            $("#acc_cel_num3").val(),
        "mail": $("#acc_email_addr").val() + "@" +
            $("#acc_email_domain").val()
    };

    var url = "/proc/mypage/member_modify/regi_acc_mng.php";
    var callback = function (result) {
        if (result == "1") {
            $("#acc_close").click();
            getAccMng();
            alert("추가 되었습니다.");
        } else {
            alert("추가를 실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//회계담당자 수정
var modiAccMng = function (seqno) {

    //공백체크
    var arr = new Array();
    var msg = new Array();
    arr[0] = "acc_name";
    arr[1] = "acc_posi";
    arr[2] = "acc_tel_num1";
    arr[3] = "acc_tel_num2";
    arr[4] = "acc_tel_num3";
    arr[5] = "acc_cel_num1";
    arr[6] = "acc_cel_num2";
    arr[7] = "acc_cel_num3";
    arr[8] = "acc_email_addr";
    arr[9] = "acc_email_domain";

    msg[0] = "회원명을 입력해주세요.";
    msg[1] = "직급을 입력해주세요.";
    msg[2] = "전화번호를 입력해주세요.";
    msg[3] = "전화번호를 입력해주세요.";
    msg[4] = "전화번호를 입력해주세요.";
    msg[5] = "휴대전화를 입력해주세요.";
    msg[6] = "휴대전화를 입력해주세요.";
    msg[7] = "휴대전화를 입력해주세요.";
    msg[8] = "이메일을 입력해주세요.";
    msg[9] = "이메일을 입력해주세요.";

    for (var i = 0; i < arr.length; i++) {
        if (checkBlank($.trim($("#" + arr[i]).val()))) {
            $("#" + arr[i]).focus();
            alert(msg[i]);
            return false;
        }
    }

    var data = {
        "seqno": seqno,
        "name": $("#acc_name").val(),
        "posi": $("#acc_posi").val(),
        "tel_num": $("#acc_tel_num1").val() + "-" +
            $("#acc_tel_num2").val() + "-" +
            $("#acc_tel_num3").val(),
        "cell_num": $("#acc_cel_num1").val() + "-" +
            $("#acc_cel_num2").val() + "-" +
            $("#acc_cel_num3").val(),
        "mail": $("#acc_email_addr").val() + "@" +
            $("#acc_email_domain").val()
    };

    var url = "/proc/mypage/member_modify/modi_acc_mng.php";
    var callback = function (result) {
        if (result == "1") {
            $("#acc_close").click();
            getAccMng();
            alert("수정 되었습니다.");
        } else {
            alert("수정을 실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//회계담당자 삭제
var delAccMng = function (seqno) {

    var data = {
        "accting_mng_seqno": seqno
    };

    var url = "/proc/mypage/member_modify/del_acc_mng.php";
    var callback = function (result) {
        if (result == "1") {
            $("#acc_close").click();
            getAccMng();
            alert("삭제 되었습니다.");
        } else {
            alert("삭제를 실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var setOccu1 = function () {
    var occu1arr = ["경영,사무", "마케팅,무역,유통", "영업,고객상담", "IT,인터넷", "연구개발,설계", "생산,제조", "전문,특수직", "디자인", "미디어"];
    var htmls = "";

    for (var i = 0; i < occu1arr.length; i++) {
        htmls += "<option value=\"" + occu1arr[i] + "\">" + occu1arr[i] + "</option>";
    }

    $("#occu1").html(htmls);
}

var setOccu2 = function () {
    var occu2arr = new Array();
    //경영,사무
    occu2arr[0] = ["자동차,조선,기계", "반도체,디스플레이", "화학,에너지,환경,식품", "전기,전자,제어", "기계설계,CAD,CAM", "통신기술,네트워크구축", "건설,설계,인테리어"];

    //마케팅,무역,유통
    occu2arr[1] = ["생산관리,공정관리,품질관리", "생산,제조,설비,조립", "포장,가공,검사", "설치,정비,A/S", "시공,현장,공무", "시설,빌딩,안전"];

    //영업,고객상담
    occu2arr[2] = ["제품,서비스영업", "금융,보험영업", "광고영업", "기술영업", "영업관리,지원", "법인영업", "채권,심사", "판매,캐셔,매장관리", "이벤트,웨딩,나레이터", "단순홍보,회원관리", "교육상담,학원관리", "아웃바운드TM", "고객센터,인바운드,CS", "부동산,창업"];

    //IT,인터넷
    occu2arr[3] = ["QA,테스터,검증", "네트워크,서버,보안,DBA", "웹기획,웹마케팅,PM", "웹프로그래머", "응용프로그래머", "시스템프로그래머", "SE,시스템분석,설계", "웹디자인", "HTML,웹표준,컨텐츠관리", "웹사이트운영", "IT,디자인,컴퓨터강사"];

    //연구개발,설계
    occu2arr[4] = ["자동차,조선,기계", "반도체,디스플레이", "화학,에너지,환경,식품", "전기,전자,제어", "기계설계,CAD,CAM", "통신기술,네트워크구축", "건설,설계,인테리어"];

    //생산,제조
    occu2arr[5] = ["생산관리,공정관리,품질관리", "생산,제조,설비,조립", "포장,가공,검사", "설치,정비,A/S", "시공,현장,공무", "시설,빌딩,안전"];

    //전문,특수직
    occu2arr[6] = ["경영분석,컨설턴트", "리서치,통계,사서", "외국어,번역,통역", "법률,특허,상표", "회계,세무", "보안,경비,경호", "의사,약사,간호사", "중고등 교사,강사", "초등,유치원,보육교사", "외국어,자격증,기술강사", "IT,디자인,학원강사", "뷰티미용,애완,스포츠", "요리,영양,제과제빵", "학습지,방문교사", "사회복지,요양보호,자원봉사", "노무,헤드헌터,직업상담"];

    //디자인
    occu2arr[7] = ["그래픽디자인,CG", "출판,편집디자인", "제품,산업디자인", "캐릭터,애니메이션", "광고,시각디자인", "건축,인테리어디자인", "의류,패션,잡화디자인"];

    //미디어
    occu2arr[8] = ["연출,제작,PD", "아나운서,리포터,성우", "영상,카메라,촬영", "기자", "작가,시나리오", "연예,매니저", "음악,음향", "광고제작,카피", "무대,스텝,오퍼레이터"];

    var htmls = "";
    var occu1cnt = $("#occu1 option").index($("#occu1 option:selected"));
    var occu2 = occu2arr[occu1cnt];

    for (var i = 0; i < occu2.length; i++) {
        htmls += "<option value=\"" + occu2[i] + "\">" + occu2[i] + "</option>";
    }

    $("#occu2").html(htmls);
}

//reply to email
function replyToEmail(target) {
    if (target == null || target == undefined) {
        target = $('body');
    }

    target.find('._replyToEmail').each(function () {
        var id = $(this).find('._id');
        var domain = $(this).find('._domain');
        var preset = $(this).find('select');

        preset.on('change', function () {
            onDomain();
        });

        function onDomain() {
            if (preset.find('option:selected').hasClass('_custom')) {
                domain.attr('readonly', false);
                domain.val('');
            } else {
                domain.attr('readonly', true);
                domain.val(preset.find('option:selected').text());
            }
        }
    });
}

var showMemberDetail = function () {

    if ($("#member_det").is(":visible") === true) {
        $(".sub_title option_title").show();
        $(".sub_title option_title_on").hide();
        $("#member_det").hide();
    } else {
        $(".sub_title option_title").hide();
        $(".sub_title option_title_on").show();
        $("#member_det").show();
    }

}

/**  
 * @brief 소셜로그인 연동
 *
 * @param dvs : 해당 채널
 */
var linkSocAcc = function (dvs) {

    switch (dvs) {
        case "naver":
            popup = window.open('/oauth/link_redir.php?dvs=naver', '', 'width=500, height=760, scrollbars=no');
            break;
        case "kakao":
            kakaoAccConnect();
            break;
        case "fb":
            fbAccConnect();
            break;
        case "google":
            popup = window.open('/oauth/link_redir.php?dvs=google', '', 'width=500, height=760, scrollbars=no');
            break;

    }
}

/**
 * @brief 소셜로그인 연동 해제
 * 
 * @param dvs : 해당 채널
 */
var delSocAcc = function (dvs) {

    var url = "/proc/mypage/member_modify/del_member_sub_id.php";
    var data = {
        "channel": dvs
    };
    var callback = function (result) {
        if (result == "1") {
            alert("연동해제 되었습니다.");
            location.reload();
        } else {
            alert("연동 해제에 실패 하였습니다. 관리자에게 문의하세요.");
        }
    };

    if (confirm("해당 소셜계정의 연동이 해제됩니다. 계속하시겠습니까?") === false) {
        return false;
    }

    ajaxCall(url, "html", data, callback);

}

/**
 * @brief 카카오 계정연동
 */
var kakaoAccConnect = function () {
    Kakao.Auth.login({
        success: function (authObj) {
            Kakao.API.request({
                url: '/v1/user/me',
                success: function (res) {
                    res["dvs"] = "link";
                    $.ajax({
                        type: "POST",
                        url: "/oauth/oauth_callback_kakao.php",
                        data: res,
                        success: function (result) {
                            if (checkBlank(result)) {
                                location.reload();
                            } else if (result == "sus1") {
                                alert("연동아이디가 입력 되었습니다.");
                                location.reload();
                            } else {
                                return alertReturnFalse(result);
                            }
                        }
                    });
                },
                fail: function (error) {
                    alert(JSON.stringify(error));
                }
            });
        },
        fail: function (err) {
            alert(JSON.stringify(err));
        }
    });
}

/**
 * @brief 소셜계정 회원탈퇴 시 페이지 이동
 */
var goWithdraw = function () {
    if (confirm("회원탈퇴 페이지로 이동합니다. 계속하시겠습니까?") === false) {
        return false;
    }
    window.location.href = "/mypage/member_quit.html";
}
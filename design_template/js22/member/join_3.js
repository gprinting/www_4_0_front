/*본인인증
 var getCerti = function(dvs) {

 alert("본인인증 없음");
 return false;
 //회원 아이디 가져옴
 var id = $("#member_id").val();

 showMask();
 var url = "/";
 var data = {};
 var callback = function(result) {
 }

 ajaxCall(url, "html", data, callback);

 $("#certi_yn").val("Y");
 alert("본인인증이 되었습니다.");
 }
 */

//다음 페이지 이동
var goPage = function () {

    //아이디 중복체크 여부
    var id_over_yn = $("#id_over_yn").val();
    if (id_over_yn != "Y") {
        alert("아이디 중복 확인을 해주세요.");
        return false;
    }

    //아이디 유효성 검사
    var id = $("#member_id").val();
    var id_pattern = /^[a-z0-9_-]{8,20}$/;
    if (!id_pattern.test(id)) {
        alert("아이디가 올바르지 않습니다.");
        $("#member_id").focus();
        return false;
    }

    //비밀번호 체크
    var ps = $("#passwd").val();
    if (ps.length < 8) {
        alert("비밀번호는 8자 이상 입력하셔야 됩니다.");
        $("#passwd").focus();
        return false;
    }

    //비밀번호 확인
    var psr = $("#passwd_re").val();
    if (ps != psr) {
        alert("비밀번호와 비밀번호 확인이 일치 하지 않습니다.");
        $("#passwd_re").focus();
        return false;
    }


    if ($("#dvs").val() === "기업") {
        //업체명 체크
        var corp_name = $("#corp_name").val();
        if (corp_name.trim() === "") {
            alert("업체명(상호)을 입력해주세요.");
            $("#corp_name").focus();
            return false;
        }

        //사업자번호 체크
        var crn1 = $("#crn1").val();
        var crn2 = $("#crn2").val();
        var crn3 = $("#crn3").val();

        if (crn1.trim() === "") {
            alert("사업자번호를 입력해주세요.");
            $("#crn1").focus();
            return false;
        }
        if (crn2.trim() === "") {
            alert("사업자번호를 입력해주세요.");
            $("#crn2").focus();
            return false;
        }
        if (crn3.trim() === "") {
            alert("사업자번호를 입력해주세요.");
            $("#crn3").focus();
            return false;
        }

        //대표자
        var repre_name = $("#repre_name").val();
        if (repre_name.trim() === "") {
            alert("대표자를 입력해주세요.");
            $("#repre_name").focus();
            return false;
        }

        //생년 체크
        var birth_year = $("#birth_year").val();
        if (birth_year === "") {
            alert("생년월일 월을 선택해주세요.");
            $("#birth_year").focus();
            return false;
        }
        //월 체크
        var birth_month = $("#birth_month").val();
        if (birth_month === "") {
            alert("생년월일 월을 선택해주세요.");
            $("#birth_month").focus();
            return false;
        }

        //일 체크
        var birth_day = $("#birth_day").val();
        if (birth_day === "") {
            alert("생년월일 일을 선택해주세요.");
            $("#birth_day").focus();
            return false;
        }

        //업태
        var bc = $("#bc").val();
        if (bc.trim() === "") {
            alert("업태를 입력해주세요.");
            $("#bc").focus();
            return false;
        }

        //업종
        var tob = $("#tob").val();
        if (tob.trim() === "") {
            alert("업종을 입력해주세요.");
            $("#tob").focus();
            return false;
        }
    } else {
        //회원명 체크
        var member_name = $("#member_name").val();
        if (member_name.trim() === "") {
            alert("회원명을 입력해주세요.");
            $("#member_name").focus();
            return false;
        }

        //생년 체크
        var birth_year = $("#birth_year").val();
        if (birth_year === "") {
            alert("생년월일 월을 선택해주세요.");
            $("#birth_year").focus();
            return false;
        }
        //월 체크
        var birth_month = $("#birth_month").val();
        if (birth_month === "") {
            alert("생년월일 월을 선택해주세요.");
            $("#birth_month").focus();
            return false;
        }

        //일 체크
        var birth_day = $("#birth_day").val();
        if (birth_day === "") {
            alert("생년월일 일을 선택해주세요.");
            $("#birth_day").focus();
            return false;
        }
    }

    //이메일 체크
    var email_addr = $("#email_addr").val();
    if (email_addr === "") {
        alert("이메일을 입력해주세요.");
        $("#email_addr").focus();
        return false;
    }

    var domain_pattern = /^[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*\.[a-zA-Z]{2,3}$/i;
    var email_domain = $("#email_domain").val();
    if (domain_pattern.test(email_domain) == false) {
        alert("이메일 도메인이 올바르지 않습니다.");
        $("#email_domain").focus();
        return false;
    }

    //전화번호 체크
    var num_pattern = /^[0-9]*$/;
    var tel_num2 = $("#tel_num2").val();
    var tel_num3 = $("#tel_num3").val();
    if (num_pattern.test(tel_num2) == false) {
        alert("전화번호가 올바르지 않습니다.");
        $("#tel_num2").focus();
        return false;
    }
    if (num_pattern.test(tel_num3) == false) {
        alert("전화번호가 올바르지 않습니다.");
        $("#tel_num3").focus();
        return false;
    }

    //휴대전화 체크
    var cel_num2 = $("#cel_num2").val();
    var cel_num3 = $("#cel_num3").val();
    if (num_pattern.test(cel_num2) == false) {
        alert("휴대전화가 올바르지 않습니다.");
        $("#cel_num2").focus();
        return false;
    }
    if (num_pattern.test(cel_num3) == false) {
        alert("휴대전화가 올바르지 않습니다.");
        $("#cel_num3").focus();
        return false;
    }

    //공백체크
    var arr = new Array();
    var msg = new Array();
    if ($("#dvs").val() == "기업") {
        arr[0] = "member_id";
        arr[1] = "passwd";
        arr[2] = "passwd_re";
        arr[3] = "corp_name";
        arr[4] = "crn1";
        arr[5] = "crn2";
        arr[6] = "crn3";
        arr[7] = "repre_name";
        arr[8] = "birth_year";
        arr[9] = "birth_month";
        arr[10] = "birth_day";
        arr[11] = "bc";
        arr[12] = "tob";
        arr[13] = "email_addr";
        arr[14] = "email_domain";
        arr[15] = "tel_num1";
        arr[16] = "tel_num2";
        arr[17] = "tel_num3";
        arr[18] = "cel_num1";
        arr[19] = "cel_num2";
        arr[20] = "cel_num3";
        arr[21] = "zipcode";
        arr[22] = "addr";
        arr[23] = "addr_detail";

        msg[0] = "아이디를 입력해주세요.";
        msg[1] = "비밀번호를 입력해주세요.";
        msg[2] = "비밀번호 확인을 입력해주세요.";
        msg[3] = "업체명을 입력해주세요.";
        msg[4] = "사업자등록번호를 입력해주세요.";
        msg[5] = "사업자등록번호를 입력해주세요.";
        msg[6] = "사업자등록번호를 입력해주세요.";
        msg[7] = "대표자를 입력해주세요.";
        msg[8] = "생년월일을 입력 혹은 선택해주세요.";
        msg[9] = "생년월일을 입력 혹은 선택해주세요.";
        msg[10] = "생년월일을 입력 혹은 선택해주세요.";
        msg[11] = "업태를 입력해주세요.";
        msg[12] = "업종을 입력해주세요.";
        msg[13] = "이메일을 입력해주세요.";
        msg[14] = "이메일을 입력해주세요.";
        msg[15] = "전화번호를 입력해주세요.";
        msg[16] = "전화번호를 입력해주세요.";
        msg[17] = "전화번호를 입력해주세요.";
        msg[18] = "휴대전화를 입력해주세요.";
        msg[19] = "휴대전화를 입력해주세요.";
        msg[20] = "휴대전화를 입력해주세요.";
        msg[21] = "우편번호를 입력해주세요.";
        msg[22] = "주소를 입력해주세요.";
        msg[23] = "주소상세를 입력해주세요.";

        //사업자 등록번호 체크
        var crn1 = $("#crn1").val();
        var crn2 = $("#crn2").val();
        var crn3 = $("#crn3").val();
        if (num_pattern.test(crn1) == false) {
            alert("사업자 등록번호가 올바르지 않습니다.");
            $("#crn1").focus();
            return false;
        }
        if (num_pattern.test(crn2) == false) {
            alert("사업자 등록번호가 올바르지 않습니다.");
            $("#crn2").focus();
            return false;
        }
        if (num_pattern.test(crn3) == false) {
            alert("사업자 등록번호가 올바르지 않습니다.");
            $("#crn3").focus();
            return false;
        }

    } else {
        arr[0] = "member_id";
        arr[1] = "passwd";
        arr[2] = "passwd_re";
        arr[3] = "member_name";
        arr[4] = "birth_year";
        arr[5] = "birth_month";
        arr[6] = "birth_day";
        arr[7] = "email_addr";
        arr[8] = "email_domain";
        arr[9] = "tel_num1";
        arr[10] = "tel_num2";
        arr[11] = "tel_num3";
        arr[12] = "cel_num1";
        arr[13] = "cel_num2";
        arr[14] = "cel_num3";
        arr[15] = "zipcode";
        arr[16] = "addr";
        arr[17] = "addr_detail";

        msg[0] = "아이디를 입력해주세요.";
        msg[1] = "비밀번호를 입력해주세요.";
        msg[2] = "비밀번호 확인을 입력해주세요.";
        msg[3] = "회원명을 입력해주세요.";
        msg[4] = "생년월일을 입력 혹은 선택해주세요.";
        msg[5] = "생년월일을 입력 혹은 선택해주세요.";
        msg[6] = "생년월일을 입력 혹은 선택해주세요.";
        msg[7] = "이메일을 입력해주세요.";
        msg[8] = "이메일을 입력해주세요.";
        msg[9] = "전화번호를 입력해주세요.";
        msg[10] = "전화번호를 입력해주세요.";
        msg[11] = "전화번호를 입력해주세요.";
        msg[12] = "휴대전화를 입력해주세요.";
        msg[13] = "휴대전화를 입력해주세요.";
        msg[14] = "휴대전화를 입력해주세요.";
        msg[15] = "우편번호를 입력해주세요.";
        msg[16] = "주소를 입력해주세요.";
        msg[17] = "주소상세를 입력해주세요.";
    }

    for (var i = 0; i < arr.length; i++) {
        if (checkBlank($.trim($("#" + arr[i]).val()))) {
            $("#" + arr[i]).focus();
            alert(msg[i]);
            return false;
        }
    }

    if (checkBlank($(':radio[name="mailing_yn"]:checked').val())) {
        alert("이메일 수신 여부를 선택해주세요.");
        return false;
    }

    if (checkBlank($(':radio[name="sms_yn"]:checked').val())) {
        alert("SMS 수신 여부를 선택해주세요.");
        return false;
    }

    /*본인인증 여부
     var certi_yn = $("#certi_yn").val();
     if (certi_yn != "Y") {
     alert("본인 인증을 해주세요.");
     return false;
     }
     */

    var f = document.frm;
    f.action = "/member/join_4.html";
    f.target = "";
    f.method = "POST";
    f.submit();
    return false;
}

//이전 페이지 이동
var backPage = function () {
    var f = document.frm;
    f.action = "/member/join_2.html";
    f.target = "";
    f.method = "POST";
    f.submit();
    return false;
}

var selectYear = function (year) {
    //초기화
    var html = "<option value=''>선택</option>";
    var day = 0;

    var nowDate = new Date();
    var nowYear = nowDate.getFullYear();
    var selectedYear = $("#birth_year option:selected").val();

    if (nowYear == selectedYear) {
        var nowMonth = nowDate.getMonth() + 1;
        //html 생성
        for (var i = 1; i <= nowMonth; i++) {
            html += "<option value='" + this.LPAD(i.toString(), 2, 0) + "'>";
            html += this.LPAD(i.toString(), 2, 0) + "</option>";
        }
    } else {
        for (var i = 1; i <= 12; i++) {
            html += "<option value='" + this.LPAD(i.toString(), 2, 0) + "'>";
            html += this.LPAD(i.toString(), 2, 0) + "</option>";
        }
    }

    $("#birth_month").empty();
    $("#birth_day").empty();
    $("#birth_day").append("<option value=''>선택</option>");
    $("#birth_month").append(html);
}

//월에 대한 일수 보여주기
var selectMon = function (mon) {
    //초기화
    var html = "<option value=''>선택</option>";
    var day = 0;

    //mon 따른 day 설정
    if (mon === "01" || mon === "03" || mon === "05" || mon === "07" ||
        mon === "08" || mon === "10" || mon === "12") {
        day = 31;
    } else if (mon === "02") {
        //윤달
        day = 29;
    } else {
        day = 30;
    }

    var nowDate = new Date();
    var nowMonth = nowDate.getMonth() + 1;
    var selectedMonth = $("#birth_month option:selected").val();

    if (nowMonth == Number(selectedMonth)) {
        var nowDay = nowDate.getDate();
        //html 생성
        for (var i = 1; i <= nowDay; i++) {
            html += "<option value='" + this.LPAD(i.toString(), 2, 0) + "'>";
            html += this.LPAD(i.toString(), 2, 0) + "</option>";
        }
    } else {
        for (var i = 1; i <= day; i++) {
            html += "<option value='" + this.LPAD(i.toString(), 2, 0) + "'>";
            html += this.LPAD(i.toString(), 2, 0) + "</option>";
        }
    }
    //setting
    $("#birth_day").empty();
    $("#birth_day").append(html);
}

/*
 * originalstr: lpad 할 text
 * length: lpad할 길이
 * strToPad: lpad 시킬 text
 */
var LPAD = function (originalstr, length, strToPad) {
    while (originalstr.length < length) {
        originalstr = strToPad + originalstr;
    }
    return originalstr;
}
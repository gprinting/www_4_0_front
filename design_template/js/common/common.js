/*
var popup = null;
    var interval = null;

        // 메인페이지 소셜로그인
    var showSocialLogin = function(dvs) {
        switch(dvs) {
            case "naver" :
                popup = window.open('/oauth/info_redir.php?dvs=naver','','width=500, height=760, scrollbars=no');
                break;

            case "kakao" :
                loginWithKakao();
                break;

            case "fb" :
                fbLogin();
                break;

            case "google" :
                popup = window.open('/oauth/info_redir.php?dvs=google','','width=500, height=760, scrollbars=no');
                break;
        }
    };

    window.fbAsyncInit = function() {
        FB.init({
            appId   : '1976307912686315',
            cookie  : true,
            xfbml   : true,
            version : 'v2.10'
        });

        FB.AppEvents.logPageView();
    };
    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/ko_KR/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    } (document, 'script', 'facebook-jssdk'));

    var fbLogin = function() {
        var callback = function(res1) {

            FB.api(
                "/me?fields=id,name,email",
                "post",
                {
                    "access_token" : res1.authResponse.accessToken,
                    "fields" : "name,email"
                },
                function(res2) {
                    $.ajax({
                        type     : "POST",
                        url      : "/oauth/oauth_callback_facebook.php",
                        data     : res2,
                        //dataType : "text",
                        success  : function(result) {
                            location.reload();
                        }
                    });
                }
            );

            //FB.logout(function(){});
        };

        FB.login(callback, {"scope" : "public_profile,email"});
    };

    Kakao.init('d8ed4e0171350fa18a476a22ab1c2412');
    function loginWithKakao() {
        Kakao.Auth.login({
            success: function(authObj) {
                Kakao.API.request({
                    url: '/v1/user/me',
                    success: function(res) {
                        $.ajax({
                            type    : "POST",
                            url     : "/oauth/oauth_callback_kakao.php",
                            data    : res,
                            success : function(result) {
                                if (checkBlank(result)) {
                                    location.reload();
                                } else {
                                    return alertReturnFalse(result);
                                }
                            }
                        });
                    },
                    fail: function(error) {
                        alert(JSON.stringify(error));
                    }
                });
            },
            fail: function(err) {
                alert(JSON.stringify(err));
            }
        });
    };
    */
/*
*/

$(document).ready(function () {
    if ($("._date").length > 0) {
        $("._date").on("click", function () {
            $(".preset button").removeClass("on");
        });
    }

    if ($(".date_btn").length > 0) {
        $(".date_btn").on("click", function () {
            $(".date_btn").removeClass("on");
            $(this).addClass("on");
        });
    }

    if ($("#is_login").val() == "1")
        $('body').addClass('login');

    var save_id = getCookie('gp_userid');
    if (save_id != "") {
        $("#id").val(save_id);
        $('input:checkbox[id="login_id_save"]').prop("checked", true);
    } else {
        $('input:checkbox[id="login_id_save"]').prop("checked", false);
    }

    checkMonthlySales();
});

var popupMask = null;
var cscenterPopup = null;
// ajax타이밍 변수(180503 추가)
var ajaxStack = 0;

// html escape 대상 배열
var entityMap = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': '&quot;',
    "'": '&#39;',
    "/": '&#x2F;'
};

/**
 * @brief 문자열에 들어있는 escape대상 문자 변환
 *
 * @param string = 대상 문자열
 */
function escapeHtml(str) {
    return String(str).replace(/[&<>"'\/]/g, function (s) {
        return entityMap[s];
    });
}

// 숫자 타입에서 쓸 수 있도록 format() 함수 추가
Number.prototype.format = function () {
    if (this == 0) return 0;

    var reg = /(^[+-]?\d+)(\d{3})/;
    var n = (this + '');

    while (reg.test(n)) n = n.replace(reg, '$1' + ',' + '$2');

    return n;
};

// 문자열 타입에서 쓸 수 있도록 format() 함수 추가
String.prototype.format = function () {
    var num = parseFloat(this);
    if (isNaN(num)) return "0";

    return num.format();
};

// trim 추가
if (typeof String.prototype.trim !== "function") {
    String.prototype.trim = function () {
        return this.replace(/^\s+|\s+$/g, '');
    }
}


//어떤 값이 공백값이거나 undefined 값이면 false 반환
var checkBlank = function (val) {
    if (val === "" ||
        val === '' ||
        val === null ||
        typeof val === "undefined") {
        return true;
    } else {
        return false;
    }
};

var isFunc = function (funcName) {
    if (typeof (window[funcName]) === "function") {
        return true;
    } else {
        return false;
    }
};

// Ajax Call 공통 함수
// 사용 예제 ajaxCall('호출주소', 'html', {data:data}, callback);
var ajaxCall = function (url, dataType, data, sucCallback) {
    if (checkBlank(url) === true) {
        return false;
    }
    higherAjaxStack();

    $.ajax({
        type: "POST",
        url: url,
        dataType: dataType,
        data: data,
        success: function (result) {
            //hideMask();
            // ajax Stack 감소 함수
            lowerAjaxStack();
            return sucCallback(result);
        },
        error: getAjaxError
    });
};

var ajaxCallNoMask = function (url, dataType, data, sucCallback) {
    if (checkBlank(url) === true) {
        return false;
    }

    $.ajax({
        type: "POST",
        url: url,
        dataType: dataType,
        data: data,
        success: function (result) {
            //hideMask();
            // ajax Stack 감소 함수
            return sucCallback(result);
        },
        error: getAjaxError
    });
};

//Ajax error 공통 함수
var getAjaxError = function (request, status, error) {
    //alert("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
    //console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
    //hideBgMask();
    hideMask();
};

//로딩 중 이미지 보이기
var showMask = function () {
    if ($("#common_layer_popup").length === 0) {
        var layerPopupHTML = '<div id="common_layer_popup" class="popupMask" style="block"><div class="loading"><img src="/design_template/images/common/icon_loading.gif" alt="불러오는 중입니다."></div><div class="layerPopupWrap"><div class="layerPopup"></div></div></div>';
        $('body').append(layerPopupHTML);
    }

    $("#common_layer_popup").fadeIn(300);
}

//로딩 중 이미지 보이기
var showAjaxMask = function () {
    if ($("#common_layer_popup").length === 0) {
        var layerPopupHTML = '<div id="common_layer_popup" class="modalMask" style="z-index:3000; background:url();"><div class="loading"><img src="/design_template/images/common/icon_loading.gif" style="z-index:10;" alt="불러오는 중입니다."></div><div class="layerPopupWrap"><div class="layerPopup"></div></div></div>';
        $('body').append(layerPopupHTML);
    }

    $("#common_layer_popup").show();
}

//로딩 중 이미지 숨기기
var hideMask = function () {
    //$("#common_layer_popup").remove();
    $("#common_layer_popup").fadeOut(300);
}

/**
 * @brief 쿠키 생성
 *
 * @param param = 쿠키생성할 정보
 * @detail param.data = [k1|v1, k3|v3, k2|v2, ...] // 쿠키데이터
 * @detail param.expire = 365 // 쿠키 만료일
 *
 * @return false
 */
function setCookie(cookie_name, value, days) {
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + days);
    var cookie_value = escape(value) + ((days == null) ? '' : ';    expires=' + exdate.toUTCString());
    document.cookie = cookie_name + '=' + cookie_value;
}

function getCookie(cookie_name) {
    var x, y;
    var val = document.cookie.split(';');

    for (var i = 0; i < val.length; i++) {
        x = val[i].substr(0, val[i].indexOf('='));
        y = val[i].substr(val[i].indexOf('=') + 1);
        x = x.replace(/^\s+|\s+$/g, ''); // 앞과 뒤의 공백 제거하기
        if (x == cookie_name) {
            return unescape(y); // unescape로 디코딩 후 값 리턴
        }
    }
}


/**
 * 로그인 처리함수
 */
var login = function () {

    if (checkBlank($("#id").val())) {
        alert("아이디를 입력 해주세요.");
        $("#id").focus();
        return false;
    }

    if (checkBlank($("#pw").val())) {
        alert("비밀번호를 입력 해주세요.");
        $("#pw").focus();
        return false;
    }

    var url = "/common/login.php";
    var data = {
        "id": $("#id").val(),
        "pw": $("#pw").val()
    };

    var save_yn = "N";
    if ($("input:checkbox[id='login_id_save']").is(":checked")) {
        save_yn = "Y";
    }

    data.id_save = save_yn;

    var callback = function (result) {
        if (result.success === false) {
            alert("로그인에 실패했습니다.");
            //location.href = "/member/login.html";
            return false;
        } else {
            if (save_yn == "Y") {
                setCookie('gp_userid', data.id, '100');
                //alert(document.f.userid.value + "저장");
            } else {
                setCookie('gp_userid', '', '100');
                //alert("아이디 저장해제");
            }

            if($("#purlogin").val() == "1") {
                $("#il").val('1');
                hideLoginBox();
                orderNextFunc();
            } else {
                location.href = result.ref;
            }

        }
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 로그인
 */
var loginKey = function (event, el) {

    if (event.keyCode == 13) {
        event.returnValue = false;
        login(el);
    }
};

/**
 * @brief 암호 입력란으로 이동
 */
var idkey = function (event, el) {
    if (event.keyCode == 13) {
        $("#" + el + "pw").focus();
    }
};

/**
 * @brief 로그아웃 처리함수
 */
var logout = function () {
    location.href = "/common/logout.php";
};

/**
 * @brief 주문 요약정보 가져옴
 *
 * @param dvs = 1주일, 해당월 구분
 */
var getOrderSummary = function (dvs) {
    var url = "/json/common/load_order_summary.php";
    var data = {
        "dvs": dvs
    };
    var callback = function (result) {
        if (checkBlank(result.err) === false) {
            alert("로그아웃되서 메인화면으로 이동합니다.");
            location.href = "/common/logout.php";
        }

        $("#summary_wait").html(result.wait);
        $("#summary_rcpt").html(result.rcpt);
        $("#summary_prdc").html(result.prdc);
        $("#summary_rels").html(result.rels);
        $("#summary_dlvr").html(result.dlvr);
        $("#summary_comp").html(result.comp);
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @function 검색 날짜 범위 설정
 * @modified 180130 이청산
 * @param num     시작 날짜
 *       ,numExt  끝 날짜
 *       ,flag    전주, 전월 플래그
 */
var dateSet = function (num, numExt, flag) {
    var day = new Date();
    var time = day.getHours();

    //전체 범위 검색시 날짜 범위 초기화
    if (num == "last") {
        var last = new Date(day - (365 * 1000 * 60 * 60 * 24));

        $("#from").datepicker("setDate", last);
        $("#to").datepicker("setDate", last);
    } else if (num == "all") {
        $("#from").val("");
        $("#to").val("");
    } else if (flag == "lw") {
        var d_day = new Date(day - (num * 1000 * 60 * 60 * 24));
        var e_day = new Date(day - (numExt * 1000 * 60 * 60 * 24));

        $("#from").datepicker("setDate", d_day);
        $("#to").datepicker("setDate", e_day);
    } else if (flag == "lm") {
        // 전달
        var year = day.getFullYear();
        var mon = day.getMonth();
        mon = (mon === 0) ? 12 : mon;
        mon = padStr('0', 2, mon);
        var date = "01";

        var e_day = new Date(year + '-' + mon + '-' + date);
        e_day.setMonth(e_day.getMonth() + 1);
        e_day.setDate(e_day.getDate() - 1);


        var from = year + '-' + mon + '-' + date;
        var to = e_day.getFullYear() + '-' +
            padStr('0', 2, e_day.getMonth() + 1) + '-' +
            padStr('0', 2, e_day.getDate());
        $("#from").val(year + '-' + mon + '-' + date);
        $("#to").val(to);
    } else if (flag == "cm") {
        // 당월
        var year = day.getFullYear();
        var mon = day.getMonth() + 1;
        mon = padStr('0', 2, mon);
        var day = "01";

        $("#from").val(year + '-' + mon + '-' + day);
        $("#to").datepicker("setDate", '0');
    } else {
        var d_day = new Date(day - (num * 1000 * 60 * 60 * 24));

        $("#from").datepicker("setDate", d_day);
        $("#to").datepicker("setDate", '0');
    }
};

//인풋박스 숫자만 가능
var onlyNumber = function (event) {

    event = event || window.event;

    var keyID = (event.which) ? event.which : event.keyCode;
    if (keyID == 8 || keyID == 46 || keyID == 37 || keyID == 39) {
        return;
    } else {
        event.target.value = event.target.value.replace(/[^0-9]/g, "");
    }
};

var chkMaxLength = function (obj) {

    if (obj.value.length > obj.maxLength) {
        obj.value = obj.value.slice(0, obj.maxLength);
    }
};

/**
 * @brief 원단위 반올림
 *
 * @param val = 반올림할 값
 *
 * @return 계산된 값
 */
var ceilVal = function (val) {
    var isMinus = (parseFloat(val) < 0) ? true : false;

    val = Math.abs(val);
    val = Math.floor(val);
    val = Math.round(val * 0.1) * 10;

    return isMinus ? val * -1 : val;
};

/**
 * @brief 전달받은 메세지 alert으로 띄우고 false값 반환
 *
 * @param str = 메세지
 *
 * @return false
 */
var alertReturnFalse = function (str) {
    alert(str);
    return false;
};

//중복체크버튼 보이기
var showBtn = function (el) {
    $(el).next().show().next().text('');
    $("#id_over_yn").val("N");
};

//아이디 중복체크
var getIdOver = function (el) {

    var id = $("#member_id").val();

    //아이디 유효성 검사
    var id_pattern = /^[a-z0-9_-]{7,20}$/;
    if (!id_pattern.test(id)) {
        alert("아이디가 올바르지 않습니다.");
        $("#member_id").focus();
        return false;
    }

    showMask();
    var url = "/ajax/common/id_check.php";
    var data = {
        "member_id": $("#member_id").val()
    };
    var callback = function (result) {
        if (result == "true") {
            $(el).hide();
            $(el).next().addClass('ok').text('사용 가능한 아이디입니다.');
            $("#id_over_yn").val("Y");
        } else {
            $(el).next().removeClass('ok').text('이미 사용 중인 아이디 입니다.');
        }
    }

    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 충전하기 버튼 클릭시 레이어 팝업 출력
 */
var showPrepaymentPop = function () {
    var url = "/ajax/common/load_prepayment_pop.php";
    popupMask = layerPopup("l_pay_card", url);
};

/*
 * 2016-06-23 김상기(추가)
 * 금액 입력창에 숫자, 백스페이스, 딜리트, 탭, F5, 좌우 방향키만
 * 입력 가능하고 우클릭 방지하는 함수
 * 인자를 받아서 허용 가능 키 추가 가능하도록 수정
 */
var numKeyCheck = function (id, arr) {
    //한글입력 불가능하도록 변경
    $("#" + id).css("ime-mode", "disabled");

    $("#" + id).keydown(function (event) {
        var code = event.which;
        var shift = event.shiftKey;

        var refuseKeycode = [46, 8, 9, 37, 39, 116];
        var ret = false;

        if (arr) {
            refuseKeycode.push(arr);
        }

        if (shift) {
            return false;
        }

        //입력받은 키값을 검사
        if ((code > 47 && code < 58) || (code > 95 && code < 106)) {
            ret = true;
        }

        for (var i = 0; i < refuseKeycode.length; i++) {
            if (code == refuseKeycode[i]) {
                ret = true;
            }
        }

        return ret;
    });

    //입력창에 우클릭메뉴 방지
    $("#" + id).bind("contextmenu", function (e) {
        return false;
    });
}

/**
 * @brief 선입금 충전 팝업 출력
 */
var doCharge = function () {
    var ts = new Date();
    ts = ts.getTime();
    var chargePrice =
        $("input[type='radio'][name='charge_price']:checked").val();
    chargePrice = chargePrice.replace(/,/g, '');

    $("#P_EP_product_amt").val(chargePrice);
    $("#P_EP_order_no").val(ts);

    if (chargePrice === '0') {
        return alertReturnFalse("결제금액이 0원입니다.");
    };

    easypay_webpay(document.p_frm_pay,
        "/webpay_card_prepay/web/normal/new_iframe_req.php",
        "hiddenifr",
        "0",
        "0",
        "iframe",
        30);

    //getPrepaymentList(10, 1);
};

/**
 * @brief 선입금 승인요청 submit
 */
var prepaySubmit = function () {
    showMask();

    var frm = document.p_frm_pay;
    frm.target = "p_iframe_pay";
    frm.action = "/webpay_card_prepay/web/new_easypay_request.php";
    frm.submit();
};

/**
 * @brief 선입금 충전여부 판단
 */
var goCharge = function () {
    var chargePrice =
        $("input[type='radio'][name='charge_price']:checked").val();
    chargePrice = chargePrice.replace(/,/g, '');
    closePopup(popupMask);
    hideMask();

    var $obj = $("#p_iframe_pay").contents().find("body");
    if (checkBlank($obj) === true) {
        return alertReturnFalse("PG사 서버가 제대로 동작하지 않습니다.");
    }

    var resCd = $obj.find("#res_cd").val();
    var amount = $obj.find("#amount").val();

    if (resCd !== "0000") {
        return alertReturnFalse($obj.find("#res_msg").val());
    }

    if (amount !== chargePrice) {
        return alertReturnFalse("결제 승인금액이 실제와 상이합니다.\n관리자에게 문의하세요.");
    }

    $("#side_prepay_price").html($obj.find("#prepay_bal").val());

    if (isFunc("getPrepaymentList") === true) {
        location.reload();
    }
};

//사이드메뉴 아코디언
var showAccordion = function (dvs) {
    if ($("#" + dvs).css("display") == "none") {
        $("#myOrder").hide();
        $("#favorite").hide();
        $("#contact").hide();
        $("#" + dvs).show("200");
    } else {
        $("#" + dvs).hide();
    }
}

/*
//가상계좌 변경
var modiBa = function(url) {
    var frm_pay = document.frm_pay;

    var today = new Date();
    var year  = today.getFullYear();
    var month = today.getMonth() + 1;
    var date  = today.getDate();
    var time  = today.getTime();

    if(parseInt(month) < 10) {
        month = "0" + month;
    }

    if(parseInt(date) < 10) {
        date = "0" + date;
    }

    frm_pay.return_url.value = url;
    frm_pay.EP_order_no.value = "ORDER_" + year + month + date + time;   //가맹점주문번호
    frm_pay.EP_expire_date.value = "" + year + month + date; // 무통장입금 입금만료일(YYYYMMDD)
    frm_pay.EP_expire_time.value = "235959";                 // 무통장입금 입금만료시간(HHMMSS)

    frm_pay.submit();
}
*/

/**
 * @brief 상품 페이지에서 셀렉트 박스 변경시 화면 이동
 *
 * @param cateSortcode = 카테고리 분류코드(대 or 중 or 소)
 */
var moveProduct = function (cateSortcode) {
    var url = "/product/common/move_product.php?cs=" + cateSortcode;
    url += "&t=" + encodeURI($("#title").val());
    location.href = url;
};

/**
 * @brief 주문 상세정보 펼치기
 *
 * @param idx     = 행 위치
 * @param seqno   = 주문공통일련번호
 * @param colspan = 열병합 값
 * @param id      = 행 id
 */
var openOrderDetail = function (idx, seqno, colspan, id) {

    if (checkBlank(id) === true) {
        id = "detail";
    }

    var url = "/ajax22/common/load_order_view.php";
    var data = {
        "order_common_seqno": seqno,
        "colspan": colspan,
    };
    var callback = function (result) {
        var th = $("#" + id + idx);
        //console.log(th);

        $("#" + id + idx).html(result);

        if ($("#im").val() == "1") {
            $("#det_op_" + idx).hide();
            $("#det_cl_" + idx).show();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var showCardReceipt = function (deal_num) {
    window.open("https://office.easypay.co.kr/receipt/ReceiptBranch.jsp?controlNo=" + deal_num + "&payment=01", "MEMB_POP_RECEIPT", 'toolbar=0,scroll=1,menubar=0,status=0,resizable=0,width=380,height=700');
}

//상세보기 접기
var closeOrderDetail = function (idx) {

    //console.log("idx : " + idx);

    $("#detail" + idx).html("");

    if ($("#im").val() == "1") {
        $("#det_op_" + idx).show();
        $("#det_cl_" + idx).hide();
    }
}

// 모바일 주문 상세보기
var openMobileOrderDetail = function (idx, seqno, colspan, id) {

    if (checkBlank(id) === true) {
        id = "detail";
    }

    var url = "";
    var data = {
        "order_common_seqno": seqno,
        "colspan": colspan,
    };
    var callback = function (result) {
        var th = $("#" + id + idx);
        //console.log(th);

        $("#" + id + idx).html(result);

    };

    showMask();
    ajaxCall(url, "html", data, callback);

}

// 모바일 장바구니 전체체크
var chkAll = function (pos) {
    if ($("input[id^='chk_list']").is(":checked") == false) {
        $("input[id^='chk_list']").prop("checked", true);
    } else {
        $("input[id^='chk_list']").prop("checked", false);
        if ($("#" + pos).is(":checked") == true) {
            $("#" + pos).prop("checked", false);
        }
    }
}

/**
 * @brief 건수 초기화
 *
 * @param val = 건수
 * @param id  = 객체 아이디
 */
var initCount = function (val = '', id = '', selectedCnt = 1) {
    val = parseInt(val);

    // 건수 초기화
    var option = "";
    for (var i = 1; i <= val; i++) {
        if (selectedCnt == i) {
            option += "<option selected=\"selected\" value=\"" + i + "\">" + i + "</option>";
        } else {
            option += "<option value=\"" + i + "\">" + i + "</option>";
        }
    }
    $("#" + id).html(option);
};


/**
 * @brief 빠른견적서에서 가격항목 데이터 추가
 *
 * @param data = 기존 data 파라미터
 *
 * @return data = 추가된 data 파라미터
 */
var getEstiPopData = function (data) {
    var paperPrice = $.trim($("#esti_paper").text());
    var outputPrice = $.trim($("#esti_output").text());
    var printPrice = $.trim($("#esti_print").text());
    var optPrice = $.trim($("#esti_opt").text());
    var supplyPrice = $.trim($("#esti_supply").text());
    var tax = $.trim($("#esti_tax").text());
    var sellPrice = $.trim($("#esti_sell_price").text());
    var salePrice = $.trim($("#esti_sale_price").text());
    var payPrice = $.trim($("#esti_pay_price").text());

    data.paper_price = paperPrice;
    data.print_price = printPrice;
    data.output_price = outputPrice;
    data.opt_price = optPrice;
    data.supply_price = supplyPrice;
    data.tax = tax;
    data.sell_price = sellPrice;
    data.sale_price = salePrice;
    data.pay_price = payPrice;

    $.each(aftArr, function (aftKo, aftEn) {
        data[aftEn + "_price"] = $.trim($("#esti_" + aftEn).text());
    });

    return data;
};

/**
 * @brief 견적서 출력 팝업 출력
 */
var showEstiPop = function () {
    makeEstiPopInfo.exec("pop");

    var dat = makeEstiPopInfo.data;
    estiDataHolder.exec(dat);
    var jsonStr = JSON.stringify(dat);
    $("#json").val(jsonStr);

    var pop = window.open('', 'estiPop', 'width=800, height=780, location=no, status=no, scrollbars=yes');
    $("#esti_frm").attr('target', 'estiPop');
    $("#esti_frm").submit();
};

/**
 * @brief 늘 하던 거에서 선택된 항목 장바구니 추가
 */
var goCartByOrder = function (flag) {
    if ($("input[name='order_chk[]']:checked").length === 0) {
        return false;
    }

    if (flag === true) {
        $("#fav_cart_flag").val('N');
    } else {
        $("#fav_cart_flag").val('Y');
    }

    $("#frm_fav").submit();
};

// 아이디 찾기
var findAccount = function () {

    var url = "/ajax/member/find_id/load_find_id.php";
    var data = {
        "name": $("#find_name").val(),
        "mail": $("#find_id").val()
    };
    var callback = function (result) {
        showFoundAccount(result);
    };

    if (checkBlank(data.name)) {
        alert("성명(상호)를 입력해주세요.");
        $("#find_name").focus();
        return false;
    }

    if (checkBlank(data.mail)) {
        alert("이메일을 입력해주세요.");
        $("#find_id").focus();
        return false;
    }

    if (!email_check(data.mail)) {
        alert("올바른 메일 주소가 아닙니다.");
        $("#find_id").focus();
        return false;
    }

    $("#find_acc").html('<button type="button" disabled>잠시만 기다려주세요.</button>');

    ajaxCall(url, "html", data, callback);
};

// 아이디 찾기 결과
var showFoundAccount = function (res) {
    var info = "";
    if (res == 1) {
        info = "회원님의 메일로 임시비밀번호를 발송해 드렸습니다.";
    } else if (res == 0) {
        info = "일치하는 회원 정보가 없습니다.";
    } else {
        info = "오류가 발생하였습니다. 관리자에게 문의 바랍니다.";
    }
    $("#result_id_box").show();
    $("#result_id").html(info);
    $("#find_id_box").hide();
};

// 로그인 창으로 돌아감
var goLoginBox = function () {
    $("#find_name").val("");
    $("#find_id").val("");
    $("#result_id_box").hide();
    $("#login_box").show();
}

/***********************메인 헤드에 있던 함수영역 시작******************/

// 메인메뉴 열기
var showMenuLayer = function (event) {
    if (event.stopPropagation) {
        event.stopPropagation();
        $("#table_menu_list").show("fast");
        $("#btn_menu_list").hide();
        $("#btn_menu_close").show();
        $("#btn_menu_wrap").addClass('on');
        console.log('있다');
    } else {
        event.cancelBubble = true;
    }
    //     $("#table_menu_list").show("fast");
    //     $("#btn_menu_list").hide();
    // $("#btn_menu_close").show();
};

// 메인메뉴 닫기
var closeMenuLayer = function () {
    $("#table_menu_list").hide("fast");
    $("#btn_menu_list").show();
    $("#btn_menu_close").hide();
    $("#btn_menu_wrap").removeClass('on');
    console.log('없다');
};

// 메인페이지 로그인 창 보이기
var showLoginBox = function () {
    if ($("#login_box").css("display") == "block") {
        hideLoginBox();
    } else {
        $(".login_layerpopup").show();
        $("#login_box").show();
    }
};

// 메인페이지 로그인 창 숨기기
var hideLoginBox = function () {
    $(".login_layerpopup").hide();
    $("#login_box").hide();
    $("#purlogin").val("0");
};

var requireLogin = function () {
    alert('로그인이 필요합니다.');
    showLoginBox();
};

// 회원가입 창 보이기
var showJoinBoxBef = function () {
    if ($("#join_box_bef").css("display") == "block") {
        hideJoinBoxBef();
    } else {
        hideLoginBox();
        $(".login_layerpopup").show();
        $("#join_box_bef").show();

    }
};

// 회원가입 창 숨기기
var hideJoinBoxBef = function () {
    $("#join_box_bef").hide();
    $(".login_layerpopup").hide();
};

// 회원가입 데이터 입력 창 보이기
var showJoinBox = function () {
    if ($("#join_box").css("display") == "block") {
        hideJoinBox();
    } else {
        hideLoginBox();
        hideJoinBoxBef();
        $(".login_layerpopup").show();
        $("#join_box").show();
    }
};

// 회원가입 데이터 입력 창 숨기기
var hideJoinBox = function () {
    $("#join_name").val("");
    $("#join_id").val("");
    $("#join_pw").val("");
    $("#join_pw_check").val("");
    $(".login_layerpopup").hide();
    $("#join_box").hide();
};

var showFindIdBox = function () {
    if ($("#find_id_box").css("display") == "block") {
        hideFindIdBox();
    } else {
        hideLoginBox();
        $("#find_acc").html('<button type="button" class="c_btn-primary-reverse btn_type_01" style="width:150px;" onclick="findAccount();">아이디 / 비밀번호 찾기</button>');
        $(".login_layerpopup").show();
        $("#find_id_box").show();
    }
};

var hideFindIdBox = function () {
    $(".login_layerpopup").hide();
    $("#find_id_box").hide();
};

// 이메일 형식 정규식 필터
var email_check = function (email) {
    var regex = /([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
    return (email != '' && email != 'undefined' && regex.test(email));
};

// 회원 중복확인, 이메일 형식 확인
var chkDupMember = function () {

    var mail = $("#join_id").val();

    var url = "/ajax/common/load_id_over_check.php";
    var data = {
        "mail": mail
    }
    var callback = function (result) {
        if (result == "false") {
            alert("사용할 수 없는 아이디입니다.");
            $("#chk_dup").val("");
        } else {
            alert("사용 가능한 아이디입니다.");
            $("#chk_dup").val("1");
        }
    }

    // 아이디 빈값일 경우
    if (mail == "") {
        alert("아이디를 입력해주세요.");
        $("#join_id").focus();
        return false;

    }
    if (!email_check(mail)) {
        alert("올바른 메일 주소가 아닙니다.");
        $("#join_id").focus();
        return false;
    }

    ajaxCall(url, "text", data, callback);
};

// 마이페이지 메뉴박스 보이기
var showMypageMenu = function () {
    if ($("#top_menu_mypage_list").css("display") == "block") {
        hideMypageMenu();
    } else {
        $("#top_menu_mypage_list").show();
    }
};

// 회원가입 정보 입력
var memberJoin = function () {

    var name = $("#join_name").val();
    var mail = $("#join_id").val();
    var mailChk = $("#chk_dup").val();
    var pw = $("#join_pw").val();

    var url = "/proc/common/insert_member_info.php";
    var data = {
        "name": name,
        "mail": mail,
        "pw": pw,
        "member_join_id_chk": mailChk
    }

    var callback = function (result) {
        if (result == "1") {
            alert("가입되었습니다. 가입한 아이디로 로그인 해 주세요.");
            hideJoinBox();
            if ($(".login_layerpopup_bg").css("display") == "block") {
                $(".login_layerpopup_bg").hide();
            }
        } else {
            alert("오류가 발생했습니다.");
        }
    }

    if (name.length < "2") {
        if (checkBlank(name)) {
            alert("이름을 입력해주세요.");
            $("#join_name").focus();
            return false;
        } else {
            alert("이름을 2자 이상으로 입력해주세요.");
            $("#join_name").focus();
            return false;
        }
    }

    if (checkBlank(mail)) {
        alert("이메일을 입력해주세요.");
        $("#join_id").focus();
        return false;
    }

    if (checkBlank(mailChk)) {
        alert("이메일 중복확인을 해주세요.");
        return false;
    }

    if (checkBlank(pw)) {
        alert("비밀번호를 입력해주세요.");
        $("#join_pw").focus();
        return false;
    }

    // 비밀번호 검증
    if (!checkPassword(pw)) {
        return false;
    }

    if (!$("#join_terms").prop("checked")) {
        alert("개인정보처리방침 및 이용약관에 동의해 주세요.");
        return false;
    }

    ajaxCall(url, "text", data, callback);
}

// 비밀번호 검증 함수
var checkPassword = function (pw) {
    if (!/^[a-zA-Z0-9]{6,16}$/.test(pw)) {
        alert('비밀번호는 숫자와 영문자 조합으로 6~16자리를 사용해야 합니다.');
        $("#join_pw").focus();
        return false;
    }

    var checkNum = pw.search(/[0-9]/g);
    var checkEng = pw.search(/[a-z]/ig);

    if (checkNum < 0 || checkEng < 0) {
        alert('숫자와 영문자를 혼용하여야 합니다.');
        $("#join_pw").focus();
        return false;
    }

    if (/(\w)\1\1\1/.test(pw)) {
        alert('같은 숫자를 연달아 사용할 수 없습니다. ex)111, 222');
        $("#join_pw").focus();
        return false;
    }

    var pwChk = $("#join_pw_check").val();

    if (pw != pwChk) {
        alert("비밀번호를 확인해주세요.");
        $("#join_pw_check").focus();
        return false;
    }

    return true;
};

// 로그인창 placeholder
var hidePlaceHd = function (dvs) {
    if (dvs.indexOf("placeholder") != "-1") {
        $('#' + dvs).hide();
        $('#' + dvs).prev().focus();
    } else {
        $('#' + dvs + "_placeholder").hide();
    }
    $('#' + dvs).blur(function () {
        var dvVal = $('#' + dvs).val();
        if (checkBlank(dvVal)) {
            $('#' + dvs + "_placeholder").show();
        }
    });
}

// 마이페이지 메뉴박스 숨기기
var hideMypageMenu = function () {
    $("#top_menu_mypage_list").hide();
};

var checkedCharge = function (obj) {
    $(".charge_label").removeClass("checked");
    $(obj).addClass("checked");
    var $radio = $(obj).prev();
    $("input[name='charge_price']").prop("checked", false);
    $radio.prop("checked", true);

    changeChargePrice($radio);
};

var changeChargePrice = function (obj) {
    $('#charge_price').val($(obj).val().format());
};

/*********************** 메인 헤드 함수영역 끝 ******************************/

var goCscenter = function (url) {
    if (cscenterPopup !== null && !cscenterPopup.closed) {
        cscenterPopup.location.href = url;
    } else {
        cscenterPopup = window.open(url, "pop", "width=1500, height=900, scrollbars=yes");
    }
};

// 주문내역으로 이동
var goOrderAll = function (dvs) {
    var url = "/mypage/order_all.html";
    url += "?dvs=" + encodeURI(dvs);
    if ($("#period_from").length > 0) {
        url += "&from=" + $("#period_from").val();
    }
    if ($("#period_to").length > 0) {
        url += "&to=" + $("#period_to").val();
    }

    window.location.href = url;
};

var padStr = function (pad, len, str) {
    str += '';
    var tmp = pad;
    for (var i = 1; i < len; i++) {
        pad += tmp;
    }

    return pad.substring(0, pad.length - str.length) + str;
}

// ajax 타이밍 관리 함수(증가)
var higherAjaxStack = function () {
    showAjaxMask();
    ajaxStack += 1;
}

// ajax 타이밍 관리 함수(감소)
var lowerAjaxStack = function () {
    ajaxStack -= 1;

    if (ajaxStack == 0) {
        hideMask();
    }
}

/*
 * @brief 입금확인 함수
 * @param name = 가상계좌 은행명
 *        num  = 가상계좌 번호
 */
var refreshDepo = function () {
    var url = "/ajax/common/load_member_prepay.php";
    var data = {
    };
    var callback = function (result) {
        var rs = result.split('@');
        $("#prepay_price_bal").text(rs[1].format());
        alert("현재 잔액은 " + rs[1].format() + " 원입니다.");
        /*
        var readyAnc = "<a id=\"chk_depo\" href=\"none\" onclick=\"refreshDepo('{$ba_name}', '{$ab_num}');\" class=\"btn_side_contrents_01\">입금확인</a>";
        $("#chk_depo").replaceWith(readyAnc);
        */
        $("#chk_depo").show();
    };
    /*
    var processing = "<a href=\"#none\" class=\"btn_side_contents_01\">진행중입니다...</a>";
    $("#chk_depo").replaceWith(processing);
    */
    $("#chk_depo").hide();

    ajaxCallNoMask(url, "html", data, callback);
}

var showPopupSyncFlag = function () {

    if ($("#sync_flag").val() == "Y") {
        var seqno = $("#sync_flag").attr('seq');
        if (checkBlank(seqno)) {
            return false;
        }
        var url = '/common/popup/l_sync.html?seqno' + seqno;
        layerPopup('l_sync', url);
    }
}

// 월매출 확인
var checkMonthlySales = function () {
    var url = "/ajax/common/load_member_monthly_sales.php";
    var data = {
        // NO DATA;
    };
    var callback = function (result) {
        if (result == "T") {
            $("#icon_new").show();
            $("#month_sales").html("다음달은 월배송 서비스를 무료로 이용가능(연장가능) 합니다.");
        } else if (result == "F") {
            $("#icon_new").hide();
            $("#month_sales").html("<span class=\"text_st_01\">월매출 33만원</span> 이 넘지않아 월배송 서비스를 무료로 이용하실 수 없습니다.");
        } else if (result == "N") {
            $("#icon_new").hide();
            $("#month_sales").html("<span class=\"text_st_01\">월매출 33만원</span> 을 넘기면 무료로 이용하실 수 있습니다.(종전가 VAT포함 55,000원)");
        }
    }

    ajaxCallNoMask(url, "html", data, callback);
}
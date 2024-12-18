/***********************************************************************************
 *** 프로 젝트 : 3.0
 *** 개발 영역 : 주문서 작성페이지
 *** 개  발  자 : 엄준현 -> 조현식
 *** 개발 날짜 : 2016.06.29
 ***********************************************************************************/

// 팝업 객체
var popupMask = null;

window.onbeforeunload = unloadFunc;
var unloadFunc = function () {
    return "입력된 정보가 사라지게 됩니다.\n계속하시겠습니까?";
};

$(document).ready(function () {
    // 변하지 않는 정보는 최초 페이지 로드할 때 인코딩
    modiProductNm();

    // 계산서 부분 초기화
    setTimeout(function () {
        $('.reciept > dt input[type=radio]:checked').click();
        getMemberInfo();
    }, 1);

    $('.reciept > dt input[type=radio]').on('click', function () {
        var dvs = $(this).attr("dvs");

        $(this).closest('dl').children('dd.on').removeClass('on')
            .find('select, input').attr('disabled', true);
        $(this).closest('dt').next().addClass('on');
        $(this).closest('dt').next().find('select, input').attr('disabled', false);

        if (dvs === "tax") {
            $(".tax .input").show();
        } else {
            $(".tax .input").hide();
        }
    });

    $('.reciept > dd.tax input[type=radio]').on('click', function () {
        $(this).closest('dd')
            .find('input[type=text]')
            .attr('readonly', !$(this).hasClass('_edit'));
        if ($(this).hasClass('_new')) {
            $(this).closest('dd').find('input[type=text]').val('');
        }
    });

    setTotalValue();

    // 171121 직배 추가로 인해 trigger 발생필요
    //$("#to_1_dlvr_way").trigger("change");

    // 직배일 경우 직배쪽에 전부 세팅
    if ($("#to_1_dlvr_way").val() == "02") {
        // 직배일 경우 옵션 선택 제한하는 함수
        setDirectLimitOption("to_1");
    } else {
        $("input[name='to_1_preset']:eq(0)").prop("checked", true)
            .trigger("click");
    }

    if ($("#to_1_dlvr_way").val() != "02") {
        setDlvrReq('1');
    }

    $("input[name='fromPreset']:eq(0)").prop("checked", true);
    changeFrom("memb");

    // 영수증 발행 정보 선택
    $('.sheet .form.reciept .inputs header input[type=radio]').on('click', function () {
        $(this).closest('.form').find('.contents.on').removeClass('on');
        $(this).closest('.form').find('.contents.' + $(this).attr('dvs')).addClass('on');
    });

    getDlvrCost.exec('to_1');
    getDlvrCost.calc('to_1');

    calcPrice();
});

/**
 * @brief 결제정보로 넘어가는 상품명 수정
 */
var modiProductNm = function () {
    var name = $($(".subject > .category_text")[0]).text();
    name += $($(".subject > .order_list_title_text")[0]).text();
    var count = $(".idx").length;

    if (count > 1) {
        name += " 외 " + (count - 1) + "건";
    }

    $("#EP_product_nm").val(encodeURIComponent(name));
};


var searchDlvrList = function () {
    $("#address_list > tr").each(function(){
        var text = $("#searchkey").val();
        console.log(this);
        if(this.innerHTML.includes(text)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

var startNaverPay = function () {
    var oPay = Naver.Pay.create({
        "mode" : "production", // development or production
        "clientId": "u86j4ripEt8LRfPGzQ8", // clientId
        "openType": "popup",
        "onAuthorize" : function(oData) {
            /*
            팝업 타입을 설정하고, onAuthorize callback function 을 설정하여 결과를 callback function 으로 전달 받을 수 있도록 지원합니다.
            onAuthorize callback function 을 설정하지 않은 경우는 returnUrl 로 참조 정보와 함께 redirect 됩니다.
            oData 객체에는 결제 인증 결과와 전달한 returnUrl 정보가 함께 전달되며,
            이 정보는 이후 승인 요청 처리를 위한 정보 (resultCode, resultMessage, returnUrl, paymentId, reserveId 등) 입니다.
            전달되는 값은 https://developer.pay.naver.com/docs/v2/api#payments-payments_window 의 성공 & 실패 응답 값을 참조해주세요.
            */
            if(oData.resultCode === "Success") {
                // 네이버페이 결제 승인 요청 처리
                console.log(oData);
            } else {
                // 필요 시 oData.resultCode 와 oData.resultMessage 를 활용하여 적절한 사용자 안내 처리 구현
            }
        }
    });

    oPay.open({
        "merchantUserKey": "fasff",
        "merchantPayKey": "12312344",
        "productName": "상품명을 입력하세요",
        "totalPayAmount": "500",
        "taxScopeAmount": "500",
        "taxExScopeAmount": "0",
        "returnUrl": "사용자 결제 완료 후 결제 결과를 받을 URL"
    });
}

/**
 * @brief 다음 API 주소검색 함수
 *
 * @param dvs = 입력 구분값
 */
var getPostcode = function (dvs) {
    if (dvs == "to_1") {
        if ($("#to_1_dlvr_way").val() == "02") {
            alert("직배의 경우 배송지를 선택할 수 없습니다.");
            return false;
        }
    }
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
            document.getElementById(dvs + '_zipcode').value = data.zonecode; //5자리 새우편번호 사용
            document.getElementById(dvs + '_addr_top').value = fullAddr;

            // 커서를 상세주소 필드로 이동한다.
            document.getElementById(dvs + '_addr_detail').focus();

            getDlvrCost.exec(dvs);
        }
    }).open();
};

/**
 * @brief 회원 주소정보, 디프린팅 주소정보 저장
 *
 * @param pos = 위치값
 * @param dvs = 라디오 버튼 구분값
 */
var loadAddrInfo = {
    "memb": null,
    "direct": null,
    "exec": function (pos, dvs) {
        var url = "/ajax22/order/load_dlvr_addr_info.php";
        var data = {
            "dvs": dvs
        };
        var callback = function (result) {
            setAddrInfo(pos, result);

            loadAddrInfo[dvs] = result;

            if (dvs !== "direct" && $("#" + pos + "_zipcode").val() != "") {
                getDlvrCost.exec(pos);
            }
        };

        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 보내시는 분 라디오 버튼 클릭시 해당하는 동작 실행
 *
 * @param dvs = 라디오 버튼 구분값
 */
var changeFrom = function (dvs) {
    if (dvs === "new") {
        var name = $("#from_dlvr_name").val();
        $(".order_sheet_table.from").find("input[type='text']").val('');
        $("#from_dlvr_name").val(name);
        return false;
    }

    var pos = "from";

    if (!checkBlank(loadAddrInfo[dvs])) {
        setAddrInfo(pos, loadAddrInfo[dvs]);
        return false;
    }

    loadAddrInfo.exec(pos, dvs);
};

/**
 * @brief 받으시는 분 라디오 버튼 클릭시 해당하는 동작 실행
 *
 * @param pos = 위치값
 * @param dvs = 라디오 버튼 구분값
 */
var changeTo = function (pos, dvs) {
    pos = "to_" + pos;
    //새로운 정보 입력인 경우
    if (dvs === "new") {
        $("#" + pos).find("input[type='text']").val('');
        return false;
    }

    if (checkBlank(loadAddrInfo.memb) === true) {
        loadAddrInfo.exec(pos, dvs);

        return false;
    }

    setAddrInfo(pos, loadAddrInfo.memb);

    if ($("#" + pos + "_zipcode").val() != "" && $("#"+dvs+"_dlvr_way").val() == "01") {
        getDlvrCost.exec(pos);
    }
};

/**
 * @brief 나의배송지 선택에서 선택버튼 클릭시
 * 주소정보 세팅
 *
 * @param obj = 선택버튼 객체
 */
var setMemberAddrInfo = {
    "pos": null,
    "exec": function (obj) {
        var arr = {};

        $(obj).nextAll("input[type='hidden']").each(function () {
            var name = $(this).attr("name");
            var val = $(this).val();

            if (name === "tel_num") {
                if (checkBlank(val) === true) {
                    val = '--';
                }

                var telNumArr = val.split('-');
                arr.tel_num1 = telNumArr[0];
                arr.tel_num2 = telNumArr[1];
                arr.tel_num3 = telNumArr[2];
            } else if (name === "cell_num") {
                if (checkBlank(val) === true) {
                    val = '--';
                }

                var cellNumArr = val.split('-');
                arr.cell_num1 = cellNumArr[0];
                arr.cell_num2 = cellNumArr[1];
                arr.cell_num3 = cellNumArr[2];
            } else {
                if (checkBlank(val) === true) {
                    val = '';
                }

                arr[name] = val;
            }
        });

        setAddrInfo(this.pos, arr);

        if ($("#" + this.pos + "_zipcode").val() != "") {
            getDlvrCost.exec(this.pos);
        }

        this.pos = null;
        closePopup(popupMask);
        popupMask = null;
    }
};

/**
 * @brief 보내는분 정보 세팅
 *
 * @param pos = 정보 세팅할 위치
 * @param arr = 정보 배열
 */
var setAddrInfo = function (pos, arr) {
    $("#" + pos + "_dlvr_name").val(arr.recei);
    $("#" + pos + "_recei").val(arr.recei);

    $("#" + pos + "_tel_num1").val(arr.tel_num1);
    $("#" + pos + "_tel_num2").val(arr.tel_num2);
    $("#" + pos + "_tel_num3").val(arr.tel_num3);

    $("#" + pos + "_cell_num1").val(arr.cell_num1);
    $("#" + pos + "_cell_num2").val(arr.cell_num2);
    $("#" + pos + "_cell_num3").val(arr.cell_num3);

    $("#" + pos + "_zipcode").val(arr.zipcode);
    $("#" + pos + "_addr_top").val(arr.addr);
    $("#" + pos + "_addr_detail").val(arr.addr_detail);
};

/**
 * @brief 삭제버튼 클릭시 주문서 작성에서 삭제하고
 * 가격 재계산, 순번 재설정 하는 함수
 *
 * @param idx = 삭제되는 tr 인덱스
 */
var removeOrder = function (idx) {
    // 해당 tr / table 삭제
    var $obj = $("#tr_" + idx).parent("tbody");
    var length = $("tr._orderDetails").length;

    removeObj($obj, length);

    // idx 재설정
    $(".idx").each(function (i) {
        $(this).html(i + 1);
    });

    //상품을 삭제할경우 서버에서 각 상품에 대한 배송비를 다시 가져와야한다
    var seq = $obj.find("input[type='hidden'][name='seq[]']").val();
    var to = $("#selected_" + seq).parents('table').attr('id');
    $("li[name='selected_" + seq + "']").remove();
    getDlvrCost.exec(to);

    setTotalValue();

    // 가격 재계산
    calcPrice();
}

/**
 * @brief 가격관련 정보 재계산
 */
var calcPrice = function () {
    var dlvrPrice = 0;
    dlvrPrice += Number($("#to_1_nc_price").val());
    dlvrPrice += Number($("#to_1_bl_price").val());
    /*
    $(".order_sheet_table.to").each(function (idx) {
        idx++;
        dlvrPrice += Number($("#to_" + idx + "_nc_price").val());
        dlvrPrice += Number($("#to_" + idx + "_bl_price").val());
    });
    */
    $("#dlvr_price").html(dlvrPrice.format());

    // 회원 등급할인 재계산
    var gradeSalePrice = 0;
    $(".grade_sale_price").each(function () {
        gradeSalePrice += parseFloat($(this).val());
    });
    //$("#grade_sale_price").html(gradeSalePrice.format());
    var memberSalePrice = 0;
    $(".member_sale_price").each(function () {
        memberSalePrice += parseFloat($(this).val());
    });

    // 판매금액 재계산
    var sellPrice = 0;
    $(".sell_price").each(function () {
        sellPrice += parseFloat($(this).val());
    });
    sellPrice += gradeSalePrice;
    sellPrice += memberSalePrice;
    $("#sell_price").html(sellPrice.format());

    // 이벤트 금액 재계산
    var eventPrice = 0;
    $(".event_price").each(function () {
        eventPrice += parseFloat($(this).val());
    });
    $("#event_sale_price").html(eventPrice.format());

    // 쿠폰 금액 재계산
    var couponPrice = 0;
    var couponPrice = $("input[type='hidden'][name='coupon_price']").val();
    couponPrice = ceilVal(couponPrice);

    // 포인트 금액 재계산
    var pointPrice = $("input[type='hidden'][name='point']").val();
    pointPrice = ceilVal(pointPrice);
    var maxPointPrice = sellPrice + dlvrPrice + gradeSalePrice + couponPrice;

    if (maxPointPrice < pointPrice) {
        $("input[type='hidden'][name='point']").val(maxPointPrice);
        $("#point").html(maxPointPrice.format());
        pointPrice = maxPointPrice;
    }

    // 총 할인금액 재계산
    var sumDiscount = gradeSalePrice + memberSalePrice + eventPrice + pointPrice + couponPrice;

    // 최종 결제금액 재계산
    var payPrice = sellPrice + dlvrPrice - sumDiscount;
    $("input[type='hidden'][name='sum_pay_price']").val(payPrice);
    $("#pay_price").html(payPrice.format());
    $("#pay_price_summary").html(payPrice.format());

    // 주문 부족금액 재계산
    var prepayPrice = $("input[type='hidden'][name='prepay_price']").val();
    prepayPrice = prepayPrice.replace(/,/g, '');
    var orderLackPrice = parseInt(prepayPrice) - payPrice;
    $("#order_lack_price").html(orderLackPrice.format());

    var payWay = $("input[type='radio'][name='card_pay_yn']:checked").val();
    if(orderLackPrice < 0 && payWay == 'N') {
        $("#is_order_lack").val("Y");
    } else {
        $("#is_order_lack").val("N");
    }

    showLackWarn(orderLackPrice);
};

/**
 * @brief 주문 목록을 실제로 삭제하는 함수
 *
 * @param $obj = 삭제대상 객체
 * @param length = 삭제할 객체 개수
 */
var removeObj = function ($obj, length) {
    if (length === 1) {
        alert("남은 주문이 한 개일 경우 삭제할 수 없습니다.");
        return false;
    }

    var str = "주문에서 삭제 하시겠습니까?"
    str += "\n(장바구니에 담긴 상태는 유지됩니다.)";

    if (confirm(str) === false) {
        return false;
    }

    $obj.remove();

    return true;
}

/**
 * @brief 나의배송지로 등록 팝업에서 등록버튼 클릭시
 */
var regiDlvrAddr = {
    "pos": null,
    "exec": function () {
        var pos = this.pos;

        if (checkBlank($("#pop_dlvr_name").val())) {
            return alertReturnFalse("업체/상호명을 입력해주세요.");
        }

        var dlvrName = $("#" + pos + "_dlvr_name").val();
        var recei = $("#" + pos + "_recei").val();

        var telNum1 = $("#" + pos + "_tel_num1").val();
        var telNum2 = $("#" + pos + "_tel_num2").val();
        var telNum3 = $("#" + pos + "_tel_num3").val();

        var telNum = '';
        if (checkBlank(telNum1) === false) {
            if (checkBlank(telNum2) === false) {
                if (checkBlank(telNum3) === false) {
                    telNum += telNum1;
                    telNum += '-' + telNum2;
                    telNum += '-' + telNum3;
                }
            }
        }

        var cellNum1 = $("#" + pos + "_cell_num1").val();
        var cellNum2 = $("#" + pos + "_cell_num2").val();
        var cellNum3 = $("#" + pos + "_cell_num3").val();

        var cellNum = '';
        if (checkBlank(cellNum1) === false) {
            if (checkBlank(cellNum2) === false) {
                if (checkBlank(cellNum3) === false) {
                    cellNum += cellNum1;
                    cellNum += '-' + cellNum2;
                    cellNum += '-' + cellNum3;
                }
            }
        }

        if (checkBlank(telNum) && checkBlank(cellNum)) {
            return alertReturnFalse("연락처나 휴대전화를 입력해주세요.");
            closePopup(popupMask);
        }

        var zipcode = $("#" + pos + "_zipcode").val();
        var addr = $("#" + pos + "_addr_top").val();
        var addrDetail = $("#" + pos + "_addr_detail").val();

        var url = "/proc/order/regi_dlvr_addr.php";
        var data = {
            "dlvr_name": escapeHtml(dlvrName),
            "recei": escapeHtml(recei),
            "tel_num": escapeHtml(telNum),
            "cell_num": escapeHtml(cellNum),
            "zipcode": escapeHtml(zipcode),
            "addr": escapeHtml(addr),
            "addr_detail": escapeHtml(addrDetail),
            "dvs": "rm"
        };
        var callback = function (result) {
            if (result === 'F') {
                return alertReturnFalse("나의배송지 등록에 실패했습니다.");
            }

            alert("나의배송지가 등록되었습니다.");

            closePopup(popupMask);
            popupMask = null;
        };

        ajaxCall(url, "text", data, callback);

        this.pos = null;
    }
};

/**
 * @brief 나의배송지 선택 버튼 클릭시 레이어 팝업 출력
 *
 * @param pos = 테이블 위치
 */
var showDlvrAddrListPop = function (pos) {
    setMemberAddrInfo.pos = pos;

    if ($("#to_1_dlvr_way").val() == "02") {
        alert("직배의 경우는 배송지 선택이 불가능합니다.");
        return false;
    }

    var url = "/ajax22/order/load_dlvr_addr_list_pop.php";
    popupMask = layerPopup("l_addressList", url);
};

/**
 * @brief 나의배송지로 등록 버튼 클릭시 레이어 팝업 출력
 */
var showDlvrAddrRegiPop = function (pos) {
    var zipcode = $("#" + pos + "_zipcode").val();
    var addr = $("#" + pos + "_addr_top").val();
    var addrDetail = $("#" + pos + "_addr_detail").val();

    if (checkBlank(zipcode) || checkBlank(addr) || checkBlank(addrDetail)) {
        return alertReturnFalse("주소 정보를 입력해주세요.");
    }

    regiDlvrAddr.pos = pos;

    var url = "/ajax22/order/load_dlvr_addr_regi_pop.html?dlvr_name=";
    url += encodeURI($("#" + pos + "_dlvr_name").val());
    popupMask = layerPopup("l_addressRegister", url);
};

/**
 * @brief 포인트 사용 버튼 클릭시 레이어 팝업 출력
 */
var showPointPop = function () {
    var pay_price = $("#sum_pay_price").val();
    var url = "/ajax22/order/load_point_pop.php?pay_price=" + pay_price;
    popupMask = layerPopup("l_point", url);
};

/**
 * @brief 쿠폰 사용 버튼 클릭시 레이어 팝업 출력
 */
var showCouponPop = function () {
    var seq = getOrderSeqStrAll();

    var url = "/ajax22/order/load_coupon_pop.php?seq=" + seq;
    popupMask = layerPopup("l_coupon", url);
};

/**
 * @brief 포인트 사용금액 적용
 */
var setPointPrice = function () {
    var ownPoint = parseInt($("#own_point").val().replace(/,/g, ''));
    var usePoint = parseInt($("#use_point").val());

    if (ownPoint < usePoint) {
        return alertReturnFalse("보유 포인트보다 사용 포인트가 큽니다.");
    }

    usePoint = ceilVal(usePoint);

    $("input[type='hidden'][name='point']").val(usePoint);
    $("#point").html(usePoint.format());

    closePopup(popupMask);
    popupMask = null;

    calcPrice();
};

/**
 * @brief 회원정보 정보 가져옴
 */
var getMemberInfo = function () {

    var url = "/json/order/load_member_info.php";
    var data = {};
    var callback = function (result) {
        $("#receipt_member_name").val(result.member_name).prop('readonly',true);
        $("#supply_corp").val(result.supply_corp).prop('readonly',true);
        $("#crn").val(result.crn).prop('readonly',true);
        $("#repre_name").val(result.repre_name).prop('readonly',true);
        $("#zipcode").val(result.zipcode).prop('readonly',true);
        $("#addr").val(result.addr).prop('readonly',true);
        $("#bc").val(result.bc).prop('readonly',true);
        $("#tob").val(result.tob).prop('readonly',true);

        if($("#supply_corp").val() != "")
            change_pay_method("N");
        else {
            change_pay_method();
        }
    };

    ajaxCall(url, "json", data, callback);
}

/**
 * @brief 관리사업자 정보 가져옴
 */
var getOrganizerInfo = function () {

    var url = "/json/order/load_organizer_info.php";
    var data = {
        "seqno": $(':radio[name="organizer_chk"]:checked').val()
    };
    var callback = function (result) {
        $("#receipt_member_name").val(result.member_name);
        $("#supply_corp").val(result.supply_corp);
        $("#crn").val(result.crn);
        $("#repre_name").val(result.repre_name);
        $("#addr").val(result.addr);
        $("#bc").val(result.bc);
        $("#tob").val(result.tob);
        closePopup(popupMask);
        popupMask = null;
    };

    ajaxCall(url, "json", data, callback);
}

/**
 * @brief 관리사업자 정보 가져옴
 */
var initPublicInput = function () {
    $("#receipt_member_name").val("").prop('readonly', false);
    $("#supply_corp").val("").prop('readonly', false);
    $("#crn").val("").prop('readonly', false);
    $("#repre_name").val("").prop('readonly', false);
    $("#zipcode").val("").prop('readonly', false);
    $("#addr").val("").prop('readonly', false);
    $("#bc").val("").prop('readonly', false);
    $("#tob").val("").prop('readonly', false);
}


var inicisCardPay = function () {
    var items = [];
    items.push({
        item_name: decodeURIComponent($("#EP_product_nm").val()),
        qty: 1, // 판매한 아이템의 수량
        unique: $("#EP_order_no").val(),
        price: $("#EP_product_amt").val() // 아이템 하나의 단가
    });

    BootPay.request({
        price: $("#EP_product_amt").val(), // 결제할 금액
        application_id: '5ba3513c396fa67d0770339d',
        name: decodeURIComponent($("#EP_product_nm").val()), // 아이템 이름,
        phone: "01050157915",
        order_id: $("#EP_order_no").val(),
        pg: 'inicis',
        method: 'card',//bank
        show_agree_window: 0, // 결제 동의창 띄우기 여부 1 - 띄움, 0 - 띄우지 않음
        items: items,
        user_info: { // 구매한 고객정보 ( 통계 혹은 PG사에서 요구하는 고객 정보 )
            username: $("#from_dlvr_name").val(),
            email: "hyeonsik5015@naver.com",
            addr: $("#from_addr_top").val() + " " + $("#from_addr_detail").val(),
            phone: $("#from_tel_num1").val() + "-" + $("#from_tel_num2").val() + "-" + $("#from_tel_num3").val()
        }
    }).error(function (data) {
        // 결제가 실패했을 때 호출되는 함수입니다.
        var msg = "결제 에러입니다.: " + JSON.stringify(data);
        alert(msg);
        console.log(data);
    }).cancel(function (data) {
        // 결제창에서 결제 진행을 하다가 취소버튼을 눌렀을때 호출되는 함수입니다.
        var msg = "결제 취소입니다.: " + JSON.stringify(data);
        alert(msg);
        console.log(data);
    }).confirm(function (data) {
        // 결제가 진행되고 나서 승인 이전에 호출되는 함수입니다.
        // 일부 결제는 이 함수가 호출되지 않을 수 있습니다. ex) 가상계좌 및 카드 수기결제는 호출되지 않습니다.
        // 만약 이 함수를 정의하지 않으면 바로 결제 승인이 일어납니다.
        this.transactionConfirm(data);
    }).done(function (data) {
        // 결제가 모두 완료되었을 때 호출되는 함수입니다.
        console.log(data);
        //params.ReceiptId = data.receipt_id;
        //params.ReceiptURL = data.receipt_url;
        //params.CardName = data.card_name;
        //params.PaymentName = data.payment_name;
        //params.RequestedAt = data.requested_at;
        //params.PurchasedAt = data.purchased_at;

        $("#card_cpn").val(data.card_code); // 카드종류
        $("#aprvl_num").val(data.receipt_id); // 승인번호
        $("#card_num").val(data.card_no); // 카드번호
        $("#cno").val(data.receipt_id); //거래번호
        $("#frm").submit();
        //goComplete();
    }).ready(function (data) {
        // 가상계좌 번호가 체번(발급) 되었을 때 호출되는 함수입니다.
        console.log(data);
    });

}


/**
 * @brief 결제확인 클릭시 확인 팝업 출력
 */
var showConfirmPop = function () {
    if($("#is_order_lack").val() == "Y" && payWay == "N" && $("#is_except").val() === 'N') {
        alert("선입금이 부족합니다.");
        return false;
    }

    // 카드결제여부
    var payWay = $("input[type='radio'][name='card_pay_yn']:checked").val();

    // 결제금액 0원인지 확인
    if (payWay === 'Y') {
        creditCardParamSet();
        // 카드결제
        var frmPay = document.frm_pay;
        //console.log(frmPay);
        //inicisCardPay();

        easypay_webpay(frmPay,
            "/webpay_card/web/normal/new_iframe_req.php",
            "hiddenifr",
            "0",
            "0",
            "iframe",
            30);


    } else if(payWay === "NAVER") {
        startNaverPay();
    } else if(payWay === "VAC") {
        startNaverPay();
    } else {
        // 선입금일 때
        window.onbeforeunload = null;

        $(".input.to.addr").each(function (idx) {
            idx++;
            unSetDirectLimitOption("to_" + idx);
        });

        $("#frm").submit();
    }

    return;

    // 보내는 사람 그룹정보 생성
    var groupInfo = "to_1=";
    var seqArr = getOrderSeqStr().split('|');
    var seqLen = seqArr.length;
    for (var i = 0; i < seqLen; i++) {
        groupInfo += seqArr[i] + '!';
    }

    // to_1=A!B|to_2=NONE
    $("#to_group").val(groupInfo.substr(0, groupInfo.length - 1));

    // 팝업관련 설정 처리
    var $modalMask = $(".modalMask.l_confirm");
    var $contentsWrap = $modalMask.find('.layerPopupWrap');

    if ($modalMask.outerHeight() > $contentsWrap.height() &&
        $modalMask.outerWidth() > $contentsWrap.width()) {
        //drag
        $contentsWrap.draggable({
            addClasses: false,
            cursor: false,
            containment: $modalMask,
            handle: "header"
        });
    } else {
        $("body").css("overflow", "hidden");
    }

    // 171117 수정됨 : top에서 2가아닌 20으로 나눔
    $modalMask.fadeIn(300, function () {
        $contentsWrap.css({
            'top': $(window).height() > $contentsWrap.height() ?
                ($(window).height() - $contentsWrap.height()) / 20 + 'px' : 0,
            'left': $modalMask.width() > $contentsWrap.width() ?
                ($modalMask.width() - $contentsWrap.width()) / 2 + 'px' : 0
        });

        if (makeConfirmPopInfo() === false) {
            hideConfirmPop();
            return false;
        }

        orderTable($('.l_confirm'));

        $modalMask.addClass("_on")
            .find("button.close")
            .on("click", hideConfirmPop);
    });
};

/**
 * @brief 결제확인 팝업에 존재하는 내용을 생성한다.
 *
 * @return ret = 생성완료시 true, 일부정보 누락시 false
 */
var makeConfirmPopInfo = function () {
    // 주문 부족금액 재계산
    var prepayPrice = $("input[type='hidden'][name='prepay_price']").val();
    prepayPrice = prepayPrice.replace(/,/g, '');
    var orderLackPrice = parseInt(prepayPrice) - payPrice;


    var ret = true;

    var tr = '';
    $("._details").each(function () {
        tr += "<tr>";
        $(this).find("tr").each(function (i) {
            $(this).find("td").each(function (j) {
                if (j > 5) {
                    return false;
                }

                if (j === 2) {
                    tr += "<td style=\"text-align:left;\">";
                } else if (j === 4 || j === 5) {
                    tr += "<td style=\"text-align:right;\">";
                } else {
                    tr += "<td style=\"text-align:center;\">";
                }

                if (j === 0) {
                    tr += $(this).find("span").text();
                } else {
                    tr += $(this).html();
                }

            });
            tr += "</tr>";
        });
    });

    $("#confirm_table").find("tbody").empty();
    $("#confirm_table").append(tr);

    // 주문자 정보
    var fromName = $("#from_dlvr_name").val();

    var fromTelNum1 = $("#from_tel_num1").val();
    var fromTelNum2 = $("#from_tel_num2").val();
    var fromTelNum3 = $("#from_tel_num3").val();

    var fromTelNum = '';
    if (checkBlank(fromTelNum1) === false) {
        if (checkBlank(fromTelNum2) === false) {
            if (checkBlank(fromTelNum3) === false) {
                fromTelNum += fromTelNum1;
                fromTelNum += '-' + fromTelNum2;
                fromTelNum += '-' + fromTelNum3;
            }
        }
    }

    var fromCellNum1 = $("#from_cell_num1").val();
    var fromCellNum2 = $("#from_cell_num2").val();
    var fromCellNum3 = $("#from_cell_num3").val();

    var fromCellNum = '';
    if (checkBlank(fromCellNum1) === false) {
        if (checkBlank(fromCellNum2) === false) {
            if (checkBlank(fromCellNum3) === false) {
                fromCellNum += fromCellNum1;
                fromCellNum += '-' + fromCellNum2;
                fromCellNum += '-' + fromCellNum3;
            }
        }
    }

    if (checkBlank(fromTelNum) && checkBlank(fromCellNum)) {
        return alertReturnFalse("보내시는분 연락처나 휴대전화를 입력해주세요.");
    }

    var fromZipcode = $("#from_zipcode").val();
    var fromAddr = $("#from_addr_top").val() + ' ' + $("#from_addr_detail").val();

    $("#confirm_from_name").html(fromName);
    $("#confirm_from_tel_num").html(fromTelNum);
    $("#confirm_from_cell_num").html(fromCellNum);
    $("#confirm_from_zipcode").html(fromZipcode);
    $("#confirm_from_addr").html(fromAddr);

    // 받는 사람 정보
    var to = "";
    var toLength = getToLength();

    for (var i = 1; i <= toLength; i++) {
        var toIdx = "to_" + i.toString();

        var toDlvrWay = $("#" + toIdx + "_dlvr_way>option:selected").text();
        var toDlvrSumWay = $("input[name='" + toIdx + "_dlvr_sum_way']:checked").val();
        var toName = $("#" + toIdx + "_dlvr_name").val();
        var toRecei = $("#" + toIdx + "_recei").val();
        if (toDlvrSumWay === "01") {
            toDlvrWay += "(선불)";
        } else {
            toDlvrWay += "(착불)";
        }

        var toTelNum1 = $("#" + toIdx + "_tel_num1").val();
        var toTelNum2 = $("#" + toIdx + "_tel_num2").val();
        var toTelNum3 = $("#" + toIdx + "_tel_num3").val();

        var toTelNum = '';
        if (checkBlank(toTelNum1) === false) {
            if (checkBlank(toTelNum2) === false) {
                if (checkBlank(toTelNum3) === false) {
                    toTelNum += toTelNum1;
                    toTelNum += '-' + toTelNum2;
                    toTelNum += '-' + toTelNum3;
                }
            }
        }

        var toCellNum1 = $("#" + toIdx + "_cell_num1").val();
        var toCellNum2 = $("#" + toIdx + "_cell_num2").val();
        var toCellNum3 = $("#" + toIdx + "_cell_num3").val();

        var toCellNum = '';
        if (checkBlank(toCellNum1) === false) {
            if (checkBlank(toCellNum2) === false) {
                if (checkBlank(toCellNum3) === false) {
                    toCellNum += toCellNum1;
                    toCellNum += '-' + toCellNum2;
                    toCellNum += '-' + toCellNum3;
                }
            }
        }

        var toZipcode = $("#" + toIdx + "_zipcode").val();
        var toAddr = $("#" + toIdx + "_addr_top").val() + ' ' +
            $("#" + toIdx + "_addr_detail").val();

        to += "\n<h3 class=\"to\">받으시는 분</h3>";
        to += "\n<table class=\"list order\">";
        to += "\n    <colgroup>";
        to += "\n        <col width=\"80\">";
        to += "\n        <col width=\"180\">";
        to += "\n        <col width=\"120\">";
        to += "\n        <col width=\"120\">";
        to += "\n        <col>";
        to += "\n    </colgroup>";
        to += "\n    <thead>";
        to += "\n        <tr>";
        to += "\n            <th>배송방법</th>";
        to += "\n            <th>성명/상호</th>";
        to += "\n            <th>담당자</th>";
        to += "\n            <th>전화번호</th>";
        to += "\n            <th>휴대전화번호</th>";
        to += "\n            <th>주소</th>";
        to += "\n        </tr>";
        to += "\n    </thead>";
        to += "\n    <tbody>";
        to += "\n        <tr>";
        to += "\n            <td>" + toDlvrWay + "</td>";
        to += "\n            <td>" + toName + "</td>";
        to += "\n            <td>" + toRecei + "</td>";
        to += "\n            <td>" + toTelNum + "</td>";
        to += "\n            <td>" + toCellNum + "</td>";
        to += "\n            <td>[" + toZipcode + "] " + toAddr + "</td>";
        to += "\n        </tr>";
        to += "\n    </tbody>";
        to += "\n</table>";
    }

    $("#confirm_to").html(to);

    // 결제 정보 및 방법
    var payPrice = parseInt($("#pay_price").text().replace(/,/g, ''));
    var sellPrice = parseInt($("#sell_price").text().replace(/,/g, ''));
    var dlvrPrice = parseInt($("#dlvr_price").text().replace(/,/g, ''));
    var cpPrice = parseInt($("#cp_price").val());
    var pointPrice = parseInt($("#point").val());

    var sumPrice = sellPrice + dlvrPrice;

    var gradeSalePrice = sumPrice - payPrice;

    var sumDiscount = cpPrice + pointPrice + gradeSalePrice;

    var payWay = $("input[type='radio'][name='card_pay_yn']:checked").val();
    payWay = (payWay === 'Y') ? "카드" : "선입금";
    var prepayPrice = $("input[type='hidden'][name='prepay_price']").val() + " 원";
    var orderLackPrice = $("#order_lack_price").text() + " 원";

    $("#confirm_sum_price").html(sumPrice.format() + " 원");
    $("#confirm_sum_discount").html(0 + " 원");
    $("#confirm_pay_price").html(payPrice.format() + " 원");
    $("#confirm_pay_way").html(payWay);
    $("#confirm_prepay_price").html(prepayPrice);

    if(payWay === '카드')
        $("#confirm_order_lack_price").html(prepayPrice);
    else
        $("#confirm_order_lack_price").html(orderLackPrice);
};

/**
 * @brief 주문확인 값 검증
 */
var validateConfirm = function () {
    var ret1 = true;
    var ret2 = true;

    // 보내시는 분 - 성명/상호 부분 체크
    if (checkBlank($("#from_dlvr_name").val()) &&
        checkBlank($("#from_recei").val())) {
        return alertReturnFalse("보내시는 분 성명/상호나 담당자를 입력해주세요.");
    }
    // 보내시는 분 - 연락처 or 휴대전화 부분 체크
    ret1 = true;
    if (checkBlank($("#from_tel_num2").val()) ||
        checkBlank($("#from_tel_num3").val())) {
        ret1 = false;
    }
    ret2 = true;
    if (checkBlank($("#from_cell_num2").val()) ||
        checkBlank($("#from_cell_num3").val())) {
        ret2 = false;
    }
    if (!ret1 && !ret2) {
        return alertReturnFalse("보내시는 분 연락처나 휴대전화를 입력해주세요.");
    }
    // 보내시는 분 - 주소 부분 체크
    if (checkBlank($("#from_zipcode").val()) ||
        checkBlank($("#from_addr_top").val())) {
        return alertReturnFalse("보내시는 분 주소정보를 입력해주세요.");
    }
    if (checkBlank($("#from_addr_detail").val())) {
        if (!confirm("보내시는 분 상세주소가 입력되지 않았습니다.\n계속 진행 하시겠습니까?")) {
            return false;
        }
    }

    var tableLength = getToLength();
    for (var i = 1; i <= tableLength; i++) {
        ret1 = true;
        ret2 = true;

        var idx = "to_" + i;

        var dlvrWay = $("#" + idx + "_dlvr_way").val();
        if (dlvrWay != "06" && dlvrWay != "07") {
            // 받으시는 분 - 성명/상호 부분 체크
            if (checkBlank($("#" + idx + "_dlvr_name").val()) &&
                checkBlank($("#" + idx + "_recei").val())) {
                if (dlvrWay != "02") {
                    return alertReturnFalse("받으시는 분 성명/상호나 담당자를 입력해주세요.");
                }
            }
            // 받으시는 분 - 연락처 or 휴대전화 부분 체크
            ret1 = true;
            if (checkBlank($("#" + idx + "_tel_num2").val()) ||
                checkBlank($("#" + idx + "_tel_num3").val())) {
                ret1 = false;
            }
            ret2 = true;
            if (checkBlank($("#" + idx + "_cell_num2").val()) ||
                checkBlank($("#" + idx + "_cell_num3").val())) {
                ret2 = false;
            }

            if (!ret1 && !ret2) {
                if ($("#" + idx + "_dlvr_way").val() == "02") {
                    return alertReturnFalse("월배송 배송지 정보가 올바르지 않습니다.");
                } else {
                    return alertReturnFalse("받으시는 분 연락처나 휴대전화를 입력해주세요.");
                }
            }
            // 받으시는 분 - 주소 부분 체크
            if (checkBlank($("#" + idx + "_zipcode").val()) ||
                checkBlank($("#" + idx + "_addr_top").val())) {
                if ($("#" + idx + "_dlvr_way").val() == "02") {
                    return alertReturnFalse("월배송 배송지 정보가 올바르지 않습니다.");
                } else {
                    return alertReturnFalse("받으시는 분 주소정보를 입력해주세요.");
                }
            }
            if (checkBlank($("#" + idx + "_addr_detail").val())) {
                if ($("#" + idx + "_dlvr_way").val() == "02") {
                    return alertReturnFalse("월배송 배송지 정보가 올바르지 않습니다.");
                } else {
                    if (confirm("받으시는 분 상세주소가 입력되지 않았습니다.\n계속 진행 하시겠습니까?") === false) {
                        return false;
                    }
                }
            }
        }

        /*
        if(!checkBlank($("#unselected_product").val())) {
            return alertReturnFalse("주문선택 되지 않은 상품이 있습니다.");
        }
        */
    }

    return true;
};

/**
 * @brief 결제확인팝업 hide
 */
var hideConfirmPop = function () {
    var $modalMask = $(".l_confirm");

    $modalMask.fadeOut(300, function () {
        $("body").css("overflow", "auto");
    });
};

/**
 * @brief 결제과정 진입 전 값 검증
 */
var chkValue = function () {
    //^^^
    var url = "/ajax22/order/chk_value.php";
    var callback = function (result) {
        if (result === "NO_LOGIN") {
            alert("로그인 후 확인 가능합니다.");
            location.replace("/member/login.html");
            return false;
        }

        /*
         */
        if (result === "ERR") {
            alert("정상적인 주문이 아닙니다.\n다시 로그인 해주세요.");
            location.replace("/common/logout.php");
            return false;
        }

        // 여기까지 왔으면 정상적인 주문이라 생각하고 주소값 확인
        restoreAddr();

    };
    ajaxCall(url, "text", $("#frm").find(':disabled').removeAttr('disabled').serialize(), callback);
};

/**
 * @brief 결제 버튼 클릭시 선택한 결제방식 팝업 출력
 */
var doPay = function () {
    hideConfirmPop();

    // 카드결제여부
    var payWay = $("input[type='radio'][name='card_pay_yn']:checked").val();

    // 결제금액 0원인지 확인
    if (payWay === 'Y') {
        creditCardParamSet();
        // 카드결제
        var frmPay = document.frm_pay;
        easypay_webpay(frmPay,
            "/webpay_card/web/normal/new_iframe_req.php",
            "hiddenifr",
            "0",
            "0",
            "iframe",
            30);
    } else if(payWay === "NAVER") {
        startNaverPay();
    } else if(payWay === "VAC") {
        startNaverPay();
    } else {
        // 선입금일 때
        window.onbeforeunload = null;

        $(".input.to.addr").each(function (idx) {
            idx++;
            unSetDirectLimitOption("to_" + idx);
        });

        $("#frm").submit();
    }
};

/**
 * @brief 주소록 추가 함수
 * DB검색 후 주소가 있으면 넘어가고 없으면 추가해준다
 */
var restoreAddr = function () {
    var dlvrName = $("#to_1_dlvr_name").val(); // 상호명
    var recei = $("#to_1_recei").val(); // 수신자
    var telNum = $("#to_1_tel_num1").val() + '-' +
        $("#to_1_tel_num2").val() + '-' +
        $("#to_1_tel_num3").val(); // 전화번호
    var cellNum = $("#to_1_cell_num1").val() + '-' +
        $("#to_1_cell_num2").val() + '-' +
        $("#to_1_cell_num3").val(); // 휴대전화번호
    var zipcode = $("#to_1_zipcode").val();
    var addrTop = $("#to_1_addr_top").val();
    var addrDet = $("#to_1_addr_detail").val();

    var url = "/proc/order/regi_dlvr_addr.php";
    var data = {
        "dlvr_name": dlvrName,
        "recei": recei,
        "tel_num": telNum,
        "cell_num": cellNum,
        "zipcode": zipcode,
        "addr": addrTop,
        "addr_detail": addrDet,
        "dvs": 'ra'
    };

    var callback = function (result) {
        // 결제 진행
        doPay();
    };

    ajaxCallNoMask(url, "html", data, callback);
}

/**
 * @brief 신용카드 결제시 파라미터 세팅
 * UTF-8을 사용하기 때문에 한글 부분은 전부 인코딩 시켜준다
 */
var creditCardParamSet = function () {
    var ts = new Date();
    ts = ts.getTime();

    // 가맹점 주문번호(EP_order_no)
    $("#EP_order_no").val(ts);
    $("#card_order_num").val(ts);

    var payPrice =
        $("input[type='hidden'][name='sum_pay_price']").val().replace(/,/g, '');

    $("#EP_product_amt").val(payPrice);
};

/**
 * @brief 결제 승인요청 submit
 */
var reqSubmit = function () {
    var frm_pay = document.frm_pay;
    frm_pay.target = "iframe_pay";
    frm_pay.action = "/webpay_card/web/new_easypay_request.php";
    frm_pay.submit();
}

/**
 * @brief 결제완료 페이지로 이동
 */
var goComplete = function () {
    var $obj = $("#iframe_pay").contents().find("body");
    var resCd = $obj.find("#res_cd").val();
    var amount = $obj.find("#amount").val();
    var cpn = $obj.find("#acquirer_nm").val();
    var aprvl = $obj.find("#auth_no").val();
    var card_num = $obj.find("#card_no").val();
    // 180612 추가됨, 카드 주문번호
    var cno = $obj.find("#cno").val();

    if (resCd !== "0000") {
        return alertReturnFalse($obj.find("#res_msg").val());
    }

    var orderLackPrice = $("#order_lack_price").text();
    orderLackPrice = orderLackPrice.replace(/,/g, '').replace(/-/g, '');

    var payWay = $("input[type='radio'][name='card_pay_yn']:checked").val();
    if (payWay === 'Y') {
        orderLackPrice = amount;
    }

    if (amount !== orderLackPrice) {
        return alertReturnFalse("결제 승인금액이 실제와 상이합니다.\n다시 진행해주세요.");
    }

    window.onbeforeunload = null;

    $("#card_cpn").val(cpn);
    $("#aprvl_num").val(aprvl);
    $("#card_num").val(card_num);
    // 180612 추가됨, 카드 주문번호
    $("#cno").val(cno);

    $("#frm").submit();
};

/**
 * @brief 일련번호 문자열 생성, 조건없음
 *
 * @return 문자열
 */
var getOrderSeqStrAll = function () {
    var seqno = ''

    $("input[type='hidden'][name='seq[]']").each(function () {
        seqno += $(this).val() + '|';
    });

    seqno = seqno.substr(0, seqno.length - 1);

    return seqno;
};

/**
 * @brief 일련번호 문자열 생성
 *
 * @return 문자열
 */
var getOrderSeqStr = function (to) {
    //171116 수정
    var selector = "#total_product";

    //var selector = "#unselected_product";
    /* 171113 주석처리
    if (to.indexOf("to_") > -1) {
        // 받으시는 분에서 선택했을 경우
        // 배송방법에 따라 가져오는 selector 틀려짐
        var dlvrWay = $('#' + to + "_dlvr_way").val();
        selector = "#parcel_quick_unselected_product";
    }
    */

    var seqno = $(selector).val();

    return seqno;
};

var getSelecedSeqStr = function (to) {
    var selcted_seq = '';

    $("#" + to).find(".items").find("li").each(function () {
        selcted_seq += $(this).attr('seq') + "|";
    });

    selcted_seq = selcted_seq.substr(0, selcted_seq.length - 1);
    return selcted_seq;
};

/**
 * @brief 쿠폰 팝업에서 적용버튼 클릭시
 */
var applyCoupon = function () {
    var couponDiscount = 0;
    var allDiscountPrice = 0;
    var discounted_product = "";
    $("input[name='cp_seqno[]']:checked").each(function (i) {
        var maxDiscountPrice = 0;
        var discount_product_seqno = 0;
        var couponSeq = $(this).val();
        var categories = $("#categories_" + couponSeq).val();
        var unit = $("#cp_unit_" + couponSeq).val();
        var max_discount_price = $("#cp_max_discount_price_" + couponSeq).val();
        var min_order_price = $("#cp_min_order_price_" + couponSeq).val();
        var discount_val = $("#cp_val_" + couponSeq).val();

        $("input[type='hidden'][name='seq[]']").each(function () {
            var seqno = $(this).val();
            if(!discounted_product.includes(seqno)) {
                var sell_price = $("#sell_price_" + seqno).val();
                var mid_cate = $("#cate_sortcode_" + seqno).val().substr(0, 6);
                if (categories.includes(mid_cate)) {
                    if (unit == "%") {
                        if (maxDiscountPrice < Math.ceil(sell_price * (discount_val / 100))) {
                            maxDiscountPrice = Number(Math.ceil(sell_price * (discount_val / 100) / 10) * 10);
                            discount_product_seqno = seqno;
                        }
                    } else {
                        var tmp_discount_val = discount_val;
                        if (Number(sell_price) < Number(tmp_discount_val))
                            tmp_discount_val = sell_price;
                        if (maxDiscountPrice < tmp_discount_val) {
                            maxDiscountPrice = tmp_discount_val;
                            discount_product_seqno = seqno;
                        }
                    }
                }
            }
        });

        discounted_product += discount_product_seqno + "|";
        couponDiscount += Number(maxDiscountPrice);
        allDiscountPrice += Number(maxDiscountPrice) + "|";
    });

    $("input[type='hidden'][name='coupon_price']").val(couponDiscount);
    $("input[type='hidden'][name='discount_product_seqno']").val(allDiscountPrice);
    $("#cp_span").html(couponDiscount.format());

    closePopup(popupMask);
    popupMask = null;
    calcPrice();
};


/***********************************************************************************
 *** 택배가격 계산, calcPrice() 호출시 첫부분에서 택배가를 계산하는데 쓰인다
 ***********************************************************************************/

/**
 * @brief 주문선택 전체상품 체크
 */
var setTotalValue = function () {
    var total_prod = "";

    $("input[type='hidden'][name='seq[]']").each(function () {
        total_prod += $(this).val() + "|";
    });

    total_prod = total_prod.substr(0, total_prod.length - 1);
    $("#total_product").val(total_prod);
};

/**
 * @brief 배송비 계산
 */
var getDlvrCost = {
    "exec": function (to) {
        if (to === "from") {
            return false;
        }

        var dlvr = $("#" + to + "_dlvr_way").val();
        $("#lb_pre").show();
        $("#lb_on_del").show();
        if(dlvr == "01") {
            //$("input[name='to_1_preset']:eq(0)").prop("checked", true)
            //    .trigger("click");
            //$("input[name=to_1_dlvr_sum_way][value='01']").prop('checked',true);
            //$("input[name=to_1_dlvr_sum_way][value='02']").prop('checked',false);
            $("input[name=to_1_dlvr_sum_way][value='01']").prop('readonly',false);
            $("input[name=to_1_dlvr_sum_way][value='02']").prop('readonly',false);
            $("input[name=to_1_dlvr_sum_way][value='01']").prop('checked', true).trigger("click");
            //$("input[name=to_1_dlvr_sum_way][value='01']").prop('checked', true).trigger("click");
        } else if(dlvr == "04" || dlvr == "05") {
            //$("input[name='to_1_preset']:eq(0)").prop("checked", true)
            //    .trigger("click");
            $("#lb_pre").hide();
            $("input[name=to_1_dlvr_sum_way][value='02']").prop('checked', true).trigger("click");
            $("input[name=to_1_dlvr_sum_way][value='01']").attr('checked',false);
            $("input[name=to_1_dlvr_sum_way][value='01']").prop('readonly',true);
        } else if(dlvr == "02" || dlvr == "06" || dlvr == "07") {
            //$("input[name='to_1_preset']:eq(0)").prop("checked", true)
            //    .trigger("click");
            $("input[name=to_1_dlvr_sum_way][value='01']").prop('checked',false);
            $("input[name=to_1_dlvr_sum_way][value='02']").prop('checked',false);
            $("input[name=to_1_dlvr_sum_way][value='01']").prop('readonly',true);
            $("input[name=to_1_dlvr_sum_way][value='02']").prop('readonly',true);

            $("#lb_pre").hide();
            $("#lb_on_del").hide();
        } else {
            $("#to_1_dlvr_sum_way_pre").attr('readonly',false);
            $("input[name=to_1_dlvr_sum_way][value='01']").prop('checked', true).trigger("click");
        }

        var callback = function (result) {
            if (result.ret < 0) {
                alert(result.msg);
                location.reload();
            }

            getDlvrCost.calc(to);
        };

        chkEmergency(callback);
    },
    "calc": function (to) {
        var seqno = $("#total_product").val();
        var expec_weight = 0;

        var dlvr = $("#" + to + "_dlvr_way").val();
        var zipcode = $("#" + to + "_zipcode").val();

        if(dlvr === "01") {
            if(zipcode == "" || zipcode.includes('-')) {
                zipcode = "04779";
            }
        }
        if (dlvr == "02" || dlvr == "06" || dlvr == "07") {
            // 직배일 경우
            setDirectLimitOption(to);
        } else {
            unSetDirectLimitOption(to);
        }

        if (dlvr == "02" || dlvr == "06" || dlvr == "07") {
            showHideDlvrPlace('1', $('#' + to + '_dlvr_way').parents('.input.to.addr'));
            //setDirectLimitOption(to);
            //$("#" + to + "_new_dlvr").trigger("click");
        } else {
            showHideDlvrPlace('', $('#' + to + '_dlvr_way').parents('.input.to.addr'));
        }

        if (checkBlank(seqno) || checkBlank(zipcode)) {
            return false;
        }

        var url = "/ajax22/order/load_dlvr_cost_info.php";
        var data = {
            "seqno": seqno,
            "dlvr_way": dlvr,
            "zipcode": zipcode
        };

        var callback = function (result) {
            var price_nc = 0;
            var price_bl = 0;
            var island_cost = result.cover.island_cost;
            var boxcount_nc = result.cover.boxcount_nc;
            var boxcount_bl = result.cover.boxcount_bl;
            var expec_weight_nc = result.cover.expec_weight_nc;
            var expec_weight_bl = result.cover.expec_weight_bl;
            var bl = result.cover.bl;
            var nc = result.cover.nc;
            var arr_bl = bl.split('|');

            var priceNcArr = result.cover.price_nc.split('|');
            var priceBlArr = result.cover.price_bl.split('|');
            var ncLength = priceNcArr.length;
            for (var i = 0; i < ncLength; i++) {
                if (!checkBlank(priceNcArr[i])) {
                    price_nc += parseInt(priceNcArr[i]);
                }
            }
            var blLength = priceBlArr.length;
            for (var i = 0; i < blLength; i++) {
                if (!checkBlank(priceBlArr[i])) {
                    price_bl += parseInt(priceBlArr[i]);
                }
            }
            var price = price_nc + price_bl;

            //착불인 경우
            var dlvr_way = $('input[name=' + to + '_dlvr_sum_way]:checked').val();

            if (price == "0" && dlvr == "04" && dlvr == "05") {
                showHideDlvrPlace('', $('#' + to + '_dlvr_way').parents('.input.to.addr'));
                alert("퀵을 이용할 수 없는 지역입니다.");
                $("#" + to + "_dlvr_way").val("01");

                if ($("#" + to + "_zipcode").val() != "") {
                    getDlvrCost.calc(to);
                }

                return;
            } else if (price == "-1") {
                showHideDlvrPlace('', $('#' + to + '_dlvr_way').parents('.input.to.addr'));
                alert("퀵배송 무게한도를 초과했습니다.");
                $("#" + to + "_dlvr_way").find("option:eq(0)");

                if ($("#" + to + "_zipcode").val() != "") {
                    getDlvrCost.calc(to);
                }
            } else {
                // 방문의 경우
                if (dlvr != "06" && dlvr != "07") {
                    showHideDlvrPlace('', $('#' + to + '_dlvr_way').parents('.input.to.addr'));
                    unSetDirectLimitOption(to);
                }

                $("#" + to + "_dlvrcost").html("배송비 : " + price.format() + "원");
                if ((dlvr === "01" || dlvr === "04" || dlvr === "05") && dlvr_way === "02") {
                    $("#" + to + "_dlvr_price").attr('value', '0');
                    $("#" + to + "_bl_price").attr('value', '0');
                    $("#" + to + "_nc_price").attr('value', '0');
                    for(var j = 0; j < arr_bl.length; j++) {
                        $("#dlvr_price_" + arr_bl[j]).val('0');
                    }

                } else {
                    $("#" + to + "_dlvr_price").attr('value', price);
                    $("#" + to + "_bl_price").attr('value', price_bl);
                    $("#" + to + "_nc_price").attr('value', price_nc);

                    for(var i = 0; i < arr_bl.length; i++) {
                        $("#dlvr_price_" + arr_bl[i]).val(priceBlArr[i]);
                    }
                }
                $("#" + to + "_bl_expec_weight").attr('value', expec_weight_bl);
                $("#" + to + "_nc_expec_weight").attr('value', expec_weight_nc);
                $("#" + to + "_bl_boxcount").attr('value', boxcount_bl);
                $("#" + to + "_nc_boxcount").attr('value', boxcount_nc);

                if (island_cost != "0") {
                    boxcount = Number(boxcount_bl) + Number(boxcount_nc);
                    alert("배송지가 도서산간 지역이므로 도서비용이 발생 되었습니다.\n" +
                        island_cost.format() + "(도서비용단가) X " + boxcount + "(덩어리갯수) = " + (Number(island_cost) * Number(boxcount)).format() + "(도서비용합계)");
                }
            }

            $("#" + to + "_bl_group").val(bl);
            $("#" + to + "_nc_group").val(nc);
            calcPrice();
        };

        ajaxCall(url, "json", data, callback);
    }
};

function change_pay_method(value) {
    if(value == "N") {
        if($("#supply_corp").val() != "") {
            $('input[name="public_dvs"][value="세금계산서"]').prop('checked', true);
            $('input[name="public_dvs"][value="세금계산서"]').trigger("click");
            $('input[name="public_dvs"][value="현금영수증"]').prop('disabled', false);
            $('input[name="public_dvs"][value="세금계산서"]').prop('disabled', false);

            $("#pay_with_prepay").show();
            $("#pay_with_card").hide();
        } else {
            $('input[name="public_dvs"][value="현금영수증"]').prop('checked', true);
            $('input[name="public_dvs"][value="현금영수증"]').trigger("click");
            $('input[name="public_dvs"][value="미발행"]').prop('disabled', false);
            $('input[name="public_dvs"][value="세금계산서"]').prop('disabled', false);

            $("#pay_with_prepay").show();
            $("#pay_with_card").hide();
        }
    } else if(value == "Y") {
        $('input[name="public_dvs"][value="미발행"]').prop('checked', true);
        $('input[name="public_dvs"][value="미발행"]').trigger("click");
        $('input[name="public_dvs"][value="현금영수증"]').prop('disabled', true);
        $('input[name="public_dvs"][value="세금계산서"]').prop('disabled', true);

        $("#pay_with_card").show();
        $("#pay_with_prepay").hide();

    } else {
        $('input[name="public_dvs"][value="현금영수증"]').prop('checked', true);
        $('input[name="public_dvs"][value="현금영수증"]').trigger("click");
        $('input[name="public_dvs"][value="미발행"]').prop('disabled', false);
        $('input[name="public_dvs"][value="세금계산서"]').prop('disabled', false);

        $("#pay_with_prepay").show();
        $("#pay_with_card").hide();
    }

    calcPrice();
}

var refreshDeposit = function () {
    var url = "/ajax/common/load_member_prepay.php";
    var data = {
    };
    var callback = function (result) {
        var rs = result.split('@');
        $("#prepay_price").text(rs[1].format());
        $("input[type='hidden'][name='prepay_price']").val(rs[1]);
        alert("현재 잔액은 " + rs[1].format() + " 원입니다.");
        calcPrice();
    };

    ajaxCallNoMask(url, "html", data, callback);
}

function findToFromSeq(seq) {
    var to_group = "";
    $(".input.to.addr").each(function () {
        var id = $(this).attr("id");

        var bl_group = $(this).find("#" + id + "_bl_group").val();
        if (bl_group.indexOf(seq) != -1) {
            to_group = id;
        }

        var nc_group = $(this).find("#" + id + "_nc_group").val();
        if (nc_group.indexOf(seq) != -1) {
            to_group = id;
        }
    });

    return to_group;
};

function removeTo(to) {
    var currentToNum = getToLength();

    if (currentToNum == 1) {
        resetToTable(1, 1);
        calcPrice();
        return;
    }

    $("#to_" + to).remove();

    for (; to < currentToNum; to++) {
        resetToTable(to + 1, to);
    }

    var newNum = currentToNum - 1;
    $("#addTo_" + newNum).show();

    setUnSelectedValue();
    //setToUnSelectedValue();
    calcPrice();
};

/**
 * @brief 받으시는분 개수 반환, hide 된건 제외
 */
var getToLength = function () {
    return $(".order_sheet_table.to").length;
};

/**
 * @brief 받으시는분 전체 초기화하고 숨김
 */
var initTo = function () {
    var toLength = getToLength();
    resetToTable(1, 1);
    //$(".input.to.addr").hide();

    for (var i = toLength; i > 1; i--) {
        $("#to_" + to).remove();
    }
};

/**
 * @brief 배송시 요구사항 처리
 *
 * @param idx = 받으시는 분 위치
 * @param obj = 자기 객체
 */
var setDlvrReq = function (idx) {
    var val = $("#to_" + idx + "_dlvr_req_sel").val();
    var str = $("#to_" + idx + "_dlvr_req_sel").find("option:selected").text();
    if (checkBlank(val)) {
        $("#to_" + idx + "_dlvr_req_sel").hide();
        $("#to_" + idx + "_dlvr_req_txt").show();
        $("#to_" + idx + "_hide_dlvr_req_txt").show();
        $("#to_" + idx + "_dlvr_req_txt").val('');

        return false;
    }

    $("#to_" + idx + "_dlvr_req_txt").val(str);
};

/**
 * @brief 배송시 요구사항 직접입력일 때 셀렉트박스 재출력
 *
 * @param idx = 받으시는 분 위치
 * @param val = 셀렉트박스 선택값
 */
var showDlvrReqSel = function (idx) {
    $("#to_" + idx + "_dlvr_req_sel").val('1');
    setDlvrReq(idx);

    $("#to_" + idx + "_dlvr_req_sel").show();
    $("#to_" + idx + "_dlvr_req_txt").hide();
    $("#to_" + idx + "_hide_dlvr_req_txt").hide();
};

/**
 * @brief 방문일경우 주소와 배송시 요구사항 숨김
 *
 */
var showHideDlvrPlace = function (dvs, obj) {
    var tar = obj.attr('id');
    if (dvs == "1") {
        $('#' + tar + '_zipcode').val("");
        $('#' + tar + '_addr_top').val("");
        $('#' + tar + '_addr_detail').val("");
        $('#' + tar + '_dlvr_req').val("");
        $('#' + tar + '_addr').hide();
        $('#' + tar + '_dlvr_req').hide();
    } else {
        $('#' + tar + '_addr').show();
        $('#' + tar + '_dlvr_req').show();
    }
};

/**
 * @brief 직배일 경우 옵션 선택 제한
 *
 */
var setDirectLimitOption = function (to) {
    $("#" + to + "_bl_group").val("");
    $("#" + to + "_nc_group").val("");
    $("#" + to + "_bl_price").val("0");
    $("#" + to + "_nc_price").val("0");
    $("#" + to + "_bl_expec_weight").val("0");
    $("#" + to + "_nc_expec_weight").val("0");
    $("#" + to + "_bl_boxcount").val("0");
    $("#" + to + "_nc_boxcount").val("0");

    $("#" + to + "_dlvr_price").val("0");
    $("#" + to + "_dlvrcost").html("배송비 : " + 0 + "원");

    $("." + to + "_preset_memb").prop("checked", true);
    $("." + to + "_preset_memb").prop("disabled", true);
    $("." + to + "_preset_new").prop("disabled", true);
    $("input[name='" + to + "_dlvr_sum_way']").prop("disabled", true);
    $("#" + to + "_dlvr_name").prop("disabled", true);
    $("#" + to + "_recei").prop("disabled", true);
    $("#" + to + "_tel_num1").prop("disabled", true);
    $("#" + to + "_tel_num2").prop("disabled", true);
    $("#" + to + "_tel_num3").prop("disabled", true);
    $("#" + to + "_cell_num1").prop("disabled", true);
    $("#" + to + "_cell_num2").prop("disabled", true);
    $("#" + to + "_cell_num3").prop("disabled", true);
    $("#" + to + "_addr_detail").prop("disabled", true);
    $("#" + to + "_dlvr_req_txt").val("");
    $("#" + to + "_dlvr_req").hide();

    loadAddrInfo.exec(to, "direct");
};

/**
 * @brief 직배가 아닐 경우 옵션 선택 제한 해제
 */
var unSetDirectLimitOption = function (to) {
    // to의 형식이 number인 경우
    if (typeof to == "number") {
        to = "to_" + to;
    }

    $("." + to + "_preset_memb").prop("disabled", false);
    $("." + to + "_preset_new").prop("disabled", false);
    $("input[name='" + to + "_dlvr_sum_way']").prop("disabled", false);
    $("#" + to + "_dlvr_name").prop("disabled", false);
    $("#" + to + "_recei").prop("disabled", false);
    $("#" + to + "_tel_num1").prop("disabled", false);
    $("#" + to + "_tel_num2").prop("disabled", false);
    $("#" + to + "_tel_num3").prop("disabled", false);
    $("#" + to + "_cell_num1").prop("disabled", false);
    $("#" + to + "_cell_num2").prop("disabled", false);
    $("#" + to + "_cell_num3").prop("disabled", false);
    $("#" + to + "_addr_detail").prop("disabled", false);
    $("#" + to + "_dlvr_req").show();
};

// 당일판 주문체크
var chkEmergency = function (callback) {
    if (typeof callback === "string") {
        callback = window[callback];
    }

    var emergencyArr = [];
    $("input[name='emergency_order']").each(function () {
        emergencyArr.push($(this).val());
    });

    var url = "/ajax22/order/chk_avail_emergency.php";
    var data = {
        "emergency_arr": emergencyArr
    };

    ajaxCall(url, "json", data, callback);
};

// 선입금 부족경고
var showLackWarn = function (lackPrice) {
    if ($("input[type='radio'][name='card_pay_yn']:checked").val() === 'N' && $("#is_except").val() === 'N' &&
        lackPrice < 0) {
        $("#lack_warn").show();
    } else {
        $("#lack_warn").hide();
    }
};
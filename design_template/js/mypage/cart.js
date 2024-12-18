$(document).ready(function () {
    //lnb 선택 효과
    mypageLnbEffect();
    changeListNum.exec(10);
    calcCartCnt();
    showPopupSyncFlag();
});

/**
 * @brief 선택된 장바구니 목록 삭제
 */
var delCart = function () {
    var seqArr = [];

    $("input[name='seq']:checked").each(function (i) {
        seqArr[i] = $(this).val();
    });

    if (seqArr.length === 0) {
        alert("삭제할 항목을 선택해주세요.");
        return false;
    }

    if (confirm("선택하신 항목을 삭제하시겠습니까?") === false) {
        return false;
    }

    var url = "/proc/order/delete_order.php";
    var data = {
        "seq": seqArr
    };

    var callback = function (result) {
        if (result === 'F') {
            alert("항목 삭제에 실패했습니다.");
            return false;
        }

        location.reload();
    };

    ajaxCall(url, "text", data, callback);
};

var uploadCartFile = function () {
    var seqStr = '';
    var uploadedFlag = false;

    $("input[name='seq']:checked").each(function (i) {
        var seqno = $(this).val();
        seqStr += seqno + '!';

        if ($("#is_upload_" + seqno).val() !== '0') {
            uploadedFlag = true;
        }
    });
    seqStr = seqStr.substr(0, seqStr.length - 1);

    if (seqStr.length === 0) {
        return alertReturnFalse("삭제할 항목을 선택해주세요.");
    }

    if (uploadedFlag) {
        if (!confirm("이미 파일이 업로드된 상품이 있습니다.\n해당 주문의 파일도 변경됩니다.")) {
            return false;
        }
    }

    var url = "/proc/mypage/upload_file.php" +
        "?seqno=" + seqStr;

    var fileName = $("#work_file_list").text();
    if (checkBlank(fileName)) {
        return alertReturnFalse("파일을 업로드해주세요.");
    }

    commonObj.uploader.settings.url = url;
    commonObj.uploader.start();
};

var delCartFile = function () {
    var seqArr = [];

    $("input[name='seq']:checked").each(function (i) {
        seqArr[i] = $(this).val();
    });

    if (seqArr.length === 0) {
        return alertReturnFalse("삭제할 항목을 선택해주세요.");
    }

    if (!confirm("선택하신 주문의\n파일을 삭제하시겠습니까?")) {
        return false;
    }

    var url = "/proc/mypage/cart/delete_cart_file.php";
    var data = {
        "seq": seqArr
    };

    var callback = function (result) {
        if (result === 'F') {
            return alertReturnFalse("주문 파일 삭제에 실패했습니다.");
        } else if (result === "-2") {
            alert("로그아웃 되었습니다.\n다시 로그인해주세요.");
            location.replace("/index.html");
            return false;
        }

        //location.reload();
    };

    ajaxCall(url, "text", data, callback);
};

var showBundleUploadPop = function () {
    $("#" + commonObj.listId).html('');

    if (!checkBlank(commonObj.uploader.files[0])) {
        commonObj.uploader.removeFile(commonObj.uploader.files[0]);
    }

    $("#pop_upload_btn").attr("onclick", "uploadCartFile();");

    $("#uploaded_file_name").html('');

    var $modalMask = $(".modalMask.l_orderInformation");
    var $contentsWrap = $modalMask.find('.layerPopupWrap');

    var hideFunc = function () {
        $modalMask.fadeOut(300, function () {
            $("body").css("overflow", "auto");
        });
    };

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

    $contentsWrap.off("click");
    $contentsWrap.on("click", function (event) {
        event.stopPropagation();
    });

    $modalMask.off("click");
    $modalMask.addClass("_on").on("click", hideFunc);

    $modalMask.find("button.close").off("click");
    $modalMask.addClass("_on").find("button.close").on("click", hideFunc);


    $modalMask.fadeIn(300, function () {
        $contentsWrap.css({
            'top': $(window).height() > $contentsWrap.height() ?
                ($(window).height() - $contentsWrap.height()) / 2 + 'px' : 0,
            'left': $modalMask.width() > $contentsWrap.width() ?
                ($modalMask.width() - $contentsWrap.width()) / 2 + 'px' : 0
        });

        orderTable($modalMask);
    });
};

/**
 * @brief 주문서 작성 페이지로 이동
 *
 * @param dvs = 선택주문인지 전체주문인지 구분값
 */
var goSheet = function (dvs) {
    var selector = "input[name='seq']:checked";
    if (dvs === true) {
        selector = "input[name='seq']";
    }

    var method = "post";
    var seq = '';
    $(selector).each(function (i) {
        seq += $(this).val() + '|';
    });

    if (seq == "") {
        alert("선택된 상품이 없습니다.");
        return;
    }

    var isOut = false;
    $(selector).parent().parent()
        .find("input[name='is_upload']").each(function () {
        if ($(this).val() === '0') {
            isOut = true;
            return alertReturnFalse("업로드되지 않은  파일이 있습니다.");
        }
    });

    if (isOut) {
        return false;
    }

    var form = document.createElement("form");
    form.setAttribute("action", "/order/sheet.html");
    form.setAttribute("method", method);

    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "seq");
    hiddenField.setAttribute("value", seq);

    form.appendChild(hiddenField);
    document.body.appendChild(form);

    form.submit();
};

// 모바일 카트 갯수 구하기
var calcCartCnt = function () {

    if (checkBlank(cnt)) {
        cnt = "0";
    }

    var cnt = $("#m_cart_list").find("input[id^='chk_list']").length;

    $("#cart_cnt").text(cnt);
};

// changeListNum, orderSearch 추가 : 2018-01-24

/**
 * @brief 보여줄 페이지 수 설정
 */
var changeListNum = {
    "listCnt": null,
    "exec": function (val) {
        listCnt = val;
        this.listCnt = val;
        orderSearch(listCnt, 1);
    }
}

/**
 * @brief 선택조건으로 검색 클릭시
 */
var orderSearch = function (listSize, page) {
    var colspan = "9";
    if ($("#member_dvs").val() == "기업") {
        colspan = "10";
    }

    var url = "/ajax/mypage/cart/load_cart_list.php";
    var data = {
        // no data;
    };
    var callback = function (result) {
        if ($("#im").val() == "1") {
            if (result.cate_list == "") {
                $("#m_cart_list").html("<tr><td colspan='4'>카트에 담긴 상품이 없습니다.</td></tr>");
                $("#paging").html("");
                $("#cart_cnt").html("0");
                return false;

            } else {
                $("#m_cart_list").html(result.cart_list);
                $("#paging").html(result.paging);
                $("#cart_cnt").html(result.rsCount);
                $("#sell_price").html(result.price_sell + " 원");
                $("#grade_sale_price").html(result.price_grade + " 원");
                $("#member_sale_price").html(result.price_member + " 원");
                $("#sale_price").html(result.price_sum + " 원");
            }
        } else {
            if (result.cate_list == "") {
                $("#cart_list").html("");
                $("#paging").html("");
                $("#cart_cnt").html("");
                return false;

            } else {
                $("#cart_list").html(result.cart_list);
                adjustBtnBox();
                $("#cart_cnt").html(result.rsCount);
            }

            // 전체 선택
            $("._general").trigger('click');
            calcCartListPriceBox(); // 180131 신규 함수 : 가격 따로 계산함
            orderTable($('body'));
        }
    };

    data.list_num = listSize;
    data.page = page;

    showMask();
    ajaxCall(url, "json", data, callback);
}

/**
 * @brief 페이지 이동
 */
var movePage = function (val) {
    orderSearch(changeListNum.listCnt, val);
}


//레이어팝업 열기
var openLayerPopup = function (num) {
    var pos = num;
    $(".layerpopup_file_upload").show();
    $(".layerpopup_bg").show();

}

//레이어팝업 닫기
var closeLayerPopup = function () {
    $(".layerpopup").hide();
    $(".layerpopup_bg").hide();
}

//장바구니 가격 따로 계산
var calcCartListPriceBox = function (flag) {

    var totalSellP = 0; // 총 판매가
    var totalGradeP = 0; //  총 등급할인가
    var totalMemberP = 0; // 총 회원할인가
    var totalEventP = 0; // 총 이벤트할인가
    var totalAddAftP = 0; // 총 후공정가
    var totalAddOptP = 0; // 총 옵션가

    var totalP = 0; // 총 정상판매가

    var sellP = 0; // 정상판매가
    var gradeP = 0; // 등급할인가
    var memberP = 0; // 회원할인가
    var eventP = 0; // 이벤트할인가
    var addAftP = 0; // 후공정가
    var addOptP = 0; // 옵션가
    var obj = "";

    if (flag == 'all') {
        if ($("._general").is(":checked") == false) {
            sellP += 0;
            sellP += 0;
            gradeP += 0;
            memberP += 0;
            eventP += 0;
            addAftP += 0;
            addOptP += 0;
        } else {
            $("._individual").each(function () {
                obj = $(this).closest('tr').find('input'); // input 확인
                sellP += parseInt(obj[1].value.replace(/,/g, '')); // 정상판매가
                gradeP += parseInt(obj[2].value.replace(/,/g, '')); // 등급할인가
                memberP += parseInt(obj[3].value.replace(/,/g, '')); // 회원할인가
                eventP += parseInt(obj[4].value.replace(/,/g, '')); // 이벤트할인가
                addAftP += parseInt(obj[5].value.replace(/,/g, '')); // 후공정가
                addOptP += parseInt(obj[6].value.replace(/,/g, '')); // 옵션가
            });
        }

    } else {

        $("._individual").each(function () {
            if ($(this).is(":checked") == true) {
                obj = $(this).closest('tr').find('input'); // input 확인
                sellP += parseInt(obj[1].value.replace(/,/g, '')); // 정상판매가
                gradeP += parseInt(obj[2].value.replace(/,/g, '')); // 등급할인가
                memberP += parseInt(obj[3].value.replace(/,/g, '')); // 회원할인가
                eventP += parseInt(obj[4].value.replace(/,/g, '')); // 이벤트할인가
                addAftP += parseInt(obj[5].value.replace(/,/g, '')); // 후공정가
                addOptP += parseInt(obj[6].value.replace(/,/g, '')); // 옵션가

            } else {
                sellP += 0;
                gradeP += 0;
                memberP += 0;
                eventP += 0;
                addAftP += 0;
                addOptP += 0;

            }
        });
    }

    totalSellP = sellP;
    totalGradeP = gradeP.format();
    totalMemberP = memberP.format();
    totalEventP = eventP.format();
    totalAddAftP = addAftP;
    totalAddOptP = addOptP;

    totalP = (sellP + addAftP + addOptP).format();
    calcEndP = (sellP + addAftP + addOptP + gradeP + memberP + eventP).format();

    $("#sell_price").html(totalP);
    $("#grade_sale_price").html(totalGradeP);
    $("#member_sale_price").html(totalMemberP);
    $("#sale_price").html(calcEndP);
}

// 파일관리팝업
var toggleFileBox = function (idx, orderSeqno, fileSeqno, obj, state) {
    if ($("#is_upload_" + orderSeqno).val() === '0') {
        $(obj).closest('tr').attr("no-add", '1');
        $(obj).parent('td').find("._hideOrderDetails").trigger("click");
        showReUploadPop(orderSeqno, state, '', '');
        return false;
    }

    var pos = $(obj).position().top; // 현재 버튼의 top값

    // 열린 팝업 닫기
    $("div.pollSlider").animate({
        "margin-right": "-140px"
    }, 10);
    $("div.pollSlider").css({
        "margin-right": "-140px"
    });
    $(".order_list_set_td").css("background", "#FFF");

    // 마스크 css
    var css = {
        "top": (pos - 25) + "px",
        "background-color": "#FFF",
        "position": "absolute",
        "width": "440px",
        "height": "80px",
        "margin-right": "-440px",
        "margin-bottom": "10px",
        "z-index": "1",
        "right": "0px"
    };
    $(".pollSlider_mask_" + idx).css(css);
    $(".pollSlider._slide_" + idx).css("top", pos - 19);

    if ($(".pollSlider._slide_" + idx).css("margin-right") == "-140px") {
        $(".pollSlider._slide_" + idx).animate({
            "margin-right": '+=180px'
        });
        $(obj).parent('td').css("background", "#a8b7d2");
    }
};

// 버튼박스 위치조절
var adjustBtnBox = function () {

    var css = {
        "margin-right": "-140px",
        "top": "2px"
    };

    var maskCss = {
        "background-color": "#FFF",
        "position": "absolute",
        "width": "440px",
        "height": "80px",
        "margin-right": "-440px",
        "z-index": "1",
        "right": "0px",
        "top": "0px"
    };

    $(".pollSlider").css(css);
    $(".pollSlider_mask").css(maskCss);
}
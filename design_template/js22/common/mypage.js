var popupMask = null;

/**
 * @brief 주문 취소
 */
// 171213 주문취소는 선입금 처리 관련 로직 수정 후 수정할것.
var cancelOrder = function (seq) {

    var url = "/proc/mypage/order_all/proc_order_cancel.php";
    var data = {
        "seqno": seq
    };
    var callback = function (result) {
        if (result.trim() == 1) {
            alert("주문취소 되었습니다.");
            closePopup(popupMask);
            var link = document.location.href;
            var str = link.split('/mypage22/');
            if (str[1].substring(0, 4) == "main") {
                getOrderList('입금');
                getOrderCnt();
            } else {
                orderSearch(30, 1);
            }

        } else if (result.trim() == -1) {
            alert("접수 이후에는 취소할수 없습니다.\n관리자에게 문의해주세요.");
            location.reload();
        } else if (result.trim() == 2) {
            alert("이미 취소된 주문입니다.");
            location.reload();
        } else if (result.trim() == 3) {
            alert("접수 이후에는 취소할수 없습니다.");
            location.reload();
        } else {
            alert("주문취소에 실패했습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 입금대기 or 접수카운트
 */
var getOrderCnt = function () {
    var url = "/ajax22/mypage/main/load_order_cnt.php";
    var data = {};
    var callback = function (result) {
        var tmp = result.split('♪♭§');
        if (tmp[0].trim()) {

            $("#tot_cnt").html(tmp[0]);

            if (tmp[1].trim()) {
                $("#waiting_cnt").html(tmp[1]);
            }

            if (tmp[2].trim()) {
                $("#application_tot_cnt").html(tmp[2]);
            }
            /*
            if (tmp[3].trim()) {
                $("#application_st_cnt").html(tmp[3]);
            }

            if (tmp[4].trim()) {
                $("#application_nw_cnt").html(tmp[4]);
            }

            if (tmp[5].trim()) {
                $("#application_pr_cnt").html(tmp[5]);
            }

            if (tmp[6].trim()) {
                $("#application_de_cnt").html(tmp[6]);
            }
            */
        } else {

        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);



}

function uploadToS3(file, file_path, key) {
    AWS.config.update({
        region: "ap-northeast-2",
        credentials: new AWS.CognitoIdentityCredentials({
            IdentityPoolId: "ap-northeast-2:b95f4416-871e-42b0-8a26-68098cbd9aa0"
        })
    });

    AWS.config.httpOptions.timeout = 0;
    key_name = key + "." + $("#file_name").val().split('.').pop();
    var contentdisposition = 'attachment; filename ="' + encodeURI($("#file_name").val()) + '"';
    var params = {
        Bucket : "orderplatform",
        Key : file_path + "/" + key_name,
        Body : file,
        ContentDisposition : contentdisposition,
        ACL:'public-read-write'
    };

    openFileProgress();
    s3 = new AWS.S3();
    s3.putObject(params).on('httpUploadProgress',
        function (evt) {
            var ratio = (evt.loaded * 100) / evt.total;
            ratio -= 1;
            $(".file_progress_wrapper").css("width",ratio + "%");

            if(ratio < 0) ratio = 0;
            ratio = Math.ceil(ratio);
            $("#ratio").html(ratio + "%");
            if(ratio < 100) {
                //$(".ajax-file-upload-bar").css("width", ratio.toFixed(0) + '%');
                //$(".ajax-file-upload-bar").html(ratio.toFixed(0) + '%');
            }
        }).send(function (err, data) {
        if(err != null) {
            alert("파일 전송중 네트워크 오류가 발생했습니다. 잠시 후 다시 시도해주십시오.");
            deleteUpload();
        } else {
            $(".file_progress_wrapper").css("width","100%");
            $("#ratio").html("100%");
            fileNextFunc();
            /*
            $("#savefile").val(savefile);
            $("#org_file").val(org_file);
            $("#cvt_file").val(org_file);
            $("#filesizeH").val(filesizeH);
            $("#up_sort").val($("#upType").val());	//	시안/후가공 업로드

            $(".ajax-file-upload-bar").css("width", "100%");
            $(".ajax-file-upload-bar").html("100%");

            $("#ajax-file-upload-abort").hide();
            $("#ajax-file-upload-done").show();
            $("#ajax-file-upload-delete").hide();

            if ($("#upType").val() == 'estimate') {						//	견적서 업로드
                var f = document.forderlist;
                f.action = "/adsMng/upload/upload_estimate.php";
                f.target = "";
                f.submit();
            } else {												//	시안/후가공 업로드
                var f = document.forderlist;
                f.action = "/adsMng/upload/upload_after.php";
                f.target = "";
                f.submit();
            }
             */
        }
    });
}

/**
 * @brief 1:1문의
 *
 */
var otoReq = function (seq) {
    var url = "/mypage22/ftf_write.html?order_common_seqno=" + seq;
    $(location).attr('href', url);

};

/**
 * @brief 클레임요청
 */
var reqClaim = function (seq, state) {
    order_seqno = seq;

    if (state != "9120") {
        alert("배송완료시에 클레임요청이 가능합니다.");
        return;
    }

    var url = "/mypage22/claim_write.html?order_common_seqno=" + seq;
    $(location).attr('href', url);

};

/**
 * @brief 주문취소 팝업
 */
var showOrderCancelPop = function (seq) {
    var url = '/mypage22/popup/l_ordercancel.html?order_seqno=' + seq;
    popupMask = layerPopup('l_orderCancel', url);
};

/**
 * @brief 주문취소 팝업
 */
var showReuploadPop = function (seq) {
    var url = '/mypage22/popup/l_ordercancel.html?order_seqno=' + seq;
    popupMask = layerPopup('l_orderCancel', url);
};

/**
 * @brief 시안보기 팝업
 */
var showDraftPop = function (seq) {
    var url = '/mypage22/popup/l_draft.html?seqno=' + seq;
    popupMask = layerPopup('l_draft', url);
};

/**
 * @brief 재주문 팝업 출력
 */
var showReOrderPop = function (seq) {
    commonObj.orderSeqno = seq;

    var url = '/mypage22/popup/l_reorder.html';
    popupMask = layerPopup('l_reorder', url);
};

// 재주문 전에 결제방식 처리
var chkReOrder = function () {
    var payway = $("input[name='reorder_payway']:checked").val();

    if (payway === "prepay") {
        commonObj.cardPayYn = 'N';
        doReOrder();
    } else {
        var url = "/ajax22/mypage/common/load_prdt_price.php";
        var data = {
            "order_seqno": commonObj.orderSeqno
        };
        var callback = function (result) {
            commonObj.cardPayYn = 'Y';
            commonObj.price = result.pay_price;

            creditCardParamSet(result.title, result.pay_price);

            var frmPay = document.frm_pay;
            easypay_webpay(frmPay,
                "/webpay_card/web/normal/iframe_req.php",
                "hiddenifr",
                "0",
                "0",
                "iframe",
                30);
        };

        ajaxCall(url, "json", data, callback);
    }
};

var reqSubmit = function () {
    var frm_pay = document.p_frm_pay;
    frm_pay.target = "iframe_pay";
    frm_pay.action = "/webpay_card_prepay/web/new_easypay_request.php";
    frm_pay.submit();
}
var goComplete = function () {
    var $obj = $("#iframe_pay").contents().find("body");
    var resCd = $obj.find("#res_cd").val();
    var amount = $obj.find("#amount").val();

    if (resCd !== "0000") {
        return alertReturnFalse($obj.find("#res_msg").val());
    }

    if (parseInt(amount) !== parseInt(commonObj.price)) {
        return alertReturnFalse("결제 승인금액이 실제와 상이합니다.\n다시 진행해주세요.");
    }

    doReOrder();
};

/**
 * @brief 신용카드 결제시 파라미터 세팅
 * UTF-8을 사용하기 때문에 한글 부분은 전부 인코딩 시켜준다
 */
var creditCardParamSet = function (title, price) {
    var ts = new Date();
    ts = ts.getTime();

    // 가맹점 주문번호(EP_order_no)
    $("#EP_order_no").val(ts);
    $("#card_order_num").val(ts);
    // 제품명
    $("#EP_product_nm").val(encodeURIComponent(title));
    // 금액
    $("#EP_product_amt").val(price);
};

/**
 * @brief 재주문
 */
var doReOrder = function () {
    var $obj = $("#iframe_pay").contents().find("body");
    var cpn = $obj.find("#acquirer_nm").val();
    var aprvl = $obj.find("#auth_no").val();
    var cno = $obj.find("#cno").val();

    var url = '/order/dup_order.html';
    var data = {
        "order_seqno": commonObj.orderSeqno,
        "card_pay_yn": commonObj.cardPayYn,
        "card_cpn": cpn,
        "aprvl_num": aprvl,
        "cno": cno
    };
    var callback = function (result) {
        if (result.success == "Y") {
            alert("재주문에 성공했습니다.");
            window.location.reload();
        } else {
            alert(result.msg);
        }
    };

    //showMask();
    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 재전송 가능 여부 판별
 *
 * @developer 180213 이청산
 */
var reUploadDist = function (seqno, state) {
    var url = "/ajax22/common/dist_reupload.php";
    var data = {
        "order_seqno": seqno,
        "order_state": state
    };

    var callback = function (result) {
        if (result.success == "1") {
            showReUploadPop(seqno, state, seqno, result.file_path, result.file_name);
        } else {
            alertReturnFalse("파일 재전송이 불가능합니다.");
            location.reload();
        }
    };

    ajaxCall(url, "json", data, callback);
}

var reuploadFile = function(seqno) {
    var url = "/ajax22/common/request_reupload.php";
    var data = {
        "order_seqno": seqno
    };

    var callback = function (result) {
        if (result == "1") {
            location.reload();
        } else {
            alertReturnFalse("파일 재전송이 불가능합니다.");
            location.reload();
        }
    };

    ajaxCall(url, "json", data, callback);
}

/**
 * @brief 재전송 가능 여부 판별
 *
 * @developer 180213 이청산
 */
var reuploadOrder = function (seqno) {
    var url = '/mypage22/popup/l_upload_again.html?order_seqno=' + seqno;
    popupMask = layerPopup('l_upload_again', url);
}
var deliveryTracking = function (seqno) {

    var url = "/ajax22/common/dlvr_check_url.php";
    var data = {
        "order_seqno": seqno
    };

    var callback = function (result) {
        if (result.success == "1") {
            window.open(result.url);
        } else {
            alertReturnFalse("실패.");
            location.reload();
        }
    };

  ajaxCall(url, "json", data, callback);
}

/**
 * @brief 재전송 팝업 출력
 */
var showReUploadPop = function (seq, orderState, fileSeqno, filePath, fileName) {
    $("#file_seqno").val(fileSeqno);
    orderState = parseInt(orderState);

    if (orderState > 2120) {
        return alertReturnFalse("접수 이후에는 재전송이 불가능합니다.");
    }

    if (orderState == 1180) {
        return alertReturnFalse("주문 취소된 상품은 재전송이 불가능합니다.");
    }

    if (1330 <= orderState && orderState <= 1360) {
        return alertReturnFalse("접수중에는 재전송이 불가능합니다.");
    }

    $("#pop_upload_btn").attr("onclick", "upload();");

    $("#uploaded_file_name").html(fileName);
    $("#uploaded_file_name").attr("href", "https://orderplatform.s3.ap-northeast-2.amazonaws.com" + filePath);

    var hideFunc = function () {
        $popupMask.removeClass('on');
        $("body").css("overflow", "auto");
    };

    var $popupMask = $(".popupMask.l_orderInformation");
    var $contentsWrap = $popupMask.find('section.popup');

    $("body").css("overflow", "hidden");

    $contentsWrap.off("click");
    $contentsWrap.on("click", function (event) {
        event.stopPropagation();
    });

    $popupMask.off("click");
    $popupMask.addClass("on").on("click", hideFunc);

    $popupMask.find("button.close").off("click");
    $popupMask.addClass("on").find("button.close").on("click", hideFunc);

    orderTable($popupMask);
};
/**
 * @brief 주문메모 LOAD
 */
var showOrderMemoPop = function (seq) {
    var url = '/mypage22/popup/l_memo.html?order_seqno=' + seq;
    popupMask = layerPopup('l_memo', url);
};

/**
 * @brief 주문파일 다운로드
 */
var downOrderFile = function (orderSeqno, fileSeqno) {
    var downUrl = "/ajax22/mypage/down_order_file.php?order_seqno=" + orderSeqno;
    downUrl += "&file_seqno=" + fileSeqno;

    $("#file_ifr").attr("src", downUrl);
};

/**
 * @brief 마이페이지 lnb 클래스 적용
 */
var mypageLnbEffect = function () {

    var url = location.href;
    var pageName = url.split("/");
    var pageNameLen = pageName.length;
    pageName = pageName[pageNameLen - 1];

    // 환불계좌관리 예외처리
    if (pageName.indexOf("refund_mng") > -1) {
        pageName = "benefits_virtual_mng.html";
    }

    var className = pageName.split(".");
    className = className[0];

    $("#" + className).parents("li").addClass("on");
    $("#" + className).addClass("on");
};

/**
 * @brief 주문 버튼박스 불러오기
 *
 * @comment 다른 열기버튼을 누를 시 열린 슬라이더를 닫고 새로 열리는 방식
 */
var openButtonBox = function (idx, seqno, dvs, flag, state, manager) {
    var dvs = $("#" + dvs);
    var html = "";

    html = makeButtonChild(idx, seqno, dvs, flag, state, manager);
}

/**
 * @brief 주문 버튼박스 불러오기
 *
 */
var closeButtonBox = function (idx) {
    $(".menuWrap._slide_" + idx).closest('td').removeClass('menuOn'); // 버튼박스의 가로값
}

/**
 * @brief 결과 테이블 마스크 미리 세팅
 */
var resultTableSet = function () {
    var i = 1;
    $(".pollSlider").each(function () {
        $(".pollSlider_mask_" + i).css("height", "61px");
        $(".pollSlider_mask_" + i).css("width", "440px");
        $(".pollSlider_mask_" + i).css("background-color", "#FFF");
        $(".pollSlider_mask_" + i).css("z-index", "1");
        $(".pollSlider_mask_" + i).css("right", "0px");
        $(".pollSlider_mask_" + i).css("margin-right", "-440px");
        $(".pollSlider_mask_" + i).css("position", "absolute");
        i++;
    });
}

/**
 * @brief 회원 메모 불러오기
 */
var showClientMemo = function (seqno) {

    var url = "";
    var data = "";
    var callback = function (result) {
    }

    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 버튼 박스 내용물 만들기
 * 
 * @developer 180207 이청산
 *
 * @comment 원래 openButtonBox에서 만들었으나 넓은 활용도를 위해 분리시킴
 *
 * @param idx   : 리스트에서의 순서,
 *        seqno : 주문 일련번호,
 *        dvs   : td id,
 *        flag  : 버튼이 쓰이는 페이지 구분값,
 *        state : 주문상태 구분값
 */
var makeButtonChild = function (idx, seqno, dvs, flag, state, manager) {

    var count = 0; // 버튼 카운트

    var html = ""; // 버튼 html
    html += '<ul>';

    //html += makeButtonDetail(seqno, "showDraftPop(" + seqno + ")", "03", "미리보기");

    // 메모 - 1320까지
    // 배송지변경 - 1320까지
    // 취소 - 2120까지
    // 재접수요청 - 2120까지
    // 취소요청 - 2120까지


    if(state <= 1300 ) {

    }
    if(state == 1325) {
       // html += makeButtonDetail(seqno, "reUploadDist(" + seqno + ", " + state + ")", "05", "파일재업로드");
    }

    if(state <= 1325) {
        if(manager == ""){ // 접수 담당자가 있을경우 취소가 안되도록 처리 
            html += makeButtonDetail(seqno, "showOrderCancelPop(" + seqno + ")", "02", "주문취소");
        }else{
            html += "";
        }
            html += makeButtonDetail(seqno, "changeDeliveryAddr(" + seqno + ")", "04", "배송지변경");
            html += makeButtonDetail(seqno, "showOrderMemoPop(" + seqno + ")", "07", "메모");
        if(state != 1325){
 //html += makeButtonDetail(seqno, "reuploadOrder(" + seqno + ")", "05", "파일재업로드");
        }
           
			//html += makeButtonDetail(seqno, "deliveryTracking(" + seqno + ")", "06", "배송추적");
    } else if(state <= 2120) {
        //html += makeButtonDetail(seqno, "reuploadOrder(" + seqno + ")", "05", "파일재업로드");
        //html += makeButtonDetail(seqno, "deliveryTracking(" + seqno + ")", "06", "배송추적");
        //html += makeButtonDetail(seqno, "showOrderCancelPop(" + seqno + ")", "02", "취소요청");
    } else if(state >= 3220) {
        html += makeButtonDetail(seqno, "deliveryTracking(" + seqno + ")", "06", "배송추적");
    }else {
        //alert("오류입니다. 관리자에게 문의하세요.");
        return false;
        /*
        html += makeButtonDetail(seqno, "showOrderCancelPop(" + seqno +")", "02", "주문취소");
        html += makeButtonDetail(seqno, "showDraftPop(" + seqno + ")", "03", "미리보기");
        html += makeButtonDetail(seqno, "", "04", "배송지변경");
        html += makeButtonDetail(seqno, "showReUploadPop(" + seqno + ")", "05", "파일재전송");
        html += makeButtonDetail(seqno, "otoReq(" + seqno + ")", "06", "1:1문의");
        html += makeButtonDetail(seqno, "showOrderMemoPop(" + seqno + ")", "07", "메모");
        html += makeButtonDetail(seqno, "", "09", "재주문");

        count = 6;
        */
    }

    html += '</ul>';
    makeButtonFrame(count, idx);
    $(".menuWrap._slide_" + idx).html(html);
    buttonAnimate(idx, dvs, count);

    return html;
}

/**
 * @brief 버튼 생성 함수
 *
 * @developer 180207 이청산
 *
 * @comment 하나의 함수로 여러가지의 버튼 생성
 *
 * @param seqno    : 주문번호
 *        funcName : 함수명
 *        num      : 그림파일 번호
 *        name     : 버튼명
 */
var makeButtonDetail = function (seqno, func, num, name) {

    var html = "";

    html += '<li>';
    html += '<button type="button" onclick="' + func + '" class="icon' + num + '" title=' + name + '>' + name + '</button>';
    html += '</li>';

    return html;
}

/**
 * @brief 버튼박스를 움직이는 함수 
 *
 * @developer 180207 이청산
 *
 * @comment 버튼박스를 움직임
 *
 * @param idx : 순서
 *        dvs : obj
 *        wpx : 버튼박스의 원소 수
 *        pos : 버튼박스의 top 값
 */
var buttonAnimate = function (idx, dvs, count) {

    // 기존에 열린 버튼이 있다면 닫는다
    dvs.closest('table').find('td.menuOn').removeClass('menuOn');
    dvs.closest('td').addClass('menuOn')

}

/**
 * @brief 버튼박스 영역 생성
 *
 * @developer 180208 이청산
 * @comment 버튼박스를 영역을 생성함
 *
 * @param count : 버튼박스의 원소 수
 */
var makeButtonFrame = function (count, idx) {
    var count = parseInt(count); // 버튼박스의 원소 수
    var fixedPx = 62; // px기준값
    var wid = count * fixedPx;
    var movPx = "";
    var widPx = wid + 10 + "px"; // wid + px > ex) 220px

    var css = {
        "width": widPx,
        "margin-right": "-" + widPx
    };

    $(".pollSlider._slide_" + idx).css(css);
}

/**
 * @brief 배송지 csv 다운로드
 *
 * @developer 180313 이청산
 * @comment 배송지 목록을 엑셀 형식으로 다운로드
 */
var dlvrExcelDown = function () {
    var url = "/ajax22/mypage/delivery_address/down_address_list.php";

    $("#file_ifr").attr("src", url);
}

/**
 * @brief 배송지 csv 업로드
 *
 * @developer 180313 이청산
 * @comment csv 파일 업로드
 */
var showExcelUploadPopup = function () {
    var url = '/mypage22/popup/l_csv.html';
    popupMask = layerPopup('l_csv', url);
}

/**
 * @brief 주문배송지 변경
 *
 * @developer 180326 이청산
 */
var changeDeliveryAddr = function (seqno) {

    var url = "/ajax22/mypage/delivery_address/load_bun_yn.php";
    var data = {
        "order_seqno": seqno
    };

    var callback = function (result) {
        // 주문상태에서 걸림 or 배송이 묶여있음
        /*
        if (result == 0 || result == 2) {
            alertReturnFalse("주문배송지 변경 가능한 조건이 아닙니다. 고객센터에 문의해주세요.");
        } else {
            var url = '/mypage22/popup/l_address_modify.html?order_seqno=' + seqno;
            popupMask = layerPopup('l_address_modify', url);
        }
        */
        var url = '/mypage22/popup/l_address_modify.html?order_seqno=' + seqno;
        popupMask = layerPopup('l_address_modify', url);
    }

    ajaxCall(url, "html", data, callback);
};

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

            if (isFunc("reCalcDlvrPrice") === true) {
                reCalcDlvrPrice(data.zonecode);
            }
        }
    }).open({
        pupName: 'postcodePopup'
    });
};



/**
 * @brief 다음 API 주소검색 함수
 *
 */
var getPostcode2 = function () {
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
            document.getElementById('zipcode2').value = data.zonecode; //5자리 새우편번호 사용
            document.getElementById('addr2').value = fullAddr;

            // 커서를 상세주소 필드로 이동한다.
            document.getElementById('addr_detail2').focus();

            if (isFunc("reCalcDlvrPrice") === true) {
                reCalcDlvrPrice(data.zonecode);
            }
        }
    }).open({
        pupName: 'postcodePopup'
    });
};

/**
 * @brief 메모 수정
 */
var modiMemo = function (seqno) {
    var url = "/proc/mypage/order_all/proc_order_memo.php";
    var data = {
        "order_seqno": seqno,
        "cust_memo": $("#cust_memo").val()
    };
    var callback = function (result) {

        if (result == '1') {
            alert("메모 수정에 성공했습니다.");
            location.reload();
        } else {
            return alertReturnFalse("메모 수정에 실패했습니다.");
        }
    };

    ajaxCall(url, "html", data, callback);

};

/**
 * 240313 팬텀디자인 코드 추가 출석체크 submit
 */
var attCheck = function (seqno, mb_id) {
    console.log("chk");
    var url = "/proc/mypage/attCheck/attCheck.php";
    var data = {
        "ae_seqno": seqno,
        "mb_id": mb_id
    };
    var callback = function (result) {

        if (result == '1') {
            alert("출석체크 되었습니다.");
            location.reload();
        } else {
            return alertReturnFalse("이미 오늘은 출석체크를 완료하였습니다.");
        } 
    };

    ajaxCall(url, "html", data, callback);

};
var cartFlag = true;
var emailPop = null;
var file;
var filename = '';
var filesize = '';
var s3;

var strArray = '';
var cvt_file = '';
var filesizeH = '';
var savefile = '';
var org_file = '';

$(document).ready(function () {
    //document.getElementById("file_progress").style.width = "50%";
    $(".file_progress_wrapper").css("width","0%");
    if (typeof $.fn.elevateZoom === "function") {
        /*
        $('#pic_view').elevateZoom({
            scrollZoom: true,
            objId: "zoom_pic"
        });

        // 재질미리보기 처리 --> 최초이미지 없으니까 처리 안됨 필요함
        $('#paper_preview').elevateZoom({
            scrollZoom: true,
            objId: "zoom_preview",
            zoomWindowOffetx: -169
        });
        */

    }
    paperViewOnOff();
});

/**
 * @brief 비규격 사이즈 입력시 자리수 재계산
 * product_design.js에서 호출한다
 */
var calcManuPosNum = {
    "defWid": 0,
    "defVert": 0,
    "maxWid": 0,
    "maxVert": 0,
    "exec": function (dvs) {
        if ($("#no_pos").length > 0) {
            return false;
        }

        var prefix = '';

        if (checkBlank(dvs)) {
            if ($("#common_prdt_dvs").length > 0) {
                dvs = commonDvs;
            } else {
                dvs = prdtDvs;
            }
            prefix = getPrefix(dvs);
        } else {
            prefix = getPrefix(dvs);
        }

        if (dvs === "st" || dvs === "ao_placard" || dvs === "etc" || dvs === "nc") {
            // 스티커는 자리수로 계산 안함
            return false;
        }

        if ($(prefix + "manu_pos_num").length === 0) {
            loadSimilarSize(dvs);
            return false;
        }

        var w = parseFloat($(prefix + "cut_wid_size").val());
        var v = parseFloat($(prefix + "cut_vert_size").val());

        var calW = (w / this.defWid) + 0.99;
        var calH = (v / this.defVert) + 0.99;
        var tmp1 = Math.floor(calW) * Math.floor(calH);

        calW = (w / this.defVert) + 0.99;
        calH = (v / this.defWid) + 0.99;
        var tmp2 = Math.floor(calW) * Math.floor(calH);

        if (calW === 1 || calH === 1) {
            $(prefix + "manu_pos_num").val(1);
        } else if (tmp1 > tmp2) {
            $(prefix + "manu_pos_num").val(tmp2);
        } else {
            $(prefix + "manu_pos_num").val(tmp1);
        }

        calcPrice();
    }
};

/**
 * @brief 전단류의 상품에서 비규격 사이즈 입력시 가장 근접한 규격사이즈 계산
 *
 * @param dvs = 제품구분값
 * @param callback = 콜백함수
 */
var loadSimilarSize = function (dvs, callback) {
    var prefix = getPrefix(dvs);

    var w = parseFloat($(prefix + "cut_wid_size").val());
    var v = parseFloat($(prefix + "cut_vert_size").val());

    if (w === 0 || v === 0) {
        return false;
    }

    var url = "/ajax22/product/load_similar_size.php";
    var data = {
        'w': w,
        'v': v,
        "cate_sortcode": $(prefix + "cate_sortcode").val(),
        "paper_mpcode": $(prefix + "paper").val()
    };

    if (checkBlank(callback)) {
        callback = function (result) {
            if (!checkBlank(result.max_wid)) {
                alert("용지 최대크기를 넘겼습니다.");

                $(prefix + "cut_wid_size").val(result.max_wid);
                $(prefix + "cut_vert_size").val(result.max_vert);
                size();
                orderSummary();
            }

            var str = result.name + " 1/" + result.divide + " 등분";

            $(prefix + "similar_size").attr("divide", result.divide);
            $(prefix + "similar_size").html(str);
            $(prefix + "size").val(result.mpcode);
            changeSize.exec(dvs);
        };
    }

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 지질느낌 검색
 *
 * @param dvs    = changeData(). callback()에서 쓰일 구분값
 * @param mpcode = 종이 맵핑코드
 */
var loadPaperDscr = {
    "obj": null,
    "exec": function (dvs, mpcode) {
        var prefix = getPrefix(dvs);
        this.obj = prefix + "paper_sense";

        var url = "/ajax22/product/load_paper_dscr.php";
        var data = {
            "mpcode": mpcode
        };
        var callback = function (result) {
            $(loadPaperDscr.obj).html(result);
        };

        ajaxCall(url, "text", data, callback);

        changeData();
    }
};

/**
 * @brief 상품 수량과 후공정/옵션의 기준단위를 비교해서
 * 값을 통일시키는 함수
 *
 * @detail case1 : 후공정 R, 상품 장 = 상품 / 500
 * case2 : 후공정 장, 상품 R = 상품 * 500
 *
 * @param amt      = 상품수량
 * @param amtUnit  = 수량단위
 * @param crtrUnit = 후공정/옵션 기준단위
 */
var amtCalc = function (dvs, amt, amtUnit, crtrUnit) {
    if (amtUnit === "R" && crtrUnit === "장") {
        amt = calcSheetCount(dvs);
    } else if (amtUnit === "장" && crtrUnit === "R") {
        amt = calcRCount(dvs);
    }

    return amt;
};

/**
 * @brief 인쇄도수 변경시 인쇄용도 변경
 */
var changeTmpt = function (dvs) {
    var prefix = getPrefix(dvs);
    var val = $(prefix + "print_tmpt > option:selected").text();

    var url = "/ajax22/product/load_print_purp.php";
    var data = {
        "cate_sortcode": $(prefix + "cate_sortcode").val(),
        "val": val
    };
    var callback = function (result) {
        $(prefix + "print_purp").html(result);
        changeData();
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @param 인쇄방식과 사이즈 계열에 해당하는 인쇄도수 검색
 *
 * @param dvs = 제품구분값
 * @param callback = ajax callback 함수
 */
var loadPrintTmptCommon = {
    "exec": function (dvs, callback) {
        var prefix = getPrefix(dvs);

        var url = "/ajax22/product/load_print_tmpt.php";
        var data = {
            "cate_sortcode": $(prefix + "cate_sortcode").val(),
            "tmpt_name": $(prefix + "print_tmpt > option:selected").text(),
            "bef_tmpt_name": $(prefix + "bef_tmpt > option:selected").text(),
            "aft_tmpt_name": $(prefix + "aft_tmpt > option:selected").text(),
            "purp_dvs": $(prefix + "print_purp").val(),
            "affil": $(prefix + "size > option:selected").attr("affil")
        };

        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 견적서 출력 > 이메일 발송 팝업 출력
 */
var showEmailPop = function () {
    var url = "/ajax22/product/load_email_pop.php";
    emailPop = layerPopup("l_email", url);
};

/**
 * @brief 견적서 팝업에서 이메일 발송 클릭시
 */
var sendEmail = function () {
    var url = "/ajax22/product/send_email.php";
    var data = makeEstiPopInfo.data;
    data.email_dvs = $("input[name='emailAddressType']:checked").val();
    data.m_acc = $("#m_acc").val();
    data.m_dom = $("#m_dom").val();
    data.d_acc = $("#d_acc").val();
    data.d_dom = $("#d_dom").val();
    var callback = function (result) {
        closePopup(emailPop);
        emailPop = null;

        if (result === 'F') {
            return alertReturnFalse("이메일 전송에 실패했습니다.");
        }

        return alertReturnFalse("이메일 전송에 성공했습니다.");
        hideMask();
    };

    showMask();
    ajaxCall(url, "text", data, callback);
};

/**
 * @brief 즉시주문
 */
var purProduct = function () {
    cartFlag = false;
    orderNextFunc();
};

/**
 * @brief 관심상품 등록
 */
var goWishlist = function () {
    if (!setSubmitParam()) {
        return false;
    }

    var url = "/mypage/add_wishlist.html";
    var data = $("#frm").serialize();
    var callback = function (result) {
        if (!checkBlank(result)) {
            alert(result);
        } else {
            alert("해당 상품을 관심상품에 추가했습니다.");
        }
    };

    showMask();
    ajaxCall(url, "text", data, callback);
};

/**
 * @brief 장바구니로 이동
 */
var goCart = function () {
    cartFlag = true;
    orderNextFunc();
};

var orderNextFunc = function () {
    if (setSubmitParam() === false) {
        return false;
    }

    var emergency = null;
    $("input[name='chk_opt']").each(function (i) {
        if ($(this).val() === "당일판" && $(this).prop("checked")) {
            emergency = $("#opt_" + i + "_val > option:selected").text();
            return false;
        }
    });

    var url = "/ajax22/product/chk_avail_emergency.php";
    var data = {
        "emergency": emergency
    };
    var callback = function (result) {
        if (result.ret < 0) {
            alert(result.msg);
            location.reload();
        }

        if($("#file_content").html() == "") {
            alert("파일을 첨부해주세요");
            return false;
        }

        if (checkBlank($("#title").val())) {
            if (confirm("업로드하신 파일명을 인쇄물제목으로 사용하시겠습니까?")) {
                $("#title").val($("#file_name").val());
            } else {
                return false;
            }
        }

        if (true) {
            var url = "/proc/order/upload_file_info.php";
            var data = {
                "file_name": $("#file_name").val(),
                "file_size": $("#file_size").val()
            };
            var callback = function (result) {
                var asd = JSON.parse(result);
                $("#file_seqno").val(asd.file_seqno);
                openFileProgress();
                uploadToS3(file,asd.file_path, asd.file_seqno);
            };

            ajaxCall(url, "text", data, callback);
            //commonObj.uploader.start();
        } else {
            if (checkBlank($("#title").val().trim())) {
                $("#cart_flag").val('Y');
                $("#title").focus();
                return alertReturnFalse("인쇄물 제목을 입력해주세요.");
            }

            fileNextFunc();
        }
    };

    ajaxCall(url, "json", data, callback);
};

function uploadToS3(file, file_path, key) {
    AWS.config.update({
        region: "ap-northeast-2",
        credentials: new AWS.CognitoIdentityCredentials({
            IdentityPoolId: "ap-northeast-2:279a2875-8c16-4e36-8bbb-833c6a591298"
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

var fileNextFunc = function () {
    var order_common_seqno = $("#order_common_seqno").val();
    var file_seqno = $("#file_seqno").val();
    //$("#file_seqno").val(commonObj.fileSeq);
    $("#oper_sys").val("W");
    var memo = document.createElement("input");
    $(memo).attr({
        "name": "cust_memo",
        "type": "hidden"
    })
        .val($("#cust_memo").val());
    $("#frm").append($(memo));

    // 180723 추가, 자사이미지 사용여부
    var owncomp_img = document.createElement("input");
    $(owncomp_img).attr({
        "name": "owncompany_img_num",
        "type": "hidden"
    })
        .val($(":input:radio[name=owncompany_img_num]:checked").val());

    $("#frm").append($(owncomp_img));

    var func = null;
    if (isFunc("submitNextFunc")) {
        func = window["submitNextFunc"];
    }

    if (checkBlank(file_seqno) && order_common_seqno == '') {
        return alertReturnFalse("파일을 업로드해주세요.");
        if (cartFlag &&
            confirm("파일을 업로드하지 않고 추가하시겠습니까?")) {
            /*
            if (checkBlank($("#title").val().trim())) {
                $("#title").focus();
                if (func !== null) {
                    func();
                }
                return alertReturnFalse("인쇄물 제목을 입력해주세요.");
            }
             */
        } else {
            if (func !== null) {
                func();
            }
            return alertReturnFalse("파일을 업로드해주세요.");
        }
    }

    if (order_common_seqno != '' || cartFlag) {
        if (order_common_seqno != '' || confirm("계속 쇼핑하시겠습니까?")) {
            $("#cart_flag").val('A');

            var url = "/order/add_cart.html";
            var data = $frm.serialize();
            var callback = function (result) {
                if(order_common_seqno != '') {
                    alert("수정 완료");
                }
                if (!checkBlank($.trim(result))) {
                    alert(result);
                }

                $("#file_seqno").val('');
                $("#oper_sys").val('');
                $("#file_content").html('');
                closeFileProgress();

                if (func !== null) {
                    func();
                }
            };

            showMask();
            ajaxCall(url, "text", data, callback);

            return false;
        }
    }

    if (cartFlag) {
        $("#cart_flag").val('Y');
    } else {
        $("#cart_flag").val('N');
    }

    $("#frm").attr("action", "/order/add_cart.html");
    $("#frm").submit();
};

/**
 * @brief 제품별 접두사 생성
 *
 * @return 제품 구분값별 접두사
 */
var getPrefix = function (dvs) {
    if (checkBlank(dvs) === true) {
        return '#';
    } else {
        return '#' + dvs + '_';
    }
}

/**
 * @brief 사이즈에 따른 연단위별 종이 장수 계산
 *
 * @param dvs = 제품 구분값
 */
var calcSheetCount = function (dvs) {
    var prefix = getPrefix(dvs);

    var amt = $(prefix + "amt").val();
    var posNum = $(prefix + "size").find("option:selected").attr("pos_num");
    var divide = $(prefix + "similar_size").attr("divide");

    if (checkBlank(divide)) {
        divide = 1;
    }

    amt = parseFloat(amt);
    posNum = parseFloat(posNum);
    divide = parseFloat(divide);

    if (amt < 0.0) {
        amt = 0.0;
    }

    var sheetCount = amt * divide * posNum * 500.0;

    // 재단 추가후공정 on
    /*
    if (divide === 1.0) {
        $(prefix + "cutting").prop("checked", false);
    } else {
        $(prefix + "cutting").prop("checked", true);
    }

    $(prefix + "cutting").trigger("click");
    */

    $(prefix + "sheet_count").val(sheetCount);
    $("#sheet_count_span").html(sheetCount.format());
};

/**
 * @brief 사이즈에 따른 장수별 종이 연수 계산
 */
var calcRCount = function (dvs) {
    var prefix = getPrefix(dvs);

    var amt = $(prefix + "amt").val();
    var posNum = $(prefix + "size").find("option:selected").attr("pos_num");
    var divide = $(prefix + "similar_size").attr("divide");

    if (checkBlank(divide)) {
        divide = 1;
    }

    amt = parseFloat(amt);
    posNum = parseFloat(posNum);
    divide = parseFloat(divide);

    if (amt < 0) {
        return 0;
    }

    var sheetCount = amt / divide / posNum / 500.0;
    /*
    sheetCount *= 10.0;
    sheetCount  = Math.ceil(sheetCount) / 10.0;
    */

    // 재단 추가후공정 on
    /*
    if (divide === 1.0) {
        $(prefix + "cutting").prop("checked", false);
    } else {
        $(prefix + "cutting").prop("checked", true);
    }

    $(prefix + "cutting").trigger("click");
    */

    $(prefix + "r_count").val(sheetCount);
    $("#r_count_span").html(sheetCount.format());
};

/**
 * @brief 등분에 따라 실인쇄 수량 계산
 *
 * @param dvs = 제품 구분값
 */
var calcPrdtAmt = function (dvs) {
    var prefix = getPrefix(dvs);
    var amtUnit = $(prefix + "amt").attr("amt_unit");
    var divide = parseInt($(prefix + "similar_size").attr("divide"));

    $(prefix + "size > option").each(function () {
        var val = parseInt($(this).val()) * divide;

        $(this).val(val);
    });
};

//특수문자 값 빈값 리턴
function inputCheckSpecial() {
    var re = /[\{\}\[\]\/?.,;:|\)*~`!^\+<>@\#$%&\\\=\(\'\"]/gi; //-_제외
    var tmp = $("#title").val();
    return $("#title").val(tmp.replace(re, ""));
}

//제품보기
var productPreview = function () {
    $("#mat_btn").show();
    $("#pro_btn").hide();
    $("#mov_btn").hide();

    $("#picture").show();
    $("#mat_preview").hide();
    $("#mov_preview").hide();

    $("#zoom_pic").show();
    $("#zoom_preview").hide();
}

//재질보기
var matPreview = function () {
    var aftStr = $.trim($(".overview").text());

    $("#mat_btn").hide();

    if (aftStr.indexOf("접지") > -1) {
        $("#pro_btn").hide();
        $("#mov_btn").show();
    } else {
        $("#pro_btn").show();
        $("#mov_btn").hide();
    }

    $("#picture").hide();
    $("#mat_preview").show();
    $("#mov_preview").hide();

    $("#zoom_pic").hide();
    $("#zoom_preview").show();
}

//동영상보기
var movPreview = function () {
    $("#util_btns").hide();
    $("#mov_btns").show();

    $("#picture").hide();
    $("#mat_preview").hide();
    $("#mov_preview").show();

    $("#zoom_pic").hide();
    $("#zoom_preview").hide();

    document.getElementById("mov").currentTime = 0;
};

// 동영상 경로 변경
var setAfterMovSrc = function (dvs) {
    var prefix = getPrefix(dvs);

    var url = "/ajax22/product/load_after_mov_src.php";
    var data = {
        "depth1": $(prefix + "foldline_dvs > option:selected").text(),
        "depth2": $(prefix + "foldline_val > option:selected").text()
    };
    var callback = function (result) {
        $("#mov_src").attr("src", result);

        var mov = document.getElementById("mov");
        if (mov !== null) {
            mov.load();
            mov.play();
            movPreview();
        }
    };

    ajaxCall(url, "text", data, callback);
};

// 동영상 확대
var movZoomIn = function () {
    $("#mov").attr({
        "width": 720,
        "height": 720
    });
    $("._anchor.ui-draggable-handle").hide();

    $("#mov_zoom_in").hide();
    $("#mov_zoom_out").show();
};

// 동영상 축소
var movZoomOut = function () {
    $("#mov").attr({
        "width": 400,
        "height": 400
    });
    $("._anchor.ui-draggable-handle").show();

    $("#mov_zoom_in").show();
    $("#mov_zoom_out").hide();
};

// 동영상 닫기
var closeMovPreview = function () {
    $("#util_btns").show();
    $("#mov_btns").hide();

    movZoomOut();
    productPreview();
};

//상세보기
var imgDetailView = function () {
    var src = $("#pic_view").attr("src");
    layerPopup('l_imgDetailView', '/product/popup/l_imgDetailView.html?src=' + src);
}

/**
 * @brief 빠른 견적서 내용 변경
 *
 * @param param = 견적서 내용값
 */
var changeQuickEsti = function (param) {
    var paper = param.paper;
    var print = param.print;
    var cut = param.cut;
    var output = param.output;
    var afterTax = param.afterTax * 1.1;
    var afterNoTax = param.afterTax;
    var opt = param.opt;
    var count = param.count;

    var gradeSaleRate = param.gradeSaleRate;
    var amtRate = param.amtRate;
    var amtAplc = param.amtAplc;
    var sellPrice = param.sellPrice;
    var salePrice = sellPrice * gradeSaleRate;
    var amtSalePrice = (sellPrice + salePrice) * amtRate + amtAplc;
    if (isNaN(amtSalePrice)) {
        amtSalePrice = 0;
    }
    salePrice = ceilVal(salePrice + amtSalePrice); // 할인가


    var supplyPaper = ceilVal(paper / 1.1);
    var supplyPrint = ceilVal(print / 1.1);
    var supplyCut = ceilVal(cut / 1.1);
    var supplyOutput = ceilVal(output / 1.1);
    var supplyAfter = afterNoTax;
    var supplyOpt = ceilVal(opt / 1.1);

    var supplyPrice = supplyPaper + supplyPrint + supplyCut + supplyOutput + supplyAfter + supplyOpt;

    var paperTax = paper - supplyPaper;
    var printTax = print - supplyPrint;
    var outputTax = output - supplyOutput;
    var cutTax = cut - supplyCut;
    var afterTax = afterTax - afterNoTax;
    var optTax = opt - supplyOpt;

    var tax = paperTax +
        printTax +
        cutTax +
        outputTax +
        afterTax +
        optTax;

    tax = ceilVal(tax);
    if (supplyPaper === 0) {
        $(".esti_paper_info").hide();
    } else {
        $(".esti_paper_info").show();
    }

    if (supplyOutput === 0) {
        $(".esti_output_info").hide();
    } else {
        $(".esti_output_info").show();
    }

    if (supplyPrint === 0) {
        $(".esti_print_info").hide();
    } else {
        $(".esti_print_info").show();
    }

    if (supplyCut === 0) {
        $(".esti_cut_info").hide();
    } else {
        $(".esti_cut_info").show();
    }

    // 견적서 종이비 변경
    $("#esti_paper").html(supplyPaper.format());
    // 견적서 출력비 변경
    $("#esti_output").html(supplyOutput.format());
    // 견적서 인쇄비 변경
    $("#esti_print").html(supplyPrint.format());
    // 견적서 재단비 변경
    $("#esti_cut").html(supplyCut.format());
    // 견적서 옵션비 변경
    $("#esti_opt").html(supplyOpt.format());
    // 견적서 건수 변경
    $("#esti_count").html(count.format());
    // 견적서 공급가 변경
    $("#esti_supply").html(supplyPrice.format());
    // 견적서 부가세 변경
    $("#esti_tax").html(tax.format());
    // 견적서 정상판매가 변경
    $("#esti_sell_price").html(sellPrice.format());
    // 견적서 할인금액 변경
    $("#esti_sale_price").html(salePrice.format());
    // 견적서 결제금액 변경
    $("#esti_pay_price").html((sellPrice).format());
};

/**
 * @brief 종이 분류 변경시 해당하는 종이명 검색
 *
 * @param dvs  = 위치구분
 * @param sort = 종이분류
 */
var loadPaperName = function (dvs, sort, callback) {
    var url = "/ajax/product/load_paper_name.php";
    var data = {
        "cate_sortcode": sortcode,
        "sort": sort,
    };

    if (checkBlank(callback)) {
        callback = function (result) {
            var prefix = getPrefix(dvs);
            $(prefix + "paper_name").html(result);

            loadPaperInfo(dvs, $(prefix + "paper_name").val(), sort);
        };
    }

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 종이명 변경시 해당하는 색상-구분-평량 검색
 *
 * @param dvs  = 위치구분
 * @param name = 종이명
 * @param sort = 종이분류
 */
var loadPaperInfo = function (dvs, name, sort, callback) {
    var prefix = getPrefix(dvs);
    var prefix1 = prefix;
    if(prefix == "#cover_" || prefix == "#inner1_" || prefix == "#inner2_" || prefix == "#inner3_") {
        prefix1 = "#ad_"
    }
    var url = "/ajax22/product/load_paper_info.php";
    var data = {
        "cate_sortcode": $(prefix1 + "cate_sortcode > option:selected").val(),
        "sort": sort,
        "name": name
    };

    if (checkBlank(callback)) {
        callback = function (result) {
            $(prefix + "paper").html(result);

            loadPaperPreview(dvs);
            loadPaperDscr.exec(dvs, $(prefix + "paper").val());
            reCalcAfterPrice(dvs, null);
        };
    }

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 후공정 체크된 것 중 다시 맵핑코드 다시 불러올 것만 처리
 *
 * @param dvs = 제품구분
 */
var getAftInfoArr = function (dvs) {
    var ret = [];
    var prefix = getPrefix(dvs);

    $("input[type='checkbox'][name='" + dvs + "_chk_after[]']").each(function () {
        //if ($(this).prop("checked") === false) {
        //    return true;
        //}

        var aftEnName = $(this).attr("aft");
        var aftKoName = $(this).val();
        var info = {};

        if (aftKoName === "코팅") {
            var depth1 =
                $(prefix + aftEnName + "_val > option:selected").text();
            depth1 = depth1.split(' ')[0];

            info.name = aftKoName;
            //info.depth1 = depth1;
            ret.push(info);

            return true;
        }
        if (aftKoName === "오시") {
            var depth1 =
                $(prefix + aftEnName + "_cnt > option:selected").text();

            info.name = aftKoName;
            info.depth1 = depth1;
            ret.push(info);

            return true;
        }
        if (aftKoName === "미싱") {
            var depth1 =
                $(prefix + aftEnName + "_cnt > option:selected").text();

            info.name = aftKoName;
            info.depth1 = depth1;
            ret.push(info);

            return true;
        }
        if (aftKoName === "접지") {
            var depth1 =
                $(prefix + aftEnName + "_dvs > option:selected").text();

            info.name = aftKoName;
            info.depth1 = depth1;
            ret.push(info);

            return true;
        }
        if (aftKoName === "넘버링") {
            var depth1 =
                $(prefix + aftEnName + "_dvs > option:selected").text();

            info.name = aftKoName;
            info.depth1 = depth1;
            ret.push(info);

            return true;
        }
        if (aftKoName === "제본") {
            var depth1 =
                $(prefix + aftEnName + "_dvs > option:selected").text();

            info.name = aftKoName;
            info.depth1 = depth1;
            ret.push(info);

            return true;
        }
        if (aftKoName === "도무송") {
            var depth1 =
                $(prefix + aftEnName + "_dvs > option:selected").text();

            info.name = aftKoName;
            info.depth1 = depth1;
            ret.push(info);

            return true;
        }
        if (aftKoName === "전체빼다") {
            var depth1 =
                $(prefix + aftEnName + "_val > option:selected").text();

            info.name = aftKoName;
            info.depth1 = depth1;
            ret.push(info);

            return true;
        }
        if (aftKoName === "타공") {
            var depth1 =
                $(prefix + aftEnName + "_cnt > option:selected").text();

            info.name = aftKoName;
            info.depth1 = depth1;
            ret.push(info);

            return true;
        }
    });

    return ret;
};

/**
 * @brief 책자형에서 실제 종이수량 계산
 *
 * @param = 계산용 파라미터
 */
var getBookletPaperAmt = function (param) {
    var amt = param.amt;
    var posNum = param.posNum;
    var page = param.page;

    return (amt / posNum) / (2.0 / page);
};

/**
 * @brief 템플릿 다운로드 팝업 출력
 */
var showTemplatePop = function () {
    var url = "/ajax22/product/template_pop/" + sortcode + ".html";
    var data = '<header><h2>준비중</h2><button class="close" title="닫기"><img src="/design_template/images/common/btn_circle_x_white.png" alt="X"></button></header><article class="error">업로드 된 템플릿이 없습니다.</article>';
    layerPopup("l_templete", url, data);
};

/**
 * @brief 출고일 확인 팝업 출력
 */
var showDeliveryPop = function () {
    var url = "/ajax/product/load_delivery_pop.php?";
    url += "cs=" + sortcode;
    url += "&cn=" + encodeURI(cateName);
    url += "&date=" + $("#expect_date").text();
    url += "&time=" + $("#expect_hour").text() + ':' + $("#expect_min").html();
    layerPopup("l_delivery", url);
};

/**
 * @brief 미리보기 배경이미지 칼선으로 변경
 */
var changePreviewImg = function (stanName) {
    var url = "/design_template/images/product/preview/" + stanName + ".jpg";
    $(".paper .content").hide();
    $(".paper").css({
        "background-image": "url(" + url + ')',
        "background-size": "100% 100%",
        "border": "0px"
    });
};

/**
 * @brief 책자형 실제 종이수량 계산
 */
var getPaperRealPrintAmt = function (dvs) {
    var prefix = getPrefix(dvs);

    var amt = parseFloat($(prefix + "amt").val());
    var posNum = parseFloat($(prefix + "size > option:selected").attr("pos_num"));
    var page = 0;

    var arrLength = dvsArr.length;
    for (var i = 0; i < arrLength; i++) {
        var dvs = dvsArr[i];
        var pfx = getPrefix(dvs);

        if (!dvsOnOff[dvs]) {
            continue;
        }

        var val = parseInt($(pfx + "page").val());
        page += val;
    }

    return (amt / posNum) / (2.0 / page);
};

/**
 * @brief 재질 미리보기 이미지 load
 *
 * @param dvs = 제품구분값
 * @parma id  = 객체 아이디 직접입력
 */
var loadPaperPreview = function (dvs, id) {
    var prefix = getPrefix(dvs);

    if (checkBlank(id)) {
        id = prefix + "paper";
    }

    var url = "/json/product/load_paper_preview.php";
    var data = {
        "paper_mpcode": $(id).val()
    };
    var callback = function (result) {
        $("#paper_preview").attr({
            "src": result.zoom,
            "data-zoom-image": result.zoom
        });

        $("#zoom_preview .zoomWindow").css("background-image",
            "url(" + result.zoom + ")");
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 인쇄용도 바뀌었을 때 설명 출력, 가격검색
 *
 * @param dvs = 제품구분
 */
var changePrintPurp = function (dvs) {
    showUvDescriptor(dvs);

    if (isFunc("loadPrintTmpt")) {
        loadPrintTmpt(dvs);
    }
};

/**
 * @brief 인쇄용도 UV일 경우 설명 출력
 *
 * @param dvs = 제품구분
 */
var showUvDescriptor = function (dvs) {
    var html = "<dt id=\"uv_descriptor\" style=\"width:100%; font-size:11px; letter-spacing: -1px; color:#555555; line-height: 1.6;\">";
    html += "    &nbsp;&nbsp;UV인쇄는 특수잉크를 사용하며<br/>";
    html += "    &nbsp;&nbsp;인쇄기자체에 말리는 장치가 달려있어,<br/>";
    html += "    &nbsp;&nbsp;인쇄후 건조가 잘 안되는 인쇄물을<br/>";
    html += "    &nbsp;&nbsp;인쇄할 때 효과가 탁월히 나타납니다.";
    html += "</dt>";

    var prefix = getPrefix(dvs);
    var purp = $(prefix + "print_purp").val();

    if (purp.indexOf("UV") > -1) {
        $(prefix + "print_purp").parent().parent().append(html);
    } else {
        $("#uv_descriptor").remove();
    }
};

/**
 * @brief 재단사이즈 최대/최소 사이즈 체크
 */
var chkMaxMinSize = {
    "exec": function (dvs, addFunc) {
        var prefix = getPrefix(dvs);

        var paperName = $(prefix + "paper > option:selected").text();
        if ($(prefix + "paper_name").length > 0) {
            paperName = $(prefix + "paper_name > option:selected").text();
        }

        var url = "/json/product/load_max_min_size.php";
        var data = {
            "cate_sortcode": sortcode,
            "paper_name": paperName
        };
        var callback = function (result) {
            var $wid = $(prefix + "cut_wid_size");
            var $vert = $(prefix + "cut_vert_size");
            var cutWid = parseInt($wid.val());
            var cutVert = parseInt($vert.val());

            var minWid = result.min_wid;
            var minVert = result.min_vert;
            var maxWid = result.max_wid;
            var maxVert = result.max_vert;

            if(maxWid == null || maxVert == null || maxWid == "" || maxVert == "") {
                if (!checkBlank(addFunc)) {
                    addFunc();
                }
                return;
            }
            var str = "최대사이즈는 " + maxWid + "*" +
                maxVert + "입니다.";

            if (cutWid > maxWid) {
                alert(str);
                $wid.val(maxWid);
                size();
            }
            if (cutVert > maxVert) {
                alert(str);
                $vert.val(maxVert);
                size();
            }

            str = "최소사이즈는 " + minWid + "*" +
                minVert + "입니다.";

            if (cutWid < minWid) {
                alert(str);
                $wid.val(minWid);
                size();
            }
            if (cutVert < minVert) {
                alert(str);
                $vert.val(minVert);
                size();
            }

            if (!checkBlank(addFunc)) {
                addFunc();
            }

            if (sortcode === "004003009") {
                getFreeTomsonPrice('', prdtDvs, getFreeTomsonParam());
            } else if (sortcode.substr(0, 3) !== "008") {
                aftRestrict.cutting.common(dvs);
            }
        };

        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 재단, 작업사이즈 가로세로 교환
 *
 * @parma dvs     = 제품구분값
 * @parma checked = 값을 변경할지, 되돌릴지 여부
 */
var swapSize = {
    "orgCutW": 0,
    "orgCutV": 0,
    "orgWorkW": 0,
    "orgWorkV": 0,
    "exec": function (dvs, checked) {
        var prefix = getPrefix(dvs);

        var cutW = $(prefix + "cut_wid_size").val();
        var cutV = $(prefix + "cut_vert_size").val();
        //var workW = $(prefix + "work_wid_size").val();
        //var workV = $(prefix + "work_vert_size").val();
        var workW = $("#work_wid_size").val();
        var workV = $("#work_vert_size").val();

        // 적용값
        var aplyCutW = 0;
        var aplyCutV = 0;
        var aplyWorkW = 0;
        var aplyWorkV = 0;

        if (checked) {
            this.orgCutW = cutW;
            this.orgCutV = cutV;
            this.orgWorkW = workW;
            this.orgWorkV = workV;

            if (parseInt(cutW) < parseInt(cutV)) {
                aplyCutW = cutV;
                aplyCutV = cutW;
            } else {
                aplyCutW = cutW;
                aplyCutV = cutV;
            }

            if (parseInt(workW) < parseInt(workV)) {
                aplyWorkW = workV;
                aplyWorkV = workW;
            } else {
                aplyWorkW = workW;
                aplyWorkV = workV;
            }
        } else {
            aplyCutW = this.orgCutW;
            aplyCutV = this.orgCutV;
            aplyWorkW = this.orgWorkW;
            aplyWorkV = this.orgWorkV;
        }


        $(prefix + "cut_wid_size").val(aplyCutW);
        $(prefix + "cut_vert_size").val(aplyCutV);
        //$(prefix + "work_wid_size").val(workV);
        //$(prefix + "work_vert_size").val(workW);
        $("#work_wid_size").val(aplyWorkW);
        $("#work_vert_size").val(aplyWorkV);

        if (typeof preview !== "undefined") {
            preview.paperSize();
        }
    }
};

/**
 * @brief 카테고리 템플릿 파일 다운로드
 *
 * @param seqno = 템플릿 일련번호
 * @param dvs   = 파일 구분값
 */
var downloadTemplate = function (dvs, seqno) {
    if (checkBlank(seqno)) {
        return false;
    }

    var url = "/product/common/template_file_down.php?";
    url += "&seqno=" + seqno;
    url += "&dvs=" + dvs;

    $("#file_ifr").attr("src", url);
};

/**
 * @brief 파일 업로드 진행도 레이어 열기
 */
var openFileProgress = function () {
    $("#file_uploading").show();
}

/**
 * @brief 파일 업로드 진행도 레이어 닫기
 */
var closeFileProgress = function () {
    $("#file_progress").css("width", "0%");
    $("#file_uploading").hide();
}

/**
 * @brief 파일 업로드 취소
 */
var cancelFileUpload = function () {
    var fileId = $("#file_id").val();

    if (confirm("파일 업로드가 취소됩니다. 계속하시겠습니까?") === false) {
        return false;
    }
    commonObj.removeList(fileId);
    closeFileProgress();
}

//
var estiDataHolder = {
    "data": null,
    "exec": function (param) {
        this.data = param;
    }
}

// 출고예정일
var loadReleaseExpect = function () {
    var prefix = null;
    if (typeof prdtDvs !== "undefined") {
        prefix = getPrefix(prdtDvs);
    } else if (!checkBlank(commonDvs)) {
        prefix = getPrefix(commonDvs);
    }

    var paperInfo = '';
    if ($("#paper_name").length > 0) {
        paperInfo += $("#paper_name").val() + ' ';
    }
    paperInfo += $(prefix + "paper > option:selected").text();

    var sizeInfo = "비규격";
    if ($("#size_dvs").val() === "stan") {
        sizeInfo = $(prefix + "size > option:selected").text();
    } else if ($(prefix + "similar_size").length > 0) {
        sizeInfo = $(prefix + "similar_size").text();
    }

    var pageArr = [];
    var afterInfo = '';
    var dvsArr = $("#prdt_dvs").val().split('|');
    var length = dvsArr.length;
    for (var i = 0; i < length; i++) {
        var dvs = dvsArr[i];

        $('.' + dvs + "_overview li").each(function () {
            if ($(this).text().indexOf("후가공") > -1) {
                return true;
            }
            afterInfo += $(this).text() + '|';
        });

        pageArr.push($('#' + dvs + "_page").val());
    }

    if (typeof commonDvs !== "undefined") {
        $("input[name='" + commonDvs + "_chk_after[]']").each(function () {
            var aft = $(this).attr("aft")
            var pfx = prefix + aft + '_';

            afterInfo += $(this).val() + " [";
            if ($(pfx + "depth1").length > 0) {
                afterInfo += $(pfx + "depth1 > option:selected").text() + '/';
            }
            if ($(pfx + "val").length > 0) {
                afterInfo += $(pfx + "val > option:selected").text();
            }
            afterInfo += ']|';
        });
    }

    var url = "/ajax22/product/load_release_expect.php";
    var data = {
        "cate_sortcode": $(prefix + "cate_sortcode").val(),
        "paper_info": paperInfo,
        "size_info": sizeInfo,
        "amt": $(prefix + "amt").val(),
        "page_arr": pageArr,
        "after_info": afterInfo.substr(0, afterInfo.length - 1),
        "emergency": isCheckedEmergency()
    };
    var callback = function (result) {
        var year = result.year;
        var month = result.month;
        var day = result.day;
        var dayTxt = result.day_txt;
        var hour = result.hour;
        var min = result.min;
        var sec = result.sec;

        if (month === "-1" || day === "-1" || hour === "-1") {
            return alertReturnFalse("출고예정일 계산불가");
        }

        $("#expect_day").html(dayTxt);
        $("#expect_date").html(month + '.' + day);
        $("#expect_hour").html(hour);
        $("#expect_min").html(min);
    };

    ajaxCall(url, "json", data, callback);
};

// 예상배송비
var loadDlvrPriceExpect = function (flag, cb = null) {
    var prefix = null;
    if (typeof prdtDvs !== "undefined") {
        prefix = getPrefix(prdtDvs);
    } else if (!checkBlank(commonDvs)) {
        prefix = getPrefix(commonDvs);
    }

    var dvsArr = $("#prdt_dvs").val().split('|');
    var amt = $(prefix + "amt").val();
    var amtUnit = $(prefix + "amt").attr("amt_unit");
    var count = 1;
    var posNum = $(prefix + "size > option:selected").attr("pos_num");
    var pageArr = [];
    var paperArr = [];
    var cutWid = $(prefix + "cut_wid_size").val();
    var cutVert = $(prefix + "cut_vert_size").val();

    if(cutWid == "") {
        cutWid = $(prefix + "size").attr("def_cut_wid");
    }
    if(cutVert == "") {
        cutVert = $(prefix + "size").attr("def_cut_vert");
    }
    if ($("#count").length > 0) {
        count = $("#count").val();
    }
    if ($(prefix + "sheet_count").length > 0) {
        amt = $(prefix + "sheet_count").val();
    }

    var length = dvsArr.length;
    for (var i = 0; i < length; i++) {
        var dvs = dvsArr[i];
        var tmp = getPrefix(dvs);

        if ($(tmp + "page").length > 0) {
            pageArr.push($(tmp + "page").val());
        }
        if ($(tmp + "paper").length > 0) {
            paperArr.push($(tmp + "paper").val());
        }
    }

    var basisweight = '';
    if ($("#paperTop").length > 0) {
        basisweight += $("#paperTop > option:selected").text()
            .split(' ')
            .pop();
    }
    if ($("#paperMid1").length > 0) {
        basisweight += '|' + $("#paperMid1 > option:selected").text()
            .split(' ')
            .pop();
    }
    if ($("#paperMid2").length > 0) {
        basisweight += '|' + $("#paperMid2 > option:selected").text()
            .split(' ')
            .pop();
    }
    if ($("#paperBot").length > 0) {
        basisweight += '|' + $("#paperBot > option:selected").text()
            .split(' ')
            .pop();
    }

    var url = "/ajax22/product/load_dlvr_price_expect.php";
    var data = {
        "dvs_arr": dvsArr,
        "cate_sortcode": $(prefix + "cate_sortcode").val(),
        "size": $(prefix + "size > option:selected").text(),
        "amt": amt,
        "amt_unit": amtUnit,
        "count": count,
        "pos_num": posNum,
        "page_arr": pageArr,
        "paper_arr": paperArr,
        "cut_wid_size": cutWid,
        "cut_vert_size": cutVert,
        "basisweight": basisweight
    };
    var callback = function (result) {
        var weight = result.weight;
        var box = result.box;
        var price = result.price;

        $("#expect_weight").val(weight.format());
        $("#expec_weight").val(weight);
        $("#expect_box").val(box.format());
        // 180427 추가 -> 배송 덩어리값
        $("#expect_box_num").val(box.format());
        $("#expect_dlvr_price").html(price.format());

        if (flag === false) {
            return false;
        }

        var dvs = null;
        if (typeof prdtDvs != "undefined") {
            dvs = prdtDvs;
        } else if (typeof commonDvs != "undefined") {
            dvs = commonDvs;
        }

        reCalcOptPrice(dvs, null);
        if(cb != null)
            cb();
    };

    ajaxCall(url, "json", data, callback);
};

// 당일판 체크확인
var isCheckedEmergency = function () {
    var ret = 0;
    $("input[name='chk_opt']").each(function () {
        if ($(this).val() === "당일판" && $(this).prop("checked")) {
            ret = 1;
            return false;
        }
    });

    return ret;
};

/**
 * @brief 제본 예시팝업 출력
 */
var showBindingPop = function () {
    var url = "/ajax22/product/load_binding_pop.php";
    layerPopup("l_binding", url);
};

/**
 * @brief 종이 보이기/숨기기(그린백때문에 추가)
 */
var paperViewOnOff = function () {
    var con = "1";
    if (con == "1") {
        $(".paper_view").hide();
    }
};

/**
 * @brief 박 옵션 추가
 */
var addFoilSection = function(i, el, dvs) {

    var price = $("#" + el + "_price").val();
    if(i == 2) {
        $("#" + el + "_ec_price").val(price);
    }
    var ec_price = $("#" + el + "_ec_price").val();
    var url = "/ajax22/product/load_foil_html.php";
    var data = {
        "el": el,
        "ec_price": ec_price,
        "dvs": dvs,
        "i": i
    };
    var callback = function (result) {

        if(i == 2) {
            ec_price = $("#" + el + "_ec_price").val();
            $("#" + el + "_price").val(ec_price * 2);
            $("#plusBtn1").hide();

            $("#addFoilSection2").html(result);
            $("#foilCnt").val(i);

        } else if (i == 3) {
            ec_price = $("#" + el + "_ec_price").val();
            $("#" + el + "_price").val(ec_price * 3);
            $("#plusBtn2").hide();
            $("#minusBtn2").hide();
            $("#plusBtn3").hide();

            $("#addFoilSection3").html(result);
            $("#foilCnt").val(i);
        }
    };

    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 박 옵션 제거
 */
var removeFoilSection = function(i, el, dvs) {
    var ec_price = $("#" + el + "_ec_price").val();
    if(i == 2) {
        $("#" + el + "_price").val(ec_price);
        $("#addFoilSection2").html("");
        $("#plusBtn1").show();
    } else if (i == 3) {
        $("#" + el + "_price").val(ec_price * 2);
        $("#addFoilSection3").html("");
        $("#plusBtn2").show();
        $("#minusBtn2").show();
    }
    getAfterPrice.common('foil', dvs);
    afterOverview(dvs);
}

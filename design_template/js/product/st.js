var tmptDvs = null;
var monoYn = null;
var prdtDvs = null;
var sortcode = null;
var cateName = null;
var amtUnit = null;
// 도무송스티커 여백 전역변수
var spaceL = 0; // 최좌측 여백
var spaceR = 0; // 최우측 여백
var spaceT = 0; // 최상단 여백
var spaceB = 0; // 최하단 여백
var minTom = 10; // 도무송 최소 사이즈

$(document).ready(function () {
    // 건수 초기화
    var order_count = $("#order_count").val();
    initCount(999, "count", order_count);

    monoYn = $("#st_mono_yn").val();
    tmptDvs = $("#st_tmpt_dvs").val();
    prdtDvs = $("#prdt_dvs").val();
    sortcode = $("#st_cate_sortcode").val();
    cateName = $("#st_cate_sortcode").find("option:selected").text();
    amtUnit = $("#st_amt").attr("amt_unit");

    showUvDescriptor(prdtDvs);
    setSizeWarning();

    var order_cut_wid = $("#order_cut_wid").val();
    var order_cut_vert = $("#order_cut_vert").val();
    var order_size_name = $("#order_size_name").val();
    var order_stan_name = $("#order_stan_name").val();

    if(order_cut_wid == "" &&
        (sortcode == "004003009" || sortcode == "004004001")) {
        order_cut_wid = 50;
    }

    if(order_cut_vert == "" &&
        (sortcode == "004003009" || sortcode == "004004001")) {
        order_cut_vert = 50;
    }

    if(sortcode == "004004001") {
        $("#st_form_amt_div").hide();
    }

    if(order_cut_wid != "" && order_cut_vert != "") {
        $("#st_cut_wid_size").val(order_cut_wid);
        $("#st_cut_vert_size").val(order_cut_vert);
        if(order_stan_name == "비규격") {

            if (typeof preview !== "undefined" &&
                sortcode.indexOf("004003") > -1 &&
                sortcode !== "004003009") {
                $("#st_size_name").val(order_size_name);
            } else {
                $("#size_dvs").val("manu");
                changeSizeDvs("manu");
                size();
            }
        }
    }

    // 특수지
    if (sortcode === "004001001") {
        showPaperNoti();
    }

    // 자유형
    if (sortcode === "004003009") {
        $(".esti_print_info").show();
        $("._cuttingSize input").val(0);
        $("._cuttingSize input").prop({
            "disabled": false,
            "readonly": false
        });
        $("#st_cut_wid_size").val(order_cut_wid);
        $("#st_cut_vert_size").val(order_cut_vert);
        //chkCoatingYn(prdtDvs);
    }

    // 도무송
    if (typeof preview !== "undefined" &&
        sortcode.indexOf("004003") > -1 &&
        sortcode !== "004003009") {
        changePreviewImg(getStanName());

        preview.cuttingSize.remove();
        preview.workingSize.remove();
        preview.btns.find('.cuttingLine').trigger("click");
        preview.btns.find('.workingLine').trigger("click");
        preview.btns.remove();
    }

    var prefix = getPrefix(prdtDvs);
    loadPrdtPrice.price.amt_rate = $(prefix + "amt_sale_rate").val();
    loadPrdtPrice.price.amt_aplc = $(prefix + "amt_sale_aplc").val();
    loadReleaseExpect();
    loadDlvrPriceExpect();
    chkCoatingYn(prdtDvs);
    //size();
    chkMaxMinSize.exec('st', changeData);
});

/**
 * @brief 도무송 미리보기 이미지 변경용 규격명 반환
 */
var getStanName = function () {
    var prefix = getPrefix(prdtDvs);
    var stanName = $(prefix + "size > option:selected").text();

    if (sortcode === "004003008") {
        // 비디오, 카세트
        var cuttingSize = $(prefix + "size > option:selected").attr("class")
            .split(' ')[1];
        cuttingSize = cuttingSize.replace("_cuttingWH", '').split('-');

        stanName = "vcc-" + cuttingSize[0] + cuttingSize[1];
    } else if (sortcode === "004003001") {
        // 보험
        stanName = stanName.split('-');
        stanName = "bohum" + '-' + stanName[1];
    }
    return stanName;
};

/**
 * @brief 종이 변경시 코팅여부 체크후 재질느낌 검색
 *
 * @param dvs = 제품구분값
 * @param val = 맵핑코드
 */
var changePaper = function (dvs, val) {
    if (sortcode === "004001001") {
        var prefix = getPrefix(dvs);
        var paperName = $(prefix + "paper > option:selected").text();

        if (paperName.indexOf("데드롱") > -1 ||
            paperName.indexOf("크라프트") > -1) {
            if($(prefix + "halfknife").prop("checked"))
                $(prefix + "halfknife").trigger("click");
            //aftRestrict.unchecked(dvs, "halfknife");
            $("li._halfknife").hide();
        } else {
            $("li._halfknife").show();
            if(!$(prefix + "halfknife").prop("checked"))
                $(prefix + "halfknife").trigger("click");
        }

        showHideDayBoard();
        showPaperNoti();
    } else {
        loadPaperPreview(dvs);
    }
    chkCoatingYn(dvs);
    loadPaperDscr.exec(dvs, val);
};

/**
 * @brief 도무송 스티커일 때 사이즈 변경시 미리보기 이미지 변경
 *
 * @param obj = 자기자신 객체
 */
var changeSize = function (obj) {
    changePreviewImg(getStanName());
    changeData();
};


var getWid = function () {
    return $("#work_wid_size");
};

var getVert = function () {
    return $("#work_vert_size");
};

var getSizeStr = function () {
    var prefix = getPrefix(prdtDvs);
    var wid1 = $(prefix + "cut_wid_size").val();
    var vert1 = $(prefix + "cut_vert_size").val();
    var wid2 = $("#work_wid_size").val();
    var vert2 = $("#work_vert_size").val();

    return wid1 + '*' + vert1 + '(' + wid2 + '*' + vert2 + ')';
};

/**
 * @brief 코팅 사용여부 체크
 *
 * @param dvs = 제품구분
 */
var chkCoatingYn = function (dvs) {
    var prefix = getPrefix(dvs);
    var paperName = $(prefix + "paper > option:selected").text();

    var html = '';
    if (paperName.indexOf("아트지") > -1) {
        html = "<option value=\"코팅\">코팅</option><option value=\"무코팅\">무코팅</option>";
        $("#coating_yn").html(html);
        $("#coating_yn").prop("readonly", false);
    } else if (paperName.indexOf("데드롱") > -1) {
        html = "<option value=\"코팅\">코팅</option>";
        $("#coating_yn").html(html);
        $("#coating_yn").prop("readonly", true);
    } else {
        html = "<option value=\"무코팅\">무코팅</option>";
        $("#coating_yn").html(html);
        $("#coating_yn").prop("readonly", true);
    }
};

/**
 * @brief 자유형 도무송 계산횽 파라미터 생성
 *
 * @return 파라미터
 */
var getFreeTomsonParam = function () {
    var prefix = getPrefix(prdtDvs);

    var stanName = $(prefix + "size > option:selected").text();

    var data = {
        "sortcode": sortcode,
        "stanName": stanName,
        "amt": $(prefix + "amt").val(),
        "amtTs": $(prefix + "form_amt").val(),
        "wid": parseInt($(prefix + "cut_wid_size").val()),
        "vert": parseInt($(prefix + "cut_vert_size").val()),
        "callback": freeTomsonCallback,
        "amtF1": "",
        "amtF2": "",
        "amtF3": "",
        "amtF4": ""
    };

    if (stanName == "혼합유형") {
        data.amtF1 = $("#form_1").val();
        data.amtF2 = $("#form_2").val();
        data.amtF3 = $("#form_3").val();
        data.amtF4 = $("#form_4").val();
    }

    return data;
};

/**
 * @brief 자유형 도무송 재단사이즈 변경시 도무송 가격 재검색
 */
var changeTomsonSize = function () {
    //chkMaxMinSize.exec(prdtDvs);
    confirmSizeAmt(false);
    chkMaxMinSize.exec('st', changeData);
};

/**
 * @brief 가격 구성요소 셀렉트박스 변경시 변경된 정보로 가격 검색
 */
var changeData = function () {
    //if (sortcode === "004003009") {
    //    changeFormSubAmt();
    //    getFreeTomsonPrice('', prdtDvs, getFreeTomsonParam());
    //    return false;
    //}

    $("input[type='checkbox'][name='st_chk_after[]']").each(function () {
        var aftKoName = $(this).val();

        if (aftKoName === "재단") {
            if ($(this).prop("checked") === false) {
                $("#st_cutting_wid").val(Number($("#st_cut_wid_size").val()));
                $("#st_cutting_vert").val(Number($("#st_cut_vert_size").val()));
                $("#work_wid_size").val(Number($("#st_cut_wid_size").val()) + 6);
                $("#work_vert_size").val(Number($("#st_cut_vert_size").val()) + 6);
            } else {
                var cuttingWidth = parseInt($("#st_cutting_wid").val());
                var cuttingHeight = parseInt($("#st_cutting_vert").val());

                // 도무송 사이즈
                var tomsonWidth = parseInt($("#st_cut_wid_size").val());
                var tomsonHeight = parseInt($("#st_cut_vert_size").val());

                if(tomsonWidth > cuttingWidth - 6) {
                    $("#st_cutting_wid").val(tomsonWidth + 6);
                    cuttingWidth = tomsonWidth + 6;
                }

                if(tomsonHeight > cuttingHeight - 6) {
                    $("#st_cutting_vert").val(tomsonHeight + 6);
                    cuttingHeight = tomsonHeight + 6;
                }

                $("#work_wid_size").val(cuttingWidth + 6);
                $("#work_vert_size").val(cuttingHeight + 6);
            }
        }
    });

    monoYn = $("#st_mono_yn").val();

    var data = {
        "dvs": prdtDvs,
        "mono_yn": monoYn,
        "cate_sortcode": sortcode,
        "amt": $("#st_amt").val(),
        "count": $("#count").val(),
        "size": $("#st_size").val(),
        "tmpt_dvs": tmptDvs
    };

    data.flag = $("#frm").find("input[name='flag']").val();
    data.paper = $("#st_paper").val();
    data.bef_tmpt = $("#st_print_tmpt").val();
    data.bef_add_print_mpcode = '';
    data.aft_print_mpcode = '';
    data.aft_add_print_mpcode = '';
    data.print_purp = $("#st_print_purp").val();
    data.page_info = "2";
    data.st_bg = $("input[name='st_bg']:checked").val();
    if (sortcode === "004003009" || sortcode === "004004001") {
        data.size = $("#st_size").val();
        data.amt_ts = $("#st_form_amt").val();
        data.amt_f1 = $("#form_1").val();
        data.amt_f2 = $("#form_2").val();
        data.amt_f3 = $("#form_3").val();
        data.amt_f4 = $("#form_4").val();
    }

    if (data.st_bg == null) {
        data.st_bg = $("#st_size > option:selected").text();
    }

    if ($("#size_dvs").val() === "stan") {
        var size1 = $("#st_size > option:selected").text();
        var w = size1.split('*')[0];
        var h = size1.split('*')[1];
        data.work_wid_size = w;
        data.work_vert_size = h;
        data.cut_wid_size = $("#st_cut_wid_size").val();
        data.cut_vert_size = $("#st_cut_vert_size").val();
        data.grade_sale = $("#st_grade_sale_rate").val();
        data.paper_info = $("#st_paper > option:selected").text();
    } else {
        data.cut_wid_size = $("#st_cut_wid_size").val();
        data.cut_vert_size = $("#st_cut_vert_size").val();
        data.work_wid_size = $("#work_wid_size").val();
        data.work_vert_size = $("#work_vert_size").val();
        data.grade_sale = $("#st_grade_sale_rate").val();
        data.paper_info = $("#st_paper > option:selected").text();
    }

    var i = 0;
    $("input[type='checkbox'][name='st_chk_after[]']").each(function () {
        if ($(this).prop("checked") === false) {
            return true;
        }

        var aft = $(this).attr("aft");
        var aft_depth = $("#st_" + aft).val() == undefined ? "" : $("#st_" + aft).val();
        var aft_depth1 = $("#st_" + aft + "_1").val() == undefined ? "" : $("#st_" + aft + "_1").val();
        var aft_depth2 = $("#st_" + aft + "_2").val() == undefined ? "" : $("#st_" + aft + "_2").val();
        var aft_depth1_val = $("#st_" + aft + "_val").val() == undefined ? "" : $("#st_" + aft + "_val").val();
        var aft_depth1_vh = $("#st_" + aft + "_vh").val() == undefined ? "" : $("#st_" + aft + "_vh").val();
        var aft_depth1_cnt = $("#st_" + aft + "_cnt").val() == undefined ? "" : $("#st_" + aft + "_cnt").val();
        var aft_depth1_dvs = $("#st_" + aft + "_dvs_1").val() == undefined ? "" : $("#st_" + aft + "_dvs_1").val();
        var aft_depth2_dvs = $("#st_" + aft + "_dvs_2").val() == undefined ? "" : $("#st_" + aft + "_dvs_2").val();
        var aft_depth1_wid = $("#st_" + aft + "_wid_1").val() == undefined ? "" : $("#st_" + aft + "_wid_1").val();
        var aft_depth1_vert = $("#st_" + aft + "_vert_1").val() == undefined ? "" : $("#st_" + aft + "_vert_1").val();
        var aft_depth2_wid = $("#st_" + aft + "_wid_2").val() == undefined ? "" : $("#st_" + aft + "_wid_2").val();
        var aft_depth2_vert = $("#st_" + aft + "_vert_2").val() == undefined ? "" : $("#st_" + aft + "_vert_2").val();
        var selector = "input[name='" +
            'st' + '_' + aft + '_' +
            aft_depth1_cnt +
            "_val']:checked";

        var mpcode = $(selector).val();

        if (mpcode != undefined) aft_depth1_val = mpcode;
        if (i != 0) {
            data.after_name += "|";
            data.aft_depth += "|";
            data.aft_depth1 += "|";
            data.aft_depth2 += "|";
            data.aft_depth1_val += "|";
            data.aft_depth1_vh += "|";
            data.aft_depth1_cnt += "|";
            data.aft_depth1_dvs += "|";
            data.aft_depth2_dvs += "|";
            data.aft_depth1_wid += "|";
            data.aft_depth1_vert += "|";
            data.aft_depth2_wid += "|";
            data.aft_depth2_vert += "|";
        } else {
            data.after_name = "";
            data.aft_depth = "";
            data.aft_depth1 = "";
            data.aft_depth2 = "";
            data.aft_depth1_val = "";
            data.aft_depth1_vh = "";
            data.aft_depth1_cnt = "";
            data.aft_depth1_dvs = "";
            data.aft_depth2_dvs = "";
            data.aft_depth1_wid = "";
            data.aft_depth1_vert = "";
            data.aft_depth2_wid = "";
            data.aft_depth2_vert = "";
        }
        data.aft_depth1 += aft_depth1;
        data.aft_depth2 += aft_depth2;
        data.aft_depth1_val += aft_depth1_val;
        data.aft_depth1_vh += aft_depth1_vh;
        data.aft_depth1_cnt += aft_depth1_cnt;
        data.aft_depth1_dvs += aft_depth1_dvs;
        data.aft_depth2_dvs += aft_depth2_dvs;
        data.aft_depth1_wid += aft_depth1_wid;
        data.aft_depth1_vert += aft_depth1_vert;
        data.aft_depth2_wid += aft_depth2_wid;
        data.aft_depth2_vert += aft_depth2_vert;
        if (aft == "foil") {
            data.aft_depth += $("#st_" + aft + "_depth").val();
            data.after_name += $("#st_" + aft + "_name").val();
        } else {
            data.after_name += aft;
            data.aft_depth += aft_depth;
        }

        i++;
    });

    $("#after_name").val(data.after_name);
    $("#aft_depth").val(data.aft_depth);
    $("#aft_depth1").val(data.aft_depth1);
    $("#aft_depth2").val(data.aft_depth2);
    $("#aft_depth1_val").val(data.aft_depth1_val);
    $("#aft_depth1_vh").val(data.aft_depth1_vh);
    $("#aft_depth1_cnt").val(data.aft_depth1_cnt);
    $("#aft_depth1_dvs").val(data.aft_depth1_dvs);
    $("#aft_depth2_dvs").val(data.aft_depth2_dvs);
    $("#aft_depth1_wid").val(data.aft_depth1_wid);
    $("#aft_depth1_vert").val(data.aft_depth1_vert);
    $("#aft_depth2_wid").val(data.aft_depth2_wid);
    $("#aft_depth2_vert").val(data.aft_depth2_vert);
    $("#paper_info").val(data.paper_info);
    loadPrdtPrice.data = data;
    loadPrdtPrice.exec();
    size();
};

/**
 * @brief 스티커 종이에따라 당일판 show/hide
 */
var showHideDayBoard = function () {
    var prefix = getPrefix(prdtDvs);
    var paper = $(prefix + "paper > option:selected").text().split(' ')[0];
    var idx = null;
    var $chkBox = null;
    $("input[name='chk_opt']").each(function (i) {
        if ($(this).val() === "당일판") {
            idx = i;
            $chkBox = $(this);
            return false;
        }
    });

    if (idx === null) {
        return false;
    }

    var $dayBoard = $chkBox.parent().parent();

    if ("투명데드롱|은무데드롱|유포지".indexOf(paper) > -1) {
        $dayBoard.hide();
        $chkBox.prop("disabled", true);
    } else {
        $chkBox.prop("disabled", false);
        $dayBoard.show();
    }
    //loadOptPrice.exec($chkBox, idx, prdtDvs);
};

/**
 * @brief 상품 가격정보 json으로 반환
 */
var loadPrdtPrice = {
    "data": {},
    "price": {},
    "exec": function () {
        var url = "/ajax/product/load_price.php";
        var callback = function (result) {
            if (checkBlank(result[prdtDvs].sell_price) === true) {
                return alertReturnFalse("해당하는 가격이 존재하지 않습니다.\n관리자에게 문의하세요.");
            }

            loadPrdtPrice.price = result[prdtDvs];

            var prefix = getPrefix(prdtDvs);
            $(prefix + "prdt_price").val(result[prdtDvs].sell_price);

            $("input[name='st_chk_after[]']:checked").each(function () {
                var $obj = $(this);
                var aft = $obj.attr('id').replace("st_", "");
                if (aft == "foil") {
                    var totalAfterPrice = 0;
                    afterPrice = loadPrdtPrice.price["foil1"];
                    if (afterPrice) {
                        setAfterPrice("st", "foil1", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    afterPrice = loadPrdtPrice.price["foil2"];
                    if (afterPrice) {
                        setAfterPrice("st", "foil2", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    afterPrice = loadPrdtPrice.price["foil3"];
                    if (afterPrice) {
                        setAfterPrice("st", "foil3", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    setAfterPrice("st", "foil", totalAfterPrice);
                } else {
                    afterPrice = loadPrdtPrice.price[aft];
                    setAfterPrice("st", aft, afterPrice);
                }
            });

            setSizeWarning();
            calcPrice();
        };

        ajaxCall(url, "json", loadPrdtPrice.data, callback);
    }
};

/**
 * @brief 화면에 출력되는 가격 및 빠른견적서 내용 수정
 *
 * @param flag = 옵션가격 재검색 후 무한루프방지
 */
var calcPrice = function (flag) {
    // 건수
    var count = parseInt($("#count").val());
    // 특별할인
    var amtRate = parseFloat(loadPrdtPrice.price.amt_rate);
    amtRate /= 100.0;
    var amtAplc = parseInt(loadPrdtPrice.price.amt_aplc);
    // 정상판매가
    var sellPrice = loadPrdtPrice.price.sell_price;
    if (checkBlank(sellPrice)) {
        changeData();
        return false;
    }
    sellPrice = ceilVal(sellPrice);
    //sellPrice *= count;
    // 등급 할인율
    var gradeSale = parseFloat($("#st_grade_sale_rate").val());
    gradeSale /= 100.0;
    // 회원 할인율
    var memberSale = parseFloat($("#st_member_sale_rate").val());
    memberSale /= 100.0;
    // 옵션비 총합
    var sumOptPrice = getSumOptPrice();
    sumOptPrice = ceilVal(sumOptPrice);
    // 후공정비 총합
    var sumAfterPrice = getSumAfterPrice(prdtDvs);
    sumAfterPrice = ceilVal(sumAfterPrice);

    // 견적서 종이비 계산
    var paper = 0;
    if (monoYn === '1') {
        paper = parseInt(loadPrdtPrice.price.paper);
        paper = ceilVal(paper);
        //paper *= count;
    }

    // 견적서 출력비 계산
    var output = 0;
    if (monoYn === '1') {
        output = parseInt(loadPrdtPrice.price.output);
        output = ceilVal(output);
        //output *= count;
    }

    // 견적서 출력비 계산
    var cut = 0;
    if (monoYn === '1') {
        cut = parseInt(loadPrdtPrice.price.cut);
        cut = ceilVal(cut);
        //output *= count;
    }

    // 견적서 인쇄비 계산
    var print = loadPrdtPrice.price.print;
    print = ceilVal(print);
    /*
    if (monoYn === '1') {
        print = parseInt(loadPrdtPrice.price.print);
        print = ceilVal(print);
        //print *= count;
    }
    */

    // 견적서 후공정비 계산
    var after = sumAfterPrice;
    // 견적서 후공정비 계산(세금 미포함)
    var esAfter = getSumAfterPrice(prdtDvs, "T");
    // 견적서 옵션비 계산
    var opt = sumOptPrice;

    // 회원등급 할인가 계산
    var calcGradeSale = sellPrice * gradeSale;
    calcGradeSale = ceilVal(calcGradeSale);
    // 특별할인가 계산
    var calcAmtSale = (sellPrice + calcGradeSale) * amtRate + amtAplc;
    calcAmtSale = ceilVal(calcAmtSale);
    // 회원 할인가 계산
    var calcMemberSale = (sellPrice + calcGradeSale) * memberSale;
    calcMemberSale = ceilVal(calcMemberSale);
    // 기본할인가 계산
    var calcSalePrice = sellPrice + calcGradeSale + calcMemberSale + calcAmtSale;
    // 결제금액 계산
    var calcPayPrice = calcSalePrice;
    // 부가세
    var tax = ceilVal(calcPayPrice / 11);
    // 공급가
    var supplyPrice = calcPayPrice - tax;

    // 정상판매가 변경
    $("#sell_price").attr("val", (sellPrice + after + opt));
    $("#sell_price strong").html((sellPrice + after + opt).format());
    // 회원등급 할인가 변경
    $("#grade_sale").html((calcGradeSale + calcMemberSale).format() + ' 원');
    // 회원특별 할인가 변경
    $("#amt_member_sale").html(calcAmtSale.format() + ' 원');
    // 결제금액 변경
    $("#sale_price").attr("val", calcPayPrice);
    $("#sale_price").html(calcPayPrice.format());
    // 공급가 변경
    $("#supply_price").html(supplyPrice.format());
    // 부가세 변경
    $("#tax").html(tax.format());

    var param = {
        "paper": paper,
        "print": print,
        "cut": cut,
        "output": output,
        "afterTax": after,
        "afterNoTax": esAfter,
        "opt": opt,
        "count": count,
        "gradeSaleRate": gradeSale,
        "amtRate": amtRate,
        "amtAplc": amtAplc,
        "sellPrice": sellPrice
    };

    loadReleaseExpect();
    loadDlvrPriceExpect(flag);
    changeQuickEsti(param);

};

/**
 * @param 인쇄방식에 해당하는 인쇄도수 검색
 *
 * @param val = 인쇄방식
 */
var loadPrintTmpt = function (dvs) {
    var callback = function (result) {
        $("#st_print_tmpt").html(result.bef_tmpt);

        if (monoYn === '1') {
            changeData();
        }
    };

    loadPrintTmptCommon.exec(dvs, callback);
};

/**
 * @brief 비규격 사이즈 선택할 경우 재단사이즈 값 초기화
 *
 * @param val = 구분값
 */
var changeSizeDvs = function (val) {
    // 비규격 사이즈 선택시 기본 사이즈로 데이터 변경
    if (val === "manu") {
        $("#cut_wid_size").val($("#st_size").attr("def_cut_wid"));
        $("#cut_vert_size").val($("#st_size").attr("def_cut_vert"));
    }

    changeData();
};

/**
 * @brief 수량변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeAmt = function () {
    reCalcAfterPrice(prdtDvs, null);
    changeData();
}

/**
 * @brief 건수변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeCount = function () {
    reCalcAfterPrice(prdtDvs, null);
    calcPrice();
}

/**
 * @brief 재단스티커 비규격 입력할 때 사이즈에 따라서 문구표시
 */
var changeCuttingSize = function (dvs) {
    setSizeWarning();
    chkMaxMinSize.exec(dvs, changeData);
};

/**
 * @brief 가격 배열에서 후공정 가격 계산해서 반환
 *
 * @param priceArr = 가격 배열
 *
 * @return 계산된 가격
 */
var getAfterCalcPrice = function (priceArr) {
    var crtrUnit = priceArr.crtr_unit;
    var amt = parseInt($("#st_amt").val());

    // 표지 종이수량
    amt = amtCalc(amt, amtUnit, crtrUnit);

    return calcAfterPrice(priceArr, amt);
};

/**
 * @brief 관심상품 등록이나 장바구니 전에 데이터 세팅
 */
var setSubmitParam = function () {
    if ($("#il").val() === "0") {
        $("#cart_flag").val('Y');
        $("#purlogin").val("1");
        showLoginBox();
        return false;
    }

    if (!aftRestrict.all(prdtDvs)) {
        return false;
    }

    if (!optRestrict.all(prdtDvs)) {
        return false;
    }

    var paperName = $("#st_paper").find("option:selected").text();
    var tmptName = $("#st_print_tmpt").find("option:selected").text();
    var sizeName = $("#st_size").find("option:selected").text();

    var sellPrice = $("#sell_price").attr("val");
    var salePrice = $("#sale_price").attr("val");
    var afterPrice = getSumAfterPrice(prdtDvs);
    var optPrice = getSumOptPrice();
    var gradeSaleRate = $("#st_member_sale_rate").val();

    var ret = makeAfterInfo.all(prdtDvs);
    var sizeDvs = $("#size_dvs").val();

    var st_bg = $("input[name='st_bg']:checked").val();
    if (st_bg == null) {
        st_bg = $("#st_size > option:selected").text();
    }
    if (typeof preview !== "undefined" &&
        sortcode.indexOf("004003") > -1 &&
        sortcode !== "004003009") {

        sizeName = $("#st_size > option:selected").text();

    } else {
        if (sizeDvs === "manu") {
            sizeName = "비규격";
        }
    }

    var stCutWidSize = $("#st_cut_wid_size").val();
    var stCutVertSize = $("#st_cut_vert_size").val();

    // 사이즈
    if (stCutWidSize == "0" || stCutVertSize == "0") {
        alert('사이즈를 입력해 주세요. 최소 사이즈는 16*16 입니다.');
        return false;
    }

    // 자유형도무송의 경우 유형에 따른 갯수 보여주게끔 함
    if (sortcode == "004003009") {
        var amtTs = $("#st_form_amt > option:selected").text();
        var F1 = parseInt($("#form_1 > option:selected").val());
        var F2 = parseInt($("#form_2 > option:selected").val());
        var F3 = parseInt($("#form_3 > option:selected").val());
        var F4 = parseInt($("#form_4 > option:selected").val());
        if (sizeName != "혼합유형") {
            sizeName += "[" + amtTs + "개]";
        } else {
            sizeName += "[";
            sizeName += "유형1:" + F1 + "개,";
            sizeName += "유형2:" + F2 + "개,";
            sizeName += "유형3:" + F3 + "개,";
            sizeName += "유형4:" + F4 + "개";
            sizeName += "]";
            // 혼합유형 시 총 갯수 체크
            var sumF = F1 + F2 + F3 + F4;
            if (amtTs != sumF) {
                alert("유형별 개수와 총 개수가 일치하지 않습니다.");
                return false;
            }
        }
    }

    var orderDetail = cateName + " / " +
        paperName + " / " +
        sizeName + " / " +
        tmptName;

    if ($("input[name='st_bg']").length > 0) {
        var bg = $("input[name='st_bg']:checked").val();
        bg = (bg === "원터치재단") ? "없음[원터치]" : "있음[투터치]";

        orderDetail += " / 빼다 : " + bg;
    }

    if ($("#coating_yn").length > 0) {
        orderDetail += " / [" + $("#coating_yn").val() + ']';
    }

    $("#order_detail").val(orderDetail);

    if (ret === false) {
        return false;
    }

    setAddOptInfo();

    $frm = $("#frm");


    $frm.find("input[name='st_cate_name']").val(cateName);
    $frm.find("input[name='st_amt_unit']").val(amtUnit);
    $frm.find("input[name='st_paper_info']").val(paperName);
    $frm.find("input[name='st_bef_tmpt_name']").val(tmptName);
    $frm.find("input[name='st_size_name']").val(sizeName);
    $frm.find("input[name='st_bg']").val(st_bg);
    $frm.find("input[name='st_sell_price']").val(sellPrice);
    $frm.find("input[name='st_sale_price']").val(salePrice);
    $frm.find("input[name='st_after_price']").val(afterPrice);
    $frm.find("input[name='opt_price']").val(optPrice);
    $frm.find("input[name='st_tomson_wid_size']").val($("#st_cut_wid_size").val());
    $frm.find("input[name='st_tomson_vert_size']").val($("#st_cut_vert_size").val());
    $frm.find("input[name='st_amt_ts']").val(amtTs);
    $frm.find("input[name='st_amt_f1']").val(F1);
    $frm.find("input[name='st_amt_f2']").val(F2);
    $frm.find("input[name='st_amt_f3']").val(F3);
    $frm.find("input[name='st_amt_f4']").val(F4);
    return true;
};

/**
 * @brief 원터치/투터치 변경시 사이즈 재변경
 */
var loadSizeInfo = function (dvs, val) {
    var prefix = getPrefix(dvs);

    var url = "/ajax/product/load_typ_size.php";
    var data = {
        "cate_sortcode": sortcode,
        "size_name": $("#st_size").find("option:selected").text(),
        "size_typ": $("input[name='st_bg']:checked").val()
    };
    var callback = function (result) {
        var cls = "_workingSize _gap" + result.gap;

        $("#st_size").html(result.html);
        $("#size_gap").attr("class", cls);
        if (isFunc("size")) {
            size();
        }
        changeData();
        orderSummary();
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 자유형 도무송 가격 계산
 */
var freeTomsonCallback = function (result) {
    var prefix = getPrefix(prdtDvs);
    $(prefix + "prdt_price").val(result.price);

    loadPrdtPrice.price.amt_rate = result.amt_rate;
    loadPrdtPrice.price.amt_aplc = result.amt_aplc;
    loadPrdtPrice.price.sell_price = result.price;
    if (isFunc("size")) {
        size();
    }
    calcPrice();
};

/**
 * @brief 견적서 팝업 본문정보 생성
 */
var makeEstiPopInfo = {
    "data": null,
    "exec": function (dvs) {
        var prefix = getPrefix(prdtDvs);

        var paper = $.trim($(prefix + "paper > option:selected").text());
        var size = $.trim($(prefix + "size > option:selected").text());
        var tmpt = $.trim($(prefix + "print_tmpt > option:selected").text());
        var amt = $.trim($(prefix + "amt").val());
        var count = $.trim($("#esti_count").text());

        if ($("input[name='st_bg']").length > 0) {
            var bg = $("input[name='st_bg']:checked").val();
            tmpt += ' ' + bg;
        }

        var after = '';
        $('.after .overview ul li').each(function () {
            after += $(this).text();
            after += ', ';
        });
        after = after.substr(0, after.length - 2);

        var data = {
            "cate_name": [
                cateName
            ],
            "paper": [
                paper
            ],
            "size": [
                size
            ],
            "tmpt": [
                tmpt
            ],
            "amt": [
                amt
            ],
            "amt_unit": [
                amtUnit
            ],
            "count": [
                count
            ],
            "after": [
                after
            ]
        };

        data = getEstiPopData(data);

        this.data = data;
    }
};

/**
 * @brief 스티커 경고문구 표시
 */
var setSizeWarning = function () {
    if (sortcode === "004001001") {
        $("#st_warning").text("");
        return false;
    }

    var prefix = getPrefix(prdtDvs);
    var cutW = parseInt($(prefix + "cut_wid_size").val());
    var cutH = parseInt($(prefix + "cut_vert_size").val());

    if (cutW <= 50 || cutH <= 50) {
        $("#st_warning").text("* 5cm이하 제작물은 후지 반칼이 걸리지 않을 수 있으며 가급적 도무송으로 주문하시면 편리합니다.");
    } else {
        $("#st_warning").text("");
    }
};

/**
 * @brief 특수지 스티커 일 때 유의사항 출력
 */
var showPaperNoti = function () {
    var prefix = getPrefix(prdtDvs);
    var paper = $(prefix + "paper > option:selected").text();

    if (paper.indexOf("데드롱") > -1) {
        $(".noti").show();
        $(".noti .noti_coating").html("<option>단면유광코팅</option>");
        $(".noti .noti_coating").show();
        $(".noti .noti_half_knife").show();
    } else if (paper.indexOf("크라프트") > -1) {
        $(".noti").show();
        $(".noti .noti_coating").html("<option>무코팅</option>");
        $(".noti .noti_coating").show();
        $(".noti .noti_half_knife").show();
    } else if (paper.indexOf("유포지") > -1) {
        $(".noti").show();
        $(".noti .noti_coating").html("<option>무코팅</option>");
        $(".noti .noti_coating").show();
        $(".noti .noti_half_knife").hide();
    } else {
        $(".noti").hide();
    }
};

/**
 * @brief 도무송 수량에따른 콤보박스 출력
 */
var changeFormSubAmt = function () {
    var prefix = getPrefix(prdtDvs);
    var thSize = $.trim($(prefix + "size > option:selected").text()); // 도무송 유형
    var thAmt = parseInt($.trim($(prefix + "form_amt > option:selected").text())); // 도무송 갯수
    var subCombo = $(prefix + "form_sub");

    if (checkBlank(thSize)) {
        return false;
    }

    // 혼합유형의 경우 콤보박스를 보여줌
    if (thSize == "혼합유형") {
        subCombo.show();
        controlSubSelBox(thAmt); // 콤보박스 컨트롤 함수
    } else {
        subCombo.hide();
    }
};

/**
 * @brief 도무송 콤보박스 컨트롤
 */
var controlSubSelBox = function (amt) {
    // 콤보박스 컨트롤 하기 전에 갯수와 사이즈 VC
    var optHtml = "";
    if (checkBlank(amt)) {
        amt = parseInt($("#st_form_amt").val());
    }
    var optHtmlAmt = parseInt(amt + 1);
    for (var i = 0; i < optHtmlAmt; i++) {
        optHtml += "<option value=" + i + ">" + i + "개</option>";
    }
    $("#form_1").html(optHtml);
    $("#form_2").html(optHtml);
    $("#form_3").html(optHtml);
    $("#form_4").html(optHtml);

    var stanName = getStanName();

    if (stanName != "혼합유형") {
        confirmSizeAmt(true);
    } else {
        confirmSizeAmt(false);
    }
};

/**
 * @brief 도무송 콤보박스 내 select박스 VC
 */
var controlFormBox = function () {
    var F1 = parseInt($("#form_1").val()); // 유형1 
    var F2 = parseInt($("#form_2").val()); // 유형2
    var F3 = parseInt($("#form_3").val()); // 유형3
    var F4 = parseInt($("#form_4").val()); // 유형4

    var FS = parseInt($("#st_form_amt").val()); // 합계값
    var FSC = F1 + F2 + F3 + F4; // 실제 합계값

    if (FSC != FS) {

    }
};

/**
 * @brief 도무송 설명 출력
 */
var showTomsonHelp = function () {
    layerPopup('l_tomsonHelp', '/product/popup/l_tomsonHelp.html');
};

/**
 * @brief 도무송 사이즈 Validation Check
 */
var confirmSizeAmt = function (flag) {
    var tomsonAmt = parseInt($("#st_form_amt").val());
    // 도무송 가로최소값
    var minTomsonSizeHori = spaceL + spaceR + minTom;
    // 도무송 세로최소값
    var minTomsonSizeVert = spaceT + spaceB + minTom;

    // 도무송 최소 넓이
    var tomSq = parseInt(minTomsonSizeHori * minTomsonSizeVert);

    // 입력사이즈 가로값
    var workSizeHori = parseInt($("#st_cut_wid_size").val());
    // 입력사이즈 세로값
    var workSizeVert = parseInt($("#st_cut_vert_size").val());

    if (workSizeHori === 0 || workSizeVert === 0) {
        return false;
    }

    if (workSizeHori < minTomsonSizeHori && workSizeHori != 0) {
        alert("가로 최소 사이즈는 " + minTomsonSizeHori + " 입니다.");
        $("#st_cut_wid_size").val(minTomsonSizeHori);
        workSizeHori = minTomsonSizeHori;
    }

    if (workSizeVert < minTomsonSizeVert && workSizeVert != 0) {
        alert("세로 최소 사이즈는 " + minTomsonSizeVert + " 입니다.");
        $("#st_cut_vert_size").val(minTomsonSizeVert);
        workSizeVert = minTomsonSizeVert;
    }

    // 입력사이즈 넓이
    var workSq = workSizeHori * workSizeVert;

    // 최대 가능한 도무송 수
    var maxTomson = workSq / tomSq;
    maxTomson = Math.floor(maxTomson); // 소수점 버림

    // 최대개수를 넘어 섰을 때
    if (tomsonAmt > maxTomson) {
        alert("현재 사이즈에서는 최대 " + maxTomson + " 개 까지 가능합니다.");
        // 최대개수 자동 변경 시켜줌
        $("#st_form_amt").val(maxTomson);
    }

    if (flag) {
        changeData();
    }
};

/**
 * @brief 혼합유형 시 총개수 컨트롤
 * @param fid : form id
 */
var adjustForm = function (fid) {
    var pointer = fid;

    var F1 = parseInt($("#form_1").val());
    var F2 = parseInt($("#form_2").val());
    var F3 = parseInt($("#form_3").val());
    var F4 = parseInt($("#form_4").val());
    var FM = parseInt($("#st_form_amt").val());

    var FS = F1 + F2 + F3 + F4;
    if (FS > FM) {
        alert("유형 총합은 총 개수(" + FM + " 개) 를 넘길 수 없습니다.");
        $("#" + pointer).val(0);
        var p = pointer.split('_')[1];
        switch (p) {
            case "1":
                F1 = 0;
                break;
            case "2":
                F2 = 0;
                break;
            case "3":
                F3 = 0;
                break;
            case "4":
                F4 = 0;
                break;
        }
    }

    getFreeTomsonPrice('', prdtDvs, getFreeTomsonParam());
};

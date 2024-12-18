var tmptDvs = null;
var monoYn = null;
var prdtDvs = null;
var sortcode = null;
var cateName = null;
var amtUnit = null;
var previewPrefix = null;

$(document).ready(function () {
    // 건수 초기화
    var order_count = $("#order_count").val();
    initCount(999, "count", order_count);

    monoYn = $("#ev_mono_yn").val();
    tmptDvs = $("#ev_tmpt_dvs").val();
    prdtDvs = $("#prdt_dvs").val();
    sortcode = $("#ev_cate_sortcode").val();
    cateName = $("#ev_cate_sortcode").find("option:selected").text();
    amtUnit = $("#ev_amt").attr("amt_unit");
    sortcodeT = sortcode.substr(0, 6);

    if (sortcode.indexOf("006001") > -1) {
        previewPrefix = "ev_";
    } else if (sortcode.indexOf("006003") > -1) {
        previewPrefix = "ev_mst_";
    }

    if (typeof preview !== "undefined" &&
        sortcodeT === "006001") {
        changePreviewImg(getStanName());

        preview.cuttingSize.remove();
        preview.workingSize.remove();
        preview.btns.find('.cuttingLine').trigger("click");
        preview.btns.find('.workingLine').trigger("click");
        preview.btns.remove();
    }

    var order_cut_wid = $("#order_cut_wid").val();
    var order_cut_vert = $("#order_cut_vert").val();
    var order_stan_name = $("#order_stan_nmae").val();

    $("#ev_size_name").val(order_stan_name);
    if(order_cut_wid != "" && order_cut_vert != "") {
        $("#ev_cut_wid_size").val(order_cut_wid);
        $("#ev_cut_vert_size").val(order_cut_vert);

        if(order_stan_name == "manu") {
            $("#size_dvs").val("manu");
            changeSizeDvs.exec("manu");
            size();
        }
    }

    var order_print = $("#order_print").val();

    if (order_print) {
        var order_print_tmpt = order_print.replace("전면 : ", "");
        order_print_tmpt = order_print_tmpt.replace("후면 :", "");
        var order_tmpt_arr = order_print_tmpt.split(" ");

        $.each(order_tmpt_arr, function( i, el ) {
            if (i == 0) {
                $("input[value='" + el + "']:first").prop("checked", true);
                chkTmptLim('bef');
            } else if (i == 1) {
                $("input[value='" + el + "']:last").prop("checked", true);
                chkTmptLim('aft');
            }

        });
    }

    var prefix = getPrefix(prdtDvs);
    loadPrdtPrice.price.amt_rate = $(prefix + "amt_sale_rate").val();
    loadPrdtPrice.price.amt_aplc = $(prefix + "amt_sale_aplc").val();

    //$("#ev_size").trigger("change");

    loadReleaseExpect();
    changeData();
});

var showTmptHelp = function () {
    layerPopup('l_evTmptHelp', '/product/popup/l_evTmptHelp.html');
};

/**
 * @brief 종이 변경시 종이에 물린 사이즈 검색
 */
var loadStan = function (dvs, val) {
    var prefix = getPrefix(dvs);

    var url = "/ajax/product/load_paper_size.php";
    var data = {
        "cate_sortcode": $(prefix + "cate_sortcode").val(),
        "paper_mpcode": val,
        "mono_dvs": $(prefix + "mono_yn").val(),
        "affil_yn": false,
        "pos_yn": '0',
        "size_typ_yn": 'N'
    };
    var callback = function (result) {
        $(prefix + "size").html(result);
        changePaper(dvs, val);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 종이변경시 미리보기랑 재질느낌 변경
 *
 * @param dvs = 제품구분값
 * @param val = 종이 맵핑코드
 */
var changePaper = function (dvs, val) {
    loadPrdtAmt(dvs);
    loadPaperPreview(dvs);
    loadPaperDscr.exec(dvs, val)
};

/**
 * @brief 바뀐 종이와 사이즈로 수량 변경
 *
 * @param dvs = 제품구분값
 * @param val = 종이맵핑코드
 */
var loadPrdtAmt = function (dvs) {
    var prefix = getPrefix(dvs);

    var url = "/ajax/product/load_amt.php";
    var data = {
        "cate_sortcode": sortcode,
        "mono_yn": $(prefix + "mono_yn").val(),
        "amt_unit": amtUnit,
        "stan_mpcode": $(prefix + "size").val(),
        "paper_mpcode": $(prefix + "paper").val(),
        "def_amt": '1000'
    };
    var callback = function (result) {
        $(prefix + "amt").html(result);

        if (isFunc("rangeBarBySelect")) {
            rangeBarBySelect();
        }
        changeData();
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 미리보기 이미지 변경용 규격명 반환
 */
var getStanName = function () {
    var prefix = getPrefix(prdtDvs);
    var stanName = $(prefix + "size > option:selected").text();

    var cuttingSize = $(prefix + "size > option:selected").attr("class")
        .split(' ')[1];
    cuttingSize = cuttingSize.replace("_cuttingWH", '').split('-');
    cuttingSize = cuttingSize[0] + cuttingSize[1] + '';

    var ret = previewPrefix + cuttingSize;

    if (previewPrefix === "ev_") {
        if (cuttingSize === "220105") {
            if (stanName.indexOf("티켓") > -1) {
                ret += "_ticket";
            } else if (stanName.indexOf("창문") > -1) {
                ret += "_window";
            }
        }
    } else if (previewPrefix === "ev_mst_") {
        if (cuttingSize === "205100") {
            if (stanName.indexOf("색모조") > -1) {
                ret += "_mojo";
            }
        } else if (cuttingSize === "220105") {
            if (stanName.indexOf("자켓") > -1) {
                ret += "_jacket";
            } else if (sortcode === "005003009") {
                ret += "_window";
            }
        } else if (cuttingSize === "200110") {
            if (stanName.indexOf("수강료") > -1) {
                ret += "_tuition";
            } else if (stanName.indexOf("월급") > -1) {
                ret += "_pay";
            }
        }
    }

    return ret;
};

/**
 * @brief 사이즈 변경시 미리보기 이미지 변경하고 가격검색
 */
var changeSize = function () {
    changePreviewImg(getStanName());
    changeData();
};

/**
 * @brief 가격 구성요소 셀렉트박스 변경시 변경된 정보로 가격 검색
 */
var changeData = function () {
    monoYn = $("#ev_mono_yn").val();

    var data = {
        "dvs": prdtDvs,
        "mono_yn": monoYn,
        "cate_sortcode": sortcode,
        "amt": $("#ev_amt").val(),
        "size": $("#ev_size").val(),
        "tmpt_dvs": tmptDvs,
        "count": $("#count").val()
    };

    data.flag = $("#frm").find("input[name='flag']").val();
    data.paper = $("#ev_paper").val();
    data.bef_tmpt = $("#ev_print_tmpt").val();
    data.bef_add_print_mpcode = '';
    data.aft_print_mpcode = '';
    data.aft_add_print_mpcode = '';
    data.print_purp = $("#ev_print_purp").val();
    data.page_info = "2";

    var i = 0;
    $("input[type='checkbox'][name='ev_chk_after[]']").each(function () {
        if ($(this).prop("checked") === false) {
            return true;
        }

        var aft = $(this).attr("aft");
        var aft_depth = $("#ev_" + aft).val() == undefined ? "" : $("#ev_" + aft).val();
        var aft_depth1 = $("#ev_" + aft + "_1").val() == undefined ? "" : $("#ev_" + aft + "_1").val();
        var aft_depth2 = $("#ev_" + aft + "_2").val() == undefined ? "" : $("#ev_" + aft + "_2").val();
        var aft_depth1_val = $("#ev_" + aft + "_val").val() == undefined ? "" : $("#ev_" + aft + "_val").val();
        var aft_depth1_vh = $("#ev_" + aft + "_vh").val() == undefined ? "" : $("#ev_" + aft + "_vh").val();
        var aft_depth1_cnt = $("#ev_" + aft + "_cnt").val() == undefined ? "" : $("#ev_" + aft + "_cnt").val();
        var aft_depth1_dvs = $("#ev_" + aft + "_dvs_1").val() == undefined ? "" : $("#ev_" + aft + "_dvs_1").val();
        var aft_depth2_dvs = $("#ev_" + aft + "_dvs_2").val() == undefined ? "" : $("#ev_" + aft + "_dvs_2").val();
        var aft_depth1_wid = $("#ev_" + aft + "_wid_1").val() == undefined ? "" : $("#ev_" + aft + "_wid_1").val();
        var aft_depth1_vert = $("#ev_" + aft + "_vert_1").val() == undefined ? "" : $("#ev_" + aft + "_vert_1").val();
        var aft_depth2_wid = $("#ev_" + aft + "_wid_2").val() == undefined ? "" : $("#ev_" + aft + "_wid_2").val();
        var aft_depth2_vert = $("#ev_" + aft + "_vert_2").val() == undefined ? "" : $("#ev_" + aft + "_vert_2").val();
        var selector = "input[name='" +
            'ev' + '_' + aft + '_' +
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
            data.aft_depth += $("#ev_" + aft + "_depth").val();
            data.after_name += $("#ev_" + aft + "_name").val();
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

    loadPrdtPrice.data = data;
    loadPrdtPrice.exec();
};

/**
 * @brief 상품 가격정보 json으로 반환
 */
var loadPrdtPrice = {
    "data": {},
    "price": {},
    "exec": function () {
        var url = null;
        if (monoYn === '0') {
            url = "/ajax/product/load_price.php";
        } else {
            url = "/ajax/product/load_price.php";
        }
        var callback = function (result) {
            if (checkBlank(result[prdtDvs].sell_price) === true) {
                return alertReturnFalse("해당하는 가격이 존재하지 않습니다.\n관리자에게 문의하세요.");
            }

            loadPrdtPrice.price = result[prdtDvs];

            var prefix = getPrefix(prdtDvs);
            $(prefix + "prdt_price").val(result[prdtDvs].sell_price);

            $("input[name='ev_chk_after[]']:checked").each(function () {
                var $obj = $(this);
                var aft = $obj.attr('id').replace("ev_", "");
                if (aft == "foil") {
                    var totalAfterPrice = 0;
                    afterPrice = loadPrdtPrice.price["foil1"];
                    if (afterPrice) {
                        setAfterPrice("ev", "foil1", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    afterPrice = loadPrdtPrice.price["foil2"];
                    if (afterPrice) {
                        setAfterPrice("ev", "foil2", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    afterPrice = loadPrdtPrice.price["foil3"];
                    if (afterPrice) {
                        setAfterPrice("ev", "foil3", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    setAfterPrice("ev", "foil", totalAfterPrice);
                } else {
                    afterPrice = loadPrdtPrice.price[aft];
                    setAfterPrice("ev", aft, afterPrice);
                }
            });

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
    var gradeSale = parseFloat($("#ev_grade_sale_rate").val());
    gradeSale /= 100.0;
    // 회원 할인율
    var memberSale = parseFloat($("#ev_member_sale_rate").val());
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

    // 견적서 인쇄비 계산
    var print = loadPrdtPrice.price.print;
    if (monoYn === '1') {
        print = parseInt(loadPrdtPrice.price.print);
        print = ceilVal(print);
        //print *= count;
    }

    // 견적서 후공정비 계산
    //var after = sumAfterPrice * count;
    var after = sumAfterPrice;
    // 견적서 후공정비 계산(세금 미포함)
    //var esAfter = getSumAfterPrice(prdtDvs, "T") * count;
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
    var calcSalePrice = sellPrice + calcGradeSale + calcMemberSale + calcAmtSale;;
    // 결제금액 계산
    var calcPayPrice = calcSalePrice;
    // 부가세
    var tax = ceilVal(calcPayPrice / 11);
    // 공급가
    var supplyPrice = calcPayPrice - tax;

    // 정상판매가 변경
    $("#sell_price").attr("val", (sellPrice));
    $("#sell_price").html((sellPrice).format() + ' 원');
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
        "output": output,
        "cut": 0,
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
    loadDlvrPriceExpect();
    changeQuickEsti(param);
};

/**
 * @param 인쇄방식에 해당하는 인쇄도수 검색
 *
 * @param val = 인쇄방식
 */
var loadPrintTmpt = function (dvs) {
    var callback = function (result) {
        $("#ev_print_tmpt").html(result.bef_tmpt);

        if (monoYn === '1') {
            changeData();
        }
    };

    loadPrintTmptCommon.exec(dvs, callback);
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
    //reCalcOptPrice(prdtDvs);
    calcPrice();
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
    var amt = parseInt($("#amt").val());

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

    if (checkTmptChk()) {
        return alertReturnFalse("인쇄 색상을 선택해주세요.");
    }

    var paperName = $("#ev_paper").find("option:selected").text();
    var tmptName = $("#ev_print_tmpt").find("option:selected").text();
    var sizeName = $("#ev_size").find("option:selected").text();

    var sellPrice = $("#sell_price").attr("val");
    var salePrice = $("#sale_price").attr("val");
    var afterPrice = getSumAfterPrice(prdtDvs);
    var optPrice = getSumOptPrice();

    var ret = makeAfterInfo.all(prdtDvs);

    if (ret === false) {
        return false;
    }

    if ($("input[name='ev_tmpt_chk[]']").length > 0) {
        tmptName = makeTmptName();

        var orderDetail = cateName + " / " +
            paperName + " / " +
            sizeName + " / " +
            tmptName;

        $("#ev_tmpt_name").val(tmptName);
        $("#order_detail").val(orderDetail);
    }

    if (sortcode.startsWith("006001") || sortcode.startsWith("006003")) {
        // 일반봉투
        var orderDetail = cateName + " / " +
            paperName + " / " +
            sizeName + " / " +
            tmptName;

        $("#order_detail").val(orderDetail);
    }

    setAddOptInfo();

    $frm = $("#frm");

    $frm.find("input[name='ev_cate_name']").val(cateName);
    $frm.find("input[name='ev_amt_unit']").val(amtUnit);
    $frm.find("input[name='ev_paper_name']").val(paperName);
    $frm.find("input[name='ev_bef_tmpt_name']").val(tmptName);
    $frm.find("input[name='ev_size_name']").val(sizeName);

    $frm.find("input[name='ev_sell_price']").val(sellPrice);
    $frm.find("input[name='ev_sale_price']").val(salePrice);
    $frm.find("input[name='ev_after_price']").val(afterPrice);
    $frm.find("input[name='opt_price']").val(optPrice);

    return true;
};

/**
 * @brief 인쇄도수 변경시 체크박스 체크해제
 */
var changeEvTmpt = function (dvs, val) {
    $("input[name='ev_tmpt_chk[]']").prop("checked", false);
    $("input[name='ev_tmpt_chk[]']").prop("disabled", false);
    changeTmpt(dvs, val);
};

/**
 * @brief 도수에 따라 체크박스 disabled 처리
 */
var chkTmptLim = function (pos) {
    var tmpt = $("#ev_print_tmpt > option:selected").text();
    var length = $("input[name='ev_tmpt_chk[]']:checked").length;

    if (tmpt === "단면1도") {
        if (length > 0) {
            $("input[name='ev_tmpt_chk[]']").prop("disabled", true);
        } else {
            $("input[name='ev_tmpt_chk[]']").prop("disabled", false);
        }
    } else if (tmpt === "단면2도") {
        if (length === 1) {
            // 선택한 면만 선택되도록 수정
            // 첫 번째 클릭했을 때
            if (pos === "bef") {
                $("#bef").find("input[name='ev_tmpt_chk[]']").prop("disabled", false);
                $("#aft").find("input[name='ev_tmpt_chk[]']").prop("disabled", true);
            } else {
                $("#bef").find("input[name='ev_tmpt_chk[]']").prop("disabled", true);
                $("#aft").find("input[name='ev_tmpt_chk[]']").prop("disabled", false);
            }
        } else if (length === 2) {
            // 두 번째 클릭했을 때
            $("input[name='ev_tmpt_chk[]']").prop("disabled", true);
        } else {
            $("input[name='ev_tmpt_chk[]']").prop("disabled", false);
        }
    } else {
        // 양면 2도
        var befLength = $("#bef").find("input[name='ev_tmpt_chk[]']:checked")
            .length;
        var aftLength = $("#aft").find("input[name='ev_tmpt_chk[]']:checked")
            .length;

        // 선택한 면은 disabled
        if (length === 1) {
            if (befLength === 1) {
                $("#bef").find("input[name='ev_tmpt_chk[]']")
                    .prop("disabled", true);
                $("#aft").find("input[name='ev_tmpt_chk[]']")
                    .prop("disabled", false);
            } else if (aftLength === 1) {
                $("#bef").find("input[name='ev_tmpt_chk[]']")
                    .prop("disabled", false);
                $("#aft").find("input[name='ev_tmpt_chk[]']")
                    .prop("disabled", true);
            }
        } else if (length === 2) {
            $("input[name='ev_tmpt_chk[]']").prop("disabled", true);
        } else {
            // 전체 체크 해제
            $("input[name='ev_tmpt_chk[]']").prop("disabled", false);
        }
    }

    $("input[name='ev_tmpt_chk[]']:checked").prop("disabled", false);

};

/**
 * @brief 인쇄도수명 생성
 *
 * @return 인쇄도수명
 */
var makeTmptName = function () {
    var ret = $("#ev_print_tmpt").find("option:selected").text() + " - ";

    var bef = "전면 : ";
    $("#bef").find("input[name='ev_tmpt_chk[]']:checked").each(function () {
        bef += $(this).val();
        bef += ' ';
    });
    bef = bef.substr(0, bef.length - 1);

    var aft = "후면 : ";
    $("#aft").find("input[name='ev_tmpt_chk[]']:checked").each(function () {
        aft += $(this).val();
        aft += ' ';
    });
    aft = aft.substr(0, aft.length - 1);

    ret += '[' + bef + ", " + aft + ']';

    return ret;
};

/**
 * @brief 인쇄도수 체크박스 검사
 */
var checkTmptChk = function () {
    var tmpt = $("#ev_print_tmpt > option:selected").text();
    var length = $("input[name='ev_tmpt_chk[]']:checked").length;
    var chkLen = $("input[name='ev_tmpt_chk[]']").length;

    // 일반봉투
    if (chkLen === 0) {
        return false;
    }

    if (tmpt === "단면1도") {
        if (length === 0) {
            return true;
        }

    } else {
        if (length < 2) {
            return true;
        }
    }
    return false;
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

        var after = '';
        $('.after .overview ul li').each(function () {
            after += $(this).text();
            after += ', ';
        });
        after = after.substr(0, after.length - 2);

        if ($("input[name='ev_tmpt_chk[]']").length > 0) {
            tmpt = makeTmptName();
        }

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

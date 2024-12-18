var tmptDvs = null;
var monoYn = null;
var prdtDvs = null;
var affil = null;
var sortcode = null;
var amtUnit = null;
var cateName = null;

$(document).ready(function () {
    monoYn = $("#mt_ncr_mono_yn").val();
    tmptDvs = $("#mt_ncr_tmpt_dvs").val();
    prdtDvs = $("#prdt_dvs").val();
    affil = $("#mt_ncr_size").find("option:selected").attr("affil");
    sortcode = $("#mt_ncr_cate_sortcode").val();
    cateName = $("#mt_ncr_cate_sortcode").find("option:selected").text();
    amtUnit = $("#mt_ncr_amt").attr("amt_unit");

    showUvDescriptor(prdtDvs);

    var order_cut_wid = $("#order_cut_wid").val();
    var order_cut_vert = $("#order_cut_vert").val();
    var order_stan_name = $("#order_stan_nmae").val();

    $("#mt_ncr_size_name").val(order_stan_name);
    if(order_cut_wid != "" && order_cut_vert != "") {
        $("#mt_ncr_cut_wid_size").val(order_cut_wid);
        $("#mt_ncr_cut_vert_size").val(order_cut_vert);

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
        order_print_tmpt = order_print_tmpt.replace("[", "");
        order_print_tmpt = order_print_tmpt.replace("]", "");
        var order_tmpt_arr = order_print_tmpt.split(",");

        $.each(order_tmpt_arr, function( i, el ) {
            if (i == 0) {
                $("input[value='" + $.trim(el) + "']:first").prop("checked", true);
                chkTmptLim('bef');
            } else if (i == 1) {
                $("input[value='" + $.trim(el) + "']:last").prop("checked", true);
                chkTmptLim('aft');
            }

        });
    }

    if (sortcode === "007001001") {
        $("#paperMid1_dt").remove();
        $("#paperMid1_dd").remove();
        $("#paperMid2_dt").remove();
        $("#paperMid2_dd").remove();
    } else if (sortcode === "007001002") {
        $("#paperMid2_dt").remove();
        $("#paperMid2_dd").remove();
    }

    loadReleaseExpect();
    loadDlvrPriceExpect();
});

/**
 * @brief 종이 변경시 호출
 */
var changePaper = function () {
    changeData();
};

/**
 * @brief 종이 맵핑코드 배열 생성
 */
var getPaperMpcodeArr = function () {
    var paperMpcodeArr = [];

    paperMpcodeArr.push($("#paperTop").val());
    if ($("#paperMid1").length > 0) {
        paperMpcodeArr.push($("#paperMid1").val());
    }
    if ($("#paperMid2").length > 0) {
        paperMpcodeArr.push($("#paperMid2").val());
    }
    paperMpcodeArr.push($("#paperBot").val());

    return paperMpcodeArr;
}

/**
 * @brief 계산형일 때 사이즈 선택할 경우 사이즈 계열에 맞는 도수값 검색
 *
 * @param val = 구분값
 */
var changeSize = {
    "exec": function (dvs) {
        var prefix = getPrefix(dvs);
        changeAmtTxt();

        var selectAffil =
            $(prefix + "size").find("option:selected").attr("affil");
        var aftInfoArr = [{
            "name": "제본",
            "depth1": "떡제본"
        },
            {
                "name": "넘버링",
                "depth1": "일반"
            }
        ];
        var size = $(prefix + "size > option:selected").text();

        loadAfterMpcode(dvs, aftInfoArr, size);

        if (affil === selectAffil) {
            changeData();
            return false;
        } else {
            affil = selectAffil;
        }

        var callback = function (result) {
            $(prefix + "print_tmpt").html(result.sheet_tmpt);
            loadPrdtAmt(dvs);
        };

        loadPrintTmptCommon.exec(dvs, callback);
    }
};

/**
 * @brief 바뀐 사이즈로 수량 변경
 *
 * @param dvs = 제품구분값
 * @param val = 종이맵핑코드
 */
var loadPrdtAmt = function (dvs) {
    var prefix = getPrefix(dvs);

    var url = "/ajax/product/load_mt_amt.php";
    var data = {
        "cate_sortcode": sortcode,
        "stan_name": $(prefix + "size > option:selected").text(),
        "amt_unit": amtUnit
    };
    var callback = function (result) {
        $(prefix + "amt").html(result);

        if (isFunc("rangeBarBySelect")) {
            rangeBarText();
        }

        changeMtTmpt($(prefix + "print_tmpt").val());
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 가격 구성요소 셀렉트박스 변경시 변경된 정보로 가격 검색
 */
var changeData = function (dvs, paperMpcodeArr) {
    monoYn = $("#mt_ncr_mono_yn").val();

    var data = {
        "dvs": prdtDvs,
        "typ": $("#mt_ncr_typ").val(),
        "cate_sortcode": sortcode,
        "amt": $("#mt_ncr_amt").val(),
        "size": $("#mt_ncr_size").val(),
        "affil": affil,
        "count": $("#count").val(),
    };

    data.paper_mpcode_arr = getPaperMpcodeArr();
    data.bef_tmpt = $("#mt_ncr_print_tmpt").val();
    data.bef_add_print_mpcode = '';
    data.aft_print_mpcode = '';
    data.aft_add_print_mpcode = '';
    data.bef_print_name = $("#mt_ncr_print_tmpt > option:selected").text();
    data.bef_add_print_name = '';
    data.aft_print_name = '';
    data.aft_add_print_name = '';
    data.print_purp = $("#mt_ncr_print_purp").val();
    data.page = "2";
    data.flattyp_yn = "N";
    data.amt_unit = amtUnit;
    data.pos_num = $("#mt_ncr_size > option:selected").attr("pos_num");
    data.diff_yn = $("#diff_yn").prop("checked");

    var i = 0;
    $("input[type='checkbox'][name='mt_ncr_chk_after[]']").each(function () {
        if ($(this).prop("checked") === false) {
            return true;
        }

        var aft = $(this).attr("aft");
        var aft_depth = $("#mt_ncr_" + aft).val() == undefined ? "" : $("#mt_ncr_" + aft).val();
        var aft_depth1 = $("#mt_ncr_" + aft + "_1").val() == undefined ? "" : $("#mt_ncr_" + aft + "_1").val();
        var aft_depth2 = $("#mt_ncr_" + aft + "_2").val() == undefined ? "" : $("#mt_ncr_" + aft + "_2").val();
        var aft_depth1_val = $("#mt_ncr_" + aft + "_val").val() == undefined ? "" : $("#mt_ncr_" + aft + "_val").val();
        var aft_depth1_vh = $("#mt_ncr_" + aft + "_vh").val() == undefined ? "" : $("#mt_ncr_" + aft + "_vh").val();
        var aft_depth1_cnt = $("#mt_ncr_" + aft + "_cnt").val() == undefined ? "" : $("#mt_ncr_" + aft + "_cnt").val();
        var aft_depth1_dvs = $("#mt_ncr_" + aft + "_dvs_1").val() == undefined ? "" : $("#mt_ncr_" + aft + "_dvs_1").val();
        var aft_depth2_dvs = $("#mt_ncr_" + aft + "_dvs_2").val() == undefined ? "" : $("#mt_ncr_" + aft + "_dvs_2").val();
        var aft_depth1_wid = $("#mt_ncr_" + aft + "_wid_1").val() == undefined ? "" : $("#mt_ncr_" + aft + "_wid_1").val();
        var aft_depth1_vert = $("#mt_ncr_" + aft + "_vert_1").val() == undefined ? "" : $("#mt_ncr_" + aft + "_vert_1").val();
        var aft_depth2_wid = $("#mt_ncr_" + aft + "_wid_2").val() == undefined ? "" : $("#mt_ncr_" + aft + "_wid_2").val();
        var aft_depth2_vert = $("#mt_ncr_" + aft + "_vert_2").val() == undefined ? "" : $("#mt_ncr_" + aft + "_vert_2").val();
        var selector = "input[name='" +
            'mt_ncr' + '_' + aft + '_' +
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
        data.after_name += aft;
        data.aft_depth += aft_depth;
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
        var url = "/ajax/product/load_price.php";
        var callback = function (result) {
            if (checkBlank(result[prdtDvs].sell_price) === true) {
                return alertReturnFalse("해당하는 가격이 존재하지 않습니다.\n관리자에게 문의하세요.");
            }

            var priceArr = result[prdtDvs];
            loadPrdtPrice.price = priceArr;

            var prefix = getPrefix(prdtDvs);
            $(prefix + "paper_price").val(priceArr.paper);
            $(prefix + "output_price").val(priceArr.output);
            $(prefix + "print_price").val(priceArr.print);
            $(prefix + "prdt_price").val(priceArr.sell_price);

            $("input[name='mt_ncr_chk_after[]']:checked").each(function () {
                var $obj = $(this);
                var aft = $obj.attr('id').replace("mt_ncr_", "");

                afterPrice = loadPrdtPrice.price[aft];
                setAfterPrice("mt_ncr", aft, afterPrice);
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
    var count = 1;
    // 정상판매가
    var sellPrice = loadPrdtPrice.price.sell_price;
    if (checkBlank(sellPrice)) {
        changeData();
        return false;
    }
    sellPrice = ceilVal(sellPrice);
    //sellPrice *= count;
    // 등급 할인율
    var gradeSale = parseFloat($("#mt_ncr_grade_sale_rate").val());
    gradeSale /= 100.0;
    // 회원 할인율
    var memberSale = parseFloat($("#mt_ncr_member_sale_rate").val());
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
    var after = sumAfterPrice;
    // 견적서 후공정비 계산(세금 미포함)
    var esAfter = getSumAfterPrice(prdtDvs, "T");
    // 견적서 옵션비 계산
    var opt = sumOptPrice;

    // 회원등급 할인가 계산
    var calcGradeSale = sellPrice * gradeSale;
    calcGradeSale = ceilVal(calcGradeSale);
    // 회원 할인가 계산
    var calcMemberSale = (sellPrice + calcGradeSale) * memberSale;
    calcMemberSale = ceilVal(calcMemberSale);
    // 기본할인가 계산
    var calcSalePrice = sellPrice + calcGradeSale + calcMemberSale;
    // 결제금액 계산
    var calcPayPrice = calcSalePrice;
    // 부가세
    var tax = ceilVal(calcPayPrice / 11);
    // 공급가
    var supplyPrice = calcPayPrice - tax;

    // 정상판매가 변경(후공정, 옵션은 할인하지 않는다)
    $("#sell_price").html(calcPayPrice.format() + "원");
    // 회원등급 할인가 변경
    $("#grade_sale").html((calcGradeSale + calcMemberSale).format() + ' 원');
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
        "sellPrice": sellPrice
    };

    loadReleaseExpect();
    loadDlvrPriceExpect(flag);
    changeQuickEsti(param);
};

/**
 * @brief 비규격 사이즈 선택할 경우 재단사이즈 값 초기화
 *
 * @param val  = 구분값
 * @param flag = 가격검색여부
 */
var changeSizeDvs = function (val, flag) {
    var prefix = getPrefix(prdtDvs);
    $(prefix + "similar_size").attr("divide", '1');

    // 비규격 사이즈 선택시 기본 사이즈로 데이터 변경
    if (val === "manu") {
        var str = $(prefix + "size > option:selected").text() + " 1/1 등분";

        $(prefix + "similar_size").show();
        $(prefix + "similar_size").html(str);
        if ($("#im").val() == "1") {
            $("#mt_ncr_size").hide();
        }
    } else {
        changeAmtTxt();
        $(prefix + "similar_size").hide();
        if ($("#im").val() == "1") {
            $("#mt_ncr_size").show();
        }
    }

    changeData();
};

/**
 * @param 인쇄방식에 해당하는 인쇄도수 검색
 *
 * @param val = 인쇄방식
 */
var loadPrintTmpt = function (dvs) {
    var callback = function (result) {
        $("#mt_ncr_print_tmpt").html(result.bef_tmpt);

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

    var prefix = getPrefix(prdtDvs);

    if (checkTmptChk()) {
        return alertReturnFalse("인쇄 색상을 선택해주세요.");
    }

    var amt = parseFloat($(prefix + "amt").val());
    var divide = parseFloat($(prefix + "similar_size").attr("divide"));

    var paperName = makePaperName();
    var sizeName = $(prefix + "size > option:selected").text();
    var posNum = $(prefix + "size > option:selected").attr("pos_num");
    var affil = $(prefix + "size > option:selected").attr("affil");

    var sellPrice = $("#sell_price").attr("val");
    var salePrice = $("#sale_price").attr("val");
    var afterPrice = getSumAfterPrice(prdtDvs);
    var optPrice = getSumOptPrice();
    var tmptName = makeTmptName();

    var diffYn = "내용 같음";
    if ($("#diff_yn").prop("checked")) {
        diffYn = "내용 다름";
    }

    var orderDetail = cateName + " / " +
        paperName + " / " +
        sizeName + " / " +
        tmptName + " / " +
        diffYn + " / " +
        $(prefix + "binding_val > option:selected").text();

    if (!checkBlank($("#binding_typ").val())) {
        orderDetail += " / " + $("#binding_typ").val()
    }

    var ret = makeAfterInfo.all(prdtDvs);

    if (ret === false) {
        return false;
    }

    setAddOptInfo();

    $("#order_detail").val(orderDetail);
    $("#mt_ncr_tmpt_name").val(tmptName);

    $frm = $("#frm");

    $frm.find("input[name='mt_ncr_diff_yn']").val($("#diff_yn").prop("checked"));
    $frm.find("input[name='mt_ncr_paper_mpcode_arr']").val(getPaperMpcodeArr());
    $frm.find("input[name='mt_ncr_amt']").val(amt * divide);
    $frm.find("input[name='mt_ncr_cate_name']").val(cateName);
    $frm.find("input[name='mt_ncr_amt_unit']").val(amtUnit);
    $frm.find("input[name='mt_ncr_affil']").val(amtUnit);
    $frm.find("input[name='mt_ncr_paper_name']").val(paperName);
    $frm.find("input[name='mt_ncr_affil']").val(affil);
    $frm.find("input[name='mt_ncr_basisweight']").val(getPaperBasisweight());
    $frm.find("input[name='mt_ncr_paper_list']").val(getPaperList());
    $frm.find("input[name='mt_ncr_bef_tmpt_name']").val($("#mt_ncr_print_tmpt")
        .find("option:selected")
        .text());
    $frm.find("input[name='mt_ncr_size_name']").val(sizeName);
    $frm.find("input[name='mt_ncr_pos_num']").val(posNum);

    $frm.find("input[name='mt_ncr_sell_price']").val(sellPrice);
    $frm.find("input[name='mt_ncr_sale_price']").val(salePrice);
    $frm.find("input[name='mt_ncr_after_price']").val(afterPrice);
    $frm.find("input[name='opt_price']").val(optPrice);

    return true;
};

/**
 * @brief 인쇄도수 변경시 체크박스 체크해제
 */
var changeMtTmpt = function (val) {
    if (chkTmptSide()) {
        $("#binding_typ").prop("disabled", true);
        $("#binding_typ").html('<option value="">인쇄방향</option>');
    } else {
        $("#binding_typ").prop("disabled", false);
        changeBinding($("#mt_ncr_binding_val > option:selected").text());
    }

    $("input[name='mt_ncr_tmpt_chk[]']").prop("checked", false);
    $("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", false);
    changeTmpt(prdtDvs, val);
};

/**
 * @brief 도수에 따라 체크박스 disabled 처리
 */
var chkTmptLim = function (pos) {
    var tmpt = $("#mt_ncr_print_tmpt > option:selected").text();
    var length = $("input[name='mt_ncr_tmpt_chk[]']:checked").length;

    if (tmpt === "단면1도") {
        if (length > 0) {
            $("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", true);
        } else {
            $("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", false);
        }
    } else if (tmpt === "단면2도") {
        if (length === 1) {
            // 선택한 면만 선택되도록 수정
            // 첫 번째 클릭했을 때
            if (pos === "bef") {
                $("#bef").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", false);
                $("#aft").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", true);
            } else {
                $("#bef").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", true);
                $("#aft").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", false);
            }
        } else if (length === 2) {
            // 두 번째 클릭했을 때
            $("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", true);
        } else {
            $("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", false);
        }
    } else if (tmpt === "양면2도") {
        var befLength = $("#bef").find("input[name='mt_ncr_tmpt_chk[]']:checked")
            .length;
        var aftLength = $("#aft").find("input[name='mt_ncr_tmpt_chk[]']:checked")
            .length;
        // 선택한 면은 disabled
        if (length === 1) {
            if (befLength === 1) {
                $("#bef").find("input[name='mt_ncr_tmpt_chk[]']")
                    .prop("disabled", true);
                $("#aft").find("input[name='mt_ncr_tmpt_chk[]']")
                    .prop("disabled", false);
            } else if (aftLength === 1) {
                $("#bef").find("input[name='mt_ncr_tmpt_chk[]']")
                    .prop("disabled", false);
                $("#aft").find("input[name='mt_ncr_tmpt_chk[]']")
                    .prop("disabled", true);
            }
        } else if (length === 2) {
            $("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", true);
        } else {
            // 전체 체크 해제
            $("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", false);
        }
    } else if (tmpt === "단면3도") {
        if (0 < length && length < 3) {
            // 선택한 면만 선택되도록 수정
            // 첫 번째 클릭했을 때
            if (pos === "bef") {
                $("#bef").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", false);
                $("#aft").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", true);
            } else {
                $("#bef").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", true);
                $("#aft").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", false);
            }
        } else if (length === 3) {
            // 세 번째 클릭했을 때
            $("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", true);
        } else {
            // 전체 체크 해제
            $("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", false);
        }
    } else if (tmpt === "전면2도+후면1도") {
        var befLength = $("#bef").find("input[name='mt_ncr_tmpt_chk[]']:checked").length;
        var aftLength = $("#aft").find("input[name='mt_ncr_tmpt_chk[]']:checked").length;

        if (pos === "bef") {
            if (befLength === 2) {
                $("#bef").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", true);
            } else {
                $("#bef").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", false);
            }
        } else {
            if (aftLength === 1) {
                $("#aft").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", true);
            } else {
                $("#aft").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", false);
            }
        }
    } else if (tmpt === "전면1도+후면2도") {
        var befLength = $("#bef").find("input[name='mt_ncr_tmpt_chk[]']:checked").length;
        var aftLength = $("#aft").find("input[name='mt_ncr_tmpt_chk[]']:checked").length;

        if (pos === "aft") {
            if (aftLength === 2) {
                $("#aft").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", true);
            } else {
                $("#aft").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", false);
            }
        } else {
            if (befLength === 1) {
                $("#bef").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", true);
            } else {
                $("#bef").find("input[name='mt_ncr_tmpt_chk[]']").prop("disabled", false);
            }
        }
    }

    $("input[name='mt_ncr_tmpt_chk[]']:checked").prop("disabled", false);
};

/**
 * @brief 인쇄도수 체크박스 검사
 */
var checkTmptChk = function () {
    var tmpt = $("#mt_ncr_print_tmpt").find("option:selected").text();
    var length = $("input[name='mt_ncr_tmpt_chk[]']:checked").length;

    if (tmpt === "단면1도") {
        if (length === 0) {
            return true;
        }
    } else if (tmpt === "단면2도" || tmpt === "양면2도") {
        if (length < 2) {
            return true;
        }
    } else if (tmpt === "단면3도" || tmpt === "전면2도+후면1도" || tmpt === "전면1도+후면2도") {
        if (length < 3) {
            return true;
        }
    }
};

/**
 * @brief 제본 변경시 제본 배치 변경
 *
 * @param val = 제본값
 */
var changeBinding = function (val) {
    if (chkTmptSide()) {
        getAfterPrice.common('binding', 'mt_ncr');
        return false;
    }

    $("#not_binding").remove();
    $("#binding_typ").html('<option value="머리+꼬리">머리+꼬리</option><option value="머리+머리">머리+머리</option>');

    if (val.indexOf("상철") > -1) {
        $("#binding_typ").val("머리+꼬리");
    } else if (val.indexOf("좌철") > -1) {
        $("#binding_typ").val("머리+머리");
    } else {
        $("#binding_typ").html('<option value="">인쇄방향</option>');
        return false;
    }

    getAfterPrice.common('binding', 'mt_ncr');
};

/**
 * @brief 도수명으로 단면/양면 구분
 *
 * @return 단면 : true / 양면 false
 */
var chkTmptSide = function () {
    var tmpt = $("#mt_ncr_print_tmpt").find("option:selected").text();
    return (tmpt.indexOf("단면") > -1) ? true : false;
};

/**
 * @brief 상하지 내용따라 출력비 재계산
 *
 * @param obj = 체크박스 객체
 */
var changeDiff = function (obj) {
    if (monoYn === '1') {
        changeData();
    }
};

/**
 * @brief 인쇄도수명 생성
 *
 * @return 인쇄도수명
 */
var makeTmptName = function () {
    var ret = $("#mt_ncr_print_tmpt").find("option:selected").text() + " - ";

    var bef = "전면 : ";
    $("#bef").find("input[name='mt_ncr_tmpt_chk[]']:checked").each(function () {
        bef += $(this).val();
        bef += ' ';
    });
    bef = bef.substr(0, bef.length - 1);

    var aft = "후면 : ";
    $("#aft").find("input[name='mt_ncr_tmpt_chk[]']:checked").each(function () {
        aft += $(this).val();
        aft += ' ';
    });
    aft = aft.substr(0, aft.length - 1);

    ret += '[' + bef + ", " + aft + ']';

    return ret;
};

/**
 * @brief 종이명 생성
 *
 * @return 종이명
 */
var makePaperName = function () {
    var top = $("#paperTop").find("option:selected").text().split(' ');
    var bot = $("#paperBot").find("option:selected").text().split(' ');

    var ret = "상 : " + top[2] + ' ' + top[3];

    if ($("#paperMid1").length > 0) {
        var mid1 = $("#paperMid1").find("option:selected").text().split(' ');
        ret += ", 중 : " + mid1[2] + ' ' + mid1[3];
    }
    if ($("#paperMid2").length > 0) {
        var mid2 = $("#paperMid2").find("option:selected").text().split(' ');
        ret += ", 중 : " + mid2[2] + ' ' + mid2[3];
    }
    ret += ", 하 : " + bot[2] + ' ' + bot[3];

    return ret;
};

/**
 * @brief 비규격 등분에 맞춰서 상품 실수량 표기
 */
var changeAmtTxt = function () {
    var prefix = getPrefix(prdtDvs);
    var divide = parseFloat($(prefix + "similar_size").attr("divide"));

    $(prefix + "amt > option").each(function () {
        var amt = parseFloat($(this).val()) * divide;
        var txt = amt + ' ' + amtUnit;

        $(this).text(txt);
    });
};

/**
 * @brief 종이 평량 문자열 생성
 *
 * @return 평량 문자열
 */
var getPaperBasisweight = function () {
    var ret = '';
    var temp = $("#paperTop > option:selected").text().split(' ');
    ret += temp[temp.length - 1];
    if ($("#paperMid1").length > 0) {
        temp = $("#paperMid1 > option:selected").text().split(' ');
        ret += '|' + temp[temp.length - 1];
    }
    if ($("#paperMid2").length > 0) {
        temp = $("#paperMid2 > option:selected").text().split(' ');
        ret += '|' + temp[temp.length - 1];
    }
    temp = $("#paperBot > option:selected").text().split(' ');
    ret += '|' + temp[temp.length - 1];

    return ret;
};

/**
 * @brief 종이 맵핑코드 문자열 생성
 *
 * @return 맵핑코드 문자열
 */
var getPaperList = function () {
    var ret = '';
    var temp = $("#paperTop").val();
    ret += temp;
    if ($("#paperMid1").length > 0) {
        temp = $("#paperMid1").val();
        ret += '|' + temp;
    }
    if ($("#paperMid2").length > 0) {
        temp = $("#paperMid2").val();
        ret += '|' + temp;
    }
    temp = $("#paperBot").val();
    ret += '|' + temp;

    return ret;
};

/**
 * @brief 견적서 팝업 본문정보 생성
 */
var makeEstiPopInfo = {
    "data": null,
    "exec": function (dvs) {
        var prefix = getPrefix(prdtDvs);

        var paper = makePaperName();
        var size = $.trim($(prefix + "size > option:selected").text());
        var tmpt = makeTmptName();
        var amt = $.trim($(prefix + "amt").val());
        var count = $.trim($("#esti_count").text());

        var after = '';
        $('.after .overview ul li').each(function () {
            after += $(this).text();
            after += ', ';
        });
        after = after.substr(0, after.length - 2);
        // 후공정 세부내역
        var afterDet = $.trim($(".mt_ncr_overview").text());

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
            ],
            "after_det": [
                afterDet
            ]
        };

        data = getEstiPopData(data);

        this.data = data;
    }
};

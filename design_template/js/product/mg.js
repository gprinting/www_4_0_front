var monoYn = null;
var prdtDvs = null;
var sortcode = null;
var cateName = null;
var amtUnit = null;

$(document).ready(function () {
    // 건수 초기화
    var order_count = $("#order_count").val();
    initCount(999, "count", order_count);

    monoYn = $("#mg_mono_yn").val();
    prdtDvs = $("#prdt_dvs").val();
    sortcode = $("#mg_cate_sortcode").val();
    cateName = $("#mg_cate_sortcode").find("option:selected").text();
    amtUnit = $("#mg_amt").attr("amt_unit");

    var order_cut_wid = $("#order_cut_wid").val();
    var order_cut_vert = $("#order_cut_vert").val();
    var order_stan_name = $("#order_stan_nmae").val();

    $("#mg_size_name").val(order_stan_name);
    if(order_cut_wid != "" && order_cut_vert != "") {
        $("#mg_cut_wid_size").val(order_cut_wid);
        $("#mg_cut_vert_size").val(order_cut_vert);

        if(order_stan_name == "manu") {
            $("#size_dvs").val("manu");
            changeSizeDvs.exec("manu");
            size();
        }
    }

    var prefix = getPrefix(prdtDvs);
    var amtRate = $(prefix + "amt_sale_rate").val();
    var amtAplc = $(prefix + "amt_sale_aplc").val();;
    if (checkBlank(amtRate)) {
        amtRate = 0;
    }
    if (checkBlank(amtAplc)) {
        amtAplc = 0;
    }

    loadPrdtPrice.price.amt_rate = amtRate;
    loadPrdtPrice.price.amt_aplc = amtAplc;

    // 자석오프너
    if (sortcode === "008002003") {
        changePreviewImg(getStanName());
        if (typeof preview !== "undefined") {
            preview.cuttingSize.remove();
            preview.workingSize.remove();
            preview.btns.find('.cuttingLine').trigger("click");
            preview.btns.find('.workingLine').trigger("click");
            preview.btns.remove();
        }
    }

    loadReleaseExpect();
    loadDlvrPriceExpect();

    changeData();
});

/**
 * @brief 도무송 미리보기 이미지 변경용 규격명 반환
 */
var getStanName = function () {
    var prefix = getPrefix(prdtDvs);
    var stanName = $(prefix + "size > option:selected").text();

    // 자석오프너
    var cuttingSize = $(prefix + "size > option:selected").attr("class")
        .split(' ')[1];
    cuttingSize = cuttingSize.replace("_cuttingWH", '').split('-');

    stanName = "jo-" + cuttingSize[0] + cuttingSize[1];

    return stanName;
};

/**
 * @brief 수량변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeAmt = function () {
    if (amtUnit === 'R') {
        calcSheetCount(prdtDvs);
    } else {
        calcRCount(prdtDvs);
    }
    reCalcAfterPrice(prdtDvs, null);
    changeData();
};

/**
 * @brief 건수변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeCount = function () {
    console.log("afafaf");
    reCalcAfterPrice(prdtDvs, null);
    calcPrice();
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

    var paperName = $("#mg_paper").find("option:selected").text();
    var sizeName = $("#mg_size").find("option:selected").text();
    var tmptName = $("#mg_print_tmpt").find("option:selected").text();

    var sellPrice = $("#sell_price").attr("val");
    var salePrice = $("#sale_price").attr("val");
    var afterPrice = getSumAfterPrice(prdtDvs);
    var optPrice = getSumOptPrice();

    if (makeAfterInfo.all(prdtDvs) === false) {
        return false;
    }

    var orderDetail = cateName + " / " +
        paperName + " / " +
        sizeName + " / " +
        tmptName;

    $("#order_detail").val(orderDetail);

    setAddOptInfo();

    $frm = $("#frm");

    $frm.find("input[name='mg_cate_name']").val(cateName);
    $frm.find("input[name='mg_amt_unit']").val(amtUnit);
    $frm.find("input[name='mg_paper_name']").val(paperName);
    $frm.find("input[name='mg_bef_tmpt_name']").val(tmptName);
    $frm.find("input[name='mg_size_name']").val(sizeName);

    $frm.find("input[name='mg_sell_price']").val(sellPrice);
    $frm.find("input[name='mg_sale_price']").val(salePrice);
    $frm.find("input[name='mg_after_price']").val(afterPrice);
    $frm.find("input[name='opt_price']").val(optPrice);

    return true;
};



/**
 * @brief 가격 구성요소 셀렉트박스 변경시 변경된 정보로 가격 검색
 */
var changeData = function () {
    loadPaperPreview(prdtDvs);
    loadPrdtPrice.data = getData();
    loadPrdtPrice.exec();
};

var getData = function() {
    monoYn = $("#mg_mono_yn").val();

    var data = {
        "dvs": prdtDvs,
        "mono_yn": monoYn,
        "cate_sortcode": $("#mg_cate_sortcode").val(),
        "amt": $("#mg_amt").val(),
        "count": $("#count").val(),
        "size": $("#mg_size").val()
    };

    data.paper = $("#mg_paper").val();
    data.bef_tmpt = $("#mg_print_tmpt").val();
    data.bef_add_print_mpcode = '';
    data.aft_print_mpcode = '';
    data.aft_add_print_mpcode = '';
    data.print_purp = $("#mg_print_purp").val();
    data.page_info = "2";
    data.cut_wid_size = $("#mg_cut_wid_size").val();
    data.cut_vert_size = $("#mg_cut_vert_size").val();
    if ($("#size_dvs").val() === "stan") {
        data.size_def_val =
            $("#mg_size").attr("def_val");
        data.def_stan_name =
            $("#mg_size > option[value='" + data.size_def_val + "']").html();
        data.manu_pos_num =
            $("#manu_pos_num").val();
        data.amt_unit =
            $("#mg_amt").attr("amt_unit");

        //data.cut_wid_size = $("#mg_cut_wid_size").val();
        //data.cut_vert_size = $("#mg_cut_vert_size").val();
    }

    var i = 0;
    $("input[type='checkbox'][name='mg_chk_after[]']").each(function () {
        if ($(this).prop("checked") === false) {
            return true;
        }

        var aft = $(this).attr("aft");
        var aft_depth = $("#mg_" + aft).val() == undefined ? "" : $("#mg_" + aft).val();
        var aft_depth1 = $("#mg_" + aft + "_1").val() == undefined ? "" : $("#mg_" + aft + "_1").val();
        var aft_depth2 = $("#mg_" + aft + "_2").val() == undefined ? "" : $("#mg_" + aft + "_2").val();
        var aft_depth1_val = $("#mg_" + aft + "_val").val() == undefined ? "" : $("#mg_" + aft + "_val").val();
        var aft_depth1_vh = $("#mg_" + aft + "_vh").val() == undefined ? "" : $("#mg_" + aft + "_vh").val();
        var aft_depth1_cnt = $("#mg_" + aft + "_cnt").val() == undefined ? "" : $("#mg_" + aft + "_cnt").val();
        var aft_depth1_dvs = $("#mg_" + aft + "_dvs_1").val() == undefined ? "" : $("#mg_" + aft + "_dvs_1").val();
        var aft_depth2_dvs = $("#mg_" + aft + "_dvs_2").val() == undefined ? "" : $("#mg_" + aft + "_dvs_2").val();
        var aft_depth1_wid = $("#mg_" + aft + "_wid_1").val() == undefined ? "" : $("#mg_" + aft + "_wid_1").val();
        var aft_depth1_vert = $("#mg_" + aft + "_vert_1").val() == undefined ? "" : $("#mg_" + aft + "_vert_1").val();
        var aft_depth2_wid = $("#mg_" + aft + "_wid_2").val() == undefined ? "" : $("#mg_" + aft + "_wid_2").val();
        var aft_depth2_vert = $("#mg_" + aft + "_vert_2").val() == undefined ? "" : $("#mg_" + aft + "_vert_2").val();
        var selector = "input[name='" +
            'nc' + '_' + aft + '_' +
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
            data.aft_depth += $("#mg_" + aft + "_depth").val();
            data.after_name += $("#mg_" + aft + "_name").val();
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
    return data;
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
        sellPrice = parseInt($("#sell_price").attr("val").replace(',', ''));
        loadPrdtPrice.price.sell_price = sellPrice;
    }

    sellPrice = ceilVal(sellPrice);
    //sellPrice *= count;

    // 등급 할인율
    var gradeSale = parseFloat($("#mg_grade_sale_rate").val());
    gradeSale /= 100.0;
    // 회원 할인율
    var memberSale = parseFloat($("#mg_member_sale_rate").val());
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
        paper *= count;
    }

    // 견적서 출력비 계산
    var output = 0;
    if (monoYn === '1') {
        output = parseInt(loadPrdtPrice.price.output);
        output = ceilVal(output);
        output *= count;
    }

    // 견적서 인쇄비 계산
    var print = loadPrdtPrice.price.print;
    if (monoYn === '1') {
        print = parseInt(loadPrdtPrice.price.print);
        print = ceilVal(print);
        print *= count;
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
        "afterTax": after,
        "cut": 0,
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

        var afterDet = $.trim($(".mg_overview").text()); // 후공정 세부내역

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
            ],
            "after_det": [
                afterDet
            ]
        };

        data = getEstiPopData(data);

        this.data = data;
    }
};

// 자석스티커 비규격
var changeSizeDvs = function (val) {
    if (val === "manu") {
        var prefix = getPrefix(prdtDvs);
        $(prefix + "cut_wid_size").val("30");
        $(prefix + "cut_vert_size").val("30");
        size();
    }

    changeData();
};

// 자석스티커 비규격
var calcMgStPrice = function () {
    chkMaxMinSize.exec(prdtDvs, changeData);
};

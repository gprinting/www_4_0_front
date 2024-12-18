var monoYn = null;
var prdtDvs = null;
var sortcode = null;
var cateName = null;
var amtUnit = null;

$(document).ready(function () {
    prdtDvs = $("#prdt_dvs").val();
    var prefix = getPrefix(prdtDvs);

    var order_count = $("#order_count").val();
    initCount(100, "count", order_count);

    monoYn = $(prefix + "mono_yn").val();
    sortcode = $(prefix + "cate_sortcode").val();
    cateName = $(prefix + "cate_sortcode").find("option:selected").text();
    amtUnit = $(prefix + "amt").attr("amt_unit");

    var amtRate = $(prefix + "amt_sale_rate").val();
    var amtAplc = $(prefix + "amt_sale_aplc").val();
    if (checkBlank(amtRate)) {
        amtRate = 0;
    }
    if (checkBlank(amtAplc)) {
        amtAplc = 0;
    }

    $(".preview .cuttingLine, .workingLine").hide();
    $(".dd_multidesign_detail").hide();

    loadPrdtPrice.price.amt_rate = amtRate;
    loadPrdtPrice.price.amt_aplc = amtAplc;

    if (sortcode === "002004001") {
        $(".noti").show();
    }

    if (sortcode === "002005001") {
        var prefix = getPrefix(prdtDvs);

        $(prefix + "dotline_t").parent("span").remove();
        $(prefix + "dotline_b").parent("span").remove();
        $(prefix + "dotline_l").prop({
            "checked": true,
            "disabled": true
        });
        $(prefix + "dotline_r").prop({
            "checked": true,
            "disabled": true
        });
    } else {
        changeData();
        //reCalcAoAfterPrice(prdtDvs, null);
    }

    loadReleaseExpect();
    loadDlvrPriceExpect();
});

/**
 * @brief 수량변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeAmt = function () {
    //reCalcAoAfterPrice(prdtDvs, null);
    if (sortcode === "002004001") {
        reCalcAoOptPrice(prdtDvs, null);
    }
    changeData();
};

/**
 * @brief 가로형 세로형에 따라 가로세로 사이즈 변경
 *
 * @param val = 가로/세로 구분값
 */
var changeSizeType = function (val) {
    var prefix = getPrefix(prdtDvs);

    if (val === "세로분할") {
        $(prefix + "cut_wid_size").hide();
        $(prefix + "cut_vert_size").show();

        $(prefix + "sel_cut_wid_size").show();
        $(prefix + "sel_cut_vert_size").hide();
    } else {
        $(prefix + "cut_wid_size").show();
        $(prefix + "cut_vert_size").hide();

        $(prefix + "sel_cut_wid_size").hide();
        $(prefix + "sel_cut_vert_size").show();
    }

    changeData();
};

var loadRackMinMaxSize = function (val) {};

/**
 * @brief 건수 변경시 디자인 세부 정보창 출력
 */
var changeCount = function(val) {
    val = parseInt(val);

    if (val > 1) {
        $(".dd_multidesign_detail").show();
    } else {
        $(".dd_multidesign_detail").hide();
    }

    changeData();
};


/**
 * @brief 가격 구성요소 셀렉트박스 변경시 변경된 정보로 가격 검색
 */
var changeData = function () {
    var prefix = getPrefix(prdtDvs);
    var data = {
        "dvs": prdtDvs,
        "mono_yn": monoYn,
        "cate_sortcode": sortcode,
        "amt": $(prefix + "amt").val(),
        "size": $(prefix + "size").val(),
        "count": $("#count").val()
    };

    data.paper = $(prefix + "paper").val();
    data.bef_tmpt = $(prefix + "print_tmpt").val();
    data.bef_add_print_mpcode = '';
    data.aft_print_mpcode = '';
    data.aft_add_print_mpcode = '';
    data.page_info = "2";

    var i = 0;
    $("input[type='checkbox'][name='ao_chk_after[]']").each(function () {
        if ($(this).prop("checked") === false) {
            return true;
        }

        var aft = $(this).attr("aft");
        var aft_depth = $("#ao_" + aft).val() == undefined ? "" : $("#ao_" + aft).val();
        var aft_depth1 = $("#ao_" + aft + "_1").val() == undefined ? "" : $("#ao_" + aft + "_1").val();
        var aft_depth2 = $("#ao_" + aft + "_2").val() == undefined ? "" : $("#ao_" + aft + "_2").val();
        var aft_depth1_val = $("#ao_" + aft + "_val").val() == undefined ? "" : $("#ao_" + aft + "_val").val();
        var aft_depth1_vh = $("#ao_" + aft + "_vh").val() == undefined ? "" : $("#ao_" + aft + "_vh").val();
        var aft_depth1_cnt = $("#ao_" + aft + "_cnt").val() == undefined ? "" : $("#ao_" + aft + "_cnt").val();
        var aft_depth1_dvs = $("#ao_" + aft + "_dvs_1").val() == undefined ? "" : $("#ao_" + aft + "_dvs_1").val();
        var aft_depth2_dvs = $("#ao_" + aft + "_dvs_2").val() == undefined ? "" : $("#ao_" + aft + "_dvs_2").val();
        var aft_depth1_wid = $("#ao_" + aft + "_wid_1").val() == undefined ? "" : $("#ao_" + aft + "_wid_1").val();
        var aft_depth1_vert = $("#ao_" + aft + "_vert_1").val() == undefined ? "" : $("#ao_" + aft + "_vert_1").val();
        var aft_depth2_wid = $("#ao_" + aft + "_wid_2").val() == undefined ? "" : $("#ao_" + aft + "_wid_2").val();
        var aft_depth2_vert = $("#ao_" + aft + "_vert_2").val() == undefined ? "" : $("#ao_" + aft + "_vert_2").val();
        var selector = "input[name='" +
            'ao' + '_' + aft + '_' +
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

    i = 0;
    var j = 0;
    $("input[type='checkbox'][name='chk_opt[]']").each(function () {
        if ($(this).prop("checked") === false) {
            j++;
            return true;
        }

        var option_name = $(this).val();
        var opt_depth1 = $("#opt_" + j + "_val > option:selected").text() == undefined ? "" : $("#opt_" + j + "_val > option:selected").text();
        var opt_depth2 = "";
        var opt_depth3 = "";
        //var opt_depth2 = $("#opt_" + aft + "_1").val() == undefined ? "" : $("#ao_" + aft + "_1").val();
        //var opt_depth3 = $("#opt_" + aft + "_2").val() == undefined ? "" : $("#ao_" + aft + "_2").val();
        var opt_cnt = $("#opt_" + j + "_cnt > option:selected").val() == undefined ? "" : $("#opt_" + j + "_cnt > option:selected").val();
        if (i != 0) {
            data.option_name += "|";
            data.opt_depth += "|";
            data.opt_depth1 += "|";
            data.opt_depth2 += "|";
            data.opt_depth3 += "|";
            data.opt_cnt += "|";
        } else {
            data.option_name = "";
            data.opt_depth = "";
            data.opt_depth1 = "";
            data.opt_depth2 = "";
            data.opt_depth3 = "";
            data.opt_cnt = "";
        }
        data.option_name += option_name;
        data.opt_depth1 += opt_depth1;
        data.opt_depth2 += opt_depth2;
        data.opt_depth3 += opt_depth3;
        data.opt_cnt += opt_cnt;
        j++;
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

    $("#option_name").val(data.option_name);
    $("#opt_depth1").val(data.opt_depth1);
    $("#opt_depth2").val(data.opt_depth2);
    $("#opt_depth3").val(data.opt_depth3);
    $("#opt_cnt").val(data.opt_cnt);

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

            loadPrdtPrice.price = result[prdtDvs];

            var prefix = getPrefix(prdtDvs);
            $(prefix + "prdt_price").val(result[prdtDvs].sell_price);
            setAfterPrice("ao", "cool_coating", result[prdtDvs].cool_coating);

            $("input[name='ao_chk_after[]']:checked").each(function () {
                var $obj = $(this);
                var aft = $obj.attr('id').replace("ao_", "");

                afterPrice = loadPrdtPrice.price[aft];
                setAfterPrice("ao", aft, afterPrice);
            });

            $("input[name='chk_opt[]']:checked").each(function () {
                setOptPrice(0, result[prdtDvs][$(this).val()]);
            });
            calcPrice(false);
        };

        ajaxCall(url, "json", loadPrdtPrice.data, callback);
    }
};

/**
 * @brief 화면에 출력되는 가격 및 빠른견적서 내용 수정
 */
var calcPrice = function (flag) {
    var prefix = getPrefix(prdtDvs);
    // 건수
    var count = 1;
    // 특별할인
    var amtRate = parseFloat(loadPrdtPrice.price.amt_rate);
    amtRate /= 100.0;
    var amtAplc = parseInt(loadPrdtPrice.price.amt_aplc);
    // 정상판매가
    var sellPrice = loadPrdtPrice.price.sell_price;


    if (checkBlank(sellPrice)) {
        sellPrice = $("#sell_price").attr("val").replace(/,/gi, '');
        loadPrdtPrice.price.sell_price = sellPrice;
    }
    sellPrice = ceilVal(sellPrice);
    //sellPrice *= count;
    // 등급 할인율
    var gradeSale = parseFloat($(prefix + "grade_sale_rate").val());
    gradeSale /= 100.0;
    // 회원 할인율
    var memberSale = parseFloat($(prefix + "member_sale_rate").val());
    memberSale /= 100.0;
    // 옵션비 총합
    var sumOptPrice = getSumOptPrice();
    sumOptPrice = ceilVal(sumOptPrice);
    // 후공정비 총합
    var sumAfterPrice = getSumAoAfterPrice(prdtDvs);
    sumAfterPrice = ceilVal(sumAfterPrice);

    // 견적서 종이비 계산
    var paper = 0;
    // 견적서 출력비 계산
    var output = 0;
    // 견적서 인쇄비 계산
    var print = loadPrdtPrice.price.print;

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

    // 정상판매가 변경(후공정, 옵션은 할인하지 않는다)
    $("#sell_price").html(calcPayPrice.format() + "원");
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
    loadDlvrPriceExpect(flag);
    changeQuickEsti(param);

    if (flag === false) {
        return false;
    }

    reCalcOptPrice(prdtDvs, null);
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

    var prefix = getPrefix(prdtDvs);

    var amtUnit = $(prefix + "amt").attr("amt_unit");
    var paperName = $(prefix + "paper").find("option:selected").text();
    var tmptName = $(prefix + "print_tmpt").find("option:selected").text();
    var sizeName = $(prefix + "size").find("option:selected").text();

    var sellPrice = $("#sell_price").attr("val");
    var salePrice = $("#sale_price").attr("val");
    var afterPrice = getSumAfterPrice(prdtDvs);
    var optPrice = getSumOptPrice();

    var sortcode = $(prefix + "cate_sortcode").val();
    if ((!setBasicAoAfterInfo(prdtDvs)) && sortcode != "002002002" && sortcode != "002002003" && sortcode != "002006001" && sortcode != "002006002") {
        return alertReturnFalse("기본 후공정을 선택해주세요.");
    }

    if (makeAoAfterInfo.all(prdtDvs) === false) {
        return false;
    }

    var orderDetail = cateName + " / " +
        paperName + " / " +
        sizeName + " / " +
        tmptName;

    $("#order_detail").val(orderDetail);

    setAddOptInfo();

    $frm = $("#frm");
    $frm.find("input[name='" + prdtDvs + "_cate_name']").val(cateName);
    $frm.find("input[name='" + prdtDvs + "_amt_unit']").val(amtUnit);
    $frm.find("input[name='" + prdtDvs + "_paper_name']").val(paperName);
    $frm.find("input[name='" + prdtDvs + "_bef_tmpt_name']").val(tmptName);
    $frm.find("input[name='" + prdtDvs + "_size_name']").val(sizeName);

    $frm.find("input[name='" + prdtDvs + "_sell_price']").val(sellPrice);
    $frm.find("input[name='" + prdtDvs + "_sale_price']").val(salePrice);
    $frm.find("input[name='" + prdtDvs + "_after_price']").val(afterPrice);
    $frm.find("input[name='opt_price']").val(optPrice);

    return true;
};

/**
 * @brief 실사출력 미싱 depth2 검색
 */
var loadAoCuttingDepth2 = function (aft, val) {
    var prefix = getPrefix(prdtDvs);
    var callback = function (result) {

        $(prefix + aft + "_val").html(result);

        // 여백 추가 each
        $(prefix + aft + "_val").each(function () {
            $(this).children().append(" 여백");
        });

        getAoAfterPrice.common(aft, prdtDvs);
    };

    var data = {
        "cate_sortcode": $(prefix + "cate_sortcode").val(),
        "after_name": $(prefix + aft).val(),
        "depth1": val,
        "flag": 'Y'
    };

    loadAoAfterDepth(data, callback);
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

        var afterDet = $.trim($(".ao_overview").text()); // 후공정 세부내역

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

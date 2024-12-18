var monoYn = null;
var prdtDvs = null;
var sortcode = null;
var cateName = null;
var amtUnit = null;

$(document).ready(function () {
    prdtDvs = $("#prdt_dvs").val();
    var prefix = getPrefix(prdtDvs);

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

    changeDesignSize();

    loadReleaseExpect();
    loadDlvrPriceExpect();
});

/**
 * @brief 수량변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeAmt = function () {
    reCalcAoAfterPrice(prdtDvs, null);
    changeData();
};

/**
 * @brief 제품사이즈 변경시 디자인사이즈 변경
 */
var changeDesignSize = function () {
    var prefix = getPrefix(prdtDvs);
    var wid = parseInt($(prefix + "cut_wid_size").val());
    var vert = parseInt($(prefix + "cut_vert_size").val());

    $("#design_wid_size").val(wid);
    $("#design_vert_size").val(vert);
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
        $(prefix + "divide_zone").hide();
    } else if (val === "가로분할") {
        $(prefix + "cut_wid_size").show();
        $(prefix + "cut_vert_size").hide();

        $(prefix + "sel_cut_wid_size").hide();
        $(prefix + "sel_cut_vert_size").show();
        $(prefix + "divide_zone").hide();
    } else {
        $(prefix + "cut_wid_size").show();
        $(prefix + "cut_vert_size").show();
        // 초기값 900 지정
        $(prefix + "cut_vert_size").val(900);

        $(prefix + "sel_cut_wid_size").hide();
        $(prefix + "sel_cut_vert_size").hide();
    }

    changeSize();
};

/**
 * @brief 실사출력 최소/최대사이즈 처리
 */
var chkAoMaxMinSize = function () {
    var prefix = getPrefix(prdtDvs);

    var url = "/json/product/load_max_min_size.php";
    var data = {
        "cate_sortcode": sortcode
    };
    var callback = function (result) {
        var $wid = $(prefix + "cut_wid_size");
        var $vert = $(prefix + "cut_vert_size");
        var wid = parseInt($wid.val());
        var vert = parseInt($vert.val());

        /*
        var minWid  = result.min_wid;
        var minVert = result.min_vert;
        var maxWid  = result.max_wid;
        var maxVert = result.max_vert;

        if ($("#size_type").val() === "세로분할") {
            minWid  = result.min_vert;
            minVert = result.min_wid;
            maxWid  = result.max_vert;
            maxVert = result.max_wid;
        }

        minWid  = parseInt(minWid);
        minVert = parseInt(minVert);
        maxWid  = parseInt(maxWid);
        maxVert = parseInt(maxVert);

        var str = "최대사이즈는 " + maxWid + "*" +
                  maxVert + "입니다.";

        if (cutWid > maxWid) {
            alert(str);
            $wid.val(maxWid);
        }
        if (cutVert > maxVert) {
            alert(str);
            $vert.val(maxVert);
        }

        str = "최소사이즈는 " + minWid + "*" +
              minVert + "입니다.";

        if (cutWid < minWid) {
            alert(str);
            $wid.val(minWid);
        }
        if (cutVert < minVert) {
            alert(str);
            $vert.val(minVert);
        }
        */

        $wid.val((wid / 100) * 100);
        $vert.val(vert - (vert % 100));

        showHideDivideArea();

        changeData();
        if (isFunc("size")) {
            size();
        }
        reCalcAoAfterPrice(prdtDvs, null);
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 가격 구성요소 셀렉트박스 변경시 변경된 정보로 가격 검색
 */
var changeData = function () {
    changeDesignSize();

    var prefix = getPrefix(prdtDvs);
    var data = {
        "dvs": prdtDvs,
        "cate_sortcode": sortcode,
        "amt": $(prefix + "amt").val(),
        "stan_mpcode": $(prefix + "size").val()
            // 값이 최종적으로 맞는다
            ,
        "cut_wid_size": $(prefix + "cut_wid_size").val(),
        "cut_vert_size": $(prefix + "cut_vert_size").val()
    };

    data.paper_mpcode = $(prefix + "paper").val();
    data.bef_print_mpcode = $(prefix + "print_tmpt").val();
    data.bef_add_print_mpcode = '';
    data.aft_print_mpcode = '';

    var i = 0;
    $("input[type='checkbox'][name='ao_placard_chk_after[]']").each(function () {
        if ($(this).prop("checked") === false) {
            return true;
        }

        var aft = $(this).attr("aft");
        var aft_depth = $("#ao_placard_" + aft).val() == undefined ? "" : $("#ao_placard_" + aft).val();
        var aft_depth1 = $("#ao_placard_" + aft + "_1").val() == undefined ? "" : $("#ao_placard_" + aft + "_1").val();
        var aft_depth2 = $("#ao_placard_" + aft + "_2").val() == undefined ? "" : $("#ao_placard_" + aft + "_2").val();
        var aft_depth1_val = $("#ao_placard_" + aft + "_val").val() == undefined ? "" : $("#ao_placard_" + aft + "_val").val();
        var aft_depth1_vh = $("#ao_placard_" + aft + "_vh").val() == undefined ? "" : $("#ao_placard_" + aft + "_vh").val();
        var aft_depth1_cnt = $("#ao_placard_" + aft + "_cnt").val() == undefined ? "" : $("#ao_placard_" + aft + "_cnt").val();
        var aft_depth1_dvs = $("#ao_placard_" + aft + "_dvs_1").val() == undefined ? "" : $("#ao_placard_" + aft + "_dvs_1").val();
        var aft_depth2_dvs = $("#ao_placard_" + aft + "_dvs_2").val() == undefined ? "" : $("#ao_placard_" + aft + "_dvs_2").val();
        var aft_depth1_wid = $("#ao_placard_" + aft + "_wid_1").val() == undefined ? "" : $("#ao_placard_" + aft + "_wid_1").val();
        var aft_depth1_vert = $("#ao_placard_" + aft + "_vert_1").val() == undefined ? "" : $("#ao_placard_" + aft + "_vert_1").val();
        var aft_depth2_wid = $("#ao_placard_" + aft + "_wid_2").val() == undefined ? "" : $("#ao_placard_" + aft + "_wid_2").val();
        var aft_depth2_vert = $("#ao_placard_" + aft + "_vert_2").val() == undefined ? "" : $("#ao_placard_" + aft + "_vert_2").val();
        var selector = "input[name='" +
            'ao_placard' + '_' + aft + '_' +
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
    $("input[type='checkbox'][name='chk_opt[]']").each(function () {
        if ($(this).prop("checked") === false) {
            i++;
            return true;
        }

        var option_name = $(this).val();
        var opt_depth1 = $("#opt_" + i + "_val > option:selected").text() == undefined ? "" : $("#opt_" + i + "_val > option:selected").text();
        var opt_depth2 = "";
        var opt_depth3 = "";
        //var opt_depth2 = $("#opt_" + aft + "_1").val() == undefined ? "" : $("#ao_" + aft + "_1").val();
        //var opt_depth3 = $("#opt_" + aft + "_2").val() == undefined ? "" : $("#ao_" + aft + "_2").val();
        var opt_cnt = $("#opt_" + i + "_cnt > option:selected").val() == undefined ? "" : $("#opt_" + i + "_cnt > option:selected").val();

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
        var url = url = "/ajax/product/load_price.php";
        var callback = function (result) {
            if (checkBlank(result[prdtDvs].sell_price) === true) {
                return alertReturnFalse("해당하는 가격이 존재하지 않습니다.\n관리자에게 문의하세요.");
            }

            loadPrdtPrice.price = result[prdtDvs];

            var prefix = getPrefix(prdtDvs);
            $(prefix + "prdt_price").val(result[prdtDvs].sell_price);

            $("input[name='ao_placard_chk_after[]']:checked").each(function () {
                var $obj = $(this);
                var aft = $obj.attr('id').replace("ao_placard_", "");

                afterPrice = loadPrdtPrice.price[aft];
                setAfterPrice("ao_placard", aft, afterPrice);
            });

            $("input[name='chk_opt[]']:checked").each(function () {
                setOptPrice(0, result[prdtDvs][$(this).val()]);
            });

            calcPrice();
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
        sellPrice = parseInt($("#sell_price").attr("val").replace(',', ''));
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

    // 180523 추가 : 현수막 분할접착비용
    var divideInfo = $.trim($("#ao_placard_divide_rs").text());
    var divideNum = parseInt(divideInfo.split('분')[0]);

    if (divideInfo) {
        var dividePrice = 3000;
        var divideStand = 1000;

        var divMet = $("#ao_placard_divide_method").val();
        var divLen = $("#ao_placard_divide_size").val();
        var widSize = parseInt($("#ao_placard_cut_wid_size").val());
        var vertSize = parseInt($("#ao_placard_cut_vert_size").val());
        var adhPrice = 0;
        var adhLen = 0;
        var adhNum = 0;

        if (divMet == "세로분할") {
            adhLen = vertSize * (divideNum - 1);
        } else {
            adhLen = widSize * (divideNum - 1);
        }
        adhNum = Math.ceil(adhLen / divideStand);
        adhPrice = adhNum * dividePrice;

        // 인쇄값에도 분할접착비가 들어 가고
        print = sellPrice;
        print += adhPrice;

        // 정상판매가에도 분할접착비가 들어가야
        // 값이 최종적으로 맞는다
        sellPrice += adhPrice;

        //
    } else {
        // 안 탈 경우 쓰레기값이 들어갈 경우 대비 0으로 지정
        adhPrice = 0;
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

    // 정상판매가 변경(후정, 옵션은 할인하지 않는다)
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
    var sizeName = $(prefix + "cut_wid_size").val() + '*' + $(prefix + "cut_vert_size").val();

    var sellPrice = $("#sell_price").attr("val");
    var salePrice = $("#sale_price").attr("val");
    var afterPrice = getSumAfterPrice(prdtDvs);
    var optPrice = getSumOptPrice();

    if (!setBasicAoAfterInfo(prdtDvs)) {
        return alertReturnFalse("기본 후공정을 선택해주세요.");
    }

    if (makeAoAfterInfo.all(prdtDvs) === false) {
        return false;
    }

    // 180416 추가 : order_detail 정보 추가
    var orderDetail = "";

    orderDetail += cateName + " / " +
        paperName + " / " +
        sizeName + " / " +
        tmptName;

    var divideMethod = $(prefix + "divide_method").val();
    var divideSize = $(prefix + "divide_size").val();
    var divideRs = $(prefix + "divide_rs").html();
    var divideData = "";

    if (!checkBlank($.trim(divideRs))) {
        divideData = " / " + divideMethod + " " + divideSize + "mm " +
            divideRs;
    }

    orderDetail += divideData;

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
    // 분할 관련 추가
    $frm.find("input[name='" + prdtDvs + "_order_detail']").val(orderDetail);

    //return false;
    return true;
};

/**
 * @brief 분할조건입력 필드 show/hide
 */
var showHideDivideArea = function () {
    var prefix = getPrefix(prdtDvs);
    var cWid = parseInt($(prefix + "cut_wid_size").val());
    var cVert = parseInt($(prefix + "cut_vert_size").val());

    cWid += 200;

    if (cWid > 1800 && cVert > 1800) {
        if ($("#size_type").val() == "직접입력") {
            $(prefix + "divide_zone").show();
            showDevideRs();
        } else {
            $(prefix + "divide_zone").hide();
            $("#ao_placard_divide_rs").text("");
        }
    } else {
        $(prefix + "divide_zone").hide();
        $("#ao_placard_divide_rs").text("");
    }
}

/**
 * @brief 분할결과 show
 */
var showDevideRs = function () {

    var prefix = getPrefix(prdtDvs);
    var cWid = parseInt($(prefix + "cut_wid_size").val());
    var cVert = parseInt($(prefix + "cut_vert_size").val());
    var method = $(prefix + "divide_method").val();
    var sizeVal = parseInt($(prefix + "divide_size").val());
    var resM = ""; // 분할 연산 받는 변수
    var resS = ""; // 분할 나머지 판별
    var resF = ""; // 최종 분할 수

    // 가로값에 240, 세로값에 40 추가
    cWid += 240;
    cVert += 40;

    if (method == "세로분할") {
        resM = parseInt(cWid / sizeVal);
        resS = parseInt(cWid % sizeVal);
    } else {
        resM = parseInt(cVert / sizeVal);
        resS = parseInt(cVert % sizeVal);
    }

    // 나머지가 0보다 클 경우 1분할 추가
    if (resS > 0) {
        resM += 1;
    }

    resF = resM + "분할";
    $("#ao_placard_divide_rs").text(resF);
    calcPrice();
}

/**
 * @brief 견적서 팝업 본문정보 생성
 */
var makeEstiPopInfo = {
    "data": null,
    "exec": function (dvs) {
        var prefix = getPrefix(prdtDvs);

        var paper = $.trim($(prefix + "paper > option:selected").text());
        var tmpt = $.trim($(prefix + "print_tmpt > option:selected").text());
        var amt = $.trim($(prefix + "amt").val());
        var count = $.trim($("#esti_count").text());

        // 현수막사이즈
        var size = $.trim($("#size_type > option:selected").text());
        var sizeDet = "(";

        if ($.trim(size) == "가로형") {
            sizeDet += $.trim($(prefix + "cut_wid_size").val());
            sizeDet += "*";
            sizeDet += $.trim($(prefix + "sel_cut_vert_size > option:selected").text());
        } else if ($.trim(size) == "세로형") {
            sizeDet += $.trim($(prefix + "sel_cut_wid_size > option:selected").text());
            sizeDet += "*";
            sizeDet += $.trim($(prefix + "cut_vert_size").val());
        } else if ($.trim(size) == "직접입력") {
            sizeDet += $.trim($(prefix + "cut_wid_size").val());
            sizeDet += "*";
            sizeDet += $.trim($(prefix + "cut_vert_size").val());
        }
        sizeDet += ")";
        size += sizeDet;

        var afterDet = $.trim($(".ao_placard_overview").text()); // 후공정 세부내역

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
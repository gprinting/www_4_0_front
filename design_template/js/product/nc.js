var monoYn = null;
var prdtDvs = null;
var sortcode = null;
var cateName = null;
var amtUnit = null;

$(document).ready(function () {
    // 건수 초기화
    var order_count = $("#order_count").val();
    initCount(999, "count", order_count);

    monoYn = $("#nc_mono_yn").val();
    prdtDvs = $("#prdt_dvs").val();
    sortcode = $("#nc_cate_sortcode").val();
    cateName = $("#nc_cate_sortcode").find("option:selected").text();
    amtUnit = $("#nc_amt").attr("amt_unit");
    var im = $("#im").val();

    calcManuPosNum.defWid = parseFloat($("#nc_size").attr("def_cut_wid"));
    calcManuPosNum.defVert = parseFloat($("#nc_size").attr("def_cut_vert"));

    var order_cut_wid = $("#order_cut_wid").val();
    var order_cut_vert = $("#order_cut_vert").val();
    if(order_cut_wid != "" && order_cut_vert != "") {
        $("#nc_cut_wid_size").val(order_cut_wid);
        $("#nc_cut_vert_size").val(order_cut_vert);
        if(calcManuPosNum.defWid !=  order_cut_wid ||
            calcManuPosNum.defVert !=  order_cut_vert) {
            $("#size_dvs").val("manu");
            changeSizeDvs("manu");
            size();
        }
    }

    chkSizeDotImpWarning();

    if (im == "0") {
        showUvDescriptor(prdtDvs);
    }

    if (typeof preview !== "undefined" && sortcode === "003003001") {
        preview.content.add(preview.content.children('.after'))
            .css('border-radius', "16px");
    }

    var prefix = getPrefix(prdtDvs);
    var amtRate = $(prefix + "amt_sale_rate").val();
    var amtAplc = $(prefix + "amt_sale_aplc").val();
    if (checkBlank(amtRate)) {
        amtRate = 0;
    }
    if (checkBlank(amtAplc)) {
        amtAplc = 0;
    }

    // 고품격명함 비규격 X
    if (sortcode === "003001003" || sortcode === "003001004") {
        $("#size_dvs").prop("disabled", true);
        $("#size_dvs ._custom").remove();
    }

    // 수입명함 재질확인
    if (sortcode === "003001002") {
        showHideDayBoard();
    }

    loadPrdtPrice.price.amt_rate = amtRate;
    loadPrdtPrice.price.amt_aplc = amtAplc;

    set3DWarning();

    chkBackBoard();

    if (sortcode === "003002003" || sortcode === "003003001" || sortcode === "003003002") {
        aftRestrict.checked(prdtDvs, "rounding");
        $("li._rounding").show();

        $(prefix + "rounding").prop("disabled", true);
        $(prefix + "rounding").prop("checked", true);

        $("input:checkbox[name='nc_rounding_dvs']").prop("disabled", true);
        $("input:checkbox[name='nc_rounding_dvs']").prop("checked", true);

        //$("input:checkbox[name='" + aft + "']").prop("checked", false);
        //$("input:checkbox[name='" + aft + "']").prop("checked", false);

        //$("input:checkbox[name='" + aft + "']").prop("checked", false);
        //$("input:checkbox[name='" + aft + "']").prop("checked", false);

        //$("input:checkbox[name='" + aft + "']").prop("checked", false);
        //$("input:checkbox[name='" + aft + "']").prop("checked", false);

        changeData();
    }

    if (sortcode === "003011001") {
        aftRestrict.checked(prdtDvs, "edgefoil");
        $("li._edgefoil").show();

        $(prefix + "edgefoil").prop("disabled", true);
        $(prefix + "edgefoil").prop("checked", true);

        //$("input:checkbox[name='nc_rounding_dvs']").prop("disabled", true);
        //$("input:checkbox[name='nc_rounding_dvs']").prop("checked", true);

        //$("input:checkbox[name='" + aft + "']").prop("checked", false);
        //$("input:checkbox[name='" + aft + "']").prop("checked", false);

        //$("input:checkbox[name='" + aft + "']").prop("checked", false);
        //$("input:checkbox[name='" + aft + "']").prop("checked", false);

        //$("input:checkbox[name='" + aft + "']").prop("checked", false);
        //$("input:checkbox[name='" + aft + "']").prop("checked", false);

        changeData();
    }

    loadReleaseExpect();
    loadDlvrPriceExpect();

    reCalcAfterPrice(prdtDvs, null);
    showHideAfterProcess();
});

var set3DWarning = function () {
    var prefix = getPrefix(prdtDvs);

    if(sortcode == "003007002")
        $(prefix + "warning").text("※ 금박부분에 글자크기는 12pt 이상만 가능합니다.");
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
    monoYn = $("#nc_mono_yn").val();

    var data = {
        "dvs": prdtDvs,
        "mono_yn": monoYn,
        "cate_sortcode": $("#nc_cate_sortcode").val(),
        "amt": $("#nc_amt").val(),
        "count": $("#count").val(),
        "size": $("#nc_size").val()
    };

    data.paper = $("#nc_paper").val();
    data.bef_tmpt = $("#nc_print_tmpt").val();
    data.bef_add_print_mpcode = '';
    data.aft_print_mpcode = '';
    data.aft_add_print_mpcode = '';
    data.print_purp = $("#nc_print_purp").val();
    data.page_info = "2";
    data.cut_wid_size = $("#nc_cut_wid_size").val();
    data.cut_vert_size = $("#nc_cut_vert_size").val();
    if ($("#size_dvs").val() === "stan") {
        data.size_def_val =
            $("#nc_size").attr("def_val");
        data.def_stan_name =
            $("#nc_size > option[value='" + data.size_def_val + "']").html();
        data.manu_pos_num =
            $("#manu_pos_num").val();
        data.amt_unit =
            $("#nc_amt").attr("amt_unit");

        //data.cut_wid_size = $("#nc_cut_wid_size").val();
        //data.cut_vert_size = $("#nc_cut_vert_size").val();
    }

    var i = 0;
    $("input[type='checkbox'][name='nc_chk_after[]']").each(function () {
        if ($(this).prop("checked") === false) {
            return true;
        }

        var aft = $(this).attr("aft");
        var aft_depth = $("#nc_" + aft).val() == undefined ? "" : $("#nc_" + aft).val();
        var aft_depth1 = $("#nc_" + aft + "_1").val() == undefined ? "" : $("#nc_" + aft + "_1").val();
        var aft_depth2 = $("#nc_" + aft + "_2").val() == undefined ? "" : $("#nc_" + aft + "_2").val();
        var aft_depth1_val = $("#nc_" + aft + "_val").val() == undefined ? "" : $("#nc_" + aft + "_val").val();
        var aft_depth1_vh = $("#nc_" + aft + "_vh").val() == undefined ? "" : $("#nc_" + aft + "_vh").val();
        var aft_depth1_cnt = $("#nc_" + aft + "_cnt").val() == undefined ? "" : $("#nc_" + aft + "_cnt").val();
        var aft_depth1_dvs = $("#nc_" + aft + "_dvs_1").val() == undefined ? "" : $("#nc_" + aft + "_dvs_1").val();
        var aft_depth2_dvs = $("#nc_" + aft + "_dvs_2").val() == undefined ? "" : $("#nc_" + aft + "_dvs_2").val();
        var aft_depth1_wid = $("#nc_" + aft + "_wid_1").val() == undefined ? "" : $("#nc_" + aft + "_wid_1").val();
        var aft_depth1_vert = $("#nc_" + aft + "_vert_1").val() == undefined ? "" : $("#nc_" + aft + "_vert_1").val();
        var aft_depth2_wid = $("#nc_" + aft + "_wid_2").val() == undefined ? "" : $("#nc_" + aft + "_wid_2").val();
        var aft_depth2_vert = $("#nc_" + aft + "_vert_2").val() == undefined ? "" : $("#nc_" + aft + "_vert_2").val();
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
            data.aft_depth += $("#nc_" + aft + "_depth").val();
            data.after_name += $("#nc_" + aft + "_name").val();
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

var calcManuPosNum = {
    "defWid": 0,
    "defVert": 0,
    "maxWid": 0,
    "maxVert": 0,
    "exec": function (dvs) {
        if ($("#no_pos").length > 0) {
            return false;
        }

        changeData();
    }
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
            $(prefix + "manu_pos_num").val(Math.ceil(result[prdtDvs].jarisu));
            $("input[name='nc_chk_after[]']:checked").each(function () {
                var $obj = $(this);
                var aft = $obj.attr('id').replace("nc_", "");
                if (aft == "foil") {
                    var totalAfterPrice = 0;
                    afterPrice = loadPrdtPrice.price["foil1"];
                    if (afterPrice) {
                        setAfterPrice("nc", "foil1", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    afterPrice = loadPrdtPrice.price["foil2"];
                    if (afterPrice) {
                        setAfterPrice("nc", "foil2", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    afterPrice = loadPrdtPrice.price["foil3"];
                    if (afterPrice) {
                        setAfterPrice("nc", "foil3", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    setAfterPrice("nc", "foil", totalAfterPrice);
                } else {
                    afterPrice = loadPrdtPrice.price[aft];
                    setAfterPrice("nc", aft, afterPrice);
                }
            });
            afterOverview("nc");
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
    // 자리수
    var posNum = 1;
    if ($("#size_dvs").val() === "manu") {
        posNum = parseFloat($("#nc_manu_pos_num").val());
    }
    // 건수
    var count = parseInt($("#count").val());
    // 특별할인
    var amtRate = parseFloat(loadPrdtPrice.price.amt_rate);
    amtRate /= 100.0;
    var amtAplc = parseInt(loadPrdtPrice.price.amt_aplc);
    // 정상판매가
    var sellPrice = loadPrdtPrice.price.sell_price;
    // 인쇄비
    var printPrice = loadPrdtPrice.price.print;
    if (checkBlank(sellPrice)) {
        sellPrice = parseInt($("#sell_price").attr("val").replace(',', ''));
        loadPrdtPrice.price.sell_price = sellPrice;
    }
    //alert(loadPrdtPrice.price.foil);
    sellPrice = ceilVal(sellPrice);
    //sellPrice *= posNum;
    //sellPrice *= count;
    // 등급 할인율
    var gradeSale = loadPrdtPrice.price.grade_rate;
    gradeSale /= 100.0;
    // 회원 할인율
    var memberSale = parseFloat($("#nc_member_sale_rate").val());
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
    var print = printPrice;
    if (monoYn === '1') {
        print = parseInt(loadPrdtPrice.price.print);
        print = ceilVal(print);
        //print *= count;
    }

    // 견적서 인쇄비 계산
    var cut = 0;
    if (monoYn === '1') {
        cut = parseInt(loadPrdtPrice.price.cut);
        cut = ceilVal(cut);
        //print *= count;
    }

    // 후공정비 계산(세금 포함)
    var after = sumAfterPrice;
    // 견적서 후공정비 계산(세금 미포함)
    var esAfter = getSumAfterPrice(prdtDvs, "T");
    // 견적서 옵션비 계산
    var opt = sumOptPrice;

    // 회원등급 할인가 계산
    var calcGradeSale = sellPrice * gradeSale;
    calcGradeSale = ceilVal(calcGradeSale);
    // 특별할인가 계산
    var calcAmtSale = (sellPrice - calcGradeSale) * amtRate + amtAplc;
    calcAmtSale = ceilVal(calcAmtSale);
    // 회원 할인가 계산
    var calcMemberSale = (sellPrice - calcGradeSale) * memberSale;
    calcMemberSale = ceilVal(calcMemberSale);
    // 기본할인가 계산
    var calcSalePrice = sellPrice - calcGradeSale + calcMemberSale + calcAmtSale;
    // 결제금액 계산
    var calcPayPrice = calcSalePrice + opt;
    // 부가세(견적서x, 하단o)
    //var tax = ceilVal(calcSalePrice / 11) + (after - esAfter);
    var tax = ceilVal(calcPayPrice / 11);
    // 공급가(견적서x, 하단o)
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
        "cut": cut,
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
    changeQuickEsti(param); // 견적서 내용 변경

};

/**
 * @param 인쇄방식에 해당하는 인쇄도수 검색
 *
 * @param val = 인쇄방식
 */
var loadPrintTmpt = function (dvs) {
    var callback = function (result) {
        var prefix = getPrefix(dvs);
        $(prefix + "print_tmpt").html(result.bef_tmpt);

        if (monoYn === '1') {
            changeData();
        }
    };

    loadPrintTmptCommon.exec(dvs, callback);
};

/**
 * @param 고급명함에서 종이변경시 수량 가져오는 함수
 *
 * @param dvs = 제품구분값
 * @param val = 종이 맵핑코드
 */
var loadPrdtAmt = function (dvs, val) {
    var prefix = getPrefix(dvs);

    var url = "/ajax/product/load_amt.php";
    var data = {
        "cate_sortcode": sortcode,
        "paper_mpcode": val,
        "mono_yn": monoYn,
        "amt_unit": amtUnit
    };

    var selected_amt = $("#nc_amt").val();
    var callback = function (result) {
        $("#nc_amt").html(result);
        $("#nc_amt").val(selected_amt);
        optionPosition = null;
        //rangeBarBySelect();
        //changePaper(dvs, val);
        changeData();
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @param 종이변경시 후공정 제약사항 체크
 *
 * @param dvs = 제품구분값
 * @param val = 종이 맵핑코드
 */
var changePaper = function (dvs, val) {
    loadPaperDscr.exec(dvs, val);

    if (sortcode == "003001001") {
        showHideAfterProcess();
    }

    // 고급(수입)명함
    if (sortcode == "003002001") {
        showHideDayBoard();
        showHideAfterProcess();
        loadPrdtAmt(dvs, val); // 새로 추가됨
    }

    // 카드명함
    if (sortcode == "003003001") {
        showHidePrintTmpt();
        chkBackBoard();
        loadPrdtAmt(dvs, val); // 새로 추가됨
    }
};

/**
 * @brief 비규격 사이즈 선택할 경우 재단사이즈 값 초기화
 *
 * @param val = 구분값
 */
var changeSizeDvs = function (val) {
    // 비규격 사이즈 선택시 기본 사이즈로 데이터 변경
    if (val === "stan") {
        $("#nc_manu_pos_num").val('1');
        $("#nc_cut_wid_size").val($("#nc_size").attr("def_cut_wid"));
        $("#nc_cut_vert_size").val($("#nc_size").attr("def_cut_vert"));
    }

    changeData();
};

/**
 * @brief 수량변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeAmt = function () {
    //reCalcAfterPrice(prdtDvs, null);
    changeData();
};

/**
 * @brief 건수변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeCount = function () {
    //reCalcAfterPrice(prdtDvs, null);
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
        //return alertReturnFalse("로그인 후 확인 가능합니다.");
    }

    if (!aftRestrict.all(prdtDvs)) {
        return false;
    }

    if (!optRestrict.all(prdtDvs)) {
        return false;
    }

    var paperName = $("#nc_paper").find("option:selected").text();
    var sizeName = $("#nc_size").find("option:selected").text();
    var tmptName = $("#nc_print_tmpt").find("option:selected").text();
    var SizeDvs = $("#size_dvs").find("option:selected").val();
    if(SizeDvs == "manu") {
        sizeName = $("#nc_cut_wid_size").val() + "*" + $("#nc_cut_vert_size").val();
    }

    var sellPrice = $("#sell_price").attr("val");
    var salePrice = $("#sale_price").attr("val");
    var afterPrice = getSumAfterPrice(prdtDvs);
    var optPrice = getSumOptPrice();

    // 백판관련
    if ($(".back_pos").length > 0 &&
        $(".back_pos").css("display") !== "none") {
        if (checkBlank($("#back_wid_size").val()) ||
            checkBlank($("#back_vert_size").val())) {
            return alertReturnFalse("백판사이즈를 입력해주세요.");
        }

        if ($("input[name='back_pos_dvs']:checked").length === 0) {
            return alertReturnFalse("백판위치를 선택해주세요.");
        }

        var orderDetail = cateName + " / " +
            paperName + " / " +
            sizeName + " / " +
            tmptName + " / " +
            "[백판사이즈 : " +
            $("#back_wid_size").val() + '*' +
            $("#back_vert_size").val() + " / " +
            "백판위치 : " +
            $("input[name='back_pos_dvs']:checked").val() +
            ']';

        $("#order_detail").val(orderDetail);
    } else {
        var orderDetail = cateName + " / " +
            paperName + " / " +
            sizeName + " / " +
            tmptName;

        $("#order_detail").val(orderDetail);
    }

    if (makeAfterInfo.all(prdtDvs) === false) {
        return false;
    }

    setAddOptInfo();

    $frm = $("#frm");

    $frm.find("input[name='nc_cate_name']").val(cateName);
    $frm.find("input[name='nc_amt_unit']").val(amtUnit);
    $frm.find("input[name='nc_paper_name']").val(paperName);
    $frm.find("input[name='nc_bef_tmpt_name']").val(tmptName);
    $frm.find("input[name='nc_size_name']").val(sizeName);

    $frm.find("input[name='nc_sell_price']").val(sellPrice);
    $frm.find("input[name='nc_sale_price']").val(salePrice);
    $frm.find("input[name='nc_after_price']").val(afterPrice);
    $frm.find("input[name='opt_price']").val(optPrice);

    return true;
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

        var afterDet = $.trim($(".nc_overview").text()); // 후공정 세부내역

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

/**
 * @brief 86*52일 때 미싱/오시
 */
var chkSizeDotImpWarning = function () {
    var prefix = getPrefix(prdtDvs);
    var size = $(prefix + "size > option:selected").text();

    if (size === "86*52") {
        var html = "<dd class=\"guide_wrap\">";
        html += "<p class=\"guide\">사이즈가 작아서 선 끝 2~3mm 후공정이 들어가지 않을 수 있습니다.</p>";
        html += "</dd>";
        $("._dotline > dl").append(html);
        $("._impression > dl").append(html);
    }
};

/*
 * @brief 옵셋카드명함 종이에따라 인쇄도수 변경(montvert:171106)
 */
var showHidePrintTmpt = function () {
    var prefix = getPrefix(prdtDvs);
    var selector = $(prefix + "paper option:selected").text();

    var keyword = "누드";
    var delimiter = selector.indexOf(keyword);

    if (delimiter > -1) {
        $(prefix + "print_tmpt > option").each(function () {
            var tmpt = $(this).text();

            if (tmpt.indexOf("4도") > -1) {
                $(this).prop("selected", true);
            } else {
                $(this).hide();
                $(this).prop("selected", false);
            }
        });
    } else {
        $(prefix + "print_tmpt > option").show();
    }

    changeTmpt('nc');
};

/**
 * @brief 고급명함 종이에따라 엠보/박 show/hide
 */
var showHideAfterProcess = function () {
    var prefix = getPrefix(prdtDvs);
    var paper = $(prefix + "paper option:selected").text()
        .split(' ')[0];

    if(paper == "스타드림")
        paper = paper + " " + $(prefix + "paper option:selected").text().split(' ')[1];
    if ("유포지|샤인스페셜".indexOf(paper) > -1) {
        if ($(prefix + "embossing").prop("checked")) {
            $(prefix + "embossing").trigger("click");
        }
        $("#after").find("li._embossing").hide();
        $("#after").find("li._foil").show();
        //$("#after").find("li._foil").hide();
    } else if ("랑데뷰|뉴크라프트보드|팝셋|그레이스|스타드림 실버|퓨리".indexOf(paper) > -1) {
        //aftRestrict.unchecked(prdtDvs, "embossing");
        //aftRestrict.unchecked(prdtDvs, "foil");

        if ($(prefix + "foil").prop("checked")) {
            $(prefix + "foil").trigger("click");
        }
        if ($(prefix + "embossing").prop("checked")) {
            $(prefix + "embossing").trigger("click");
        }
        $("#after").find("li._embossing").hide();
        $("#after").find("li._foil").hide();
    } else if(paper == "코팅(무광코팅)" || paper == "무코팅") {
        var p = $(prefix + "paper option:selected").text();
        if(p.indexOf("무광코팅") > -1) {
            //aftRestrict.unchecked(prdtDvs, "embossing");
            if ($(prefix + "embossing").prop("checked")) {
                $(prefix + "embossing").trigger("click");
            }
            $("#after").find("li._embossing").hide();
        } else {
            $("#after").find("li._embossing").show();
        }
    } else if("Extra".indexOf(paper) > -1) {
        //var p = $(prefix + "paper option:selected").text();
        $("#after").find("li._edgefoil").show();
    }
    else {
        if ($(prefix + "edgefoil").prop("checked")) {
            $(prefix + "edgefoil").trigger("click");
        }
        $("#after").find("li._embossing").show();
        $("#after").find("li._foil").show();
        $("#after").find("li._edgefoil").hide();
    }
};

/**
 * @brief 고급명함 종이에따라 당일판 show/hide
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

    if ("샤인스페셜|갤럭시로얄|스코틀랜드|유포지".indexOf(paper) > -1) {
        $dayBoard.hide();
        $chkBox.prop("disabled", true);
    } else {
        $chkBox.prop("disabled", false);
        $dayBoard.show();
    }
    //loadOptPrice.exec($chkBox, idx, prdtDvs);
};

/**
 * @brief 카드명함 용지에 따라서 백판관련 로직 수행
 */
var chkBackBoard = function () {
    var prefix = getPrefix(prdtDvs);
    var paper = $(prefix + "paper > option:selected").text();

    if (paper.indexOf("누드") > -1) {
        $("#back_pos_bef").prop("checked", true);
        $("#back_pos_bef").prop("disabled", false);

        $("#back_pos_both").prop("disabled", true);
        $("#back_pos_aft").prop("disabled", true);

        $(".back_size").show();
        $(".back_pos").show();
        $(".back_noti").hide();
    } else {
        $("#back_pos_both").prop("disabled", false);
        $("#back_pos_bef").prop("checked", true);
        $("#back_pos_aft").prop("disabled", false);
    }

    if (paper.indexOf("홀로그램") > -1 ||
        paper.indexOf("반누드플러스") > -1) {
        $("#back_wid_size").prop("readonly", false);
        $("#back_vert_size").prop("readonly", false);

        $(".back_size").show();
        $(".back_pos").show();
        $(".back_noti").hide();
    } else if (paper.indexOf("플러스") > -1) {
        $("#back_wid_size").val("82");
        $("#back_vert_size").val("50");
        $("#back_wid_size").prop("readonly", true);
        $("#back_vert_size").prop("readonly", true);

        $(".back_size").show();
        $(".back_pos").show();
        $(".back_noti").show();
    } else {
        $(".back_size").hide();
        $(".back_pos").hide();
        $(".back_noti").hide();
    }
};

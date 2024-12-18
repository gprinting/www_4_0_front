var tmptDvs = null;
var monoYn = null;
var prdtDvs = null;
var sortcode = null;
var cateName = null;
var amtUnit = null;

$(document).ready(function () {
    // 건수 초기화
    var order_count = $("#order_count").val();
    initCount(999, "count", order_count);

    monoYn = $("#gb_mono_yn").val();
    tmptDvs = $("#gb_tmpt_dvs").val();
    prdtDvs = $("#prdt_dvs").val();
    sortcode = $("#gb_cate_sortcode").val();
    cateName = $("#gb_cate_sortcode").find("option:selected").text();
    amtUnit = $("#gb_amt").attr("amt_unit");

    showUvDescriptor(prdtDvs);

    var order_cut_wid = $("#order_cut_wid").val();
    var order_cut_vert = $("#order_cut_vert").val();
    if(order_cut_wid != "" && order_cut_vert) {
        $("#gb_cut_wid_size").val(order_cut_wid);
        $("#gb_cut_vert_size").val(order_cut_vert);
    }
    changeAmt();

    var prefix = getPrefix(prdtDvs);
    loadPrdtPrice.price.amt_rate = $(prefix + "amt_sale_rate").val();
    loadPrdtPrice.price.amt_aplc = $(prefix + "amt_sale_aplc").val();

    loadReleaseExpect();
    loadDlvrPriceExpect();
});

/**
 * @brief 종이변경시 미리보기랑 재질느낌 변경
 *
 * @param dvs = 제품구분값
 * @param val = 종이 맵핑코드
 */
var changePaper = function (dvs, val) {
    loadPaperPreview(dvs);
    loadPaperDscr.exec(dvs, val)
};

/**
 * @brief 가격 구성요소 셀렉트박스 변경시 변경된 정보로 가격 검색
 */
var changeData = function () {
    monoYn = $("#gb_mono_yn").val();

    var data = {
        "dvs": prdtDvs,
        "mono_yn": monoYn,
        "cate_sortcode": sortcode,
        "amt": $("#gb_amt").val(),
        "size": $("#gb_size").val(),
        "tmpt_dvs": tmptDvs,
        "count": $("#count").val()
    };

    data.flag = $("#frm").find("input[name='flag']").val();
    data.paper = $("#gb_paper").val();
    data.bef_tmpt = $("#gb_print_tmpt").val();
    data.bef_add_print_mpcode = '';
    data.aft_print_mpcode = '';
    data.aft_add_print_mpcode = '';
    data.print_purp = $("#gb_print_purp").val();
    data.page_info = "2";

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

            console.log(result);

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
    // 180427 추가됨
    // 자리수
    var posNum = 1;
    if ($("#size_dvs").val() === "manu") {
        posNum = parseFloat($("#nc_manu_pos_num").val());
    }
    // 여기까지
    // 건수
    var count = parseInt($("#count").val());
    // 특별할인
    var amtRate = parseFloat(loadPrdtPrice.price.amt_rate);
    amtRate /= 100.0;
    var amtAplc = parseInt(loadPrdtPrice.price.amt_aplc);
    // 정상판매가
    var sellPrice = loadPrdtPrice.price.sell_price;
    if (checkBlank(sellPrice)) {
        /*
        changeData();
        return false;
        */
        sellPrice = parseInt($("#sell_price").attr("val").replace(',', ''));
        loadPrdtPrice.price.sell_price = sellPrice;
    }
    sellPrice = ceilVal(sellPrice);
    // 180427 새로 추가됨(posNum)
    sellPrice *= posNum;
    //sellPrice *= count;
    // 등급 할인율
    var gradeSale = parseFloat($("#gb_grade_sale_rate").val());
    gradeSale /= 100.0;
    // 회원 할인율
    var memberSale = parseFloat($("#gb_member_sale_rate").val());
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

    //180427 수정 (count 다 빠짐)
    // 견적서 후공정비 계산
    var after = sumAfterPrice;
    //var after = sumAfterPrice * count;
    // 견적서 후공정비 계산(세금 미포함)
    var esAfter = getSumAfterPrice(prdtDvs, "T");
    //var esAfter = getSumAfterPrice(prdtDvs, "T") * count;
    // 견적서 옵션비 계산(180427 수정)
    var opt = sumOptPrice;
    //var opt = sumOptPrice * count;

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

    // 정상판매가 변경(180427 수정)
    //$("#sell_price").attr("val", sellPrice);
    $("#sell_price").attr("val", (sellPrice));
    $("#sell_price strong").html((sellPrice).format());
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
};

/**
 * @param 인쇄방식에 해당하는 인쇄도수 검색
 *
 * @param val = 인쇄방식
 */
var loadPrintTmpt = function (dvs) {
    var callback = function (result) {
        $("#gb_print_tmpt").html(result.bef_tmpt);

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
    var amt = parseInt($("#gb_amt").val());
    var amtUnit = $("#gb_amt").attr("amt_unit");

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

    var paperName = $("#gb_paper").find("option:selected").text();
    var tmptName = $("#gb_print_tmpt").find("option:selected").text();
    var sizeName = $("#gb_size").find("option:selected").text();

    var sellPrice = $("#sell_price").attr("val");
    var salePrice = $("#sale_price").attr("val");
    var afterPrice = getSumAfterPrice(prdtDvs);
    var optPrice = getSumOptPrice();

    var ret = makeAfterInfo.all(prdtDvs);

    if (ret === false) {
        return false;
    }

    var orderDetail = cateName + " / " +
        paperName + " / " +
        sizeName + " / " +
        tmptName;

    $("#order_detail").val(orderDetail);

    setAddOptInfo();

    $frm = $("#frm");

    $frm.find("input[name='gb_cate_name']").val(cateName);
    $frm.find("input[name='gb_amt_unit']").val(amtUnit);
    $frm.find("input[name='gb_paper_name']").val(paperName);
    $frm.find("input[name='gb_bef_tmpt_name']").val(tmptName);
    $frm.find("input[name='gb_size_name']").val(sizeName);

    $frm.find("input[name='gb_sell_price']").val(sellPrice);
    $frm.find("input[name='gb_sale_price']").val(salePrice);
    $frm.find("input[name='gb_after_price']").val(afterPrice);
    $frm.find("input[name='opt_price']").val(optPrice);
    console.log(sellPrice);
    console.log(salePrice);

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

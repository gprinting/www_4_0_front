var prdtDvs = null;
var sortcode = null;
var cateName = null;

$(document).ready(function () {
    prdtDvs = $("#prdt_dvs").val();
    var prefix = getPrefix(prdtDvs);

    sortcode = $(prefix + "cate_sortcode").val();
    cateName = $(prefix + "cate_name").val();

    loadReleaseExpect();
    //loadDlvrPriceExpect();
});

/**
 * @brief 거치대 depth3 검색
 */
var loadRackDepth3 = function (idx, val) {
    var prefix = getPrefix(prdtDvs);
    var optPrefix = getPrefix("opt_" + idx);
    var url = "/ajax/product/load_ao_opt_depth.php";
    var data = {
        "cate_sortcode": $(prefix + "cate_sortcode").val(),
        "opt_name": "거치대",
        "depth1": $(optPrefix + "depth1").text(),
        "depth2": val,
        "flag": 'Y'
    };
    var callback = function (result) {
        console.log("pref : " + optPrefix);
        $(optPrefix + "val").html(result);
        $(optPrefix + "val").trigger("change");

        loadRackPrice(idx);
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 최대 최소사이즈 변경
 */
var changeRackMinMaxSize = function (idx, obj) {
    console.log("idx : " + idx);
    console.log(obj);
    var $option = $(obj).find("option:selected");

    var minWid = $option.attr("min_wid");
    var minVert = $option.attr("min_vert");
    var maxWid = $option.attr("max_wid");
    var maxVert = $option.attr("max_vert");

    var optPrefix = getPrefix("opt_" + idx);
    $(optPrefix + "min_wid").val(minWid);
    $(optPrefix + "min_vert").val(minVert);
    $(optPrefix + "max_wid").val(maxWid);
    $(optPrefix + "max_vert").val(maxVert);
};

/**
 * @brief 거치대 가격 계산
 */
var loadRackPrice = function (idx) {
    var optPrefix = getPrefix("opt") + idx;

    var url = "/ajax/product/load_ao_rack_price.php";
    var data = {
        "dvs": prdtDvs,
        "depth1": $(optPrefix + "_depth1").text(),
        "depth2": $(optPrefix + "_depth2").val(),
        "depth3": $(optPrefix + "_val > option:selected").text(),
        "amt": $(optPrefix + "_amt").val()
    };
    var callback = function (result) {
        result = result[prdtDvs];
        var box = result.box;
        var ea = result.ea;
        var price = result.price;

        $(optPrefix + "_box").html(box.format());
        $(optPrefix + "_ea").html(ea.format());
        $(optPrefix + "_price_dd").html(price.format());
        $(optPrefix).attr("price", price);

        setSummary();
        calcPrice();
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 주문내역 입력
 */
var setSummary = function () {
    var $summary = $(".summary").find("ul");

    var html = '';
    $(".selection").each(function (i) {
        var optPrefix = getPrefix("opt") + i;
        var price = parseInt($(optPrefix).attr("price"));

        if (price === 0) {
            return true;
        }

        var depth1 = $(optPrefix + "_depth1").text();
        var depth2 = $(optPrefix + "_depth2").val();
        var depth3 = $(optPrefix + "_val > option:selected").text();

        var amt = $(this).find("input[name='opt_amt']").val();
        var amtUnit = $(this).find(".amt_unit").text();

        html += "<li>";
        html += "<strong style=\"font-weight:bold\">" + depth1 + "</strong>&nbsp;";
        html += depth2;
        if (depth3 !== '-') {
            html += ' ' + depth3 + ", ";
        }
        html += ", ";
        html += amt + amtUnit;
        html += "</li>";
    });

    $summary.html(html);
};

/**
 * @brief 판매가격 처리
 */
var calcPrice = function () {
    // 거치대별 금액
    var innerPrice = 0;
    var outerPrice = 0;
    var miniPrice = 0;
    // 정상판매가
    var sumPrice = 0;
    $(".selection").each(function (i) {
        var optPrefix = getPrefix("opt") + i;
        var depth1 = $(optPrefix + "_depth1").text();
        var price = parseInt($(optPrefix).attr("price"));

        sumPrice += price;

        switch (depth1) {
            case "실내용거치대":
                innerPrice = price;
                break;
            case "실외용거치대":
                outerPrice = price;
                break;
            case "미니배너거치대":
                miniPrice = price;
                break;
        }
    });
    // 부가세
    var tax = Math.round(sumPrice / 11);
    // 공급가
    var supply = sumPrice - tax;
    // 할인금액
    var saleRate = parseFloat(0) / 100;
    var saleAplc = 0;
    var sale = ceilVal(sumPrice * saleRate) + saleAplc;
    var salePrice = sumPrice - sale;

    // 정상판매가
    $("#sell_price").attr("val", sumPrice);
    $("#sell_price").html(sumPrice.format() + " 원");
    // 공급가변경
    $("#supply_price").html(supply.format());
    // 부가세변경
    $("#tax").html(tax.format());
    // 결제금액
    $("#sale_price").attr("val", salePrice);
    $("#sale_price").html(salePrice.format());

    // 빠른견적서
    $("#esti_inner_rack").html(ceilVal(innerPrice / 1.1).format());
    $("#esti_outer_rack").html(ceilVal(outerPrice / 1.1).format());
    $("#esti_mini_rack").html(ceilVal(miniPrice / 1.1).format());
    $("#esti_supply").html(supply.format());
    $("#esti_tax").html(tax.format());
    $("#esti_sell_price").html(sumPrice.format());
    $("#esti_sale_price").html(sale.format());
    $("#esti_pay_price").html(salePrice.format());

    loadReleaseExpect();
    //loadDlvrPriceExpect();
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

    var opt_amt = 0;
    var i = 0;
    $(".input_count").each(function () {
        opt_amt += parseInt($("#opt_" + i + "_amt").val());
        i++;
    });

    if (checkBlank(opt_amt) || opt_amt == "0") {
        alert("거치대 수량을 선택해 주세요.");
        return false;
    }

    var sellPrice = $("#sell_price").attr("val");
    var salePrice = $("#sale_price").attr("val");
    var optPrice = 0;

    // 가격 0이면 checked 해제, 수량정보 생성
    $(".selection").each(function () {
        var $chk = $(this).find("input[name='chk_opt']");
        var price = parseInt($chk.attr("price"));

        if (price === 0) {
            $chk.prop("checked", false);
        } else {
            $chk.prop("checked", true);
        }

        var amt = $(this).find("input[name='opt_amt']").val();
        var amtUnit = $(this).find(".amt_unit").text();
        var info = amt.format() + amtUnit;
        if ($(this).find(".amt_detail").length > 0) {
            info += '(' + $(this).find(".amt_detail").text() + ')';
        }

        $(this).find("input[name='opt_info']").val(info);
        $(this).find("input[name='opt_data']").val(amt);

        optPrice += price;
    });

    setAddOptInfo();

    // 주문상세정보 생성
    var orderDetail = makeOrderDetail();

    $frm = $("#frm");
    $frm.find("input[name='" + prdtDvs + "_sell_price']").val(sellPrice);
    $frm.find("input[name='" + prdtDvs + "_sale_price']").val(salePrice);
    $frm.find("input[name='opt_price']").val(optPrice);
    $frm.find("input[name='" + prdtDvs + "_order_detail']").val(orderDetail);

    return true;
};

var makeOrderDetail = function () {
    var ret = '';
    $(".selection").each(function (i) {
        var optPrefix = getPrefix("opt") + i;
        var price = parseInt($(optPrefix).attr("price"));

        if (price === 0) {
            return true;
        }

        var depth1 = $(optPrefix + "_depth1").text();
        var depth2 = $(optPrefix + "_depth2").val();
        var depth3 = $(optPrefix + "_val > option:selected").text();

        var amt = $(this).find("input[name='opt_amt']").val();
        var amtUnit = $(this).find(".amt_unit").text();

        ret += depth2;
        if (depth3 !== '-') {
            ret += ' ' + depth3 + ", ";
        }
        ret += ", ";
        ret += amt + amtUnit;
        ret += " / ";
    });

    ret = ret.substr(0, ret.length - 2);

    return ret;
};
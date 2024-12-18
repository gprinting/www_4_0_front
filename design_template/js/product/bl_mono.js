var monoYn = null;
var prdtDvs = null;
var affil = null;
var sortcode = null;
var amtUnit = null;
var cateName = null;
// 일반옵셋 존재하는지 체크
var printPurpChk = false;
var defaultPurp = null;

$(document).ready(function () {
    // 건수 초기화
    var order_count = $("#order_count").val();
    initCount(999, "count", order_count);

    monoYn = $("#bl_mono_yn").val();
    prdtDvs = $("#prdt_dvs").val();
    affil = $("#bl_size").find("option:selected").attr("affil");
    sortcode = $("#bl_cate_sortcode").val();
    cateName = $("#bl_cate_sortcode").find("option:selected").text();
    amtUnit = $("#bl_amt").attr("amt_unit");

    $("#r_count_div").show();
    calcRCount(prdtDvs);

    var prefix = getPrefix(prdtDvs);
    if (sortcode === "003003002") {
        $(".option._folding > ._closed").trigger("click");

        $(prefix + "print_purp > option").each(function () {
            if ($(this).prop("selected")) {
                defaultPurp = $(this).val();
            }

            if ($(this).val() === "일반옵셋") {
                printPurpChk = true;
            }
        });

        chkPrintPurp(prdtDvs);
    }
    calcLaminexMaxCount();
    $(prefix + "foldline_info").val(80);
    showUvDescriptor(prdtDvs);

    // 당일판 기본 체크해제
    $("label:contains('당일판')").children().prop("checked", false);

    aftShowHideByPaper(prdtDvs);

    //reCalcAfterPrice(prdtDvs, null);
    changeData();
    loadReleaseExpect();
    loadDlvrPriceExpect();
});

// 종이 평량에 따른 후가공 show/hide
var aftShowHideByPaper = function (dvs) {
    var prefix = getPrefix(dvs);
    var basisweight = parseInt($(prefix + "paper > option:selected").text().split(' ').pop());

    if (basisweight < 200) {
        aftRestrict.unchecked(dvs, "thomson_impression");
        $('.' + dvs + "_after_list li._thomson_impression").hide();
        $('.' + dvs + "_after_list li._impression").show();
        $('.' + dvs + "_after_list li.foldline").show();
    } else {
        aftRestrict.unchecked(dvs, "impression");
        $('.' + dvs + "_after_list li._thomson_impression").show();
        $('.' + dvs + "_after_list li._impression").hide();

        if (basisweight >= 300) {
            aftRestrict.unchecked(dvs, "foldline");
            $('.' + dvs + "_after_list li.foldline").hide();
            return false;
        }

        $('.' + dvs + "_after_list li.foldline").show();
    }
}

/**
 * @brief 계산형일 때 사이즈 선택할 경우 사이즈 계열에 맞는 도수값 검색
 *
 * @param val = 구분값
 */
var changeSize = {
    "exec": function (dvs) {
        var prefix = getPrefix(dvs);
        var selectAffil = $(prefix + "size > option:selected").attr("affil");

        if (affil === selectAffil) {
            loadPrdtAmt(dvs);
            return false;
        }

        var callback = function (result) {
            $(prefix + "bef_tmpt").html(result.bef_tmpt);
            $(prefix + "aft_tmpt").html(result.aft_tmpt);
            affil = selectAffil;
            loadPrdtAmt(dvs);
        };

        loadPrintTmptCommon.exec(dvs, callback);
    }
};

/**
 * @brief 바뀐 종이와 사이즈로 수량 변경
 *
 * @param dvs = 제품구분값
 * @param val = 종이맵핑코드
 */
var loadPrdtAmt = function (dvs) {
    var prefix = getPrefix(dvs);
    var size = $(prefix + "size > option:selected").text();
    var old_amt = $(prefix + "amt > option:selected").val();
    console.log($(prefix + "size > option:selected"));

    var url = "/ajax/product/load_amt.php";
    var data = {
        "cate_sortcode": sortcode,
        "amt_unit": amtUnit,
        "size_name": size
    };
    var callback = function (result) {
        $(prefix + "amt").html(result);

        if($(prefix + "amt option[value='" + old_amt + "']").length >0) {
            $(prefix + "amt").val(old_amt);
        }
        calcRCount(dvs);

        var aftInfoArr = getAftInfoArr(dvs);
        loadAfterMpcode(dvs, aftInfoArr, size);

        if (isFunc("rangeBarBySelect")) {
            rangeBarText();
        }
        changeData();
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 인쇄방식 체크
 *
 * @param dvs = 제품구분값
 */
var chkPrintPurp = function (dvs, callback) {
    var prefix = getPrefix(dvs);
    var name = $(prefix + "paper_name").val();

    if (name === "랑데뷰" && printPurpChk) {
        var html = "<option value=\"일반옵셋\""
        if (defaultPurp === "일반옵셋") {
            html += " selected =\"selected\" ";
        }
        html += ">일반옵셋</option>";
        html += "<option value=\"UV특수옵셋\"";
        if (defaultPurp === "UV특수옵셋") {
            html += " selected =\"selected\" ";
        }
        html += ">UV특수옵셋</option>";
    } else if (printPurpChk) {
        var html = "<option value=\"UV특수옵셋\">UV특수옵셋</option>";
    }

    $(prefix + "print_purp").html(html);

    if (checkBlank(callback)) {
        callback = function (result) {
            $("#bl_bef_tmpt").html(result.bef_tmpt);
            $("#bl_aft_tmpt").html(result.aft_tmpt);
        };
    }

    loadPrintTmpt(dvs, callback);
};

/**
 * @brief 종이명 변경시 인쇄방식 체크하고 종이정보 검색
 */
var changePaperName = function (dvs, val) {
    var callback = function (result) {
        $("#bl_bef_tmpt").html(result.bef_tmpt);
        $("#bl_aft_tmpt").html(result.aft_tmpt);

        loadPaperInfo(dvs, val);
    };

    loadPaperPreview(dvs);
    showHideDayBoard();
    chkPrintPurp(dvs, callback);
};

/**
 * @param 종이변경시 후공정 제약사항 체크
 *
 * @param dvs = 제품구분값
 * @param val = 종이 맵핑코드
 */
var changePaper = function (dvs, val) {
    aftShowHideByPaper(dvs);
    loadPaperPreview(dvs);
    calcLaminexMaxCount();
    reCalcAfterPrice(dvs, null);
    //changeData();
};

/**
 * @brief 독판전단 종이에따라 당일판 show/hide
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

    if ("아트지|스노우지".indexOf(paper) > -1) {
        $chkBox.prop("disabled", false);
        $dayBoard.show();
    } else {
        $dayBoard.hide();
        $chkBox.prop("disabled", true);
    }
    //loadOptPrice.exec(chkBox, idx, prdtDvs);
};

/**
 * @brief 가격 구성요소 셀렉트박스 변경시 변경된 정보로 가격 검색
 */
var changeData = function () {
    monoYn = $("#bl_mono_yn").val();

    var data = {
        "dvs": prdtDvs,
        "cate_sortcode": sortcode,
        "amt": $("#bl_amt").val(),
        "count": $("#count").val(),
        "size": $("#bl_size").val(),
        "affil": affil
    };

    data.paper = $("#bl_paper").val();
    data.bef_tmpt = $("#bl_bef_tmpt").val();
    data.bef_add_print_mpcode = '';
    data.aft_tmpt = $("#bl_aft_tmpt").val();
    data.aft_add_print_mpcode = '';
    data.bef_print_name = $("#bl_bef_tmpt > option:selected").text();
    data.bef_add_print_name = '';
    data.aft_print_name = $("#bl_aft_tmpt > option:selected").text();
    data.aft_add_print_name = '';
    data.print_purp = $("#bl_print_purp").val();
    data.page = "2";
    data.flattyp_yn = "Y";
    data.amt_unit = amtUnit;
    data.pos_num = $("#bl_size > option:selected").attr("pos_num");

    var i = 0;
    $("input[type='checkbox'][name='bl_chk_after[]']").each(function () {
        if ($(this).prop("checked") === false) {
            return true;
        }

        var aft = $(this).attr("aft");
        var aft_depth = $("#bl_" + aft).val() == undefined ? "" : $("#bl_" + aft).val();
        var aft_depth1 = $("#bl_" + aft + "_1").val() == undefined ? "" : $("#bl_" + aft + "_1").val();
        var aft_depth2 = $("#bl_" + aft + "_2").val() == undefined ? "" : $("#bl_" + aft + "_2").val();
        var aft_depth1_val = $("#bl_" + aft + "_val").val() == undefined ? "" : $("#bl_" + aft + "_val").val();
        var aft_depth1_vh = $("#bl_" + aft + "_vh").val() == undefined ? "" : $("#bl_" + aft + "_vh").val();
        var aft_depth1_cnt = $("#bl_" + aft + "_cnt").val() == undefined ? "" : $("#bl_" + aft + "_cnt").val();
        var aft_depth1_dvs = $("#bl_" + aft + "_dvs_1").val() == undefined ? "" : $("#bl_" + aft + "_dvs_1").val();
        var aft_depth2_dvs = $("#bl_" + aft + "_dvs_2").val() == undefined ? "" : $("#bl_" + aft + "_dvs_2").val();
        var aft_depth1_wid = $("#bl_" + aft + "_wid_1").val() == undefined ? "" : $("#bl_" + aft + "_wid_1").val();
        var aft_depth1_vert = $("#bl_" + aft + "_vert_1").val() == undefined ? "" : $("#bl_" + aft + "_vert_1").val();
        var aft_depth2_wid = $("#bl_" + aft + "_wid_2").val() == undefined ? "" : $("#bl_" + aft + "_wid_2").val();
        var aft_depth2_vert = $("#bl_" + aft + "_vert_2").val() == undefined ? "" : $("#bl_" + aft + "_vert_2").val();
        var aft_depth1_amt = $("#bl_" + aft + "_amt").val() == undefined ? "" : $("#bl_" + aft + "_amt").val();
        var selector = "input[name='" +
            'bl' + '_' + aft + '_' +
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
            data.aft_depth1_amt += "|";
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
            data.aft_depth1_amt = "";
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
        data.aft_depth1_amt += aft_depth1_amt;
        if (aft == "foil") {
            data.aft_depth = $("#bl_" + aft + "_depth").val();
            data.after_name = $("#bl_" + aft + "_name").val();
        }
        i++;
    });


    var selectAffil = $("#bl_size").find("option:selected").attr("affil");
    $("#affil").val(selectAffil);
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

            $(prefix + "paper_price").val(result[prdtDvs].paper);
            $(prefix + "output_price").val(result[prdtDvs].output);
            $(prefix + "print_price").val(result[prdtDvs].print);

            $("input[name='bl_chk_after[]']:checked").each(function () {
                var $obj = $(this);
                var aft = $obj.attr('id').replace("bl_", "");

              if (aft == "foil") {
                    afterPrice = loadPrdtPrice.price["foil1"];
                    if (afterPrice) {
                        setAfterPrice("bl", "foil1", afterPrice);
		    }
                    afterPrice = loadPrdtPrice.price["foil2"];
                    if (afterPrice) {
                        setAfterPrice("bl", "foil2", afterPrice);
		    }
                    afterPrice = loadPrdtPrice.price["foil3"];
                    if (afterPrice) {
                        setAfterPrice("bl", "foil3", afterPrice);
		    }
		} else {
                    afterPrice = loadPrdtPrice.price[aft];
                    setAfterPrice("bl", aft, afterPrice);
		}
            });

            afterOverview("bl");       
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
    // 정상판매가
    var sellPrice = loadPrdtPrice.price.sell_price;
    if (checkBlank(sellPrice)) {
        changeData();
        return false;
    }
    sellPrice = ceilVal(sellPrice);
    //sellPrice *= count;
    // 등급 할인율
    var gradeSale = parseFloat($("#bl_grade_sale_rate").val());
    gradeSale /= 100.0;
    // 회원 할인율
    var memberSale = parseFloat($("#bl_member_sale_rate").val());
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
 * @param 인쇄방식에 해당하는 인쇄도수 검색
 *
 * @param val = 인쇄방식
 * @param callback = 콜백함수,
 * 셀렉트박스 변경 외적으로 처리되는 부분 때문에 추가
 */
var loadPrintTmpt = function (dvs, callback) {
    if (checkBlank(callback)) {
        callback = function (result) {
            $("#bl_bef_tmpt").html(result.bef_tmpt);
            $("#bl_aft_tmpt").html(result.aft_tmpt);

            changeData();
        };
    }

    loadPrintTmptCommon.exec(dvs, callback);
};

/**
 * @brief 비규격 사이즈 선택할 경우 재단사이즈 값 초기화
 *
 * @param val  = 구분값
 */
var changeSizeDvs = function (val) {
    var prefix = getPrefix(prdtDvs);
    $(prefix + "similar_size").attr("divide", '1');

    // 비규격 사이즈 선택시 기본 사이즈로 데이터 변경
    if (val === "manu") {
        var str = $(prefix + "size > option:selected").text() + " 1/1 등분";

        $(prefix + "similar_size").show();
        $(prefix + "similar_size").html(str);
        if ($("#im").val() === "1") {
            $("#bl_size").hide();
        }
    } else {
        $(prefix + "similar_size").hide();
        calcRCount(prdtDvs);
        if ($("#im").val() === "1") {
            $("#bl_size").show();
        }
    }

    changeData();
};

/**
 * @brief 수량변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeAmt = function () {
    calcRCount(prdtDvs);
    reCalcAfterPrice(prdtDvs, null);
    changeData();
    calcLaminexMaxCount();
};

/**
 * @brief 건수변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeCount = function () {
    reCalcAfterPrice(prdtDvs, null);
    calcPrice();
}

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

    var amtUnit = $("#bl_amt").attr("amt_unit");
    var paperName = $("#bl_paper").find("option:selected").text();
    var befTmpt = $("#bl_bef_tmpt").find("option:selected").text();
    var aftTmpt = $("#bl_aft_tmpt").find("option:selected").text();
    var sizeName = $("#bl_size").find("option:selected").text();

    var sellPrice = $("#sell_price").attr("val");
    var salePrice = $("#sale_price").attr("val");
    var afterPrice = getSumAfterPrice(prdtDvs);
    var optPrice = getSumOptPrice();

    var ret = makeAfterInfo.all(prdtDvs);

    if (ret === false) {
        return false;
    }

    setAddOptInfo();

    $frm = $("#frm");

    $frm.find("input[name='bl_cate_name']").val(cateName);
    $frm.find("input[name='bl_amt_unit']").val(amtUnit);
    $frm.find("input[name='bl_paper_name']").val(paperName);
    $frm.find("input[name='bl_bef_tmpt_name']").val(befTmpt);
    $frm.find("input[name='bl_aft_tmpt_name']").val(aftTmpt);
    $frm.find("input[name='bl_size_name']").val(sizeName);

    $frm.find("input[name='bl_sell_price']").val(sellPrice);
    $frm.find("input[name='bl_sale_price']").val(salePrice);
    $frm.find("input[name='bl_after_price']").val(afterPrice);
    $frm.find("input[name='bl_opt_price']").val(optPrice);

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
        var befTmpt = $.trim($(prefix + "bef_tmpt > option:selected").text());
        var aftTmpt = $.trim($(prefix + "aft_tmpt > option:selected").text());
        var amt = $.trim($(prefix + "amt").val());
        var count = $.trim($("#esti_count").text());

        var afterDet = $.trim($(".bl_overview").text()); // 후공정 세부내역

        if ($(prefix + "paper_name").length > 0) {
            paper = $(prefix + "paper_name").val() + paper;
        }

        var tmpt = "전면 : " + befTmpt + " / 후면 : " + aftTmpt;

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

var monoYn = null;
var prdtDvs = null;
var affil = null;
var sortcode = null;
var amtUnit = null;
var cateName = null;

$(document).ready(function () {
    // 건수 초기화
    var order_count = $("#order_count").val();
    initCount(999, "count", order_count);

    monoYn = $("#ad_mono_yn").val();
    prdtDvs = $("#prdt_dvs").val();
    affil = $("#ad_size").find("option:selected").attr("affil");
    sortcode = $("#ad_cate_sortcode").val();
    cateName = $("#ad_cate_sortcode").find("option:selected").text();
    amtUnit = $("#ad_amt").attr("amt_unit");

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

    var prefix = getPrefix(prdtDvs);

    showUvDescriptor(prdtDvs);

    if (amtUnit === 'R') {
        $("#sheet_count_div").show();
        calcSheetCount(prdtDvs);
    } else {
        $("#r_count_div").show();
        calcRCount(prdtDvs);
    }

    // 홀더
    if (sortcode === "001004001") {
        var stanName = $(prefix + "size > option:selected").text();
        stanName = stanName.split('[')[0];
        changePreviewImg(stanName);

        if (typeof preview !== "undefined") {
            preview.cuttingSize.remove();
            preview.workingSize.remove();
            preview.btns.find('.cuttingLine').trigger("click");
            preview.btns.find('.workingLine').trigger("click");
            preview.btns.remove();
        }

        aftRestrict.checked(prdtDvs, "bonding");
        $("li._bonding").show();

        $(prefix + "bonding").prop("disabled", true);
    } else {
        impressShowHide(prdtDvs);
    }

    loadPrdtPrice.price.amt_rate = $(prefix + "amt_sale_rate").val();
    loadPrdtPrice.price.amt_aplc = $(prefix + "amt_sale_aplc").val();

    changeData();
    loadReleaseExpect();
    loadDlvrPriceExpect();

});

/**
 * @brief 종이 바뀔 때 평량에 따라 규격 변경
 *
 * @param dvs       = 제품구분값
 * @param val       = 종이맵핑코드
 * @param sizeTypYn = 사이즈 타입명 노출 여부
 */
var changePaper = function (dvs, val, sizeTypYn) {
    var prefix = getPrefix(dvs);
    var $obj = $("#ad_size > option:selected");
    var affil = $obj.attr("affil");
    var posNum = $obj.attr("pos_num");

    var url = "/ajax/product/load_paper_size.php";
    var data = {
        "cate_sortcode": sortcode,
        "mono_yn": $(prefix + "mono_yn").val(),
        "affil_yn": affil,
        "pos_yn": posNum,
        "size_typ_yn": sizeTypYn,
        "paper_mpcode": val
    };
    var callback = function (result) {
        $(prefix + "size").html(result);

        if (isFunc("size")) {
            size();
        }

        if(sortcode != '001002001')
            loadPrdtAmt(dvs);
    };

    loadPaperPreview(dvs);

    if (sortcode !== "001004001") {
        impressShowHide(dvs);
    }

    ajaxCall(url, "html", data, callback);
};

// 오시, 도무송오시 show/hide
var impressShowHide = function (dvs) {
    var prefix = getPrefix(dvs);
    var basisweight = parseInt($(prefix + "paper > option:selected").text().split(' ').pop());

    if (basisweight < 250) {
        $(prefix + "thomson_impression").prop("disabled", false);
        $(prefix + "coating").prop("disabled", false);

        aftRestrict.unchecked(dvs, "thomson_impression");
        aftRestrict.unchecked(dvs, "coating");
        $("li._thomson_impression").hide();
        $("li._impression").show();
    } else {
        aftRestrict.unchecked(dvs, "impression");
        $("li._thomson_impression").show();
        $("li._impression").hide();

        aftRestrict.checked(dvs, "thomson_impression");
        //aftRestrict.checked(dvs, "coating");

        $(prefix + "thomson_impression").prop("disabled", true);
        //$(prefix + "coating").prop("disabled", true);
    }
}

// ajax submit 후 처리함수
var submitNextFunc = function () {
    var prefix = getPrefix(prdtDvs);
    $(prefix + "bonding").prop("disabled", true);
    if (sortcode !== "001004001") {
        impressShowHide(dvs);
    }
};

/**
 * @brief 바뀐 종이와 사이즈로 수량 변경
 *
 * @param dvs = 제품구분값
 */
var loadPrdtAmt = function (dvs) {
    var prefix = getPrefix(dvs);

    var url = "/ajax/product/load_amt.php";
    var data = {
        "cate_sortcode": sortcode,
        "mono_yn": $(prefix + "mono_yn").val(),
        "amt_unit": amtUnit,
        "stan_mpcode": $(prefix + "size").val(),
        "paper_mpcode": $(prefix + "paper").val()
    };
    var callback = function (result) {
        $(prefix + "amt").html(result);

        if (amtUnit === 'R') {
            calcSheetCount(dvs);
        } else {
            calcRCount(dvs);
        }

        var aftInfoArr = null;
        if (sortcode === "001002001") {
            if ($(prefix + "impression").prop("checked")) {
                aftInfoArr = [{
                    "name": "오시",
                    "depth1": "-"
                }];
            } else if ($(prefix + "thomson_impression").prop("checked")) {
                aftInfoArr = [{
                    "name": "도무송오시",
                    "depth1": "-"
                }];
            }
        } else {
            aftInfoArr = getAftInfoArr(dvs);
        }
        var size = $(prefix + "size > option:selected").text();
        loadAfterMpcode(dvs, aftInfoArr, size);

        if (isFunc("rangeBarBySelect")) {
            rangeBarBySelect();
        }
        changeData();
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 가격 구성요소 셀렉트박스 변경시 변경된 정보로 가격 검색
 */
var changeData = function () {
    monoYn = $("#ad_mono_yn").val();

    var data = {
        "dvs": prdtDvs,
        "mono_yn": monoYn,
        "cate_sortcode": sortcode,
        "amt": $("#ad_amt").val(),
        "count": $("#count").val(),
        "size": $("#ad_size").val()
    };

    data.paper = $("#ad_paper").val();
    data.bef_tmpt = $("#ad_print_tmpt").val();
    data.bef_add_print_mpcode = '';
    data.aft_print_mpcode = '';
    data.aft_add_print_mpcode = '';
    data.print_purp = $("#ad_print_purp").val();
    data.page_info = "2";

    var i = 0;
    $("input[type='checkbox'][name='ad_chk_after[]']").each(function () {
        if ($(this).prop("checked") === false) {
            return true;
        }

        var aft = $(this).attr("aft");
        var aft_depth = $("#ad_" + aft).val() == undefined ? "" : $("#ad_" + aft).val();
        var aft_depth1 = $("#ad_" + aft + "_1").val() == undefined ? "" : $("#ad_" + aft + "_1").val();
        var aft_depth2 = $("#ad_" + aft + "_2").val() == undefined ? "" : $("#ad_" + aft + "_2").val();
        var aft_depth1_val = $("#ad_" + aft + "_val").val() == undefined ? "" : $("#ad_" + aft + "_val").val();
        var aft_depth1_vh = $("#ad_" + aft + "_vh").val() == undefined ? "" : $("#ad_" + aft + "_vh").val();
        var aft_depth1_cnt = $("#ad_" + aft + "_cnt").val() == undefined ? "" : $("#ad_" + aft + "_cnt").val();
        var aft_depth1_dvs = $("#ad_" + aft + "_dvs_1").val() == undefined ? "" : $("#ad_" + aft + "_dvs_1").val();
        var aft_depth2_dvs = $("#ad_" + aft + "_dvs_2").val() == undefined ? "" : $("#ad_" + aft + "_dvs_2").val();
        var aft_depth1_wid = $("#ad_" + aft + "_wid_1").val() == undefined ? "" : $("#ad_" + aft + "_wid_1").val();
        var aft_depth1_vert = $("#ad_" + aft + "_vert_1").val() == undefined ? "" : $("#ad_" + aft + "_vert_1").val();
        var aft_depth2_wid = $("#ad_" + aft + "_wid_2").val() == undefined ? "" : $("#ad_" + aft + "_wid_2").val();
        var aft_depth2_vert = $("#ad_" + aft + "_vert_2").val() == undefined ? "" : $("#ad_" + aft + "_vert_2").val();
        var selector = "input[name='" +
            'ad' + '_' + aft + '_' +
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
        if (aft == "foil") {
            data.aft_depth = $("#ad_" + aft + "_depth").val();
            data.after_name = $("#ad_" + aft + "_name").val();
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
        //if (monoYn === '0') {
        url = "/ajax/product/load_price.php";
        /*} else {
            url = "/ajax/product/load_calc_price.php";
        }*/
        var callback = function (result) {
            if (checkBlank(result[prdtDvs].sell_price) === true) {
                return alertReturnFalse("해당하는 가격이 존재하지 않습니다.\n관리자에게 문의하세요.");
            }

            loadPrdtPrice.price = result[prdtDvs];

            var prefix = getPrefix(prdtDvs);
            $(prefix + "prdt_price").val(result[prdtDvs].sell_price);

            $("input[name='ad_chk_after[]']:checked").each(function () {
                var $obj = $(this);
                var aft = $obj.attr('id').replace("ad_", "");

                if (aft == "foil") {
                    afterPrice = loadPrdtPrice.price["foil1"];
                    if (afterPrice) {
                        setAfterPrice("ad", "foil1", afterPrice);
		    }
                    afterPrice = loadPrdtPrice.price["foil2"];
                    if (afterPrice) {
                        setAfterPrice("ad", "foil2", afterPrice);
		    }
                    afterPrice = loadPrdtPrice.price["foil3"];
                    if (afterPrice) {
                        setAfterPrice("ad", "foil3", afterPrice);
		    }
		} else {
                    afterPrice = loadPrdtPrice.price[aft];
                    setAfterPrice("ad", aft, afterPrice);
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
    // 정상판매가
    var sellPrice = loadPrdtPrice.price.sell_price;
    if (checkBlank(sellPrice)) {
        changeData();
        return false;
    }
    sellPrice = ceilVal(sellPrice);
    //sellPrice *= count;
    // 등급 할인율
    var gradeSale = parseFloat($("#ad_grade_sale_rate").val());
    gradeSale /= 100.0;
    // 회원 할인율
    var memberSale = parseFloat($("#ad_member_sale_rate").val());
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
        $(".esti_paper_info").css("display", "");
    } else {
        $(".esti_paper_info").css("display", "none");
    }

    // 견적서 출력비 계산
    var output = 0;
    if (monoYn === '1') {
        output = parseInt(loadPrdtPrice.price.output);
        output = ceilVal(output);
        output *= count;
        $(".esti_output_info").css("display", "");
    } else {
        $(".esti_output_info").css("display", "none");
    }

    // 견적서 출력비 계산
    var cut = 0;
    if (monoYn === '1') {
        cut = parseInt(loadPrdtPrice.price.cut);
        cut = ceilVal(cut);
        cut *= count;
        $(".esti_cut_info").css("display", "");
    } else {
        $(".esti_cut_info").css("display", "none");
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
    var calcPayPrice = calcSalePrice + opt;
    // 부가세
    var tax = ceilVal(calcPayPrice / 11);
    // 공급가
    var supplyPrice = calcPayPrice - tax;

    // 정상판매가 변경
    $("#sell_price").attr("val", (sellPrice + opt));
    $("#sell_price").html((sellPrice + opt).format() + ' 원');
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
        "cut": cut,
        "output": output,
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
 */
var loadPrintTmpt = function (dvs) {
    var callback = function (result) {
        $("#ad_print_tmpt").html(result.bef_tmpt);

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
    var prefix = getPrefix(prdtDvs);

    // 비규격 사이즈 선택시 기본 사이즈로 데이터 변경
    if (val === "manu") {
        var str = $(prefix + "size > option:selected").text() + " 1/1";

        $(prefix + "similar_size").show();
        $(prefix + "similar_size").html(str);
        if ($("#im").val() == "1") {
            $("#ad_size").hide();
        }
    } else {
        $(prefix + "similar_size").hide();
        if ($("#im").val() == "1") {
            $("#ad_size").show();
        }
    }

    changeData();
};

/**
 * @brief 계산형일 때 사이즈 선택할 경우 사이즈 계열에 맞는 도수값 검색
 *
 * @param val = 구분값
 */
var changeSize = {
    "exec": function (dvs) {
        loadPrdtAmt(dvs);
    }
};

/**
 * @brief 계산형일 때 사이즈 선택할 경우 사이즈 계열에 맞는 도수값 검색
 *
 * @param val = 구분값
 */
var changeSizeTyp = function () {
    var prefix = getPrefix(prdtDvs);
    var stanName = $(prefix + "size > option:selected").text();
    changePreviewImg(stanName.split('[')[0]);

    if (stanName.indexOf("HD-007") > -1) {
        // 끼움식이라 접착 안씀
        $("li._bonding").hide();
        $(prefix + "bonding").prop("disabled", false);
        aftRestrict.unchecked(prdtDvs, "bonding");
    } else {
        aftRestrict.checked(prdtDvs, "bonding");
        $(prefix + "bonding").prop("disabled", true);
        $("li._bonding").show();
    }

    var aftInfoArr = [{
        "name": "전체빼다",
        "depth1": "있음",
        "name": "접착",
        "depth1": "있음"
    }];
    loadAfterMpcode(prdtDvs, aftInfoArr, stanName.split('(')[0]);
    reCalcAfterPrice(prdtDvs, null);

    //changeSize.exec(prdtDvs);
    changeData();
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

    var ret = makeAfterInfo.all(prdtDvs);

    if (ret === false) {
        return false;
    }

    setAddOptInfo();

    if ($(prefix + "paper_name").length > 0) {
        paperName = $(prefix + "paper_name").val() + ' ' + paperName;
    }

    $(prefix + "bonding").prop("disabled", false);

    $(prefix + "thomson_impression").prop("disabled", false);
    $(prefix + "coating").prop("disabled", false);

    $frm = $("#frm");

    $frm.find("input[name='ad_cate_name']").val(cateName);
    $frm.find("input[name='ad_amt_unit']").val(amtUnit);
    $frm.find("input[name='ad_paper_name']").val(paperName);
    $frm.find("input[name='ad_bef_tmpt_name']").val(tmptName);
    $frm.find("input[name='ad_size_name']").val(sizeName);

    $frm.find("input[name='ad_sell_price']").val(sellPrice);
    $frm.find("input[name='ad_sale_price']").val(salePrice);
    $frm.find("input[name='ad_after_price']").val(afterPrice);
    $frm.find("input[name='ad_opt_price']").val(optPrice);

    return true;
};

/**
 * @brief 건수변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeCount = function () {
    reCalcAfterPrice(prdtDvs, null);
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

        if ($(prefix + "paper_name").length > 0) {
            paper = $(prefix + "paper_name").val() + ' ' + paper;
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

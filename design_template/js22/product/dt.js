var monoYn = null;
var prdtDvs = null;
var sortcode = null;
var cateName = null;
var amtUnit = null;

$(document).ready(function () {
    // 건수 초기화
    var order_count = $("#order_count").val();
    initCount(999, "count", order_count);

    monoYn = $("#dt_mono_yn").val();
    prdtDvs = $("#prdt_dvs").val();
    sortcode = $("#dt_cate_sortcode").val();
    cateName = $("#dt_cate_sortcode").find("option:selected").text();
    amtUnit = $("#dt_amt").attr("amt_unit");
    var im = $("#im").val();

    calcManuPosNum.defWid = parseFloat($("#dt_size").attr("def_cut_wid"));
    calcManuPosNum.defVert = parseFloat($("#dt_size").attr("def_cut_vert"));

    var order_cut_wid = $("#order_cut_wid").val();
    var order_cut_vert = $("#order_cut_vert").val();
    var order_stan_name = $("#order_stan_nmae").val();

    $("#dt_size_name").val(order_stan_name);
    if(order_cut_wid != "" && order_cut_vert != "") {
        $("#dt_cut_wid_size").val(order_cut_wid);
        $("#dt_cut_vert_size").val(order_cut_vert);

        if(order_stan_name == "manu") {
            $("#size_dvs").val("manu");
            changeSizeDvs.exec("manu");
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

    aftShowHideByPaper(prdtDvs);

    chkBackBoard();

    //loadReleaseExpect();
    loadDlvrPriceExpect();

    changeDigitalCuttingSize();
    reCalcAfterPrice(prdtDvs, null);

});

window.onload  = function() {
    var dt_sortcode = $("#dt_sortcode").val();

    if(dt_sortcode == "011006002"){ //소량 리플렛 , 팜플렛 기본 옵션 
        $("input:checkbox[id='opt_0']").trigger("click");

    }
}

/**
 * @brief 종이명 변경시 인쇄방식 체크하고 종이정보 검색
 */
var changePaperSort = function (dvs, val) {
    loadPaperName(dvs, val);
};

/**
 * @brief 가격 구성요소 셀렉트박스 변경시 변경된 정보로 가격 검색
 */
var changeData = function () {
    monoYn = $("#dt_mono_yn").val();
    $("input[name='chk_opt[]']").each(function (i) {
        if ($(this).val() === "빠른출고" && $(this).prop("checked")) {
            if($("#opt_" + i + "_val > option:selected").text() == "오늘출고") {
                $("#div_out_date").html("* 예상출고일 : " + $("#out_date1").val());
            }

            if($("#opt_" + i + "_val > option:selected").text() == "내일출고") {
                $("#div_out_date").html("* 예상출고일 : " + $("#out_date2").val());
            }
        }
    });

    var data = {
        "dvs": prdtDvs,
        "mono_yn": monoYn,
        "cate_sortcode": $("#dt_cate_sortcode").val(),
        "amt": $("#dt_amt").val(),
        "count": $("#count").val(),
        "size": $("#dt_size").val()
    };

    data.paper = $("#dt_paper").val();
    data.bef_tmpt = $("#dt_print_tmpt").val();
    data.bef_add_print_mpcode = '';
    data.aft_print_mpcode = '';
    data.aft_add_print_mpcode = '';
    data.print_purp = $("#dt_print_purp").val();
    data.page_info = "2";
    data.cut_wid_size = $("#dt_cut_wid_size").val();
    data.cut_vert_size = $("#dt_cut_vert_size").val();
    data.minimum_amt = $("#minimum_amt").val();
    data.size_name = $("#dt_size").find("option:selected").text();
    if ($("#size_dvs").val() === "manu") {
        data.def_val =
            $("#dt_size").attr("def_val");
        data.def_stan_name =
            $("#dt_size > option[value='" + data.def_val + "']").html();
        data.manu_pos_num =
            $("#manu_pos_num").val();
        data.amt_unit =
            $("#dt_amt").attr("amt_unit");
    }

    var i = 0;
    $("input[type='checkbox'][name='dt_chk_after[]']").each(function () {
        if ($(this).prop("checked") === false) {
            return true;
        }

        var aft = $(this).attr("aft");
        var aft_depth = $("#dt_" + aft).val() == undefined ? "" : $("#dt_" + aft).val();
        var aft_depth1 = $("#dt_" + aft + "_1").val() == undefined ? "" : $("#dt_" + aft + "_1").val();
        var aft_depth2 = $("#dt_" + aft + "_2").val() == undefined ? "" : $("#dt_" + aft + "_2").val();
        var aft_depth1_val = $("#dt_" + aft + "_val").val() == undefined ? "" : $("#dt_" + aft + "_val").val();
        var aft_depth1_vh = $("#dt_" + aft + "_vh").val() == undefined ? "" : $("#dt_" + aft + "_vh").val();
        var aft_depth1_cnt = $("#dt_" + aft + "_cnt").val() == undefined ? "" : $("#dt_" + aft + "_cnt").val();
        var aft_depth1_dvs = $("#dt_" + aft + "_dvs_1").val() == undefined ? "" : $("#dt_" + aft + "_dvs_1").val();
        var aft_depth2_dvs = $("#dt_" + aft + "_dvs_2").val() == undefined ? "" : $("#dt_" + aft + "_dvs_2").val();
        var aft_depth1_wid = $("#dt_" + aft + "_wid_1").val() == undefined ? "" : $("#dt_" + aft + "_wid_1").val();
        var aft_depth1_vert = $("#dt_" + aft + "_vert_1").val() == undefined ? "" : $("#dt_" + aft + "_vert_1").val();
        var aft_depth2_wid = $("#dt_" + aft + "_wid_2").val() == undefined ? "" : $("#dt_" + aft + "_wid_2").val();
        var aft_depth2_vert = $("#dt_" + aft + "_vert_2").val() == undefined ? "" : $("#dt_" + aft + "_vert_2").val();
        var selector = "input[name='" +
            'dt' + '_' + aft + '_' +
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
            data.aft_depth += $("#dt_" + aft + "_depth").val();
            data.after_name += $("#dt_" + aft + "_name").val();
        } else {
            data.after_name += aft;
            data.aft_depth += aft_depth;
        }
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
    $("#opt_mp_list").val(data.opt_mp_list);
    $("#paper_info").val(data.paper_info);

    loadPaperPreview(prdtDvs);
    loadPrdtPrice.data = data;
    loadPrdtPrice.exec();
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
            $(prefix + "manu_pos_num").val(result[prdtDvs].jarisu);
            $("input[name='dt_chk_after[]']:checked").each(function () {
                var $obj = $(this);
                var aft = $obj.attr('id').replace("dt_", "");
                if (aft == "foil") {
                    var totalAfterPrice = 0;
                    afterPrice = loadPrdtPrice.price["foil1"];
                    if (afterPrice) {
                        setAfterPrice("dt", "foil1", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    afterPrice = loadPrdtPrice.price["foil2"];
                    if (afterPrice) {
                        setAfterPrice("dt", "foil2", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    afterPrice = loadPrdtPrice.price["foil3"];
                    if (afterPrice) {
                        setAfterPrice("dt", "foil3", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    setAfterPrice("dt", "foil", totalAfterPrice);
                } else {
                    afterPrice = loadPrdtPrice.price[aft];
                    setAfterPrice("dt", aft, afterPrice);
                }
            });

            i = 0;
            var j = 0;
            $("input[type='checkbox'][name='chk_opt[]']").each(function () {
                if ($(this).prop("checked") === false) {
                    j++;
                    return true;
                }

                var $obj = $(this);
                var idx = $obj.attr('id').replace("opt_", "");
                var optPrice = loadPrdtPrice.price[$obj.val()];
                setOptPrice(idx, optPrice);
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
    // 자리수
    var posNum = 1;
    if ($("#size_dvs").val() === "manu") {
        posNum = parseFloat($("#dt_manu_pos_num").val());
    }

    var dt_sortcode = $("#dt_sortcode").val();
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
    // 재단비
    var cutPrice = loadPrdtPrice.price.cut;
    // 용지비
    var paperPrice = loadPrdtPrice.price.paper;
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
    var memberSale = parseFloat($("#dt_member_sale_rate").val());
    memberSale /= 100.0;
    // 옵션비 총합
    var sumOptPrice = getSumOptPrice();
    sumOptPrice = ceilVal(sumOptPrice);
    // 후공정비 총합
    var sumAfterPrice = getSumAfterPrice(prdtDvs);
    sumAfterPrice = ceilVal(sumAfterPrice);

    // 견적서 종이비 계산
    var paper = paperPrice;
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

    // 견적서 재단비 계산
    var cut = cutPrice;
  
    if (monoYn === '1') {
        cut = parseInt(loadPrdtPrice.price.cut);
        cut = ceilVal(print);
        //print *= count;
    }

    // 후공정비 계산(세금 포함)
    var after = sumAfterPrice;
    // 견적서 후공정비 계산(세금 미포함)
    var esAfter = getSumAfterPrice(prdtDvs, "T");
    // 견적서 옵션비 계산
    var opt = sumOptPrice;

    if(dt_sortcode == "011006002"){//디지털 소량 리플렛 팜플렛 일 경우
        print = Number(print) + Number(cut); // 인쇄비
        cut = sumOptPrice; // 재단비 (옵션을 여기로 )
        opt = 0;
    }
 

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
    $("#sell_price").attr("val", (sellPrice + opt));
    $("#sell_price strong").html((sellPrice + opt).format());
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
        "cut": cut,
        "output": output,
        "afterTax": after,
        "afterNoTax": esAfter,
        "opt": opt,
        "count": count,
        "gradeSaleRate": gradeSale,
        "amtRate": amtRate,
        "amtAplc": amtAplc,
        "sellPrice": sellPrice
    };

    //loadReleaseExpect();
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
    var callback = function (result) {
        $("#dt_amt").html(result);
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

    aftShowHideByPaper(dvs);
};

var changeSizeDvs2 = function() {

    var name =  $('#chgbutton').text();

    console.log(name);

    if(name == "직접입력"){
        $('#chgbutton').text("규격선택");
        changeSizeDvs("manu");
        $("#size_dvs").val("manu").prop("selected", true);
    }else{
        $('#chgbutton').text("직접입력");
        changeSizeDvs("stan");
        $("#size_dvs").val("stan").prop("selected", true);
    }
    size();
    
}

/**
 * @brief 비규격 사이즈 선택할 경우 재단사이즈 값 초기화
 *
 * @param val = 구분값
 */
var changeSizeDvs = function (val) {
    // 비규격 사이즈 선택시 기본 사이즈로 데이터 변경
    if (val === "manu") {
        $("#dt_manu_pos_num").val('1');
        $("#cut_wid_size").val($("#dt_size").attr("def_cut_wid"));
        $("#cut_vert_size").val($("#dt_size").attr("def_cut_vert"));
        $('#chgbutton').text("규격선택");
    }else if(val === "stan"){
        $('#chgbutton').text("직접입력");
    }

    changeData();
};

var changeDigitalCuttingSize = function() {
    var work_wid_size = parseInt($("#dt_cut_wid_size").val()) + 2;
    var work_vert_size = parseInt($("#dt_cut_vert_size").val()) + 2;
    var cutting_count = calcProductCount(454,310, work_wid_size, work_vert_size);

    $("#minimum_amt").val(cutting_count);

    var now_amt = $("#dt_amt").val();
    var isExist = false;
    $("#dt_amt").html("");
    var dt_amt_html = "";
    for(var i = 1; i <= 4001; i = i) {
        if(i == 1) {
            var amt = cutting_count * (i);
        } else {
            var amt = cutting_count * (i-1);
        }

        if(now_amt == amt) {
            dt_amt_html += "<option selected='selected' value='" + amt + "'>" + amt + " 장" + "</option>";
            isExist = true;
        }
        else
            dt_amt_html += "<option value='" + (amt ) + "'>" + (amt ) + " 장" + "</option>";

        if(i == 1) i = i + 2;
        else if(i<5) i = i + 1;
        else if(i<=20) i++;
        else if(i<=100) i = i + 5;
        else if(i<=500) i = i + 10;
        else if(i<=1500) i = i + 50;
        else i = i + 100;
    }

    $("#dt_amt").html(dt_amt_html);

    if(!isExist) $('#dt_amt option:eq(0)').prop('selected', true);
    rangeBarText();
    changeData();
}

var aftShowHideByPaper = function (dvs) {
    var prefix = getPrefix(dvs);
    var basisweight = parseInt($(prefix + "paper > option:selected").text().split(' ').pop());

    if (basisweight < 400) {
        if($(prefix + "thomson_impression").prop("checked"))
            $(prefix + "thomson_impression").trigger("click");
        //aftRestrict.unchecked(dvs, "thomson_impression");
        $('.' + dvs + "_after_list li._thomson_impression").hide();
        $('.' + dvs + "_after_list li._impression").show();
        $('.' + dvs + "_after_list li.foldline").show();
    } else {
        if($(prefix + "impression").prop("checked"))
            $(prefix + "impression").trigger("click");
        //aftRestrict.unchecked(dvs, "impression");
        $('.' + dvs + "_after_list li._thomson_impression").show();
        $('.' + dvs + "_after_list li._impression").hide();

        if (basisweight >= 300) {
            if($(prefix + "foldline").prop("checked"))
                $(prefix + "foldline").trigger("click");
            //aftRestrict.unchecked(dvs, "foldline");
            $('.' + dvs + "_after_list li.foldline").hide();
            return false;
        }

        $('.' + dvs + "_after_list li.foldline").show();
    }
}

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
    }

    if (!aftRestrict.all(prdtDvs)) {
        return false;
    }

    if (!optRestrict.all(prdtDvs)) {
        return false;
    }

    var paperName = $("#dt_paper").find("option:selected").text();
    var sizeName = $("#dt_size").find("option:selected").text();
    var tmptName = $("#dt_print_tmpt").find("option:selected").text();
    var SizeDvs = $("#size_dvs").find("option:selected").val();
    if(SizeDvs == "manu") {
        sizeName = $("#dt_cut_wid_size").val() + "mm * " + $("#dt_cut_vert_size").val() + "mm";
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

    $frm.find("input[name='dt_cate_name']").val(cateName);
    $frm.find("input[name='dt_amt_unit']").val(amtUnit);
    $frm.find("input[name='dt_paper_name']").val(paperName);
    $frm.find("input[name='dt_bef_tmpt_name']").val(tmptName);
    $frm.find("input[name='dt_size_name']").val(sizeName);

    $frm.find("input[name='dt_sell_price']").val(sellPrice);
    $frm.find("input[name='dt_sale_price']").val(salePrice);
    $frm.find("input[name='dt_after_price']").val(afterPrice);
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

        var afterDet = $.trim($(".dt_overview").text()); // 후공정 세부내역

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
        var html = "<dd>";
        html += "<p class=\"guide\"사이즈가 작아서 선 끝 2~3mm 후공정이 들어가지 않을 수 있습니다.</p>";
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

    changeTmpt('dt');
};

/**
 * @brief 고급명함 종이에따라 엠보/박 show/hide
 */
var showHideAfterProcess = function () {
    var prefix = getPrefix(prdtDvs);
    var paper = $(prefix + "paper option:selected").text()
        .split(' ')[0];

    if ("유포지|샤인스페셜".indexOf(paper) > -1) {
        aftRestrict.unchecked(prdtDvs, "embossing");
        $("#after").find("li._embossing").hide();
        //$("#after").find("li._foil").hide();
    } else if ("랑데뷰".indexOf(paper) > -1) {
        aftRestrict.unchecked(prdtDvs, "embossing");
        aftRestrict.unchecked(prdtDvs, "foil");
        $("#after").find("li._embossing").hide();
        $("#after").find("li._foil").hide();
    } else {
        $("#after").find("li._embossing").show();
        $("#after").find("li._foil").show();
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

var calcProductCount = function(paperwidth, paperheight, productwidth, productheight) {
    var calc1 = Math.floor(paperwidth / productwidth) * Math.floor(paperheight / productheight);
    var calc2 = Math.floor(paperwidth / productheight) * Math.floor(paperheight / productwidth);
    return (calc1 > calc2) ? calc1 : calc2;
}
$(document).ready(function () {
	$("select").change(function(){
		changeData();
	});
});
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

    monoYn = $("#vo_mono_yn").val();
    prdtDvs = $("#prdt_dvs").val();
    affil = $("#vo_size").find("option:selected").attr("affil");
    sortcode = $("#vo_cate_sortcode").val();
    cateName = $("#vo_cate_sortcode").find("option:selected").text();
    amtUnit = $("#vo_amt").attr("amt_unit");

    $("#r_count_div").show();
    calcRCount(prdtDvs);

    var order_cut_wid = $("#order_cut_wid").val();
    var order_cut_vert = $("#order_cut_vert").val();
    var order_stan_name = $("#order_stan_nmae").val();

    $("#vo_size_name").val(order_stan_name);
    if(order_cut_wid != "" && order_cut_vert != "") {
        $("#vo_cut_wid_size").val(order_cut_wid);
        $("#vo_cut_vert_size").val(order_cut_vert);

        if(order_stan_name == "manu") {
            $("#size_dvs").val("manu");
            changeSizeDvs.exec("manu");
            size();
        }
    }


    if(sortcode == "014003001"){
        $("#"+prdtDvs+"_foldline").trigger("click");
    }else if(sortcode == "014002001"){
        $("#"+prdtDvs+"_binding").trigger("click");
        $("#vo_binding_val option:eq(5)").prop("selected",true);        
    }
 
    var max = $("#vo_amt > option:last-child").val();
    aftRestrict.laminex.max = parseInt(max);
    $("#vo_laminex_max").html(max.format());

    /*
    $("input[name='chk_opt']").each(function() {
        var opt = $(this).val();

        if (opt === "당일판") {
            var idx =  $(this).attr("id").split('_')[1];
            optRestrict[opt].common(prdtDvs, idx, false);
            return false;
        }
    });
    */

    showUvDescriptor(prdtDvs);
    //changeReleaseStr();

    var prefix = getPrefix(prdtDvs);
    var amtRate = $(prefix + "amt_sale_rate").val();
    var amtAplc = $(prefix + "amt_sale_aplc").val();
    if (checkBlank(amtRate)) {
        amtRate = 0;
    }
    if (checkBlank(amtAplc)) {
        amtAplc = 0;
    }
    loadPrdtPrice.price.amt_rate = amtRate;
    loadPrdtPrice.price.amt_aplc = amtAplc;

    loadReleaseExpect();
    showHideDayBoard();
    changeData();
});

/**
 * @brief 종이 바뀔 때 평량에 따라 규격 변경
 *
 * @param dvs = 제품구분값
 * @param val = 종이맵핑코드
 */
var changePaper = function (dvs, val) {
    var prefix = getPrefix(dvs);
    var affil = $(prefix + "size > option:selected").attr("affil");
    var posNum = $(prefix + "size > option:selected").attr("pos_num");

    changeSizeDvs.flag = false;

    $("#size_dvs").val("stan");
    $("#size_dvs").trigger("change");

    //changeReleaseStr();

    var url = "/ajax/product/load_paper_size.php";
    var data = {
        "cate_sortcode": sortcode,
        "mono_yn": $(prefix + "mono_yn").val(),
        "affil_yn": affil,
        "pos_yn": posNum,
        "size_typ_yn": 'N',
        "paper_mpcode": val
    };
    var callback = function (result) {
        $(prefix + "size").html(result);
        if (isFunc("size")) {
            size();
        }
        loadPrdtAmt(dvs);
    };

    loadPaperPreview(dvs);
    showHideDayBoard();

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 바뀐 종이와 사이즈로 수량 변경
 *
 * @param dvs = 제품구분값
 * @param val = 종이맵핑코드
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
        calcRCount(dvs);

        var aftInfoArr = getAftInfoArr(dvs);
        var size = $(prefix + "size > option:selected").text();
        //loadAfterMpcode(dvs, aftInfoArr, size);

    
        var num_size = $("#vo_size").val();
        var cate_num = $("#vo_cate_sortcode").val();

        if(cate_num == "014003001"){ //접지 상품만 후가공 지정 
            if(num_size == "847"){
                $("#vo_foldline_dvs option:eq(0)").prop("selected",true);
                loadFoldlineDepth2("2단접지", 'vo');
            }else if(num_size == "849"){
                $("#vo_foldline_dvs option:eq(2)").prop("selected",true);
                loadFoldlineDepth2("4단접지", 'vo');
                $("#vo_foldline_val option:eq(0)").prop("selected",true);
            }else if(num_size == "851"){
                $("#vo_foldline_dvs option:eq(2)").prop("selected",true);
                loadFoldlineDepth2("4단접지", 'vo');
               
            }else if(num_size == "850"){
                $("#vo_foldline_dvs option:eq(2)").prop("selected",true);
                loadFoldlineDepth2("4단접지", 'vo');
                $("#vo_foldline_val option:eq(1)").prop("selected",true);
            }else if(num_size == "848"){
                $("#vo_foldline_dvs option:eq(1)").prop("selected",true);
                loadFoldlineDepth2("3단접지", 'vo');
            }

        }

        if(cate_num == "014002001"){ //책자형 상품만 후가공 지정 
            if(num_size == "846"){
                console.log("846");
                $("#vo_binding_val option:eq(5)").prop("selected",true);
            }else if(num_size == "854"){
                console.log("854");
                $("#vo_binding_val option:eq(2)").prop("selected",true);
            }else if(num_size == "852"){
                console.log("852");
                $("#vo_binding_val option:eq(3)").prop("selected",true);               
            }else if(num_size == "855"){
                console.log("855");
                $("#vo_binding_val option:eq(0)").prop("selected",true);
            }else if(num_size == "853"){
                console.log("853");
                $("#vo_binding_val option:eq(4)").prop("selected",true);
            }else if (num_size == "856"){
                console.log("856");
                $("#vo_binding_val option:eq(1)").prop("selected",true);
            }

        }
        
     
        //$("#vo_foldline_dvs option:eq(1)").prop("selected",true);

        if (isFunc("rangeBarBySelect")) {
            rangeBarText();
        }
        changeData();
        calcLaminexMaxCount();
        //changeReleaseStr();
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 계산형일 때 사이즈 선택할 경우 사이즈 계열에 맞는 도수값 검색
 *
 * @param val = 구분값
 */
var changeSize = {
    "exec": function (dvs) {
        loadPrdtAmt(dvs);
        showHideDayBoard();
    }
};

/**
 * @brief 합판전단 종이에따라 당일판 show/hide
 */
var showHideDayBoard = function () {
    var prefix = getPrefix(prdtDvs);
    var weight = $(prefix + "paper > option:selected").text().split(' ').pop();
    var idx = null;
    var $chkBox = null;
    $("input[name='chk_opt[]']").each(function (i) {
        if ($(this).val() === "당일판") {
            idx = i;
            $chkBox = $(this);
            return false;
        }
    });

    if (idx === null) {
        return false;
    }

    loadOptPrice.exec($chkBox, idx, prdtDvs);
};

var changeData = function() {

    /*var num_size = $("#vo_size").val();
    var cate_num = $("#vo_cate_sortcode").val();

    if(cate_num == "014003001"){ //접지 상품만 후가공 지정 
        console.log("dd1");
        if(num_size == "847"){
        }else if(num_size == "849"){
            $("#vo_foldline_val option:eq(0)").prop("selected",true);
        }else if(num_size == "851"){
            $("#vo_foldline_val option:eq(2)").prop("selected",true);
        }else if(num_size == "850"){
            $("#vo_foldline_val option:eq(1)").prop("selected",true);
        }else if(num_size == "848"){
        }

    } */
    loadDlvrPriceExpect(true, changeData1);
}

/**
 * @brief 가격 구성요소 셀렉트박스 변경시 변경된 정보로 가격 검색
 */
var changeData1 = function () {
    changeSizeDvs.flag = true;
    monoYn = $("#vo_mono_yn").val();

    var data = {
        "dvs": prdtDvs,
        "mono_yn": monoYn,
        "cate_sortcode": sortcode,
        "amt": $("#vo_amt").val(),
        "size": $("#vo_size").val(),
        "count": $("#count").val(),
        "affil": affil
    };

    data.flag = $("#frm").find("input[name='flag']").val();
    data.paper = $("#vo_paper").val();
    data.paper_info = $("#vo_paper > option:selected").text()
    data.bef_tmpt = $("#vo_print_tmpt").val();
    data.bef_add_print_mpcode = '';
    data.aft_print_mpcode = '';
    data.aft_add_print_mpcode = '';
    data.print_purp = $("#vo_print_purp").val();
    data.page_info = $("#vo_page").val();;
    data.cut_wid_size = $("#vo_cut_wid_size").val();
    data.cut_vert_size = $("#vo_cut_vert_size").val();
    data.expect_box_num = $("#expect_box").val();
    data.size_name = $("#vo_size").find("option:selected").text();
    if ($("#size_dvs").val() === "manu") {
        data.size_def_val =
            $("#vo_size").attr("def_val");
        data.def_stan_name =
            $("#vo_size > option[value='" + data.size_def_val + "']").html();
        data.manu_pos_num =
            $("#manu_pos_num").val();
        data.amt_unit =
            $("#vo_amt").attr("amt_unit");
    }

    var i = 0;
    $("input[type='checkbox'][name='vo_chk_after[]']").each(function () {
        if ($(this).prop("checked") === false) {
            return true;
        }

        var aft = $(this).attr("aft");
        var aft_depth = $("#vo_" + aft).val() == undefined ? "" : $("#vo_" + aft).val();
        var aft_depth1 = $("#vo_" + aft + "_1").val() == undefined ? "" : $("#vo_" + aft + "_1").val();
        var aft_depth2 = $("#vo_" + aft + "_2").val() == undefined ? "" : $("#vo_" + aft + "_2").val();
        var aft_depth1_val = $("#vo_" + aft + "_val").val() == undefined ? "" : $("#vo_" + aft + "_val").val();
        var aft_depth1_vh = $("#vo_" + aft + "_vh").val() == undefined ? "" : $("#vo_" + aft + "_vh").val();
        var aft_depth1_cnt = $("#vo_" + aft + "_cnt").val() == undefined ? "" : $("#vo_" + aft + "_cnt").val();
        var aft_depth1_dvs = $("#vo_" + aft + "_dvs_1").val() == undefined ? "" : $("#vo_" + aft + "_dvs_1").val();
        var aft_depth2_dvs = $("#vo_" + aft + "_dvs_2").val() == undefined ? "" : $("#vo_" + aft + "_dvs_2").val();
        var aft_depth1_wid = $("#vo_" + aft + "_wid_1").val() == undefined ? "" : $("#vo_" + aft + "_wid_1").val();
        var aft_depth1_vert = $("#vo_" + aft + "_vert_1").val() == undefined ? "" : $("#vo_" + aft + "_vert_1").val();
        var aft_depth2_wid = $("#vo_" + aft + "_wid_2").val() == undefined ? "" : $("#vo_" + aft + "_wid_2").val();
        var aft_depth2_vert = $("#vo_" + aft + "_vert_2").val() == undefined ? "" : $("#vo_" + aft + "_vert_2").val();
        var selector = "input[name='" +
            'vo' + '_' + aft + '_' +
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
            data.aft_depth += $("#vo_" + aft + "_depth").val();
            data.after_name += $("#vo_" + aft + "_name").val();
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
        var opt_mp_list = $("#opt_" + j + "_val > option:selected").val() == undefined ? "" : $("#opt_" + j + "_val > option:selected").val();

        if (i != 0) {
            data.option_name += "|";
            data.opt_depth += "|";
            data.opt_depth1 += "|";
            data.opt_depth2 += "|";
            data.opt_depth3 += "|";
            data.opt_cnt += "|";
            data.opt_mp_list += "|";
        } else {
            data.option_name = "";
            data.opt_depth = "";
            data.opt_depth1 = "";
            data.opt_depth2 = "";
            data.opt_depth3 = "";
            data.opt_cnt = "";
            data.opt_mp_list = "";
        }
        data.option_name += option_name;
        data.opt_depth1 += opt_depth1;
        data.opt_depth2 += opt_depth2;
        data.opt_depth3 += opt_depth3;
        data.opt_cnt += opt_cnt;
        data.opt_mp_list += opt_mp_list;
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
    $("#affil").val(data.affil);

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

           $("input[name='vo_chk_after[]']:checked").each(function () {
                var $obj = $(this);
                var aft = $obj.attr('id').replace("vo_", "");

               // alert(aft);

                if(aft == "binding"){
                    var totalAfterPrice = 0;
                    afterPrice = loadPrdtPrice.price[aft];
                    setAfterPrice("vo", aft, afterPrice);
                }

                if(aft == "foldline"){
                    var totalAfterPrice = 0;
                    afterPrice = loadPrdtPrice.price[aft];
                    setAfterPrice("vo", aft, afterPrice);
                }
               /* if (aft == "foil") {
                    var totalAfterPrice = 0;
                    afterPrice = loadPrdtPrice.price["foil1"];
                    if (afterPrice) {
                        setAfterPrice("bl", "foil1", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    afterPrice = loadPrdtPrice.price["foil2"];
                    if (afterPrice) {
                        setAfterPrice("bl", "foil2", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    afterPrice = loadPrdtPrice.price["foil3"];
                    if (afterPrice) {
                        setAfterPrice("bl", "foil3", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    setAfterPrice("bl", "foil", totalAfterPrice);
                } else {
                    afterPrice = loadPrdtPrice.price[aft];
                    setAfterPrice("bl", aft, afterPrice);
                }
                */
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
            afterOverview("vo");
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
    var gradeSale = parseFloat($("#vo_grade_sale_rate").val());
    gradeSale /= 100.0;
    // 회원 할인율
    var memberSale = parseFloat($("#vo_member_sale_rate").val());
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
        $("#vo_print_tmpt").html(result.bef_tmpt);

        if (monoYn === '1') {
            changeData();
        }
    };

    loadPrintTmptCommon.exec(dvs, callback);
};

/**
 * @brief 비규격 사이즈 선택할 경우 재단사이즈 값 초기화
 *
 * @param val  = 구분값
 */
var changeSizeDvs = {
    "flag": true,
    "exec": function (val) {
        // 비규격 사이즈 선택시 기본 사이즈로 데이터 변경
        var prefix = getPrefix(prdtDvs);
        $(prefix + "similar_size").attr("divide", '1');

        // 비규격 사이즈 선택시 기본 사이즈로 데이터 변경
        if (val === "manu") {
            var str = $(prefix + "size > option:selected").text() + " 1/1 등분";

            $(prefix + "similar_size").show();
            $(prefix + "similar_size").html(str);
            if ($("#im").val() == "1") {
                $("#vo_size").hide();
            }
        } else {
            $(prefix + "similar_size").hide();
            calcRCount(prdtDvs);
            if ($("#im").val() == "1") {
                $("#vo_size").show();
            }
        }

        if (changeSizeDvs.flag) {
            changeData();
        }
    }
};

/**
 * @brief 수량변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeAmt = function () {
    calcRCount(prdtDvs);
    reCalcAfterPrice(prdtDvs, null);
    changeData();
    showHideDayBoard();
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

    var amtUnit = $("#vo_amt").attr("amt_unit");
    var paperName = $("#vo_paper").find("option:selected").text();
    if($("#vo_paper_name").val() != null) {
        paperName = $("#vo_paper_name").val() + " " + paperName;
    }

    var tmptName = $("#vo_print_tmpt").find("option:selected").text();
    var sizeName = $("#vo_size").find("option:selected").text();

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

    $frm.find("input[name='vo_cate_name']").val(cateName);
    $frm.find("input[name='vo_amt_unit']").val(amtUnit);
    $frm.find("input[name='vo_paper_name']").val(paperName);
    $frm.find("input[name='vo_bef_tmpt_name']").val(tmptName);
    $frm.find("input[name='vo_size_name']").val(sizeName);

    $frm.find("input[name='vo_sell_price']").val(sellPrice);
    $frm.find("input[name='vo_sale_price']").val(salePrice);
    $frm.find("input[name='vo_after_price']").val(afterPrice);
    $frm.find("input[name='vo_opt_price']").val(optPrice);

    return true;
};

/**
 * @brief 견적서 팝업 본문정보 생성
 */
var makeEstiPopInfo = {
    "data": null,
    "exec": function (dvs) {
        var prefix = getPrefix(prdtDvs);

        var paper = $.trim($("#vo_paper > option:selected").text());
        var size = $.trim($(prefix + "size > option:selected").text());
        var tmpt = $.trim($("#vo_print_tmpt > option:selected").text());
        var amt = $.trim($("#vo_amt").val());
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

/**
 * @brief 납기문구 수정
 *
var changeReleaseStr = function() {
    var prefix = getPrefix(prdtDvs);
    var paper = $(prefix + "paper > option:selected").text();
    paper = paper.split(' ');
    var basisweight = parseInt(paper[paper.length - 1]);
    var size = $(prefix + "size > option:selected").text();
    var amt = parseFloat($(prefix + "amt").val());

    var str = null;

    if (basisweight === 90) {
        str  = "* 평일 오후 7시 마감(토요일 접수 없음).";
        if (amt === 0.5) {
            str += "<br/>* 출고까지 2~3일 정도 소요됩니다.";
        } else if ((size === "8절" || size === "A3") &&
                amt === 1.0 || amt === 3.0) {
            str += "<br/>* 출고까지 2~3일 정도 소요됩니다.";
	} else {
            str += "<br/>* 익일 출고됩니다.";
	}
    } else if (basisweight === 120 || basisweight === 150 || basisweight === 180) {
        str  = "* 평일 오후 6시 마감(토요일 접수 없음).";
        str += "<br/>* 납기일은 3~4일 정도 소요됩니다.";
    } else {
        str  = "* 평일 오후 6시 마감(토요일 접수 없음).";
        str += "<br/>* 익일 출고됩니다.";
    }

    $("#release_str").html(str);
};
*/

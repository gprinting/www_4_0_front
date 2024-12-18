var monoYn = null;
var prdtDvs = null;
var sortcode = null;
var cateName = null;
var amtUnit = null;

$(document).ready(function () {
    // 건수 초기화
    var order_count = $("#order_count").val();
    initCount(999, "count", order_count);

    monoYn = $("#etc_mono_yn").val();
    prdtDvs = $("#prdt_dvs").val();
    sortcode = $("#etc_cate_sortcode").val();
    cateName = $("#etc_cate_sortcode").find("option:selected").text();
    amtUnit = $("#etc_amt").attr("amt_unit");

    var order_cut_wid = $("#order_cut_wid").val();
    var order_cut_vert = $("#order_cut_vert").val();
    var order_stan_name = $("#order_stan_nmae").val();

    $("#bl_size_name").val(order_stan_name);
    if(order_cut_wid != "" && order_cut_vert != "") {
        $("#bl_cut_wid_size").val(order_cut_wid);
        $("#bl_cut_vert_size").val(order_cut_vert);

        if(order_stan_name == "manu") {
            $("#size_dvs").val("manu");
            changeSizeDvs.exec("manu");
            size();
        }
    }
    changeAmt();

    var prefix = getPrefix(prdtDvs);

    calcManuPosNum.defWid = parseFloat($("#etc_size").attr("def_cut_wid"));
    calcManuPosNum.defVert = parseFloat($("#etc_size").attr("def_cut_vert"));

    setSizeWarning();

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

    if (sortcode === "008001003" || sortcode === "008001004") {
        // 문어발
        changeSizeTyp();

        var stanName = $(prefix + "size_typ > option:selected").text();
        stanName = stanName.split(' ')[0];
        changePreviewImg(stanName);

        if (typeof preview !== "undefined") {
            preview.cuttingSize.remove();
            preview.workingSize.remove();
            preview.btns.find('.cuttingLine').trigger("click");
            preview.btns.find('.workingLine').trigger("click");
            preview.btns.remove();
        }
    } else if (sortcode === "008001005") {
        // 문고리
        changeSizeTyp();

        var stanName = $(prefix + "size > option:selected").text();
        stanName = stanName.split(' ')[0].replace('(', '').replace(')', '');
        changePreviewImg(stanName);

        if (typeof preview !== "undefined") {
            preview.cuttingSize.remove();
            preview.workingSize.remove();
            preview.btns.find('.cuttingLine').trigger("click");
            preview.btns.find('.workingLine').trigger("click");
            preview.btns.remove();
        }
    }

    $("#r_count_div").show();
    calcRCount(prdtDvs);
    loadReleaseExpect();
    loadDlvrPriceExpect();
    changeData();
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
    var affil = $("#etc_size > option:selected").attr("affil");
    var posNum = $("#etc_size > option:selected").attr("pos_num");

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

        loadPrdtAmt(dvs);
    };

    loadPaperPreview(dvs);

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 문어발에서 종이 바뀔 때 규격 변경
 */
var changeMultiplePaper = function () {
    loadPaperPreview(prdtDvs);
    changeData();
};

/**
 * @brief 복권에서 주의사항 출력
 */
var setSizeWarning = function () {
    var prefix = getPrefix(prdtDvs);
    var amt = $(prefix + "amt > option:selected").val();
    if (parseInt(amt) < 3000) {
        $(prefix + "warning").text("※ 총수량 3천장 미만 주문시 납기가 오래걸릴 수 있습니다.");
    } else {
        $(prefix + "warning").text("");
    }
};

/**
 * @brief 복권 상세수량 추가
 */
var addDetailAmt = function () {
    var $copyObj = $("#detail_amt_div").clone();
    $copyObj.find("input[name='detail_con']").val('');
    $copyObj.addClass("detail_amt_div");
    $copyObj.removeAttr("id");

    $("#detail_amt_dd").append($copyObj);
};

/**
 * @brief 상세수량 삭제
 */
var removeDetailAmt = function () {
    var $objArr = $(".detail_amt_div");
    var len = $objArr.length;

    $($objArr[len - 1]).remove();
};

/**
 * @brief 수량과 상세수량이 맞는지 확인
 */
var chkTotalAmt = function () {
    if ($("#detail_amt_dd").length === 0) {
        return true;
    }

    var prefix = getPrefix(prdtDvs);
    var $detailAmtArr = $("#detail_amt_dd").find("select[name='detail_amt']");
    var len = $detailAmtArr.length;
    var amt = parseInt($(prefix + "amt").val());

    var detailAmt = 0;
    for (var i = 0; i < len; i++) {
        detailAmt += parseInt($($detailAmtArr[i]).val());
    }

    if (amt !== detailAmt) {
        return false;
    }

    return true;
};

/**
 * @brief 주문상세에 추가될 상세수량 내용 생성
 *
 * @return 상세수량 내용
 */
var makeDetailAmt = function () {
    var $detailAmtArr = $("#detail_amt_dd").find("select[name='detail_amt']");
    var $detailConArr = $("#detail_amt_dd").find("input[name='detail_con']");
    var len = $detailAmtArr.length;

    var ret = '';

    for (var i = 0; i < len; i++) {
        var amt = $($detailAmtArr[i]).val();
        var con = $($detailConArr[i]).val();

        ret += '[' + amt.format() + "매:" + con + "], ";
    }

    return ret.substr(0, ret.length - 2);
};

/**
 * @brief 수량변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeAmt = function () {
    //reCalcAfterPrice(prdtDvs, null);
    calcRCount(prdtDvs);
    changeData();
    setSizeWarning();
};

var changeData = function() {
    loadDlvrPriceExpect(true, changeData1);
}

/**
 * @brief 가격 구성요소 셀렉트박스 변경시 변경된 정보로 가격 검색
 */
var changeData1 = function () {
    var prefix = getPrefix(prdtDvs);
    monoYn = $(prefix + "mono_yn").val();

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
    data.print_purp = $(prefix + "print_purp").val();
    data.page_info = "2";
    data.cut_wid_size = $("#etc_cut_wid_size").val();
    data.cut_vert_size = $("#etc_cut_vert_size").val();
    data.expect_box_num = $("#expect_box").val();

    if ($("#size_dvs").val() === "manu") {
        data.def_val =
            $(prefix + "size").attr("def_val");
        data.def_stan_name =
            $(prefix + "size > option[value='" + data.def_val + "']").html();
        data.manu_pos_num =
            $("#manu_pos_num").val();
        data.amt_unit =
            $(prefix + "amt").attr("amt_unit");
    }

    var i = 0;
    $("input[type='checkbox'][name='etc_chk_after[]']").each(function () {
        if ($(this).prop("checked") === false) {
            return true;
        }

        var aft = $(this).attr("aft");
        var aft_depth = $("#etc_" + aft).val() == undefined ? "" : $("#etc_" + aft).val();
        var aft_depth1 = $("#etc_" + aft + "_1").val() == undefined ? "" : $("#etc_" + aft + "_1").val();
        var aft_depth2 = $("#etc_" + aft + "_2").val() == undefined ? "" : $("#etc_" + aft + "_2").val();
        var aft_depth1_val = $("#etc_" + aft + "_val").val() == undefined ? "" : $("#etc_" + aft + "_val").val();
        var aft_depth1_vh = $("#etc_" + aft + "_vh").val() == undefined ? "" : $("#etc_" + aft + "_vh").val();
        var aft_depth1_cnt = $("#etc_" + aft + "_cnt").val() == undefined ? "" : $("#etc_" + aft + "_cnt").val();
        var aft_depth1_dvs = $("#etc_" + aft + "_dvs_1").val() == undefined ? "" : $("#etc_" + aft + "_dvs_1").val();
        var aft_depth2_dvs = $("#etc_" + aft + "_dvs_2").val() == undefined ? "" : $("#etc_" + aft + "_dvs_2").val();
        var aft_depth1_wid = $("#etc_" + aft + "_wid_1").val() == undefined ? "" : $("#etc_" + aft + "_wid_1").val();
        var aft_depth1_vert = $("#etc_" + aft + "_vert_1").val() == undefined ? "" : $("#etc_" + aft + "_vert_1").val();
        var aft_depth2_wid = $("#etc_" + aft + "_wid_2").val() == undefined ? "" : $("#etc_" + aft + "_wid_2").val();
        var aft_depth2_vert = $("#etc_" + aft + "_vert_2").val() == undefined ? "" : $("#etc_" + aft + "_vert_2").val();
        var selector = "input[name='" +
            'etc' + '_' + aft + '_' +
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
            data.aft_depth += $("#etc_" + aft + "_depth").val();
            data.after_name += $("#etc_" + aft + "_name").val();
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

    loadPaperPreview(prdtDvs);
    loadPrdtPrice.data = data;
    loadPrdtPrice.exec();

    loadPrdtPrice.price.amt_rate = $(prefix + "amt_sale_rate").val();
    loadPrdtPrice.price.amt_aplc = $(prefix + "amt_sale_aplc").val();
};

/**
 * @brief 건수변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeCount = function () {
    //reCalcAfterPrice(prdtDvs, null);
    changeData();
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
    var prefix = getPrefix(prdtDvs);
    $("#etc_cut_wid_size").val($(prefix + "size").attr("def_cut_wid"));
    $("#etc_cut_vert_size").val($(prefix + "size").attr("def_cut_vert"));
    // 비규격 사이즈 선택시 기본 사이즈로 데이터 변경
    if (val === "manu") {
        $(prefix + "manu_pos_num").val('1');

        if ($(prefix + "similar_size")) {
            var str = $(prefix + "size > option:selected").text() + " 1/1 등분";
            $(prefix + "similar_size").show();
            $(prefix + "similar_size").html(str);
        }

        if ($("#im").val() == "1") {
            $("._roomNumber").addClass("_on");
            $("#etc_size").hide();
        }
        $('#chgbutton').text("규격선택");
    }else if(val =="stan"){

        $('#chgbutton').text("직접입력");
        
        if ($(prefix + "similar_size")) {
            $(prefix + "similar_size").hide();
        }

        if ($("#im").val() == "1") {
            $("._roomNumber").removeClass("_on");
            $("#etc_size").show();
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
    "door": function (dvs) {
        var prefix = getPrefix(dvs);
        var size = $(prefix + "size > option:selected").text();
        size = size.split(' ')[0].replace('(', '').replace(')', '');
        changePreviewImg(size);

        changeSize.exec('etc');
    },
    "menu": function (dvs) {
        var prefix = getPrefix(dvs);
        var size = $(prefix + "size > option:selected").text();
        var callback = null;

        if (size.indexOf("B5") > 0 || size.indexOf("B4") > 0) {
            callback = function (result) {
                $(prefix + "amt").html(result);
                calcRCount(dvs);

                $("li._punching").show();
                var aftInfoArr = [{
                    "name": "타공",
                    "depth1": $(prefix + "punching_cnt > option:selected").text()
                }];
                loadAfterMpcode(dvs, aftInfoArr, size);

                if (isFunc("rangeBarBySelect")) {
                    rangeBarBySelect();
                }
                changeData();
            };
        } else {
            aftRestrict.unchecked(dvs, "punching");
            $("li._punching").hide();
        }

        loadPrdtAmt(dvs, callback);
    },
    "exec": function (dvs) {
        if (sortcode === "008001005") {
            var prefix = getPrefix(prdtDvs);
            var size = $(prefix + "size > option:selected").text();
            if(size == "(G3) 장32절") {
                isChange = false;
                $(prefix + "paper > option").each(function() {
                    if(this.label == "스노우지 백색 150g" || this.label == "아트지 백색 150g") {
                        $(this).hide();

                        if($(this).prop("selected")) {
                            isChange = true;
                        }
                    } else {
                        if(isChange) {
                            $(this).prop("selected",true);
                            isChange = false;
                        }
                    }
                });

            } else {
                $(prefix + "paper > option").each(function() {
                    $(this).show();
                });
            }
        }
        //reCalcAfterPrice(dvs, null);
        loadPrdtAmt(dvs);
    }
};

/**
 * @brief 문어발에서 사이즈 변경시 제품유형 검색
 */
var loadSizeTyp = function () {
    var prefix = getPrefix(prdtDvs);

    var url = "/ajax/product/load_typ.php";
    var data = {
        "pos_yn": 'Y',
        "cate_sortcode": sortcode,
        "stan_name": $(prefix + "size > option:selected").text()
    };
    var callback = function (result) {
        $(prefix + "size_typ").html(result);
        changeSizeTyp();
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 문어발 제품유형 변경시 맵핑코드 사이즈로 입력
 */
var changeSizeTyp = function () {
    var prefix = getPrefix(prdtDvs);
    var val = $(prefix + "size_typ").val();

    $(prefix + "size > option:selected").attr("value", val);

    changeSize.exec(prdtDvs);

    var stanName = $(prefix + "size_typ > option:selected").text();
    stanName = stanName.split(' ')[0];
    changePreviewImg(stanName);
};

/**
 * @brief 바뀐 종이와 사이즈로 수량 변경
 *
 * @param dvs = 제품구분값
 */
var loadPrdtAmt = function (dvs, callback) {
    var prefix = getPrefix(dvs);

    var url = "/ajax/product/load_amt.php";
    var data = {
        "cate_sortcode": sortcode,
        "mono_yn": $(prefix + "mono_yn").val(),
        "amt_unit": amtUnit,
        "stan_mpcode": $(prefix + "size").val(),
        "paper_mpcode": $(prefix + "paper").val()
    };


    var selected_amt = $("#etc_amt").val();
    if (checkBlank(callback)) {
        callback = function (result) {
            $(prefix + "amt").html(result);
            //$(prefix + "amt").val($(prefix + "amt > option:first").val());
            //$(prefix + "amt").val(selected_amt);
            calcRCount(dvs);

            var aftInfoArr = getAftInfoArr(dvs);
            var size = $(prefix + "size > option:selected").text();


            loadAfterMpcode(dvs, aftInfoArr, size);

            if (isFunc("rangeBarBySelect")) {
                rangeBarBySelect();
            }
            changeData();
        };
    }

    ajaxCall(url, "html", data, callback);
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
            $(prefix + "manu_pos_num").val(result[prdtDvs].jarisu);

            $("input[name='etc_chk_after[]']:checked").each(function () {
                var $obj = $(this);
                var aft = $obj.attr('id').replace("etc_", "");
                if (aft == "foil") {
                    var totalAfterPrice = 0;
                    afterPrice = loadPrdtPrice.price["foil1"];
                    if (afterPrice) {
                        setAfterPrice("etc", "foil1", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    afterPrice = loadPrdtPrice.price["foil2"];
                    if (afterPrice) {
                        setAfterPrice("etc", "foil2", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    afterPrice = loadPrdtPrice.price["foil3"];
                    if (afterPrice) {
                        setAfterPrice("etc", "foil3", afterPrice);
                        totalAfterPrice += parseInt(afterPrice);
                    }
                    setAfterPrice("etc", "foil", totalAfterPrice);
                } else {
                    afterPrice = loadPrdtPrice.price[aft];
                    setAfterPrice("etc", aft, afterPrice);
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
    var prefix = getPrefix(prdtDvs);

    // 자리수
    var posNum = 1;
    if ($("#size_dvs").val() === "manu" && $(prefix + "manu_pos_num").length > 0) {
        posNum = parseFloat($(prefix + "manu_pos_num").val());
    }
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
    //sellPrice *= posNum;
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

    // 정상판매가 변경(후공정, 옵션은 할인하지 않는다)
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
 * @brief 관심상품 등록이나 장바구니 전에 데이터 세팅
 */
var setSubmitParam = function () {
    if ($("#il").val() === "0") {
        $("#cart_flag").val('Y');
        $("#purlogin").val("1");
        showLoginBox();
        return false;
    }

    // 복권
    if (chkTotalAmt() === false) {
        return alertReturnFalse("수량과 상세수량이 맞지 않습니다.");
    }

    if (!aftRestrict.all(prdtDvs)) {
        return false;
    }

    if (!optRestrict.all(prdtDvs)) {
        return false;
    }

    var prefix = getPrefix(prdtDvs);
    var paperName = $(prefix + "paper").find("option:selected").text();
    var tmptName = $(prefix + "print_tmpt").find("option:selected").text();
    var sizeName = $(prefix + "size").find("option:selected").text();

    var sellPrice = $("#sell_price").attr("val");
    var salePrice = $("#sale_price").attr("val");
    var afterPrice = getSumAfterPrice(prdtDvs);
    var optPrice = getSumOptPrice();

    //if (!makeAfterInfo.all(prdtDvs)) {
    //    return false;
    //}

    // 복권
    if(sortcode == "008001001") {
        if ($("#detail_amt_dd").length > 0) {
            var sizeDvs = $("#size_dvs").val();
            if (sizeDvs === "manu") {
                sizeName = "비규격";
            }

            var orderDetail = cateName + " / " +
                paperName + " / " +
                sizeName + " / " +
                tmptName + " / 상세수량 : " +
                makeDetailAmt();

            $("#order_detail").val(orderDetail);

            var $detailAmtArr = $("#detail_amt_dd").find("select[name='detail_amt']");
            var len = $detailAmtArr.length;
            $("#count").val(len);
        }
    }
    else if (sortcode === "008001003" || sortcode === "008001004") {
        // 문어발
        var orderDetail = cateName + " / " +
            paperName + " / " +
            sizeName + " (" +
            $(prefix + "size_typ > option:selected").text() + ") / " +
            tmptName;

        $("#order_detail").val(orderDetail);
    } else if (sortcode === "008001006") {
        // 메모지
        var orderDetail = cateName + " / " +
            paperName + " / " +
            sizeName + " / " +
            tmptName + " / [제본매수 :  " +
            $(prefix + "binding_count").val() + " / " +
            $(prefix + "binding_val").val() + ']';

        $("#order_detail").val(orderDetail);
    } else {
        var orderDetail = cateName + " / " +
            paperName + " / " +
            sizeName + " / " +
            tmptName;

        $("#order_detail").val(orderDetail);
    }

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

        var afterDet = $.trim($(".etc_overview").text()); // 후공정 세부내역

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

var tmptDvs = null;
var monoYn = null;
var affil = null;
var sortcode = null;
var amtUnit = null;
var cateName = null;
var commonDvs = null;
// 표지내지 on/off 확인
var dvsOnOff = {
    "cover": true,
    "inner1": true,
    "inner2": false,
    "inner3": false,
    "exec": function (dvs) {
        dvsOnOff[dvs] = !dvsOnOff[dvs];

        // 내자 2, 3에 해당하는 미리보기
        if (dvs === "inner2") {
            if(dvsOnOff[dvs]) {
                $('#preview2').show();
                $('.inner2_after').show();
                $('.addBtn_inner2').hide();
                $('.delBtn_inner2').show();

                $('.addBtn_inner3').show();

                $('.inner3_pages').removeClass('_off');

                $('#btn_inner3').show();
            } else {
                $('#preview2').hide();
                $('.inner2_after').hide();
                $('.addBtn_inner2').show();
                $('.delBtn_inner2').hide();

                $('.addBtn_inner3').hide();

                $('#btn_inner3').hide();
            }
        } else if (dvs === "inner3") {
            if(dvsOnOff[dvs]) {
                $('#preview3').show();
                $('.inner3_after').show();
                $('.addBtn_inner3').hide();
                $('.delBtn_inner3').show();

                $('.delBtn_inner2').hide();
            } else {
                $('#preview3').hide();
                $('.inner3_after').hide();
                $('.addBtn_inner3').show();
                $('.delBtn_inner3').hide();

                $('.delBtn_inner2').show();
            }
        }

        reCalcAfterPrice(commonDvs, null);
        reCalcAfterPrice(dvs, null);
        changeData("all");
    }
};
var dvsIdx = {
    "cover": 0,
    "inner1": 1,
    "inner2": 2,
    "inner3": 3
};
var dvsKo = {
    "cover": "표지",
    "inner1": "내지1",
    "inner2": "내지2",
    "inner3": "내지3"
};
var dvsArr = [
    "cover",
    "inner1",
    "inner2",
    "inner3"
];

$(document).ready(function () {
    monoYn = $("#cover_mono_yn").val();
    tmptDvs = $("#ad_tmpt_dvs").val();
    affil = $("#ad_size").find("option:selected").attr("affil");
    sortcode = $("#ad_cate_sortcode").val();
    cateName = $("#ad_cate_sortcode").find("option:selected").text();
    amtUnit = $("#ad_amt").attr("amt_unit");
    commonDvs = $("#common_prdt_dvs").val();

    $("#cover_cate_sortcode").val(sortcode);
    $("#inner1_cate_sortcode").val(sortcode);
    $("#inner2_cate_sortcode").val(sortcode);
    $("#inner3_cate_sortcode").val(sortcode);

    $("#ad_print_purp").val($("#cover_print_purp").val());

    // 제본 셀렉트박스 초기화
    chkBookletBinding(calcBindingPrice.getPage());
    showUvDescriptor("cover");
    showUvDescriptor("inner1");

    loadReleaseExpect();
    loadDlvrPriceExpect();

    // 내지 박, 형압삭제
    $("li._foil").each(function (i) {
        if (i === 0) {
            return true;
        }
        $(this).remove();
    });
    $("div._foil").each(function (i) {
        if (i === 0) {
            return true;
        }
        $(this).remove();
    });
    $("li._press").each(function (i) {
        if (i === 0) {
            return true;
        }
        $(this).remove();
    });
    $("div._press").each(function (i) {
        if (i === 0) {
            return true;
        }
        $(this).remove();
    });
    $("li._embossing").each(function (i) {
        if (i === 0) {
            return true;
        }
        $(this).remove();
    });
    $("div._embossing").each(function (i) {
        if (i === 0) {
            return true;
        }
        $(this).remove();
    });


    // 내지1 0p 삭제
    $("#inner1_page > option:first").remove();

    impressShowHide("cover");
    impressShowHide("inner1");
    impressShowHide("inner2");
    impressShowHide("inner3");

    changeData("all");
});

/**
 * @brief 페이지 수에 따라서 가능한 제본만 표시
 * 171102 이청산 수정(trim형식 변환, ie8 대응)
 *
 * @param page = 페이지수
 */
var chkBookletBinding = function (page) {
    var prefix = getPrefix(commonDvs);
    var $depth1Obj = $(prefix + "binding_depth1");
    var selectedDepth1 = $depth1Obj.find("option:selected").text();
    //var depth1Str      = $depth1Obj.text().trim();
    var depth1Str = $.trim($depth1Obj.text());

    if (page < 28) {
        // 표지/내지전체 평량 200g 이상 + 12p 이상이면 무선 가능
        if (12 <= page) {
            var arrLength = dvsArr.length;
            var flag = true;
            for (var i = 0; i < arrLength; i++) {
                var tmp = dvsArr[i];

                if (!dvsOnOff[tmp]) {
                    continue;
                }

                var prefix = getPrefix(tmp);
                var weight = $(prefix + "paper > option:selected").text()
                    .split(' ')
                    .pop();
                weight = parseInt(weight);

                if (weight < 200) {
                    flag = false;
                    break;
                }
            }

            if (flag) {
                var html = "<option value=\"중철제본\">중철제본</option>";
                html += "<option value=\"무선제본\">무선제본</option>";
                html += "<option value=\"스프링제본\">스프링제본</option>";

                $depth1Obj.html(html);

                return loadBindingDepth2("중철제본", commonDvs);
            }
        }

        // 4 ~ 28p 까지는 중철제본만
        // 무선제본 삭제
        if (depth1Str.indexOf("무선") > -1) {
            $depth1Obj.find("option[value='무선제본']").remove();
        }

        // 선택되어있던 값이 무선제본일 경우 중철제본 depth2 재검색
        if (selectedDepth1 === "무선제본") {
            var html = "<option value=\"중철제본\">중철제본</option>";
            $depth1Obj.html(html);
            return loadBindingDepth2("중철제본", commonDvs);
        }
    } else if (28 <= page && page <= 36) {
        // 28 ~ 36p 까지는 중철/무선제본 둘 다
        var html = "<option value=\"중철제본\">중철제본</option>";
        html += "<option value=\"무선제본\">무선제본</option>";
        html += "<option value=\"스프링제본\">스프링제본</option>";

        $depth1Obj.html(html);

        return loadBindingDepth2("중철제본", commonDvs);
    } else if (36 < page) {
        // 36p 초과는 무선제본만 -> 중철제본 삭제
        // 중철제본 삭제
        if (depth1Str.indexOf("중철") > -1) {
            $depth1Obj.find("option[value='중철제본']").remove();
        }

        // 선택되어있던 값이 무선제본일 경우 중철제본 depth2 재검색
        if (selectedDepth1 === "중철제본") {
            var html = "<option value=\"무선제본\">무선제본</option>";
            html += "<option value=\"스프링제본\">스프링제본</option>";
            $depth1Obj.html(html);
            return loadBindingDepth2("무선제본", commonDvs);
        }
    }

    return true;
};


/**
 * @brief 종이명 변경시 인쇄방식 체크하고 종이정보 검색
 */
var changePaperSort = function (dvs, val) {
    loadPaperName(dvs, val);
};

/**
 * @param 종이변경시 후공정 제약사항 체크
 *
 * @param dvs = 제품구분값
 * @param val = 종이 맵핑코드
 */
var changePaper = function (dvs) {
    loadPaperPreview(dvs);
    reCalcAfterPrice(dvs, null);
    impressShowHide(dvs);
    changeData("all");
};

// 오시, 도무송오시 show/hide
var impressShowHide = function (dvs) {
    var prefix = getPrefix(dvs);
    var basisweight = parseInt($(prefix + "paper > option:selected").text().split(' ').pop());

    if (basisweight < 250) {
        aftRestrict.unchecked(dvs, "thomson_impression");
        $('.' + dvs + "_after_list li._thomson_impression").hide();
        $('.' + dvs + "_after_list li._impression").show();
    } else {
        aftRestrict.unchecked(dvs, "impression");
        $('.' + dvs + "_after_list li._thomson_impression").show();
        $('.' + dvs + "_after_list li._impression").hide();
    }
}

/**
 * @brief 비규격 사이즈 선택할 경우 재단사이즈 값 초기화
 *
 * @param val  = 구분값
 */
var changeSizeDvs = function (val) {
    var prefix = getPrefix(commonDvs);
    $(prefix + "similar_size").attr("divide", '1');

    // 비규격 사이즈 선택시 기본 사이즈로 데이터 변경
    if (val === "manu") {
        var str = $(prefix + "size > option:selected").text() + " 1/1 등분";

        $(prefix + "similar_size").show();
        $(prefix + "similar_size").html(str);
    } else {
        $(prefix + "similar_size").hide();
        calcSheetCount(commonDvs);
    }

    changeData("all");
};

/**
 * @brief 계산형일 때 사이즈 선택할 경우 사이즈 계열에 맞는 도수값 검색
 *
 * @param val = 구분값
 */
var changeSize = {
    "exec": function () {
        var prefix = getPrefix(commonDvs);

        if (monoYn === '1') {
            var selectAffil =
                $(prefix + "size").find("option:selected").attr("affil");
            var bindingDepth1 =
                $(prefix + "binding_depth1 > option:selected").text();

            if (affil === selectAffil) {
                loadBindingDepth2(bindingDepth1, commonDvs);
                changeData("all");
                return false;
            } else {
                affil = selectAffil;
            }

            var callback = function (result) {
                var arrLength = dvsArr.length;

                for (var i = 0; i < arrLength; i++) {
                    var pfx = getPrefix(dvsArr[i]);

                    $(pfx + "bef_tmpt").html(result.bef_tmpt);
                    $(pfx + "aft_tmpt").html(result.aft_tmpt);
                }

                loadBindingDepth2(bindingDepth1, commonDvs);
                changeData("all");
            };

            loadPrintTmptCommon.exec(commonDvs, callback);
        } else {
            loadBindingDepth2(bindingDepth1, commonDvs);
            changeData("all");
        }
    }
};

/**
 * @brief 수량변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeAmt = function () {
    var arrLength = dvsArr.length;
    reCalcAfterPrice(commonDvs, null);

    for (var i = 0; i < arrLength; i++) {
        var tmp = dvsArr[i];

        if (!dvsOnOff[tmp]) {
            continue;
        }

        reCalcAfterPrice(dvsArr[i], null);
    }
    changeData("all");
};

/**
 * @brief 페이지 변경시 제본가격 등 재계산
 *
 * @param dvs = 제품 구분값
 * @param val = 페이지
 */
var changePage = function (dvs, val) {
    //if (chkBookletBinding(calcBindingPrice.getPage())) {
    //    reCalcAfterPrice(commonDvs, null);
    //}
    //reCalcAfterPrice(dvs, null);
    changeData("all");
};

/**
 * @brief 가격 구성요소 셀렉트박스 변경시 변경된 정보로 가격 검색
 *
 * @param dvs = 범위 구분
 */
var changeData = function (dvs) {
    if(dvs == null) dvs = 'all';
    monoYn = $("#cover_mono_yn").val();

    var data = {
        "dvs": "ad",
        "cate_sortcode": sortcode,
        "amt": $("#ad_amt").val(),
        "amt_unit": amtUnit,
        "size": $("#ad_size").val(),
        "cut_wid_size": $("#ad_cut_wid_size").val(),
        "cut_vert_size": $("#ad_cut_vert_size").val(),
        "pos_num": $("#ad_size > option:selected").attr("pos_num"),
        "affil": affil,
        "binding_mpcode": $("#ad_binding_val").val(),
        "binding_depth1": $("#ad_binding_depth1").val()
    };

    var arrLength = dvsArr.length;

    for (var i = 0; i < arrLength; i++) {
        var tmp = dvsArr[i];
        var pfx = getPrefix(tmp);
        var page = $(pfx + "page").val();

        if (!dvsOnOff[tmp] || page === '0') {
            continue;
        }

        tmp += '_';

        data[tmp + "paper"] = $(pfx + "paper").val();
        data[tmp + "bef_tmpt"] = $(pfx + "bef_tmpt").val();
        data[tmp + "bef_add_tmpt"] = '';
        data[tmp + "aft_tmpt"] = $(pfx + "aft_tmpt").val();
        data[tmp + "aft_add_tmpt"] = '';
        data[tmp + "bef_tmpt_name"] =
            $(pfx + "bef_tmpt > option:selected").text();
        data[tmp + "bef_add_tmpt_name"] = '';
        data[tmp + "aft_tmpt_name"] =
            $(pfx + "aft_tmpt > option:selected").text();
        data[tmp + "aft_add_tmpt_name"] = '';
        data[tmp + "print_purp"] = $(pfx + "print_purp").val();
        data[tmp + "page_info"] = page;

        var j = 0;
        $("input[type='checkbox'][name='"+tmp+"chk_after[]']").each(function () {
            if ($(this).prop("checked") === false) {
                return true;
            }

            var aft = $(this).attr("aft");
            var aft_depth = $("#" + tmp + aft).val() == undefined ? "" : $("#" + tmp + aft).val();
            var aft_depth1 = $("#" + tmp + aft + "_1").val() == undefined ? "" : $("#" + tmp + aft + "_1").val();
            var aft_depth2 = $("#" + tmp  + aft + "_2").val() == undefined ? "" : $("#" + tmp + aft + "_2").val();
            var aft_depth1_val = $("#" + tmp  + aft + "_val").val() == undefined ? "" : $("#" + tmp + aft + "_val").val();
            var aft_depth1_vh = $("#" + tmp  + aft + "_vh").val() == undefined ? "" : $("#" + tmp + aft + "_vh").val();
            var aft_depth1_cnt = $("#" + tmp + aft + "_cnt").val() == undefined ? "" : $("#" + tmp + aft + "_cnt").val();
            var aft_depth1_dvs = $("#" + tmp + aft + "_dvs_1").val() == undefined ? "" : $("#" + tmp + aft + "_dvs_1").val();
            var aft_depth2_dvs = $("#" + tmp + aft + "_dvs_2").val() == undefined ? "" : $("#" + tmp + aft + "_dvs_2").val();
            var aft_depth1_wid = $("#" + tmp + aft + "_wid_1").val() == undefined ? "" : $("#" + tmp + aft + "_wid_1").val();
            var aft_depth1_vert = $("#" + tmp + aft + "_vert_1").val() == undefined ? "" : $("#" + tmp + aft + "_vert_1").val();
            var aft_depth2_wid = $("#" + tmp + aft + "_wid_2").val() == undefined ? "" : $("#" + tmp + aft + "_wid_2").val();
            var aft_depth2_vert = $("#" + tmp + aft + "_vert_2").val() == undefined ? "" : $("#" + tmp + aft + "_vert_2").val();
            var selector = "input[name='" +
                tmp + aft + '_' +
                aft_depth1_cnt +
                "_val']:checked";

            var mpcode = $(selector).val();

            if (mpcode != undefined) aft_depth1_val = mpcode;
            if (j != 0) {
                data[tmp + "after_name"] += "|";
                data[tmp + "aft_depth"] += "|";
                data[tmp + "aft_depth1"] += "|";
                data[tmp + "aft_depth2"] += "|";
                data[tmp + "aft_depth1_val"] += "|";
                data[tmp + "aft_depth1_vh"] += "|";
                data[tmp + "aft_depth1_cnt"] += "|";
                data[tmp + "aft_depth1_dvs"] += "|";
                data[tmp + "aft_depth2_dvs"] += "|";
                data[tmp + "aft_depth1_wid"] += "|";
                data[tmp + "aft_depth1_vert"] += "|";
                data[tmp + "aft_depth2_wid"] += "|";
                data[tmp + "aft_depth2_vert"] += "|";
            } else {
                data[tmp + "after_name"] = "";
                data[tmp + "aft_depth"] = "";
                data[tmp + "aft_depth1"] = "";
                data[tmp + "aft_depth2"] = "";
                data[tmp + "aft_depth1_val"] = "";
                data[tmp + "aft_depth1_vh"] = "";
                data[tmp + "aft_depth1_cnt"] = "";
                data[tmp + "aft_depth1_dvs"] = "";
                data[tmp + "aft_depth2_dvs"] = "";
                data[tmp + "aft_depth1_wid"] = "";
                data[tmp + "aft_depth1_vert"] = "";
                data[tmp + "aft_depth2_wid"] = "";
                data[tmp + "aft_depth2_vert"] = "";
            }
            data[tmp + "aft_depth1"] += aft_depth1;
            data[tmp + "aft_depth2"] += aft_depth2;
            data[tmp + "aft_depth1_val"] += aft_depth1_val;
            data[tmp + "aft_depth1_vh"] += aft_depth1_vh;
            data[tmp + "aft_depth1_cnt"] += aft_depth1_cnt;
            data[tmp + "aft_depth1_dvs"] += aft_depth1_dvs;
            data[tmp + "aft_depth2_dvs"] += aft_depth2_dvs;
            data[tmp + "aft_depth1_wid"] += aft_depth1_wid;
            data[tmp + "aft_depth1_vert"] += aft_depth1_vert;
            data[tmp + "aft_depth2_wid"] += aft_depth2_wid;
            data[tmp + "aft_depth2_vert"] += aft_depth2_vert;
            if (aft == "foil") {
                data[tmp + "aft_depth"] += $("#" + tmp + aft + "_depth").val();
                data[tmp + "after_name"] += $("#" + tmp + aft + "_name").val();
            } else {
                data[tmp + "after_name"] += aft;
                data[tmp + "aft_depth"] += aft_depth;
            }

            $("#"+tmp+"after_name").val(data[tmp+"after_name"]);
            $("#"+tmp+"aft_depth").val(data[tmp+"aft_depth"]);
            $("#"+tmp+"aft_depth1").val(data[tmp+"aft_depth1"]);
            $("#"+tmp+"aft_depth2").val(data[tmp+ "aft_depth2"]);
            $("#"+tmp+"aft_depth1_val").val(data[tmp+ "aft_depth1_val"]);
            $("#"+tmp+"aft_depth1_vh").val(data[tmp+ "aft_depth1_vh"]);
            $("#"+tmp+"aft_depth1_cnt").val(data[tmp+ "aft_depth1_cnt"]);
            $("#"+tmp+"aft_depth1_dvs").val(data[tmp+ "aft_depth1_dvs"]);
            $("#"+tmp+"aft_depth2_dvs").val(data[tmp+ "aft_depth2_dvs"]);
            $("#"+tmp+"aft_depth1_wid").val(data[tmp+ "aft_depth1_wid"]);
            $("#"+tmp+"aft_depth1_vert").val(data[tmp+ "aft_depth1_vert"]);
            $("#"+tmp+"aft_depth2_wid").val(data[tmp+ "aft_depth2_wid"]);
            $("#"+tmp+"aft_depth2_vert").val(data[tmp+ "aft_depth2_vert"]);

            j++;
        });
    }

    $("#ad_affil").val(data.affil);
    $("#pos_num").val(data.pos_num);
    $("#binding_mpcode").val(data.binding_mpcode);
    $("#cover_page_info").val(data.cover_page_info);
    $("#inner1_page_info").val(data.inner1_page_info);
    $("#inner2_page_info").val(data.inner2_page_info);
    $("#inner3_page_info").val(data.inner3_page_info);
    loadPrdtPrice.data = data;
    loadPrdtPrice.exec(dvs);
};

var loadPrintTmpt = function (dvs, callback) {
    if (checkBlank(callback)) {
        callback = function (result) {
            $("#" + dvs + "_bef_tmpt").html(result.bef_tmpt);
            $("#" + dvs + "_aft_tmpt").html(result.aft_tmpt);

            changeData('all');
        };
    }

    loadPrintTmptCommon.exec(dvs, callback);
};

/**
 * @brief 상품 가격정보 검색
 */
var loadPrdtPrice = {
    "data": {},
    "price": {
        "cover": null,
        "inner1": null,
        "inner2": null,
        "inner3": null,
    },
    "exec": function (dvs) {
        var url = null;
        if (monoYn === '0') {
            url = "/ajax/product/load_price.php";
        } else {
            url = "/ajax/product/load_price.php";
        }
        var callback = function (result) {
            if (dvs === "all") {
                if (parseInt(result[commonDvs]["cover"].sell_price) === 0) {
                    return alertReturnFalse("해당하는 가격이 존재하지 않습니다.\n관리자에게 문의하세요.");
                }

                loadPrdtPrice.price = result[commonDvs];

                var arrLength = dvsArr.length;
                $("#ad_binding_price").val(result[commonDvs].binding_price);

                for (var i = 0; i < arrLength; i++) {
                    var tmp = dvsArr[i];
                    var pfx = getPrefix(tmp);
                    var priceArr = loadPrdtPrice.price[tmp];

                    $(pfx + "paper_price").val(priceArr.paper);
                    $(pfx + "output_price").val(priceArr.output);
                    $(pfx + "print_price").val(priceArr.print);
                    $(pfx + "sell_price").val(priceArr.sell_price);
                    $(pfx + "binding_price").val(priceArr.sell_price);

                    $("input[name='"+tmp+"_chk_after[]']:checked").each(function () {
                        var $obj = $(this);
                        var aft = $obj.attr('id').replace(tmp + "_", "");
                        if (aft == "foil") {
                            var totalAfterPrice = 0;
                            afterPrice = loadPrdtPrice.price[tmp + "_foil1"];
                            if (afterPrice) {
                                setAfterPrice(tmp, "foil1", afterPrice);
                                totalAfterPrice += parseInt(afterPrice);
                            }
                            afterPrice = loadPrdtPrice.price[tmp + "_foil2"];
                            if (afterPrice) {
                                setAfterPrice(tmp, "foil2", afterPrice);
                                totalAfterPrice += parseInt(afterPrice);
                            }
                            afterPrice = loadPrdtPrice.price[tmp + "_foil3"];
                            if (afterPrice) {
                                setAfterPrice(tmp, "foil3", afterPrice);
                                totalAfterPrice += parseInt(afterPrice);
                            }
                            setAfterPrice(tmp, "foil", totalAfterPrice);
                        } else {
                            afterPrice = loadPrdtPrice.price[tmp + "_" + aft];
                            setAfterPrice(tmp, aft, afterPrice);
                        }
                    });
                }
            } else {
                if (parseInt(result[commonDvs][dvs].sell_price) === 0) {
                    return alertReturnFalse("해당하는 가격이 존재하지 않습니다.\n관리자에게 문의하세요.");
                }

                loadPrdtPrice.price[dvs] = result[commonDvs][dvs];

                var pfx = getPrefix(dvs);
                var priceArr = loadPrdtPrice.price[dvs];
                $(pfx + "paper_price").val(priceArr.paper);
                $(pfx + "output_price").val(priceArr.output);
                $(pfx + "print_price").val(priceArr.print);
                $(pfx + "sell_price").val(priceArr.sell_price);
            }

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
    var i2Flag = dvsOnOff[dvsArr[2]];
    var i3Flag = dvsOnOff[dvsArr[3]];

    // 정상판매가
    var coverSellPrice = parseInt($("#cover_sell_price").val());
    var inner1SellPrice = parseInt($("#inner1_sell_price").val());
    var inner2SellPrice = 0;
    if (i2Flag) {
        inner2SellPrice = parseInt($("#inner2_sell_price").val());
    }
    var inner3SellPrice = 0;
    if (i3Flag) {
        inner3SellPrice = parseInt($("#inner3_sell_price").val());
    }

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
    var bindingPrice = parseInt($("#ad_binding_price").val());
    $("#esti_binding").html(Math.ceil(bindingPrice / 1.1).format());
    var coverAfterPrice = ceilVal((getSumAfterPrice(dvsArr[0]) *  1.1) / 10) * 10;
    var inner1AfterPrice = ceilVal((getSumAfterPrice(dvsArr[1]) *  1.1) / 10) * 10;
    var inner2AfterPrice = ceilVal((getSumAfterPrice(dvsArr[2]) *  1.1) / 10) * 10;
    if (i2Flag) {
        getSumAfterPrice(dvsArr[2]);
    }
    var inner3AfterPrice = 0;
    if (i3Flag) {
        getSumAfterPrice(dvsArr[3]);
    }
    var sumAfterPrice = bindingPrice + coverAfterPrice + inner1AfterPrice +
        inner2AfterPrice + inner3AfterPrice;
    sumAfterPrice = ceilVal(sumAfterPrice);

    var sellPrice = coverSellPrice + inner1SellPrice +
        inner2SellPrice + inner3SellPrice;
    sellPrice = ceilVal(sellPrice) + sumAfterPrice;

    // 견적서 종이비 계산
    var paper = 0;
    if (monoYn === '1') {
        var coverPaper = parseInt($("#cover_paper_price").val());
        var inner1Paper = parseInt($("#inner1_paper_price").val());
        var inner2Paper = 0;
        if (i2Flag) {
            inner2Paper = parseInt($("#inner2_paper_price").val());
        }
        var inner3Paper = 0;
        if (i3Flag) {
            inner3Paper = parseInt($("#inner3_paper_price").val());
        }
        paper = ceilVal(coverPaper + inner1Paper +
            inner2Paper + inner3Paper);
    }

    // 견적서 출력비 계산
    var output = 0;
    if (monoYn === '1') {
        var coverOutput = parseInt($("#cover_output_price").val());
        var inner1Output = parseInt($("#inner1_output_price").val());
        var inner2Output = 0;
        if (i2Flag) {
            inner2Output = parseInt($("#inner2_output_price").val());
        }
        var inner3Output = 0;
        if (i3Flag) {
            inner3Output = parseInt($("#inner3_output_price").val());
        }
        output = ceilVal(coverOutput + inner1Output +
            inner2Output + inner3Output);
    }

    // 견적서 인쇄비 계산
    var print = loadPrdtPrice.price.print;
    if (monoYn === '1') {
        var coverPrint = parseInt($("#cover_print_price").val());
        var inner1Print = parseInt($("#inner1_print_price").val());
        var inner2Print = 0;
        if (i2Flag) {
            inner2Print = parseInt($("#inner2_print_price").val());
        }
        var inner3Print = 0;
        if (i3Flag) {
            inner3Print = parseInt($("#inner3_print_price").val());
        }
        print = ceilVal(coverPrint + inner1Print +
            inner2Print + inner3Print);
    }
    var cut = 0;
    // 견적서 후공정비 계산
    var after = sumAfterPrice;
    // 견적서 후공정비 계산(세금 미포함)
    var esAfter = ceilVal(sumAfterPrice / 1.1);
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
    $("#sell_price").attr("val", (sellPrice));
    $("#sell_price").html((sellPrice).format() + ' 원');
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
        "count": 1,
        "gradeSaleRate": gradeSale,
        "sellPrice": sellPrice
    };

    loadReleaseExpect();
    loadDlvrPriceExpect(flag);
    changeQuickEsti(param);
};

/**
 * @brief 장바구니로 이동
 */
var setSubmitParam = function () {
    if ($("#il").val() === "0") {
        $("#cart_flag").val('Y');
        $("#purlogin").val("1");
        showLoginBox();
        return false;
    }

    if (!chkSubmitValidation()) {
        return false;
    }

    setAddOptInfo();

    var optPrice = getSumOptPrice();

    var amt = $("#ad_amt").val();
    var sizeName = $("#ad_size > option:selected").text();
    var posNum = $("#ad_size > option:selected").attr("pos_num");
    var stanMpcode = $("#ad_size").val();
    var sellPrice = $("#sell_price").attr("val");
    var salePrice = $("#sale_price").attr("val");
    var cutWid = $("#ad_cut_wid_size").val();
    var cutVert = $("#ad_cut_vert_size").val();
    var workWid = $("#ad_work_wid_size").val();
    var workVert = $("#ad_work_vert_size").val();

    $frm = $("#frm");

    var arrLength = dvsArr.length;
    var addAfterPrice = 0;
    for (var i = 0; i < arrLength; i++) {
        var dvs = dvsArr[i];
        var prefix = getPrefix(dvs);

        if (!dvsOnOff[dvs]) {
            continue;
        }

        if ($(prefix + "page").val() === '0') {
            continue;
        }

        var afterPrice = getSumAfterPrice(dvs);
        var ret = makeAfterInfo.all(dvs);
        if (ret === false) {
            return false;
        }
        addAfterPrice += afterPrice;

        var paperPrice = $(prefix + "paper_price").val();
        var outputPrice = $(prefix + "output_price").val();
        var printPrice = $(prefix + "print_price").val();

        var paperName = $(prefix + "paper_name").val();
        paperName += ' ' + $(prefix + "paper > option:selected").text();
        var befTmptName = $(prefix + "bef_tmpt > option:selected").text();
        var aftTmptName = $(prefix + "aft_tmpt > option:selected").text();

        $frm.find("input[name='" + dvs + "_cate_sortcode']").val(sortcode);
        $frm.find("input[name='" + dvs + "_cate_name']").val(cateName);
        $frm.find("input[name='" + dvs + "_amt']").val(amt);
        $frm.find("input[name='" + dvs + "_amt_unit']").val(amtUnit);
        $frm.find("input[name='" + dvs + "_size']").val(stanMpcode);
        $frm.find("input[name='" + dvs + "_cut_wid_size']").val(cutWid);
        $frm.find("input[name='" + dvs + "_cut_vert_size']").val(cutVert);
        $frm.find("input[name='" + dvs + "_work_wid_size']").val(workWid);
        $frm.find("input[name='" + dvs + "_work_vert_size']").val(workVert);
        $frm.find("input[name='" + dvs + "_paper_name']").val(paperName);
        $frm.find("input[name='" + dvs + "_size_name']").val(sizeName);
        $frm.find("input[name='" + dvs + "_pos_num']").val(posNum);
        $frm.find("input[name='" + dvs + "_bef_tmpt_name']").val(befTmptName);
        $frm.find("input[name='" + dvs + "_aft_tmpt_name']").val(aftTmptName);
        $frm.find("input[name='" + dvs + "_after_price']").val(afterPrice);

        var temp = parseInt($(prefix + "sell_price").val()) + afterPrice;
        $(prefix + "sell_price").val(temp);
    }

    addAfterPrice += getSumAfterPrice(commonDvs)

    // 공통
    $("#prdt_dvs").val(getPrdtDvs());

    $("#ad_order_detail").val(makeOrderDetail());
    $frm.find("input[name='opt_price']").val(optPrice);
    $frm.find("input[name='ad_amt_unit']").val(amtUnit);
    $frm.find("input[name='ad_sell_price']").val(sellPrice);
    $frm.find("input[name='ad_sale_price']").val(salePrice);
    $frm.find("input[name='ad_after_price']").val(addAfterPrice);
    $frm.find("input[name='ad_sheet_count']").val(getPaperRealPrintAmt(commonDvs));
    $frm.find("input[name='ad_size_name']").val(sizeName);

    //return false;
    return true;
};

/**
 * @brief submit 전에 validation 체크
 */
var chkSubmitValidation = function () {
    // 표지 평량이 내지 평량보다 커야됨
    var arrLength = dvsArr.length;
    var coverBasisweight = 0;
    for (var i = 0; i < arrLength; i++) {
        var dvs = dvsArr[i];

        if (!dvsOnOff[dvs]) {
            continue;
        }

        var prefix = getPrefix(dvs);

        var paper = $(prefix + "paper > option:selected").text().split(' ');
        var basisweight = parseInt(paper[paper.length - 1]);

        if (dvs === "cover") {
            coverBasisweight = basisweight;
        }

        if (basisweight > coverBasisweight) {
            return alertReturnFalse("표지의 평량이 내지보다 낮습니다.");
        }
    }

    return true;
};

/**
 * @brief 제품구분값 생성
 */
var getPrdtDvs = function () {
    var arrLength = dvsArr.length;
    var prdtDvs = '';

    for (var i = 0; i < arrLength; i++) {
        var dvs = dvsArr[i];
        var prefix = getPrefix(dvs);

        if (!dvsOnOff[dvs]) {
            continue;
        }

        if ($(prefix + "page").val() === '0') {
            continue;
        }

        prdtDvs += dvs + '|';
    }

    return prdtDvs.substr(0, prdtDvs.length - 1);
};

/**
 * @brief 주문내역 생성
 *
 * @detail 일반지 카다로그 / A4 / 50부 / 표지 : 모조지 백색 70g, 전면 - 4도 / 후면 - 없음, 2p / 내지1 : ~이하동일~
 *
 * @return 주문내역
 */
var makeOrderDetail = function () {
    var ret = '';

    var arrLength = dvsArr.length;
    var prdtDvs = '';

    // 공통카테고리
    ret += cateName;
    ret += " / ";
    ret += $("#ad_size > option:selected").text();
    ret += ", ";
    ret += $("#ad_amt > option:selected").text();

    for (var i = 0; i < arrLength; i++) {
        var dvs = dvsArr[i];
        var prefix = getPrefix(dvs);
        var ko = dvsKo[dvs];

        if (!dvsOnOff[dvs]) {
            continue;
        }

        if ($(prefix + "page").val() === '0') {
            continue;
        }

        ret += "\n/ " + ko + " : ";
        ret += $(prefix + "paper_name").val() + ' ';
        ret += $(prefix + "paper > option:selected").text();
        ret += ", 전면 - ";
        ret += $(prefix + "bef_tmpt > option:selected").text();
        ret += ", 후면 - ";
        ret += $(prefix + "aft_tmpt > option:selected").text();
        ret += ", ";
        ret += $(prefix + "page > option:selected").text();
    }

    return ret;
};

/**
 * @brief 견적서 팝업 본문정보 생성
 */
var makeEstiPopInfo = {
    "data": null,
    "exec": function () {
        var prefix = getPrefix(commonDvs);

        var size = $.trim($(prefix + "size > option:selected").text());
        var amt = $.trim($(prefix + "amt").val());
        var count = $.trim($("#esti_count").text());
        var afterDet = [];
        afterDet[0] = $.trim($(".cover_overview").text());
        afterDet[1] = $.trim($(".inner1_overview").text());
        afterDet[2] = $.trim($(".inner2_overview").text());
        afterDet[3] = $.trim($(".inner3_overview").text());

        var data = {
            "cate_name": [
                cateName
            ],
            "paper": [],
            "size": [
                size
            ],
            "tmpt": [],
            "amt": [
                amt
            ],
            "amt_unit": [
                amtUnit
            ],
            "page": [],
            "count": [
                count
            ],
            "after": [
                // 제본 추가하기
            ],
            "after_det": afterDet,
            "booklet": 'Y'
        };

        data = getEstiPopData(data);

        var prdtDvsArr = getPrdtDvs().split('|');
        var prdtDvsArrLen = prdtDvsArr.length;

        for (var i = 0; i < prdtDvsArrLen; i++) {
            var dvs = prdtDvsArr[i];
            prefix = getPrefix(dvs);

            var paper = $.trim($(prefix + "paper > option:selected").text());
            var page = $.trim($(prefix + "page").val());
            var befTmpt = $.trim($(prefix + "bef_tmpt > option:selected").text());
            var aftTmpt = $.trim($(prefix + "aft_tmpt > option:selected").text());
            var after = '';

            if ($(prefix + "paper_name").length > 0) {
                paper = $(prefix + "paper_name").val() + ' ' + paper;
            }

            var tmpt = "전면 : " + befTmpt + " / 후면 : " + aftTmpt;

            $("." + dvs + "_after .overview ul li").each(function () {
                after += $(this).text();
                after += ', ';
            });

            after = after.substr(0, after.length - 2);

            data.paper.push(paper);
            data.page.push(page);
            data.tmpt.push(tmpt);
            data.after.push(after);
        }

        this.data = data;
    }
};

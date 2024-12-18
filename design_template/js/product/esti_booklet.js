var cateName = null;
var commonDvs = null;
var sortcode = null;
var manual = "<option value=\"-1\">직접선택</option>";

var dvsOnOff = {
    "cover": true,
    "inner1": true,
    "inner2": false,
    "inner3": false,
    "exec": function (dvs, flag) {
        dvsOnOff[dvs] = flag;
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
    sortcode = $("#esti_cate_sortcode").val();
    cateName = $("#esti_cate_sortcode").find("option:selected").text();
    commonDvs = $("#common_prdt_dvs").val();

    $("#cover_cate_sortcode").val(sortcode);
    $("#inner1_cate_sortcode").val(sortcode);
    $("#inner2_cate_sortcode").val(sortcode);
    $("#inner3_cate_sortcode").val(sortcode);

    var today = new Date();
    var year = today.getFullYear();
    var month = today.getMonth() + 1;
    var date = today.getDate();
    $("#title").val(year + '-' + month + '-' + date + " 별도견적");

    // 내지 타공삭제
    $("li._punching").each(function (i) {
        if (i === 0) {
            return true;
        }
        $(this).remove();
    });
    $("div._punching").each(function (i) {
        if (i === 0) {
            return true;
        }
        $(this).remove();
    });

    if($("#is_admin").val() == 1) {
        $("#user_mode").css('display','none');
    } else {
        $("#admin_mode").css('display','none');
    }
});

/**
 * @brief 종이명 변경시 인쇄방식 체크하고 종이정보 검색
 */
var changePaperSort = function (dvs, val) {
    var prefix = getPrefix(dvs);
    if (val === "-1") {
        $(prefix + "ext_paper").show();
        $(prefix + "paper_name").hide();
        $(prefix + "paper").hide();

        $(prefix + "paper_name").html(manual);
        $(prefix + "paper").html(manual);
        return false;
    }
    $(prefix + "ext_paper").hide();
    $(prefix + "paper_name").show();
    $(prefix + "paper").show();

    var callback = function (result) {
        var prefix = getPrefix(dvs);
        $(prefix + "paper_name").html(manual + result);

        changePaperName(dvs, $(prefix + "paper_name").val());
    };

    loadPaperName(dvs, val, callback);
};

/**
 * @brief 종이명 변경시 인쇄방식 체크하고 종이정보 검색
 */
var changePaperName = function (dvs, val) {
    var prefix = getPrefix(dvs);

    if (val === "-1") {
        $(prefix + "paper").html(manual);
        return false;
    }

    var callback = function (result) {
        var prefix = getPrefix(dvs);
        $(prefix + "paper").html(manual + result);

        loadPaperPreview(dvs);
        loadPaperDscr.exec(dvs, $(prefix + "paper").val());
        reCalcAfterPrice(dvs, null);
    };

    loadPaperInfo(dvs, val, $(prefix + "paper_sort").val(), callback);
};

var changeSpColor = function (typ, dvs, val) {
    var id = getPrefix(typ) + "ext_" + dvs + "_tmpt";
    $(id).val('');

    if (parseInt(val) < 0) {
        $(id).hide();
    } else {
        $(id).show();
    }
};

/**
 * @brief 견적 정보 파라미터 생성
 */
var setSubmitParam = function () {
    var amt = $("#esti_amt").val();
    var amtUnit = $("#esti_amt").attr("amt_unit");
    var sizeName = $("#esti_size > option:selected").text();
    var posNum = $("#esti_size > option:selected").attr("pos_num");
    var cutWid = $("#esti_cut_wid_size").val();
    var cutVert = $("#esti_cut_vert_size").val();
    var workWid = $("#esti_work_wid_size").val();
    var workVert = $("#esti_work_vert_size").val();

    if ($(prefix + "size_dvs").val() === "manu") {
        sizeName = "비규격(" + cutWid + '*' + cutVert + ')';
    }

    var prefix = getPrefix(commonDvs);
    var amtInfo = $(prefix + "amt").val() + amtUnit;
    /*
    if (!checkBlank($.trim($(prefix + "ext_amt").val()))) {
        amtInfo += " [" + $(prefix + "ext_amt").val() + ']';
    }
    */

    $frm = $("#frm");

    var arrLength = dvsArr.length;
    for (var i = 0; i < arrLength; i++) {
        var dvs = dvsArr[i];
        prefix = getPrefix(dvs);

        if (!dvsOnOff[dvs]) {
            continue;
        }
        if ($(prefix + "page").val() === '0') {
            continue;
        }
        var ret = makeAfterInfo.all(dvs);
        if (ret === false) {
            return false;
        }

        var paperInfo = makePaperInfo(dvs);
        var tmptInfoArr = makeTmptInfo(dvs);
        var printPurpInfo = makeInfo(dvs, "print_purp");
        var pageInfo = dvsKo[dvs] + ' ' + $(prefix + "page").val() + 'p';
        /*
        if (!checkBlank($.trim($(prefix + "ext_page").val()))) {
            pageInfo += " [" + $(prefix + "ext_page").val() + ']';
        }
        */
        var afterInfo = makeAfterSummary(dvs);

        $frm.find("input[name='" + dvs + "_cate_sortcode']").val(sortcode);
        $frm.find("input[name='" + dvs + "_amt_unit']").val(amtUnit);
        $frm.find("input[name='" + dvs + "_paper_info']").val(paperInfo);
        $frm.find("input[name='" + dvs + "_bef_tmpt_info']").val(tmptInfoArr.bef);
        $frm.find("input[name='" + dvs + "_aft_tmpt_info']").val(tmptInfoArr.aft);
        $frm.find("input[name='" + dvs + "_print_purp_info']").val(printPurpInfo);
        $frm.find("input[name='" + dvs + "_amt_info']").val(amtInfo);
        $frm.find("input[name='" + dvs + "_size_name']").val(sizeName);
        $frm.find("input[name='" + dvs + "_cut_wid_size']").val(cutWid);
        $frm.find("input[name='" + dvs + "_cut_vert_size']").val(cutVert);
        $frm.find("input[name='" + dvs + "_work_wid_size']").val(workWid);
        $frm.find("input[name='" + dvs + "_work_vert_size']").val(workVert);
        $frm.find("input[name='" + dvs + "_page_info']").val(pageInfo);
        $frm.find("input[name='" + dvs + "_after_info']").val(afterInfo);
    }

    // 공통
    $("#prdt_dvs").val(getPrdtDvs());
    $("#esti_detail").val(makeEstiDetail());

    $("#esti_amt_unit").val(amtUnit);
    $("#esti_sheet_count").val(getPaperRealPrintAmt(commonDvs));

    if (typeof commonObj !== "undefined") {
        $("#file_seqno").val(commonObj.fileSeq);
    }

    return true;
};

/**
 * @brief 주문내역 생성
 *
 * @detail 일반지 카다로그 / A4 / 50부 / 표지 : 모조지 백색 70g, 전면 - 4도 / 후면 - 없음, 2p / 내지1 : ~이하동일~
 *
 * @return 주문내역
 */
var makeEstiDetail = function () {
    var ret = '';

    var arrLength = dvsArr.length;
    var prdtDvs = '';

    // 공통카테고리
    ret += cateName;
    ret += " / ";
    ret += $("#esti_size > option:selected").text();
    ret += " / ";
    ret += $("#esti_amt > option:selected").text();

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

        ret += " / " + ko + " : ";
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
 * @brief 견적값 입력
 *
 * @param flag = 파일업로드 여부
 */
var insertEsti = function (flag) {
    if ($("#il").val() === "0") {
        $("#cart_flag").val('Y');
        $("#purlogin").val("1");
        showLoginBox();
        return false;
    }
    if (checkBlank($.trim($("#title").val()))) {
        return alertReturnFalse("제목을 입력해주세요.");
    }

    if (typeof commonObj === "undefined" ||
        checkBlank(commonObj.uploader.runtime)) {
        $("#frm").submit();
        return false;
    }

    if (commonObj.uploader.files.length > 0) {
        commonObj.uploader.start();
    } else {
        fileNextFunc();
    }
    return false;
};

var fileNextFunc = function () {
    setSubmitParam();
    $("#frm").submit();
};

var showCoverPageExt = function (page) {
    page = parseInt(page);

    if (page > 4) {
        $("#cover_ext_page").show();
    } else {
        $("#cover_ext_page").val('');
        $("#cover_ext_page").hide();
    }
};

var changeData = function () {};
var calcPrice = function () {};
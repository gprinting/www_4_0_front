var prdtDvs = null;
var sortcode = null;
var cateName = null;
var affil = null;
var manual = "<option value=\"-1\">직접선택</option>";

var file;
var filename = '';
var filesize = '';
var s3;

var strArray = '';
var cvt_file = '';
var filesizeH = '';
var savefile = '';
var org_file = '';

$(document).ready(function () {
    // 건수 초기화
    initCount(999, "count");

    prdtDvs = $("#prdt_dvs").val();
    sortcode = $("#esti_cate_sortcode").val();
    cateName = $("#esti_cate_sortcode > option:selected").text();
    amtUnit = $("#esti_amt").attr("amt_unit");
    affil = $("#esti_size").find("option:selected").attr("affil");

    calcRCount(prdtDvs);

    calcLaminexMaxCount();
    $("input[name='esti_chk_after[]']").removeAttr("onclick");

    var today = new Date();
    var year = today.getFullYear();
    var month = today.getMonth() + 1;
    var date = today.getDate();
    $("#title").val(year + '-' + month + '-' + date + " 별도견적");

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
    $(prefix + "ext_paper").val('');

    if (val === "-1") {
        $(prefix + "paper").closest('ul').addClass('setCustom');

        $(prefix + "paper_name").html(manual);
        $(prefix + "paper").html(manual);
        return false;
    }
    $(prefix + "paper").closest('ul').removeClass('setCustom');

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
    } else {
        $(prefix + "similar_size").hide();
        calcSheetCount(prdtDvs);
    }
};

/**
 * @brief 수량변경시 후공정 가격 재계산 및 상품가격 재검색
 */
var changeAmt = function (val) {
    calcRCount(prdtDvs);
    calcLaminexMaxCount();
};

/**
 * @brief 계산형일 때 사이즈 선택할 경우 사이즈 계열에 맞는 도수값 검색
 *
 * @param val = 구분값
 */
var changeSize = {
    "exec": function (dvs) {
        calcRCount(dvs);
    }
};

var changeSpColor = function (dvs, val) {
    var id = getPrefix(prdtDvs) + "ext_" + dvs + "_tmpt";
    if (parseInt(val) < 0) {
        $(id).closest('ul').removeClass('addCustom');
    } else {
        $(id).closest('ul').addClass('addCustom');
    }
};

/**
 * @brief 견적 정보 파라미터 생성
 */
var setSubmitParam = function () {
    var prefix = getPrefix(prdtDvs);
    var amtUnit = $(prefix + "amt").attr("amt_unit");
    var sizeName = $(prefix + "size > option:selected").text();
    var cutWid = $("#esti_cut_wid_size").val();
    var cutVert = $("#esti_cut_vert_size").val();

    if ($("#size_dvs").val() === "manu") {
        sizeName = "비규격(" + cutWid + '*' + cutVert + ')';
    }

    var amtInfo = makeInfo(prdtDvs, "amt");
    var paperInfo = makePaperInfo(prdtDvs);
    var tmptInfoArr = makeTmptInfo(prdtDvs);
    var printPurpInfo = makeInfo(prdtDvs, "print_purp");
    var stanInfo = makeStanInfo(prdtDvs);

    var estiDetail = cateName +
        " / 수량 : " + amtInfo +
        " / 종이 : " + paperInfo +
        " / 전면도수 : " + tmptInfoArr.bef +
        " / 후면도수 : " + tmptInfoArr.aft +
        " / 인쇄방식 : " + printPurpInfo +
        " / 사이즈 : " + stanInfo;

    $(prefix + "amt_unit").val(amtUnit);
    $(prefix + "size_name").val(sizeName);
    $("#esti_detail").val(estiDetail);

    $(prefix + "amt_info").val(amtInfo);
    $(prefix + "paper_info").val(paperInfo);
    $(prefix + "bef_tmpt_info").val(tmptInfoArr.bef);
    $(prefix + "aft_tmpt_info").val(tmptInfoArr.aft);
    $(prefix + "print_purp_info").val(printPurpInfo);
    $(prefix + "stan_info").val(stanInfo);
    $(prefix + "after_info").val(makeAfterSummary(prdtDvs));

    if (typeof commonObj !== "undefined") {
        $("#file_seqno").val(commonObj.fileSeq);
    }

    setAddOptInfo();

    return true;
};

/**
 * @brief 견적값 입력
 */
var insertEsti = function () {
    if ($("#il").val() === "0") {
        alert("로그인 후 확인 가능합니다.");
        return false;
    }
    if (checkBlank($.trim($("#title").val()))) {
        return alertReturnFalse("제목을 입력해주세요.");
    }

    var ret = makeAfterInfo.all(prdtDvs);

    if (ret === false) {
        return false;
    }

    if (typeof commonObj === "undefined") {
        $("#frm").submit();
        return false;
    }

    if ($("#file_name").val() != '') {
        var url = "/proc/order/upload_file_info.php";
        var data = {
            "file_name": $("#file_name").val(),
            "file_size": $("#file_size").val()
        };
        var callback = function (result) {
            var asd = JSON.parse(result);
            $("#file_seqno").val(asd.file_seqno);
            openFileProgress();
            uploadToS3(file,asd.file_path, asd.file_seqno);
        };

        ajaxCall(url, "text", data, callback);
        //commonObj.uploader.start();
    } else {
        fileNextFunc();
    }
    return false;
};

/**
 * @brief 견적값 입력
 */
var insertEstiAdmin = function () {
    if ($("#il").val() === "0") {
        alert("로그인 후 확인 가능합니다.");
        return false;
    }
    if (checkBlank($.trim($("#title").val()))) {
        return alertReturnFalse("제목을 입력해주세요.");
    }

    var ret = makeAfterInfo.all(prdtDvs);

    if (ret === false) {
        return false;
    }

    if ($("#file_name").val() == '') {
        fileNextFunc();
        return false;
    }

    if ($("#file_name").val() != '') {
        var url = "/proc/order/upload_esti_file_info.php";
        var data = {
            "file_name": $("#file_name").val(),
            "file_size": $("#file_size").val()
        };
        var callback = function (result) {
            var asd = JSON.parse(result);
            $("#file_seqno").val(asd.file_seqno);
            openFileProgress();
            uploadToS3(file,asd.file_path, asd.file_seqno);
        };

        ajaxCall(url, "text", data, callback);
        //commonObj.uploader.start();
    } else {
        fileNextFunc();
    }
    return false;
};

var fileNextFunc = function () {
    setSubmitParam();

    var memo = document.createElement("input");
    $(memo).attr({
            "name": "cust_memo",
            "type": "hidden"
        })
        .val($("#cust_memo").val());
    $("#frm").append($(memo));

    $("#frm").submit();
};

var changeData = function () {};
var calcPrice = function () {};
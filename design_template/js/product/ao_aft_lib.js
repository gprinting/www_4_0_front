/**
 * @brief 후공정 가격검색을 위해 후공정명을
 * 공통으로 사용하는 영어명으로 변경하고 실검색 함수로 전달
 *
 * @param obj = 체크확인용
 * @param val = 후공정명
 */
var loadAoAfterPrice = {
    "aft": null,
    "exec": function (checked, val, dvs) {
        var aft = afterKo2En(val);
        this.aft = aft;

        if (!checked) {
            calcPrice();
            quickEstiAftHide(dvs, aft);
            return false;
        }

        getAoAfterPrice.common(aft, dvs);
    }
};

/**
 * @brief 실사 후공정 가격처리
 * 실제로 수행하는 함수는 각 페이지별 자바스크립트 파일에
 * 존재하는 공통된 이름을 가진 함수를 호출한다
 *
 * 각 상품별로 처리 로직이 다를 수 있기 때문에 별도로 처리한다
 *
 * @param aft = 후공정 구분값
 * @param mpcode = 맵핑코드
 */
var getAoAfterPrice = {
    "basicCallback": function (aft, dvs) {
        // 기본후공정일 때 공통콜백
        var callback = function (result) {
            var price = parseInt(result.price);

            setAfterPrice(dvs, aft, price);

            if (price > 0) {
                setEstiAfterPrice(aft, price);
            } else {
                quickEstiAftHide(dvs, aft);
            }

            calcPrice(false);
        };

        return callback;
    },
    "load": function (aft, data, dvs, callback) {
        // 가격이 없을경우 검색하는 함수
        var url = "/ajax/product/load_ao_after_price.php";

        ajaxCall(url, "json", data, callback);
    },
    "common": function (aft, dvs) {
        var prefix = getPrefix(dvs);

        if (!$(prefix + aft).prop("checked")) {
            return false;
        }

        //getAoAfterPrice[aft](aft, dvs);
        changeData();
    },
    "heat_cutting": function (aft, dvs) {
        getHeatCuttingPrice(aft, dvs);
    },
    "add_dotline": function (aft, dvs) {
        getAddDotlinePrice(aft, dvs);
    },
    "dotline": function (aft, dvs) {
        getDotlinePrice(aft, dvs);
    },
    "ring": function (aft, dvs) {
        getRingPrice(aft, dvs);
    },
    "scroll": function (aft, dvs) {
        getScrollPrice(aft, dvs);
    },
    "cool_coating": function (aft, dvs) {
        getCoolCoatingPrice(aft, dvs);
    },
    "punching": function (aft, dvs) {
        getPunchingPrice(aft, dvs);
    },
    "cutting": function (aft, dvs) {
        getCuttingPrice(aft, dvs);
    }
};

/**
 * @brief 후공정 가격 초기화
 */
var initAfterPrice = function () {
    getAoAfterPrice.price = {
        "heat_cutting": null,
        "add_dotline": null,
        "dotline": null,
        "ring": null,
        "scroll": null,
        "cool_coating": null,
        "punching": null,
        "cutting": null
    };
};

/**
 * @brief 수량 변경시 후공정 가격 재계산 함수
 *
 * @param dvs     = 제품 구분값
 * @param addFunc = 재계산 전에 추가로 실행할 함수, 없으면 null
 */
var reCalcAoAfterPrice = function (dvs, addFunc) {
    if (!checkBlank(addFunc)) {
        addFunc();
    }

    $("input[name='" + dvs + "_chk_after[]']:checked").each(function () {
        var $obj = $(this);

        loadAoAfterPrice.exec($obj.prop("checked"), $obj.val(), dvs)
    });
};

/**
 * @brief 후공정 가격 합산해서 반환
 *
 * @param dvs = 제품 구분값
 *
 * @return 합산된 후공정 가격
 */
var getSumAoAfterPrice = function (dvs) {
    var prefix = getPrefix(dvs);
    // 추가후공정 가격
    var ret = getSumAfterPrice(dvs);

    return ret;
};

/**
 * @brief 실사 후공정 하위항목 검색
 *
 * @param data     = ajaxCall에서 사용할 파라미터
 * @param callback = ajaxCall에서 사용할 callback함수
 */
var loadAoAfterDepth = function (data, callback) {
    var url = "/ajax/product/load_ao_after_depth.php";
    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 실사출력 미싱 depth2 검색
 */
var loadAoDotlineDepth2 = function (aft, val) {
    var prefix = getPrefix(prdtDvs);
    var callback = function (result) {
        $(prefix + aft + "_val").html(result);
        getAoAfterPrice.common(aft, prdtDvs);
    };

    var data = {
        "cate_sortcode": $(prefix + "cate_sortcode").val(),
        "after_name": $(prefix + aft).val(),
        "depth1": val,
        "flag": 'Y'
    };

    loadAoAfterDepth(data, callback);
};

/**
 * @brief 사방쇠고리 일 때 처리
 */
var chkRingFourDirection = function (aft, dvs, depth1) {
    var selector = "select[name='" + dvs + '_' + aft + "_dvs']";
    if (depth1.indexOf("사방") > -1) {
        $(selector).val('1').prop("disabled", true);
    } else {
        $(selector).val('-1').prop("disabled", false);
    }
};

/**
 * @brief 고리 셀렉트박스 변경시 사방부분 등 처리
 */
var changeAoAfterRing = function (aft, dvs, obj) {

    var depth1 = $(obj).find("option:selected").text();

    chkRingFourDirection(aft, dvs, depth1);

    getAoAfterPrice.common(aft, dvs);
};

/**
 * @brief 실사 후공정 정보 생성 객체
 *
 * @param dvs = 제품 구분값
 *
 * @return validation 체크 통과여부
 */
var makeAoAfterInfo = {
    "msg": '',
    "all": function (dvs) {
        var ret = true;
        var aft = null;
        var func = null;

        $("input[name='" + dvs + "_chk_after[]']").each(function () {
            if ($(this).prop("checked") === false) {
                return true;
            }

            aft = $(this).attr("aft");
            func = makeAoAfterInfo[aft];

            if (checkBlank(func) === true) {
                return true;
            }

            ret = func(dvs, aft);

            if (ret === false) {
                return alertReturnFalse(makeAoAfterInfo.msg);
            }

            if (ret === true) {
                ret = '';
            }

            var prefix = getPrefix(dvs) + aft;
            $(prefix + "_info").val(ret);
        });

        return ret;
    },
    "makeChkTblrStr": function ($obj, str, addStr) {
        var ret = '';

        if (!$obj.prop("checked")) {
            return ret;
        }

        if (!checkBlank(str)) {
            ret += "/";
        }
        if (!checkBlank(addStr)) {
            ret += addStr;
        }
        ret += $obj.val();

        return ret;
    },
    "makeSelTblrStr": function ($obj, str, addStr) {
        var ret = '';

        if ($obj.val() === "-1") {
            return ret;
        }

        if (!checkBlank(str)) {
            ret += "/";
        }
        if (!checkBlank(addStr)) {
            ret += addStr;
        }
        ret += $obj.val();

        return ret;
    },
    "heat_cutting": function (dvs, aft) {
        var prefix = getPrefix(dvs) + aft;

        var $t = $(prefix + "_t");
        var $b = $(prefix + "_b");
        var $l = $(prefix + "_l");
        var $r = $(prefix + "_r");

        if (!$t.prop("checked") && !$b.prop("checked") &&
            !$l.prop("checked") && !$r.prop("checked")) {
            makeAoAfterInfo.msg = "재단 위치를 선택해주세요.";
            return false;
        }

        var ret = '';
        ret += makeAoAfterInfo.makeChkTblrStr($t, ret, '');
        ret += makeAoAfterInfo.makeChkTblrStr($b, ret, '');
        ret += makeAoAfterInfo.makeChkTblrStr($l, ret, '');
        ret += makeAoAfterInfo.makeChkTblrStr($r, ret, '');

        return ret;
    },
    "dotline": function (dvs, aft) {
        var prefix = getPrefix(dvs) + aft;

        var $t = $(prefix + "_t");
        var $b = $(prefix + "_b");
        var $l = $(prefix + "_l");
        var $r = $(prefix + "_r");

        if (!$t.prop("checked") && !$b.prop("checked") &&
            !$l.prop("checked") && !$r.prop("checked")) {
            makeAoAfterInfo.msg = "미싱 위치를 선택해주세요.";
            return false;
        }

        var ret = '';
        ret += makeAoAfterInfo.makeChkTblrStr($t, ret, '');
        ret += makeAoAfterInfo.makeChkTblrStr($b, ret, '');
        ret += makeAoAfterInfo.makeChkTblrStr($l, ret, '');
        ret += makeAoAfterInfo.makeChkTblrStr($r, ret, '');

        return ret;
    },
    "add_dotline": function (dvs, aft) {
        return makeAoAfterInfo.dotline(dvs, aft);
    },
    "ring": function (dvs, aft) {
        var prefix = getPrefix(dvs) + aft;

        var $t = $(prefix + "_t");
        var $b = $(prefix + "_b");
        var $l = $(prefix + "_l");
        var $r = $(prefix + "_r");

        if ($t.val() === "-1" && $b.val() === "-1" &&
            $l.val() === "-1" && $r.val() === "-1") {
            makeAoAfterInfo.msg = "고리 개수를 선택해주세요.";
            return false;
        }

        var ret = '';
        ret += makeAoAfterInfo.makeSelTblrStr($t, ret, "상_");
        ret += makeAoAfterInfo.makeSelTblrStr($b, ret, "하_");
        ret += makeAoAfterInfo.makeSelTblrStr($l, ret, "좌_");
        ret += makeAoAfterInfo.makeSelTblrStr($r, ret, "우_");

        return ret;
    },
    "scroll": function (dvs, aft) {
        return true;
    },
    "cool_coating": function (dvs, aft) {
        return true;
    },
    "punching": function (dvs, aft) {
        return true;
    },
    "cutting": function (dvs, aft) {
        return true;
    }
};

var setBasicAoAfterInfo = function (dvs) {
    var prefix = getPrefix(dvs);
    // 없음이 기본후공정 수 만큼 있으면 안됨
    var length = $(".ao_basic_after").length;
    var count = 0;

    $(".ao_basic_after").each(function () {
        var aft = $(this).find("input[name='" + dvs + "_chk_after[]']")
            .attr("aft");
        var mpcode = $(prefix + aft + "_val").val();
        var txt = $(prefix + aft + "_val > option:selected").text();

        if (txt === "없음") {
            $(prefix + aft).prop("checked", false);
            count++;
        } else {
            $(prefix + aft).prop("checked", true);
        }

        $(prefix + aft + "_mpcode").val(mpcode);
    });

    if (length === count) {
        return false;
    }

    return true;
};
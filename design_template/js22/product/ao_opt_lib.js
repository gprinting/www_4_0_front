var optArr = {
    "추가물품": "addGoods",
    "포장": "package",
    "실내용거치대": "indoorRack",
    "실외용거치대": "outdoorRack",
    "거치대": "rack"
};

/**
 * @brief 옵션 가격 검색
 *
 * @param obj = 체크확인용 객체
 * @param idx = 옵션 위치
 * @param dvs = 제품구분값
 */
var loadAoOptPrice = {
    "data": {},
    "idx": null,
    "exec": function (obj, idx, dvs) {
        if (!$(obj).prop("checked")) {
            optSlideUp(idx);

            calcPrice(false);
            return false;
        }

        optSlideDown(idx);

        this.calc(idx, dvs);
    },
    "calc": function (idx, dvs) {
        changeData();
        /*
        //var prefix = getPrefix(dvs);

        this.idx = idx;

        if (!$("#opt_" + idx).prop("checked")) {
            return false;
        }

        var optName = $("#opt_" + idx).val();

        var url = "/ajax/product/load_ao_opt_price.php";
        var data = this.getData[optArr[optName]](idx);
        var callback = function (result) {
            loadOptPrice.data[loadOptPrice.idx] = result;
            var count = parseInt($("#count").val());
            if (isNaN(count)) {
                count = 1;
            }
            var optPrice = parseInt(result.price) * count;

            setOptPrice(idx, optPrice);

            var opt = $("#opt_" + idx).val();

            calcPrice(false);
        };

        ajaxCall(url, "json", data, callback);

         */
    },
    "getData": {
        "addGoods": function (idx) {
            var prefix = getPrefix("opt") + idx;
            var optName = $(prefix).val();
            var depth1 = $(prefix + "_depth1").val();
            var depth2 = $(prefix + "_depth2").val();
            var depth3 = $(prefix + "_val > option:selected").text();
            var amt = $(prefix + "_info").val();

            var data = {
                "name": optName,
                "depth1": depth1,
                "depth2": depth2,
                "depth3": depth3,
                "amt": amt
            };

            return data;
        },
        "package": function (idx) {
            var prefix = getPrefix("opt") + idx;
            var optName = $(prefix).val();
            var depth1 = $(prefix + "_val > option:selected").text();

            var data = {
                "name": optName,
                "depth1": depth1,
                "amt": $(getPrefix(prdtDvs) + "amt").val(),
            };

            return data;
        },
        "indoorRack": function (idx) {},
        "outdoorRack": function (idx) {},
        "rack": function (idx) {
            var prefix = getPrefix("opt") + idx;
            var optName = $(prefix).val();
            var depth1 = $(prefix + "_depth1").val();
            var depth2 = $(prefix + "_depth2").val();
            var depth3 = $(prefix + "_val > option:selected").text();
            var amt = $(prefix + "_info").val();

            var data = {
                "name": optName,
                "depth1": depth1,
                "depth2": depth2,
                "depth3": depth3,
                "amt": amt
            };

            return data;
        }
    }
};

/**
 * @brief 실사 옵션 하위항목 검색
 *
 * @param data     = ajaxCall에서 사용할 파라미터
 * @param callback = ajaxCall에서 사용할 callback함수
 */
var loadAoOptDepth = function (data, callback) {
    var url = "/ajax/product/load_ao_opt_depth.php";
    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 수량 변경시 옵션 가격 재계산 함수
 */
var reCalcAoOptPrice = function (dvs) {
    $("input[name='chk_opt']:checked").each(function () {
        var $obj = $(this);
        var opt = $obj.val();
        var idx = parseInt($obj.attr("id").split('_')[1]);

        loadAoOptPrice.calc(idx, dvs);
    });
};

/**
 * @brief 로프 하위항목 검색
 *
 * @param idx = 옵션 인덱스
 * @param val = 옵션 depth1, depth2
 * @param dvs = 제품 구분값
 * @param depth = 현 depth
 */
var loadAddGoodsDepth = function (idx, val, dvs, depth) {
    var prefix = getPrefix("opt") + idx + '_';

    var callback = function (result) {
        switch (depth) {
            case '1':
                $(prefix + "depth2").html(result);
                $(prefix + "depth2").trigger("change");
                break;
            case '2':
                $(prefix + "val").html(result);
                loadAoOptPrice.calc(idx, prdtDvs);
                break;
        }
    };

    var data = {
        "cate_sortcode": $(getPrefix(dvs) + "cate_sortcode").val(),
        "opt_name": "추가물품"
    };

    switch (depth) {
        case '1':
            data.depth1 = $(prefix + "depth1").val();
            data.flag = 'N';
            break;
        case '2':
            data.depth1 = $(prefix + "depth1").val();
            data.depth2 = $(prefix + "depth2").val();
            data.flag = 'Y';
            break;
    }

    loadAoOptDepth(data, callback);
};
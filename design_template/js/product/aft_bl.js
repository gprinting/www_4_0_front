/**
 * @brief 라미넥스 가격 처리 함수
 */
var getLaminexPlySheetPrice = function (aft, dvs) {
    getLaminexPrice(aft, dvs);
};

/**
 * @brief 미싱 가격 처리 함수
 */
var getDotlinePlySheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var aftInfoArr = getAftInfoArr(dvs);
    var size = $(prefix + "size > option:selected").text();
    loadAfterMpcode(dvs, aftInfoArr, size);

    getDotlinePrice(aft, dvs);
};

/**
 * @brief 박 가격 처리 함수
 */
var getFoilPlySheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var amt = $(prefix + "sheet_count").val();

    if (checkBlank(amt)) {
        amt = $(prefix + "amt").val();
    }

    return getAfterFoilPressPrice(aft, dvs, amt);
};

/**
 * @breif 형압 가격 처리 함수
 */
var getPressPlySheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var amt = $(prefix + "sheet_count").val();

    if (checkBlank(amt)) {
        amt = $(prefix + "amt").val();
    }

    return getAfterFoilPressPrice(aft, dvs, amt);
};

/**
 * @breif 엠보싱 가격 처리 함수
 */
var getEmbossingPlySheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var amt = $(prefix + "sheet_count").val();

    if (checkBlank(amt)) {
        amt = $(prefix + "amt").val();
    }

    return getAfterFoilPressPrice(aft, dvs, amt);
};

/**
 * @brief 오시 가격 처리 함수
 */
var getImpressionPlySheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var aftInfoArr = getAftInfoArr(dvs);
    var size = $(prefix + "size > option:selected").text();
    loadAfterMpcode(dvs, aftInfoArr, size);

    getDotlinePrice(aft, dvs);
};

/**
 * @brief 재단 가격 처리 함수
 */
var getCuttingPlySheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 접착 가격 처리 함수
 */
var getBondingPlySheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 제본 가격 처리 함수
 */
var getBindingPlySheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs) + aft;
    if (checkBlank($(prefix + "_val > option:selected").attr("name"))) {
        getAfterPriceCommon(aft, dvs, null);
    } else {
        loadBindingDepth2($(prefix + "_dvs").val(), dvs);
    }
};

/**
 * @brief 타공 가격 처리 함수
 */
var getPunchingPlySheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 넘버링 가격 처리 함수
 */
var getNumberingPlySheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @breif 도무송 가격 처리함수
 */
var getThomsonPlySheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var param = {
        "wid": $(prefix + aft + "_wid_1").val(),
        "vert": $(prefix + aft + "_vert_1").val(),
        "stanName": $(prefix + aft + "_val > option:selected").text(),
        "amt": $("#sheet_count_span").text(),
        "callback": function (result) {
            setAfterPrice(dvs, aft, result.price);
            calcPrice();
        }
    };
    getFreeTomsonPrice(aft, dvs, param);
};

/**
 * @brief 접지 가격 처리 함수
 */
var getFoldlinePlySheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 코팅 가격 처리 함수
 */
var getCoatingPlySheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};
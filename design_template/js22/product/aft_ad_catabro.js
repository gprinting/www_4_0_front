/**
 * @brief 제본 가격 검색
 *
 * @param aft = 후공정 구분값
 */
var getBindingCalcBookletPrice = function (aft, dvs) {
    return getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 박 가격 처리 함수
 */
var getFoilCalcBookletPrice = function (aft, dvs) {
    var commonPfx = getPrefix(commonDvs);
    var prefix = getPrefix(dvs);
    var amt = $(commonPfx + "amt").val();
    var posNum = $(commonPfx + "size > option:selected").attr("pos_num");
    var page = parseInt($(prefix + "page").val());

    if (page === 0) {
        return false;
    }

    var param = {
        "amt": amt,
        "posNum": posNum,
        "page": page
    };
    amt = getBookletPaperAmt(param);
    return getAfterFoilPressPrice(aft, dvs, amt);
};

/**
 * @breif 형압 가격 처리 함수
 */
var getPressCalcBookletPrice = function (aft, dvs) {
    var commonPfx = getPrefix(commonDvs);
    var prefix = getPrefix(dvs);
    var amt = $(commonPfx + "amt").val();
    var posNum = $(commonPfx + "size > option:selected").attr("pos_num");
    var page = parseInt($(prefix + "page").val());

    if (page === 0) {
        return false;
    }

    var param = {
        "amt": amt,
        "posNum": posNum,
        "page": page
    };
    amt = getBookletPaperAmt(param);
    return getAfterFoilPressPrice(aft, dvs, amt);
};

/**
 * @brief 오시 가격 처리 함수
 */
var getImpressionCalcBookletPrice = function (aft, dvs) {
    getDotlinePrice(aft, dvs);
};

/**
 * @brief 코팅 가격 처리 함수
 */
var getCoatingCalcBookletPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @breif 엠보싱 가격 처리 함수
 */
var getEmbossingCalcBookletPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var amt = $(prefix + "sheet_count").val();
    return getAfterFoilPressPrice(aft, dvs, amt);
};
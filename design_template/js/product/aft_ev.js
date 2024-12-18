/**
 * @brief 접착 가격 처리 함수
 */
var getBondingPlySheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 가공 가격 처리 함수
 */
var getManufacturePlySheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};


/**
 * @brief 박 확정형 가격 검색
 *
 * @param aft = 후공정 구분값
 */
var getFoilPlySheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var amt = $(prefix + "amt").val();
    return getAfterFoilPressPrice(aft, dvs, amt);
};

/**
 * @brief 타공 확정형 가격 검색
 *
 * @param aft = 후공정 구분값
 */
var getPunchingPlySheetPrice = function (aft, dvs) {
    return getAfterPriceCommon(aft, dvs, null);
};
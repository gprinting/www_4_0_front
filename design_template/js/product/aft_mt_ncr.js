/**
 * @brief 제본 가격 검색
 *
 * @param aft = 후공정 구분값
 */
var getBindingCalcSheetPrice = function (aft, dvs) {
    return getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 넘버링 가격 처리 함수
 */
var getNumberingCalcSheetPrice = function (aft, dvs) {
    return getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 미싱 가격 처리 함수
 */
var getDotlineCalcSheetPrice = function (aft, dvs) {
    return getDotlinePrice(aft, dvs);
};

var getPunchingCalcSheetPrice = function (aft, dvs) {
    return getAfterPriceCommon(aft, dvs, null);
};
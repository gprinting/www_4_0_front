/**
 * @brief 코팅 가격 처리 함수
 */
var getCoatingPlySheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 오시 가격 처리 함수
 */
var getImpressionPlySheetPrice = function (aft, dvs) {
    getDotlinePrice(aft, dvs);
};

/**
 * @brief 가공 가격 처리 함수
 */
var getManufacturePlySheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 타공 가격 처리 함수
 */
var getPunchingPlySheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 접착 가격 처리 함수
 */
var getBondingPlySheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 라미넥스 가격 처리 함수
 */
var getLaminexPlySheetPrice = function (aft, dvs) {
    getLaminexPrice(aft, dvs);
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
 * @brief 형압 확정형 가격 검색
 *
 * @param aft = 후공정 구분값
 */
var getPressPlySheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var amt = $(prefix + "amt").val();
    return getAfterFoilPressPrice(aft, dvs, amt);
};

/**
 * @brief 전체빼다 가격 처리 함수
 */
var getBackgroundPlySheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @breif 엠보싱 가격 처리 함수
 */
var getEmbossingPlySheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var amt = $(prefix + "amt").val();
    return getAfterFoilPressPrice(aft, dvs, amt);
};
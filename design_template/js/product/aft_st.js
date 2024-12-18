/**
 * @brief 명함 오시 가격 계산 함수, 미싱 가격하고 동일
 */
var getImpressionPrice = getDotlinePrice;

/**
 * @brief 귀도리 확정형 가격 검색
 *
 * @param aft = 후공정 구분값
 */
var getRoundingPlySheetPrice = function (aft, dvs) {
    return getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 미싱 확정형 가격 검색
 *
 * @param aft = 후공정 구분값
 */
var getDotlinePlySheetPrice = function (aft, dvs) {
    return getDotlinePrice(aft, dvs);
};

/**
 * @brief 오시 확정형 가격 검색
 *
 * @param aft = 후공정 구분값
 */
var getImpressionPlySheetPrice = function (aft, dvs) {
    return getImpressionPrice(aft, dvs);
};

/**
 * @brief 타공 확정형 가격 검색
 *
 * @param aft = 후공정 구분값
 */
var getPunchingPlySheetPrice = function (aft, dvs) {
    return getAfterPriceCommon(aft, dvs, null);
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
 * @breif 엠보싱 가격 처리 함수
 */
var getEmbossingPlySheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var amt = $(prefix + "amt").val();
    return getAfterFoilPressPrice(aft, dvs, amt);
};

/**
 * @brief 후지반칼 확정형 가격 검색
 *
 * @param aft = 후공정 구분값
 */
var getHalfknifePlySheetPrice = function (aft, dvs) {
    return getAfterPriceCommon(aft, dvs, null);
};
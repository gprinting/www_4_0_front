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
 * @brief 박 확정형 가격 검색
 *
 * @param aft = 후공정 구분값
 */
var getFoilPlySheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var amt = $(prefix + "amt").val();
    return getAfterFoilPressPrice(aft, dvs, amt);
};

var getThomsonPlySheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var param = {
        "wid": $(prefix + aft + "_wid_1").val(),
        "vert": $(prefix + aft + "_vert_1").val(),
        "stanName": $(prefix + aft + "_val > option:selected").text(),
        "amt": $("#sheet_count_span").text(),
        "callback": function (result) {

        }
    };
    getFreeTomsonPrice(aft, dvs, param);
};

/**
 * @brief 오시 확정형 가격 검색
 *
 * @param aft = 후공정 구분값
 */
var getImpressionPlySheetPrice = function (aft, dvs) {
    return getDotlinePrice(aft, dvs);
};

/**
 * @brief 재단 확정형 가격 검색
 *
 * @param aft = 후공정 구분값
 */
var getCuttingPlySheetPrice = function (aft, dvs) {
    return getAfterPriceCommon(aft, dvs, null);
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
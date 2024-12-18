/**
 * @brief 라미넥스 가격 처리 함수
 */
var getLaminexCalcSheetPrice = function (aft, dvs) {
    getLaminexPrice(aft, dvs);
};

/**
 * @brief 미싱 가격 처리 함수
 */
var getDotlineCalcSheetPrice = function (aft, dvs) {
    getDotlinePrice(aft, dvs);
};

/**
 * @brief 박 가격 처리 함수
 */
var getFoilCalcSheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var amt = $(prefix + "sheet_count").val();
    return getAfterFoilPressPrice(aft, dvs, amt);
};

/**
 * @breif 형압 가격 처리 함수
 */
var getPressCalcSheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var amt = $(prefix + "sheet_count").val();
    return getAfterFoilPressPrice(aft, dvs, amt);
};

/**
 * @breif 엠보싱 가격 처리 함수
 */
var getEmbossingCalcSheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var amt = $(prefix + "sheet_count").val();
    return getAfterFoilPressPrice(aft, dvs, amt);
};

/**
 * @brief 오시 가격 처리 함수
 */
var getImpressionCalcSheetPrice = function (aft, dvs) {
    getDotlinePrice(aft, dvs);
};

/**
 * @brief 재단 가격 처리 함수
 */
var getCuttingCalcSheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 접착 가격 처리 함수
 */
var getBondingCalcSheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 제본 가격 처리 함수
 */
var getBindingCalcSheetPrice = function (aft, dvs) {
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
var getPunchingCalcSheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 넘버링 가격 처리 함수
 */
var getNumberingCalcSheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @breif 도무송 가격 처리함수
 */
var getThomsonCalcSheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
    //var prefix  = getPrefix(dvs);
    //var param   = {
    //    "wid"      : $(prefix + aft + "_wid").val(),
    //    "vert"     : $(prefix + aft + "_vert").val(),
    //    "stanName" : $(prefix + aft + "_val > option:selected").text(),
    //    "amt"      : $("#sheet_count_span").text(),
    //    "callback" : function(result) {
    //        setAfterPrice(dvs, aft, result);
    //        calcPrice();
    //    }
    //};
    //getFreeTomsonPrice(aft, dvs, param);
};

/**
 * @brief 접지 가격 처리 함수
 */
var getFoldlineCalcSheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};

/**
 * @brief 코팅 가격 처리 함수
 */
var getCoatingCalcSheetPrice = function (aft, dvs) {
    getAfterPriceCommon(aft, dvs, null);
};
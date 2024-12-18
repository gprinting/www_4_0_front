/**
 * @brief 가공
 */
var getManufactureCalcSheetPrice = function (aft, dvs) {};

/**
 * @brief 귀도리
 */
var getRoundingCalcSheetPrice = function (aft, dvs) {};

/**
 * @brief 넘버링
 */
var getNumberingCalcSheetPrice = function (aft, dvs) {};

/**
 * @brief 도무송 가격 처리함수
 */
var getThomsonCalcSheetPrice = function (aft, dvs) {};

/**
 * @brief 라미넥스
 */
var getLaminexCalcSheetPrice = function (aft, dvs) {};

/**
 * @brief 미싱
 */
var getDotlineCalcSheetPrice = function (aft, dvs) {};

/**
 * @brief 박
 */
var getFoilCalcSheetPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var amt = $(prefix + "amt").val();
    return getAfterFoilPressPrice(aft, dvs, amt);
};

/**
 * @brief 복권실크
 */
var getLotterysilkCalcSheetPrice = function (aft, dvs) {};

/**
 * @brief 엠보싱
 */
var getEmbossingCalcSheetPrice = function (aft, dvs) {};

/**
 * @brief 오시
 */
var getImpressionCalcSheetPrice = function (aft, dvs) {};

/**
 * @brief 재단
 */
var getCuttingCalcSheetPrice = function (aft, dvs) {};

/**
 * @brief 접지
 */
var getFoldlineCalcSheetPrice = function (aft, dvs) {};

/**
 * @brief 접착
 */
var getBondingCalcSheetPrice = function (aft, dvs) {};

/**
 * @brief 제본
 */
var getBindingCalcSheetPrice = function (aft, dvs) {};

/**
 * @brief 코팅
 */
var getCoatingCalcSheetPrice = function (aft, dvs) {};

/**
 * @brief 타공
 */
var getPunchingCalcSheetPrice = function (aft, dvs) {};

/**
 * @brief 형압
 */
var getPressCalcSheetPrice = function (aft, dvs) {};
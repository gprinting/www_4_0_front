/**
 * @brief 가공
 */
var getManufactureCalcBookletPrice = function (aft, dvs) {};

/**
 * @brief 귀도리
 */
var getRoundingCalcBookletPrice = function (aft, dvs) {};

/**
 * @brief 넘버링
 */
var getNumberingCalcBookletPrice = function (aft, dvs) {};

/**
 * @brief 도무송 가격 처리함수
 */
var getThomsonCalcBookletPrice = function (aft, dvs) {};

/**
 * @brief 라미넥스
 */
var getLaminexCalcBookletPrice = function (aft, dvs) {};

/**
 * @brief 미싱
 */
var getDotlineCalcBookletPrice = function (aft, dvs) {};

/**
 * @brief 박
 */
var getFoilCalcBookletPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs);
    var amt = $(prefix + "amt").val();
    return getAfterFoilPressPrice(aft, dvs, amt);
};

/**
 * @brief 복권실크
 */
var getLotterysilkCalcBookletPrice = function (aft, dvs) {};

/**
 * @brief 엠보싱
 */
var getEmbossingCalcBookletPrice = function (aft, dvs) {};

/**
 * @brief 오시
 */
var getImpressionCalcBookletPrice = function (aft, dvs) {};

/**
 * @brief 재단
 */
var getCuttingCalcBookletPrice = function (aft, dvs) {};

/**
 * @brief 접지
 */
var getFoldlineCalcBookletPrice = function (aft, dvs) {};

/**
 * @brief 접착
 */
var getBondingCalcBookletPrice = function (aft, dvs) {};

/**
 * @brief 제본
 */
var getBindingCalcBookletPrice = function (aft, dvs) {};

/**
 * @brief 코팅
 */
var getCoatingCalcBookletPrice = function (aft, dvs) {};

/**
 * @brief 타공
 */
var getPunchingCalcBookletPrice = function (aft, dvs) {};

/**
 * @brief 형압
 */
var getPressCalcBookletPrice = function (aft, dvs) {};
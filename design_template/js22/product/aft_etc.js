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
    var prefix = getPrefix(dvs);
    var name = $(prefix + aft).val();

    var data = {
        "cate_sortcode": sortcode,
        "after_name": name
    };
    getAfterPriceCommon(aft, dvs, null, data);
};
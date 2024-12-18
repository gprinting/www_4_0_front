<?
/*
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016-11-28 엄준현 추가
 *============================================================================
 *
 */
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/common_define/prdt_default_info.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");
include_once(INC_PATH . "/common_lib/CalcPriceUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new FrontCommonUtil();
$dao = new ProductCommonDAO();
$fb = new FormBean();

$calcUtil = new CalcPriceUtil();

$sell_site = $fb->session("sell_site");

$fb = $fb->getForm();

$dvs_arr = ["cover", "inner1", "inner2", "inner3"];
$price_arr = array();

// 공통파라미터
$dvs           = $fb["dvs"];
$cate_sortcode = $fb["cate_sortcode"];
$flattyp_yn    = 'N';
$amt           = $fb["amt"];
$amt_unit      = $fb["amt_unit"];
$affil         = $fb["affil"];
$pos_num       = $fb["pos_num"];
$stan_mpcode   = $fb["stan_mpcode"];

if ($dvs !== "all") {
    $dvs_arr = [$dvs];
}

$temp = array();
$temp["sell_site"]     = $sell_site;
$temp["cate_sortcode"] = $cate_sortcode;
$temp["amt_unit"]      = $amt_unit;
$temp["flattyp_yn"]    = $flattyp_yn;

$temp["amt"]     = $amt;
$temp["pos_num"] = $pos_num;
$temp["affil"]   = $affil;

foreach ($dvs_arr as $dvs) {
    $prefix = $dvs . '_';

    $paper_mpcode = $fb[$prefix . "paper_mpcode"];

    if (empty($paper_mpcode)) {
        continue;
    }

    $print_bef_mpcode = $fb[$prefix . "bef_print_mpcode"];
    $print_aft_mpcode = $fb[$prefix . "aft_print_mpcode"];
    $print_bef_add_mpcode = $fb[$prefix . "bef_add_print_mpcode"];
    $print_aft_add_mpcode = $fb[$prefix . "aft_add_print_mpcode"];

    $print_bef_name = $fb[$prefix . "bef_print_name"];
    $print_aft_name = $fb[$prefix . "aft_print_name"];
    $print_bef_add_name = $fb[$prefix . "bef_add_print_name"];
    $print_aft_add_name = $fb[$prefix . "aft_add_print_name"];

    $temp["cate_paper_mpcode"]  = $paper_mpcode;
    $temp["cate_output_mpcode"] = $stan_mpcode;

    $temp["bef_print_mpcode"]     = $print_bef_mpcode;
    $temp["aft_print_mpcode"]     = $print_aft_mpcode;
    $temp["bef_add_print_mpcode"] = $print_bef_add_mpcode;
    $temp["aft_add_print_mpcode"] = $print_aft_add_mpcode;

    $page_info    = explode('!', $fb[$prefix . "page_info"]);
    $page         = $page_info[0];
    $page_detail  = $page_info[1];
    $temp["page"] = $page;

    $calcUtil->setData($temp);

    $print_name_arr = array();
    $print_name_arr["bef_print_name"] = $print_bef_name;
    $print_name_arr["aft_print_name"] = $print_aft_name;
    $print_name_arr["bef_add_print_name"] = $print_bef_add_name;
    $print_name_arr["aft_add_print_name"] = $print_aft_add_name;

    $paper_price  = $util->ceilVal($calcUtil->calcPaperPrice($print_name_arr));
    $print_price  = $util->ceilVal($calcUtil->calcBookletPrintPrice());
    $output_price = $util->ceilVal($calcUtil->calcBookletOutputPrice());
    $sell_price   = $paper_price + $print_price + $output_price;

    $price_arr[$dvs]["paper"]  = $paper_price;
    $price_arr[$dvs]["output"] = $output_price;
    $price_arr[$dvs]["print"]  = $print_price;
    $price_arr[$dvs]["sell"]   = $sell_price;
}

$price_json  = '{';
$price_json .= " \"cover\"  : {\"paper\"  : \"%s\",";
$price_json .= "               \"print\"  : \"%s\",";
$price_json .= "               \"output\" : \"%s\",";
$price_json .= "               \"sell_price\" : \"%s\"},";
$price_json .= " \"inner1\" : {\"paper\"  : \"%s\",";
$price_json .= "               \"print\"  : \"%s\",";
$price_json .= "               \"output\" : \"%s\",";
$price_json .= "               \"sell_price\" : \"%s\"},";
$price_json .= " \"inner2\" : {\"paper\"  : \"%s\",";
$price_json .= "               \"print\"  : \"%s\",";
$price_json .= "               \"output\" : \"%s\",";
$price_json .= "               \"sell_price\" : \"%s\"},";
$price_json .= " \"inner3\" : {\"paper\"  : \"%s\",";
$price_json .= "               \"print\"  : \"%s\",";
$price_json .= "               \"output\" : \"%s\",";
$price_json .= "               \"sell_price\" : \"%s\"}";
$price_json .= '}';

$outer  = '{';
$outer .= " \"%s\"  : %s";

$outer  = sprintf($outer, "ad", $price_json);
$outer  = sprintf($outer, $util->ceilVal($price_arr["cover"]["paper"])
                        , $util->ceilVal($price_arr["cover"]["print"])
                        , $util->ceilVal($price_arr["cover"]["output"])
                        , $util->ceilVal($price_arr["cover"]["sell"])
                        , $util->ceilVal($price_arr["inner1"]["paper"])
                        , $util->ceilVal($price_arr["inner1"]["print"])
                        , $util->ceilVal($price_arr["inner1"]["output"])
                        , $util->ceilVal($price_arr["inner1"]["sell"])
                        , $util->ceilVal($price_arr["inner2"]["paper"])
                        , $util->ceilVal($price_arr["inner2"]["print"])
                        , $util->ceilVal($price_arr["inner2"]["output"])
                        , $util->ceilVal($price_arr["inner2"]["sell"])
                        , $util->ceilVal($price_arr["inner3"]["paper"])
                        , $util->ceilVal($price_arr["inner3"]["print"])
                        , $util->ceilVal($price_arr["inner3"]["output"])
                        , $util->ceilVal($price_arr["inner3"]["sell"])
                        );
$outer .= '}';

echo $outer;

$conn->Close();
?>

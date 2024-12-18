<?
/*
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016-11-20 엄준현 추가
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

$sell_site = $fb->session("sell_site");

$fb = $fb->getForm();

$cate_sortcode = $fb["cate_sortcode"];
$dvs           = $fb["dvs"];
$flattyp_yn    = $fb["flattyp_yn"];
$amt_unit      = $fb["amt_unit"];

$paper_mpcode = $fb["paper_mpcode"];
$stan_mpcode  = $fb["stan_mpcode"];
$print_bef_mpcode = $fb["bef_print_mpcode"];
$print_aft_mpcode = $fb["aft_print_mpcode"];
$print_bef_add_mpcode = $fb["bef_add_print_mpcode"];
$print_aft_add_mpcode = $fb["aft_add_print_mpcode"];

$print_bef_name = $fb["bef_print_name"];
$print_aft_name = $fb["aft_print_name"];
$print_bef_add_name = $fb["bef_add_print_name"];
$print_aft_add_name = $fb["aft_add_print_name"];

$amt     = $fb["amt"];
$affil   = $fb["affil"];
$pos_num = $fb["pos_num"];

$page_info   = explode('!', $fb["page_info"]);
$page        = $page_info[0];
$page_detail = $page_info[1];

$temp = [];
$temp["sell_site"]     = $sell_site;
$temp["cate_sortcode"] = $cate_sortcode;
$temp["amt_unit"]      = $amt_unit;
$temp["flattyp_yn"]    = $flattyp_yn;

$temp["amt"]     = $amt;
$temp["page"]    = $page;
$temp["pos_num"] = $pos_num;
$temp["affil"]   = $affil;

$temp["cate_paper_mpcode"]  = $paper_mpcode;
$temp["cate_output_mpcode"] = $stan_mpcode;

$temp["bef_print_mpcode"]     = $print_bef_mpcode;
$temp["aft_print_mpcode"]     = $print_aft_mpcode;
$temp["bef_add_print_mpcode"] = $print_bef_add_mpcode;
$temp["aft_add_print_mpcode"] = $print_aft_add_mpcode;

// 마스터 인쇄일 때, 수량 낱장여부 별도처리
if ($cate_sortcode === "007002001") {
    $temp["flattyp_yn"] = 'N';

    $temp["amt"] =
        PrdtDefaultInfo::MST_GROUP * 2 * $amt;
}

$calc_util = new CalcPriceUtil($temp);

unset($temp);
$temp["bef_print_name"] = $print_bef_name;
$temp["aft_print_name"] = $print_aft_name;
$temp["bef_add_print_name"] = $print_bef_add_name;
$temp["aft_add_print_name"] = $print_aft_add_name;

$paper_price  = $util->ceilVal($calc_util->calcPaperPrice($temp));
$print_price  = $util->ceilVal($calc_util->calcSheetPrintPrice());
$output_price = $util->ceilVal($calc_util->calcSheetOutputPrice());
$sell_price   = $paper_price + $print_price + $output_price;

$price_json  = '{';
$price_json .= " \"paper\"  : \"%s\",";
$price_json .= " \"print\"  : \"%s\",";
$price_json .= " \"output\" : \"%s\",";
$price_json .= " \"sell_price\" : \"%s\"";
$price_json .= '}';

$outer  = '{';
$outer .= " \"%s\"  : %s";

$outer  = sprintf($outer, $dvs, $price_json);
$outer  = sprintf($outer, $util->ceilVal($paper_price)
                        , $util->ceilVal($print_price)
                        , $util->ceilVal($output_price)
                        , $util->ceilVal($sell_price));
$outer .= '}';

echo $outer;

$conn->Close();
?>

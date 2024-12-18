<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/ActualPriceUtil.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

// $fb include sess_common
$dao = new ProductCommonDAO();
$util = new FrontCommonUtil();
$actualUtil = new ActualPriceUtil();

$member_seqno = $fb->session("org_member_seqno");

$fb = $fb->getForm();

$wid  = $fb["wid"];
$vert = $fb["vert"];
$amt  = intval($fb["amt"]);

$sell_price  = $actualUtil->calcPrice($wid, $vert) * $amt;
$sell_price  = $util->ceilVal($sell_price);

$amt_member_sale_rate       = 0;
$amt_member_sale_aplc_price = 0;

if ($is_login) {
    $param = [];
    $param["member_seqno"]  = $member_seqno;
    $param["cate_sortcode"] = $fb["cate_sortcode"];
    $param["paper_mpcode"]  = $fb["paper_mpcode"];
    $param["bef_print_mpcode"]     = $fb["bef_print_mpcode"];
    $param["aft_print_mpcode"]     = $fb["aft_print_mpcode"];
    $param["bef_add_print_mpcode"] = $fb["bef_add_print_mpcode"];
    $param["aft_add_print_mpcode"] = $fb["aft_add_print_mpcode"];
    $param["stan_mpcode"]   = $fb["stan_mpcode"];
    $param["amt"]           = $amt;

    $rs = $dao->selectAmtMemberCateSale($conn, $param);

    $amt_member_sale_rate       = doubleval($rs["rate"]);
    $amt_member_sale_aplc_price = doubleval($rs["aplc_price"]);
}

$ret  = "{\"%s\" : {";
$ret .=              "\"sell_price\" : \"%s\",";
$ret .=              "\"amt_rate\"   : \"%s\",";
$ret .=              "\"amt_aplc\"   : \"%s\"";
$ret .=            "}";
$ret .= "}";

echo sprintf($ret, $fb["dvs"]
                 , $sell_price
                 , $amt_member_sale_rate
                 , $amt_member_sale_aplc_price);
$conn->Close();

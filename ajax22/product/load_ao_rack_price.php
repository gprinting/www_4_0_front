<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/product/ProductAoDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/ActualPriceUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new ActualPriceUtil();
$dao = new ProductAoDAO();

$member_seqno = $fb->session("org_member_seqno");

$fb = $fb->getForm();

$depth1 = $fb["depth1"];
$depth2 = $fb["depth2"];
$depth3 = $fb["depth3"];
$amt    = $fb["amt"];

// 거치대 단가검색
$param = [];
$param["opt_name"] = "거치대";
$param["depth1"] = $depth1;
$param["depth2"] = $depth2;
$param["depth3"] = $depth3;

$unitprice = $dao->selectAoOptUnitprice($conn, $param);

unset($param);
$param["depth1"]    = $depth1;
$param["depth2"]    = $depth2;
$param["unitprice"] = $unitprice;
$param["amt"]       = $amt;

$ret = $util->calcRackPrice($param);

$box   = $ret["box"];
$ea    = $ret["ea"];
$price = $ret["price"];

$member_sale_rate       = 0;
$member_sale_aplc_price = 0;

if ($is_login) {
    $param = [];
    $param["member_seqno"]  = $member_seqno;
    $param["cate_sortcode"] = $fb["cate_sortcode"];

    // 추가, 나중에 aplc도 추가해서 구현하기
    //$rs = $dao->selectCateMemberSaleRate($conn, $param);
}

$ret  = "{\"%s\" : {";
$ret .=              "\"box\"   : \"%s\",";
$ret .=              "\"ea\"    : \"%s\",";
$ret .=              "\"price\" : \"%s\",";
$ret .=              "\"sale_rate\"  : \"%s\",";
$ret .=              "\"sale_aplc\"  : \"%s\"";
$ret .=            "}";
$ret .= "}";

echo sprintf($ret, $fb["dvs"]
                 , $box
                 , $ea
                 , $price * 1.1
                 , $member_sale_rate
                 , $member_sale_aplc_price);

$conn->Close();

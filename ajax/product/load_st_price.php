<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 재단스티커 비규격 스티커 가격 계산
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/04/18 엄준현 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/NonStandardUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$nsUtil = new NonStandardUtil();
$util = new CommonUtil();
$fb = new FormBean();

$sell_site    = $fb->session("sell_site");
$member_seqno = $fb->session("org_member_seqno");

$fb = $fb->getForm();

$dvs = $fb["dvs"];

$st_bg = $fb["st_bg"];

$wid   = $fb["cut_wid_size"];
$vert  = $fb["cut_vert_size"];
$amt   = $fb["amt"];
$price = $util->rmComma($fb["prdt_price"]);
$grade_sale = $fb["grade_sale"];
$paper_info = $fb["paper_info"];

$param = [];
$param["cate_sortcode"] = $fb["cate_sortcode"];
$param["st_bg"] = $st_bg;
$param["wid"]   = $wid;
$param["vert"]  = $vert;
$param["amt"]   = $amt;
$param["grade_sale"] = $grade_sale;
$param["paper_info"] = $paper_info;

$sell_price = $nsUtil->stCalcPrice($param);

$ret  = "{\"%s\" : {";
$ret .=              "\"sell_price\" : \"%s\",";
$ret .=              "\"amt_rate\"   : \"%s\",";
$ret .=              "\"amt_aplc\"   : \"%s\"";
$ret .=            "}";
$ret .= "}";


echo sprintf($ret, $dvs
                 , $util->ceilVal($sell_price)
                 , '0'
                 , '0');
$conn->Close();
exit;
?>

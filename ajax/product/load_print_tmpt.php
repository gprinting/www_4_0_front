<?
/*
 * Copyright (c) 2016-2017 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016-12-20 엄준현 수정(인쇄도수 기본값 적용로직 수정)
 * 2017-02-01 엄준현 수정()
 *============================================================================
 *
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new CommonUtil();
$dao = new ProductCommonDAO();
$fb = new FormBean();

$sell_site = $fb->session("sell_site");

$cate_sortcode = $fb->form("cate_sortcode");
$purp_dvs      = $fb->form("purp_dvs");
$affil         = $fb->form("affil");

$tmpt_name     = $fb->form("tmpt_name");
$aft_tmpt_name = $fb->form("bef_tmpt_name");
$bef_tmpt_name = $fb->form("aft_tmpt_name");

if (empty($tmpt_name)) {
    $default_print = array(
        "bef_print" => $bef_tmpt_name,
        "aft_print" => $aft_tmpt_name
    );
} else {
    $default_print = $tmpt_name;
}

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["purp_dvs"]      = $purp_dvs;
$param["affil"]         = $affil;

$param["default_print"] = $default_print;
$param["default_purp"]  = -1;

$temp = array();

// 카테고리와 인쇄용도로 전/후면 인쇄도수 검색
$rs = $dao->selectCatePrintTmptHtml($conn, $param, $temp);

$sheet_tmpt   = $rs["단면"] . $rs["양면"];
$bef_tmpt     = $rs["전면"];
$bef_add_tmpt = $rs["전면추가"];
$aft_tmpt     = $rs["후면"];
$aft_add_tmpt = $rs["후면추가"];

$ret  = '{';
$ret .= " \"sheet_tmpt\"   : \"%s\",";
$ret .= " \"bef_tmpt\"     : \"%s\",";
$ret .= " \"bef_add_tmpt\" : \"%s\",";
$ret .= " \"aft_tmpt\"     : \"%s\",";
$ret .= " \"aft_add_tmpt\" : \"%s\"";
$ret .= '}';

echo sprintf($ret, $util->convJsonStr($sheet_tmpt)
                 , $util->convJsonStr($bef_tmpt)
                 , $util->convJsonStr($bef_add_tmpt)
                 , $util->convJsonStr($aft_tmpt)
                 , $util->convJsonStr($aft_add_tmpt));

$conn->Close();
?>

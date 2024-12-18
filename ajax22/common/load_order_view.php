<?
/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/09/02 엄준현 수정(클레임 선택일 때 관리버튼 안나오도록 수정)
 * 2016/11/15 엄준현 수정(결제금액 부분 로직 수정)
 *============================================================================
 *
 */

define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new FrontCommonUtil();
$dao = new OrderInfoDAO();
$session = $fb->getSession();
$order_common_seqno = $fb->form("order_common_seqno");

$param = array();
$param["order_common_seqno"] = $order_common_seqno;
$fields = $dao->selectOrderCommon($conn, $param)->fields;
unset($rs);

// 후공정 검색결과
$param["order_seqno"] = $order_common_seqno;
$aft_rs = $dao->selectOrderAfterSet($conn, $param);
// 옵션 검색결과
$opt_rs = $dao->selectOrderOptSet($conn, $param);
// 파일 검색결과
$file_rs = $dao->selectOrderFileSet($conn, $param);

$member_dvs = $fb->session("member_dvs");

$colspan = "9";
if ($member_dvs == "기업") {
    $colspan = "10";
} else if ($is_mobile) {
    $colspan = "4";
}

if (empty($fb->form("colspan")) === false) {
    $colspan = $fb->form("colspan");
}

$param = [];
$param["conn"]               = $conn;
$param["dao"]                = $dao;
$param["colspan"]            = $colspan;
$param["file_path"]          = $fields["file_path"];
$param["save_file_name"]     = $fields["save_file_name"];
$param["title"]              = $fields["title"];
$param["amt"]                = doubleval($fields["amt"]);
$param["count"]              = $fields["count"];
$param["amt_unit_dvs"]       = $fields["amt_unit_dvs"];

$param["sell_price"]        = $fields["sell_price"];
$param["pay_price"]         = $fields["pay_price"];
$param["grade_sale_price"]  = $fields["grade_sale_price"];
$param["member_sale_price"] = $fields["member_sale_price"];
$param["event_price"]       = $fields["event_price"];
$param["use_point_price"]   = $fields["use_point_price"];
$param["add_after_price"]   = $fields["add_after_price"];
$param["add_opt_price"]     = $fields["add_opt_price"];
$param["dlvr_price"]        = $fields["dlvr_price"];

$param["expec_weight"]       = $fields["expec_weight"];
$param["order_detail"]       = $fields["order_detail"];
$param["dlvr_way"]           = $fields["dlvr_way"];
$param["zipcode"]            = $fields["zipcode"];
$param["addr"]               = $fields["addr"];
$param["addr_detail"]        = $fields["addr_detail"];
$param["order_common_seqno"] = $order_common_seqno;
$param["order_state"]        = $fields["order_state"];
$param["order_state_arr"]    = $session["state_arr"];
$param["btn_flag"]           = true;

// 클레임 선택일 때 관리버튼 출력 안되도록 하는 부분
$referer = $_SERVER["HTTP_REFERER"];

if (strpos($referer, "claim_select.html") > -1
        || strpos($referer, "cart.html") > -1
        || strpos($referer, "sheet.html") > -1) {
    $param["btn_flag"] = false;
}

//함수 여러개 만들 필요가 있음
// referer 따라서 함수 분기 시켜야 함(구현 필요!!!)

// 모바일일 때
if ($is_mobile) {
    echo makeOrderdetailMobile($param, $util, $opt_rs, $aft_rs, $file_rs);
} else {
    echo makeOrderdetail($param, $util, $opt_rs, $aft_rs, $file_rs);
}
$conn->close();
?>

<?php
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");

if ($is_login === false) {
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new FrontCommonUtil();
$dao = new OrderInfoDAO();

$session = $fb->getSession();
$fb = $fb->getForm();

//$conn->debug = 1;

$interest_prdt_seqno = $fb["seqno"];
$colspan = '6';

$param = [];
$param["interest_prdt_seqno"] = $interest_prdt_seqno;
$param["member_seqno"]        = $session["org_member_seqno"];
$fields = $dao->selectPrdtList($conn, $param)->fields;
// 후공정 검색결과
$aft_rs = $dao->selectPrdtAfter($conn, $param);
// 옵션 검색결과
$opt_rs = $dao->selectPrdtOpt($conn, $param);

$param = [];
$param["colspan"]      = $colspan;

$param["title"]        = $fields["title"];
$param["amt"]          = doubleval($fields["amt"]);
$param["count"]        = $fields["count"];
$param["amt_unit_dvs"] = $fields["amt_unit_dvs"];

$param["order_detail"] = nl2br($fields["order_detail"]);

echo makePrdtDetail($param, $util, $opt_rs, $aft_rs);
$conn->close();
?>

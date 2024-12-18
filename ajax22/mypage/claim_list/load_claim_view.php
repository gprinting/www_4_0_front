<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/ClaimInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ClaimInfoDAO();
$session = $fb->getSession();
$order_common_seqno = $fb->form("order_common_seqno");

//$conn->debug = 1;

$param = array();
$param["table"] = "order_detail";
$param["order_common_seqno"] = $order_common_seqno;

$rs = $dao->selectOrderDetail($conn, $param);

//차후 쓰게 될지 모름 - 낱장구분
$flattyp_yn = $rs->fields["flattyp_yn"];

//2017-08-07 montvert : 기본정보 가져오는 쿼리
$rs_c = $dao->selectOrderCommonBasicInfo($conn, $param);

$html = "";
$li = "\n                                     <li>%s</li>";

$param = array();
$param["table"] = "order_detail";
$param["col"] = "order_detail_dvs_num";
$param["where"]["order_common_seqno"] = $order_common_seqno;

$s_seq = $dao->selectData($conn, $param)->fields["order_detail_dvs_num"];

$param = array();
$param["table"] = "order_detail_brochure";
$param["col"] = "order_detail_dvs_num";
$param["where"]["order_common_seqno"] = $order_common_seqno;

$b_seq = $dao->selectData($conn, $param)->fields["order_detail_dvs_num"];

$param = array();
$param["table"] = "order_after_history";
$param["col"] = "after_name";
$param["where"]["order_detail_dvs_num"] = $s_seq;
//2017-08-07 montvert : 기본 후공정은 보여주지 말아야함
$param["where"]["basic_yn"] = "N";

$s_rs = $dao->selectData($conn, $param);

while ($s_rs && !$s_rs->EOF) {

    $html .= sprintf($li, $s_rs->fields["after_name"]);
    $s_rs->moveNext();
}

$param = array();
$param["table"] = "order_after_history";
$param["col"] = "after_name";
$param["where"]["order_detail_dvs_num"] = $b_seq;
//2017-08-07 montvert : 기본 후공정은 보여주지 말아야함
$param["where"]["basic_yn"] = "N";

$b_rs = $dao->selectData($conn, $param);

while ($b_rs && !$b_rs->EOF) {

    $html .= sprintf($li, $b_rs->fields["after_name"]);
    $b_rs->moveNext();
}

$member_dvs = $fb->session("member_dvs");

$param = array();
$param["colspan"] = "8";
$param["order_regi_date"]    = $rs_c->fields["order_regi_date"];
$param["order_num"]          = $rs_c->fields["order_num"];
$param["title"]              = $rs_c->fields["title"];
//$param["receipt_dvs"]      = $rs->fields["receipt_dvs"];
$param["dlvr_way"]           = $rs_c->fields["dlvr_way"];
$param["cate_name"]          = $rs->fields["cate_name"];
$param["file_path"]          = $rs->fields["file_path"];
$param["save_file_name"]     = $rs->fields["save_file_name"];
$param["order_detail"]       = $rs->fields["order_detail"];
$param["after_html"]         = $html;
$param["amt"]                = $rs->fields["amt"];
$param["count"]              = $rs->fields["count"];
$param["grade_sale_price"]   = $rs->fields["grade_sale_price"];
$param["event_price"]        = $rs->fields["event_price"];
$param["expec_weight"]       = $rs->fields["expec_weight"];
$param["order_common_seqno"] = $order_common_seqno;
$param["order_state"]        = $fb->form("order_state");

echo makeOrderClaimdetail($param);
$conn->close();
?>

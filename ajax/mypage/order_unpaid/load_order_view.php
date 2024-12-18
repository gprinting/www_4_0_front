<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new OrderInfoDAO();
$session = $fb->getSession();
$order_common_seqno = $fb->form("order_common_seqno");


$param = array();
$param["order_common_seqno"] = $order_common_seqno;

$rs = $dao->selectOrderDetail($conn, $param);

//차후 쓰게 될지 모름 - 낱장구분
$flattyp_yn = $rs->fields["flattyp_yn"];

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

$s_rs = $dao->selectData($conn, $param);

while ($s_rs && !$s_rs->EOF) {

    $html .= sprintf($li, $s_rs->fields["after_name"]);
    $s_rs->moveNext();
}

$param = array();
$param["table"] = "order_after_history";
$param["col"] = "after_name";
$param["where"]["order_detail_dvs_num"] = $b_seq;

$b_rs = $dao->selectData($conn, $param);

while ($b_rs && !$b_rs->EOF) {

    $html .= sprintf($li, $b_rs->fields["after_name"]);
    $b_rs->moveNext();
}

$member_dvs = $fb->session("member_dvs");

$colspan = "10";
if ($member_dvs == "기업") {
    $colspan = "11";
}

$param = array();
$param["colspan"] = $colspan;
$param["cate_name"] = $rs->fields["cate_name"];
$param["file_path"] = $rs->fields["file_path"];
$param["save_file_name"] = $rs->fields["save_file_name"];
$param["title"] = $rs->fields["title"];
$param["order_detail"] = $rs->fields["order_detail"];
$param["after_html"] = $html;
$param["amt"] = $rs->fields["amt"];
$param["count"] = $rs->fields["count"];
$param["grade_sale_price"] = $rs->fields["grade_sale_price"];
$param["event_price"] = $rs->fields["event_price"];
$param["expec_weight"] = $rs->fields["expec_weight"];
$param["dlvr_way"] = $rs->fields["dlvr_way"];
$param["order_common_seqno"] = $order_common_seqno;
$param["order_state"] = $fb->form("order_state");

echo makeOrderdetail($param);
$conn->close();
?>

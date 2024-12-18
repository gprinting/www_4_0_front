<?php
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderInfoDAO();

$order_seqno = $fb->form("order_seqno"); // 주문 seqno
$param_state = $fb->form("order_state"); // 넘어온 state

// 180221 엄준현 수정
$state_arr = $fb->session("state_arr");
$state_str = sprintf("%s|%s|%s|%s|1325", $state_arr["입금대기"]
    , $state_arr["입금완료"]
    , $state_arr["접수대기"]
    , $state_arr["파일에러"]);

$param = array();
$param["order_seqno"] = $order_seqno;

$rs = $dao->selectOrderInfo($conn, $param);

$now_state = $rs->fields["order_state"];

$json = "{\"success\" : \"%s\", \"file_seqno\" : \"%s\", \"file_name\" : \"%s\", \"file_path\" : \"%s\"}";

if (strpos($state_str, $now_state) === false) {
    echo sprintf($json, '-1', '', '');
    exit;
}

$file_rs = $dao->selectOrderFileSet($conn, $param)->fields;
echo sprintf($json, '1', $file_rs["order_file_seqno"], $file_rs["origin_file_name"], $file_rs["file_path"] .  $file_rs["save_file_name"]);

$conn->Close();
exit;
?>

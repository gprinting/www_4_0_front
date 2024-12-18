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

// 180221 엄준현 수정
$state_arr = $fb->session("state_arr");
//$state_str = sprintf("1320|%s|%s|%s", $state_arr["입금대기"]
//                                  , $state_arr["입금완료"]
//                                 , $state_arr["접수대기"]
//                                , $state_arr["파일에러"]);
$state_str = "1320|1385|2120|1380";
// DB값과 비교

$param = array();
$param["order_seqno"] = $order_seqno;

$rs = $dao->selectOrderInfo($conn, $param);

$now_state = $rs->fields["order_state"];

$json = "{\"success\" : \"%s\", \"file_seqno\" : \"%s\", \"file_name\" : \"%s\"}";

if (strpos($state_str, $now_state) === false) {
    echo "-1";
    exit;
}

$record_param = array();
$record_param["state"] = "1320";
$record_param["empl_id"] = "";
$record_param["kind"] = "재업로드";
$record_param["before"] = "";
$record_param["after"] = "고객이 파일 재업로드요청";
$record_param["order_common_seqno"] = $order_seqno;
$dao->insertOrderInfoHistory($conn, $record_param);

$file_rs = $dao->updateReuploadOrder($conn, $param)->fields;
echo "1";

$conn->Close();
exit;
?>

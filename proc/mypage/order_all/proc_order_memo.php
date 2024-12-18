<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$orderDAO = new OrderInfoDAO();
$conn->StartTrans();
$check = 1;

//$conn->debug = 1;

$param = array();
$param["order_seqno"] = $fb->form("order_seqno");
$param["cust_memo"]   = $fb->form("cust_memo");

$result = $orderDAO->updateOrderMemo($conn, $param);
if(!$result) $check = 0;

echo $check;
$conn->CompleteTrans();
$conn->close();
?>

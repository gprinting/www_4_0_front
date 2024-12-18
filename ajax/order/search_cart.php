<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/CartDAO.inc");

$frontUtil = new FrontCommonUtil();

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

//$conn->debug =1;

$fb = new FormBean();
$dao = new CartDAO();

// 로그인 상태인지 체크하는부분 include
//include_once($_SERVER["DOCUMENT_ROOT"] . "/common/login_check.php");

$session = $fb->getSession();
$state_arr = $session["state_arr"];
$param["member_seqno"] = $session["org_member_seqno"];
$param["order_state"]  = $state_arr["주문대기"];

$cart_list = $dao->selectCartOrderList($conn, $param);
if (!$cart_list) {
    echo "error!";
    exit;
}

$seq_arr = array();
$i = 0;
while($cart_list && !$cart_list->EOF) {
    $seq_arr[$i] .= $cart_list->fields["order_common_seqno"];
    $i++;

    $cart_list->MoveNext();
}

echo json_encode($seq_arr);

?>


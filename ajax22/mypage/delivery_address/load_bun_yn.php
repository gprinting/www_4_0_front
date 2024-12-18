<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");

if ($is_login === false) {
    echo "<script>alert('로그인이 필요합니다.'); return false;</script>";
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderInfoDAO();
$check = 1;

$session = $fb->getSession();
$state_arr = $session["state_arr"];

$order_seqno = $fb->form("order_seqno");

$param = array();
$param["order_seqno"] = $order_seqno;

// 주문 정보 select
$order_info = $dao->selectOrderInfoForDlvr($conn, $param);

$fields = $order_info->fields;
$order_state = $fields["order_state"];
$bun_yn      = $fields["bun_yn"];

// 주문상태가 배송지변경이 불가한상태인 경우
if ($order_state >= 2320) {
    $check = 0; 
// 배송이 묶인 주문의 경우
} else if ($bun_yn == "Y") {
    $check = 2;
}

echo $check;
?>

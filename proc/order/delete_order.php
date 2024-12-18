<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/Template.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/order/CartDAO.inc");
include_once($_SERVER["INC"] . "/common_define/common_config.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");

$frontUtil = new FrontCommonUtil();

if ($is_login === false) {
    $frontUtil->errorGoBack("로그인 상태가 아닙니다.");
    exit;
}


//장바구니에서 삭제
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new CartDAO();
$ndao = new FrontCommonDAO();

$fb = $fb->getForm();

$seq_arr = $fb["seq"];
$count_seq_arr = count($seq_arr);
$param = [];

for ($i = 0; $i < $count_seq_arr; $i++) {
    $order_common_seqno = $seq_arr[$i];
    $param["order_common_seqno"] = $order_common_seqno;
    $delete_ret = $dao->deleteOrderCommon($conn, $param);



    //$cart_prdt = $log_dao->searchPrdtList("1120");
    //$_SESSION["cart_prdt_count"]    = $cart_prdt["count"];

    if ($delete_ret === false) {
        goto ERR;
    }

}

$param["order_state"] = "1120";
$param["seqno"] = $_SESSION["member_seqno"];

$rs = $ndao->selectOrderListByState($conn, $param);
$count = $ndao->selectFoundRows($conn);
$_SESSION["cart_prdt_count"]    = $count;

echo 'T';
$conn->Close();
exit;

ERR:
    echo 'F';
    $conn->Close();
    exit;
?>

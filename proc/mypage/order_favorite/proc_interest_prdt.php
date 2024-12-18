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

$seqno_set = explode(",", $fb->form("select_prdt"));

//관심상품 FOR문 돌며 장바구니 INSERT
for ($i = 0; $i < count($seqno_set); $i++) {

    $param = array();
    $param["prdt_seqno"] = $seqno_set[$i];
    $interest_result = $orderDAO->selectPrdt($conn, $param);
    if (!$interest_result) $check = 0;

    /**
     * @brief 장바구니에 관심상품 INSERT
     */
    $param = array();
    $result = $orderDAO->insertShb($conn, $interest_result);
    $shb_seqno = $conn->insert_ID();
    if (!$result) $check = 0;

    /**
     * @brief 장바구니에 관심상품 정보를 넣기 위한 SELECT
     */
    //장바구니를 위한 공통 파라미터
    $param = array();
    $param["prdt_seqno"] = $seqno_set[$i];

    //관심상품 상세 결과
    $detail_result = $orderDAO->selectPrdtDetailSet($conn, $param);
    if (!$detail_result) $check = 0;

    //관심상품 후공정 결과
    $after_result = $orderDAO->selectPrdtAfterSet($conn, $param);
    if (!$after_result) $check = 0;

    //관심상품 옵션 결과
    $opt_result = $orderDAO->selectPrdtOptSet($conn, $param);
    if (!$opt_result) $check = 0;

    /**
     * @brief 장바구니에 관심상품 정보 INSERT
     */
    //주문번호 SELECT
    $param = array();
    $param["order_seqno"] = $shb_seqno;
    $result = $orderDAO->selectOrderNum($conn, $param);
    if (!$result) $check = 0;

    //장바구니를 위한 공통 파라미터
    $param = array();
    $param["shb_seqno"] = $shb_seqno;
    $param["order_num"] = $result->fields["order_num"];

    //장바구니 상세 INSERT
    $result = $orderDAO->insertShbDetail($conn, $detail_result, $param);
    if (!$result) $check = 0;

    //장바구니 후공정 INSERT
    $result = $orderDAO->insertShbAfter($conn, $after_result, $param);
    if (!$result) $check = 0;

    //장바구니 옵션 INSERT
    $result = $orderDAO->insertShbOpt($conn, $opt_result, $param);
    if (!$result) $check = 0;

}

if ($check == 1) {
    
    echo "1";

} else {

    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>

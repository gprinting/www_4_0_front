<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/common_define/order_status.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/CartDAO.inc");

if ($is_login === false) {
    echo "<script>alert('로그인이 필요합니다.'); return false;</script>";
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$frontUtil = new FrontCommonUtil();

$fb = new FormBean();
$orderDAO = new OrderInfoDAO();
$cartDAO = new CartDAO();

$order_seqno  = $fb->form("order_seqno");
$member_seqno = $fb->session("org_member_seqno");
$state_arr = $fb->session("state_arr");

$amt = $fb->form("amt");
$count = $fb->form("count");
$price = $fb->form("price");

$param = array();
$param["order_seqno"] = $order_seqno;
$order_result = $orderDAO->selectOrderNum($conn, $param);

$title = "";
if ($order_result) {

    $title = $order_result->fields["title"];
    //이벤트 상품은 재주문 불가
    if ($order_result->fields["event_yn"] == "Y") {

        echo "2";
        exit;
    }

} else {
    $check = 0;
}

/*삭제 예정 -> 재주문시 주문대기로 간다면 필요없는 영역*/
/**
 * @brief 회원 일련번호로 회원등급 SELECT
 *        회원등급으로 구매할인율 SELECT
 *        구매할인율로 판매가격 계산
 */

$param = array();
$param["member_seqno"] = $order_result->fields["member_seqno"];
$member_result = $orderDAO->selectMemberInfo($conn, $param);

$param = array();
$param["grade"] = $member_result->fields["grade"];
$grade_result = $orderDAO->selectGradeRate($conn, $param);
$sale_rate = $grade_result->fields["sales_sale_rate"];

/**
 * @brief 결제 금액
 */
$pay_price = 
    (int)$order_result->fields["sell_price"] * (int)(100- $sale_rate) / 100;
$param["pay_price"] = $pay_price;

/**
 * @brief 선입금 금액
 */
$prepay_price = 
    (int)$member_result->fields["prepay_price"] - $pay_price;

/**
 * @brief 회원 선입금 처리
 *        예외회원이 아니고 선입금이 부족한경우 :
 *                  -> 주문부족금액과 선입금  UPDATE
 *        예외회원이거나 선입금이 부족하지 않은경우 :
 *                  -> 선입금만  UPDATE
 */
$param = array();
$param["member_seqno"] = $order_result->fields["member_seqno"];
$param["price"] = $prepay_price;
$param["type"] = "";
$state_dvs = "310";

if ($member_result->fields["member_typ"] != "예외회원") {

    //선입금액이 부족할때
    if ($prepay_price < 0) {

        $param["price"] = (int)$member_result->fields["order_lack_price"] + $pay_price - (int)$member_result->fields["prepay_price"];
        $param["type"] = "lack";
        $state_dvs = "210";

    }
}
/*삭제 예정 여기까지*/

/**
 * @brief 주문공통 재주문 INSERT
 */
$insert_param = array();
//$insert_param["pay_price"] = $pay_price; =>재주문 시 새주문 상태가 주문대기이면 필요 없음
//$insert_param["state"] = $state_dvs; =>재주문 시 새주문 상태가 주문대기이면 필요 없음
$insert_param["amt"] = $amt;
$insert_param["count"] = $count;
$insert_param["order_num"] = $order_result;
$insert_param["price"] = $price;

$cate_sortcode = $orderDAO->selectOrderCateSortcode($conn,$order_seqno);
$insert_param["new_order_num"] = $frontUtil->makeOrderNum($conn, $cartDAO,
    $cate_sortcode);

// order_common
$insert_param["old_order_seqno"] = $order_seqno;
$result = $orderDAO->insertReorder($conn, $insert_param);
$order_seqno = $conn->insert_ID();


// order_detail
if (!$result) $check = 0;
$insert_param["order_seqno"] = $order_seqno;
$insert_param["order_detail_dvs_num"] = "S" . $insert_param["new_order_num"] . "01";
$orderDAO->insertReworkOrderDetail($conn, $insert_param);

// order_file
$orderDAO->insertReworkOrderFile($conn, $insert_param);

// order_after_history
$orderDAO->insertReworkOrderAfterHistory($conn, $insert_param);

// order_opt_history
$orderDAO->insertReworkOrderOptHistory($conn, $insert_param);

echo $check . "♪♭§" . $title;


$conn->CompleteTrans();
$conn->Close();
?>

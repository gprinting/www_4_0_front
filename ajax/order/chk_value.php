<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/SheetDAO.inc");
//include_once($_SERVER["DOCUMENT_ROOT"] . "/common_define/order_status.php");
include_once(INC_PATH . "/common_define/order_status.inc");

$frontUtil = new FrontCommonUtil();
$err_line = 0;
$msg = "";

if ($is_login === false) {
    $err_line = __LINE__;
    $msg = "NO_LOGIN";
    goto ERR;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

//$conn->debug = 1;

$fb = new FormBean();
$dao = new SheetDAO();

$session = $fb->getSession();
$fb = $fb->getForm();

$state_arr = $session["state_arr"];
$member_seqno = $session["org_member_seqno"];

$form_use_point = intval($fb["use_point"]);
$form_prepay_price = intval($fb["prepay_price"]);

$own_point = intval($session["own_point"]);
//$prepay_price = intval($session["prepay_price_money"]) + intval($session);
$prepay_rs = $dao->selectMemberPrepay($conn, $member_seqno);

$prepay_price = intval($prepay_rs->fields["prepay_price_money"]) 
              + intval($prepay_rs->fields["prepay_price_card"]);

// 가격 쪽 파라미터 확인
$seq_arr = $fb["seq"];

$param = [];
$param["member_seqno"] = $session["org_member_seqno"];
$param["order_state"]  = $state_arr["주문대기"];
$param["order_common_seqno"] = $dao->arr2paramStr($conn, $seq_arr);
$order_rs = $dao->selectCartOrderList($conn, $param);

while ($order_rs && !$order_rs->EOF) {
    $fields = $order_rs->fields;

    $seqno = $fields["order_common_seqno"];
    $rs_basic_price      = intval($fields["basic_price"]);
    $rs_grade_sale_price = intval($fields["grade_sale_price"]);
    $rs_event_price      = intval($fields["event_price"]);
    $rs_add_after_price  = intval($fields["add_after_price"]);

    $rs_basic_price += $rs_add_after_price;

    $form_basic_price      = intval($fb["basic_price_" . $seqno]);
    $form_grade_sale_price = intval($fb["grade_sale_price_" . $seqno]);
    $form_event_price      = intval($fb["event_price_" . $seqno]);

    if ($rs_basic_price !== $form_basic_price ||
            $rs_grade_sale_price !== $form_grade_sale_price ||
            $rs_event_price !== $form_event_price) {
        $err_line = __LINE__;
        $msg = "ERR";
        goto ERR;
    }

    $order_rs->MoveNext();
}

// 현재 자신이 가진 포인트보다 값이 큰지 확인
if ($own_point < $form_use_point) {
    $err_line = __LINE__;
    $msg = "ERR";
    goto ERR;
}

// 현재 자신이 가진 선입금액보다 값이 큰지 확인
if ($prepay_price < $form_prepay_price) {
    $err_line = __LINE__;
    $msg = "ERR";
    goto ERR;
}

echo "PASS";
exit;

ERR:
    echo $err_line;
    echo $msg;
    exit;
?>

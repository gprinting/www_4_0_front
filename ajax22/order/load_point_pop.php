<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/doc/front/order/SheetPopup.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/SheetDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc"); 


$frontUtil = new FrontCommonUtil();

if ($is_login === false) {
    $frontUtil->errorGoBack("로그인 상태가 아닙니다.");
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SheetDAO();
$dao_front = new FrontCommonDAO();

$session = $fb->getSession();
$param["member_seqno"] = $session["member_seqno"];

$point = $dao_front->get_point_sum($conn, $session["member_seqno"]);
		// 현재 전체 포인트 내역 가져오기
$own_point = $point['sum_point'];

$pay_price = $fb->form("pay_price");

// 보유포인트 계산
//$point = $dao->selectEarnPoint($conn, $param);
$session = $fb->getSession();
unset($fb);

//$own_point = $point;

echo pointPopup($own_point, $pay_price);

exit;
?>

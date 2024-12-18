<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MemberInfoDAO();

$conn->debug= 1;

$check = 1;

$param = array();
$param["bank_name"] = $fb->form("bank_name");
$param["bank_num"]  = $fb->form("bank_num");

$rs = $dao->selectVirtBaMember($conn, $param);

if (!$rs || $rs->EOF) {
    // 아무런 에러메세지 보여주지 않음
    $check = 0; 
    exit;
}

$member_seqno = $rs->fields["member_seqno"];

$prepay_rs = $dao->selectMemberPrepayLock($conn, $member_seqno);

if (!$prepay_rs || $prepay_rs->EOF) {
    $check = 2;
    exit;
}

$prepay_money = intval($prepay_rs["prepay_price_money"]);
$prepay_card  = intval($prepay_rs["prepay_price_card"]);

$total_prepay = number_format($prepay_money + $prepay_card);

echo $check . "@" . $total_prepay;
$conn->Close();
exit;
?>

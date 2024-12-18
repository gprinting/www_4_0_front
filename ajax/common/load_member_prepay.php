<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MemberInfoDAO();

$check = 1;
$id = $_SESSION["id"];

$rs     = $dao->selectPrepayPrice($conn, $id);
$fields = $rs->fields;

//선입금
$prepay_price_money = doubleval($fields[0]);
$prepay_money = intval($prepay_price_money);
$total_prepay = ($prepay_money);
echo $check . "@" . $total_prepay;
$conn->Close();
exit;
?>

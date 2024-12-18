<?php
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberInfoDAO();

//$conn->debug= 1;

$check = "T";

$session = $fb->getSession();
$member_seqno = $session["member_seqno"];

$today = date('Y-m-d');
$date  = explode('-', $today);
$first_day = $date[0] ."-". $date[1] ."-01";

$param = array();
$param["member_seqno"] = $member_seqno;
$param["from"] = $first_day;
$param["to"]   = $today;

$fields = $dao->selectMemberMonthlySales($conn, $param);

$net_sales      = intval($fields["net"]);
$card_net_sales = intval($fields["card_net"]);

$sum_net_sales  = $net_sales + $card_net_sales;

if ($sum_net_sales < 330000) {
    $check = "F";
}

if (empty($member_seqno)) {
    $check = "N";
}

echo $check;
exit;

?>

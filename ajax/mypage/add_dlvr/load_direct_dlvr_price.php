<?php
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");
include_once(INC_PATH . "/common_lib/DateUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberInfoDAO();
$DateUtil = new DateUtil();

//$conn->debug= 1;

$session = $fb->getSession();
$member_seqno = $session["member_seqno"];

$direct_price = 0;

$today = date('Y-m-d');
$date  = explode('-', $today);
$year  = $date[0];

// 25일 이전에 신청할 경우 이번달 월배송 신청임,
// ex) 7월 24일 신청시 직전달인 6월달 매출을 검색함
// 실제 월배송이 걸리는 달은 7월임, 8월이 아님(기간이 짧음)
if ($date[2] > 0 && $date[2] < 25) {
    // 직전달 첫날 ~ 말일까지의 매출 확인 
    $last_month = $date[1] - 1;
    if ($last_month < 1) {
        $last_month = 12;
        $year = $year - 1;
    }
    $last_month_last_day = $DateUtil->getLastDay($year, $last_month);
    $month_start = $year ."-". $last_month ."-01";
    $month_end   = $year ."-". $last_month ."-". $last_month_last_day;

// 25일부터는 이번달 매출을 기준(첫날부터 오늘까지)으로 잡는다.
} else {
    $month_start = $year ."-". $date[1] ."-01";    
    $month_end   = $today;   
}

$param = array();
$param["member_seqno"] = $member_seqno;
$param["from"] = $month_start;
$param["to"]   = $month_end;

$fields = $dao->selectMemberMonthlySales($conn, $param);

$net_sales      = intval($fields["net"]);
$card_net_sales = intval($fields["card_net"]);

$sum_net_sales  = $net_sales + $card_net_sales;

if ($sum_net_sales < 330000) {
    $direct_price = 55000;
} else {
    $direct_price = 0;
}
unset($param);

echo number_format($direct_price) . "원";

?>

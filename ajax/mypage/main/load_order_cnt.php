<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MainDAO.inc"); 


$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$template = new Template();
$frontUtil = new FrontCommonUtil();
$dao = new MainDAO();

$session = $fb->getSession();
$seqno = $session["org_member_seqno"];

$state_arr = $session["state_arr"];

$member_dvs = $session["member_dvs"];

if ($member_dvs == "기업") {

    $param = array();
    $param["member_seqno"] = $seqno;

    $rs = $dao->getBuPerSeqno($conn, $param);

    $bu_seqno = $seqno;
    while ($rs && !$rs->EOF) {

        $bu_seqno .= "," . $rs->fields["member_seqno"];
        $rs->moveNext();
    }
}

$param = array();
if ($member_dvs == "기업") {
    $param["member_seqno"] = $bu_seqno;
} else {
    $param["member_seqno"] = $seqno;
}

$today = date("Y.m.d");
$a_week_ago = date("Y.m.d", mktime(0,0,0,date("m"), date("d")-6, date("Y")));

$param["not"] = $state_arr["주문취소"];
$param["from"] = $a_week_ago;
$param["to"] = $today;

$rs = $dao->selectOrderStatusCount($conn, $param);

$tot_cnt = intval($rs->fields["cnt"]);

$param["not"] = "";

//상태
$state_rs = $dao->selectStateAdminDvs($conn);
$count_arr = array();
while ($state_rs && !$state_rs->EOF) {
    $dvs = $state_rs->fields["dvs"];

    $range = $dao->selectStateAdminRange($conn, $dvs);

    $param["state_min"] = $range["min"];
    $param["state_max"] = $range["max"];

    $rs = $dao->selectOrderStatusCount($conn, $param);
    $count_arr[$dvs] = $rs->fields["cnt"];

    $state_rs->MoveNext();
}

$tot_cnt -= intval($count_arr["주문"]);
$tot_cnt  = $tot_cnt < 0 ?  0 : $tot_cnt;

echo $tot_cnt . "♪♭§" . $count_arr["입금"] . "♪♭§" . 
     $count_arr["접수"] . "♪♭§" .
     $count_arr["조판"];/* . "♪♭§" . 
     $count_arr[""] . "♪♭§" .
     $count_arr[""] . "♪♭§" . 
     $count_arr[""];*/

$conn->Close();
?>

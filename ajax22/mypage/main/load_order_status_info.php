<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc"); 
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MainDAO.inc"); 
include_once(INC_PATH . "/com/nexmotion/doc/front/mypage/MainDOC.inc");
include_once(INC_PATH . "/common_lib/DateUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$template = new Template();
$frontUtil = new FrontCommonUtil();
$dateUtil = new DateUtil();
$dao = new MainDAO();

$typ = $fb->form("typ");
$session = $fb->getSession();
//$seqno = $session["org_member_seqno"];
$seqno = $session["member_seqno"];
$member_dvs = $session["member_dvs"];

//$conn->debug = 1;

//오늘
$today = date("Y.m.d");
$ymd_arr = explode('.', $today);
$dateUtil->setData([
    'y' => $ymd_arr[0],
    'm' => $ymd_arr[1],
    'd' => $ymd_arr[2]
]);

//기간
if ($typ == "W") {
    //최근 1주
    $from = $dateUtil->calcDate('d', -7);
    $from = sprintf("%s.%s.%s", $from['y']
                              , str_pad(strval($from['m']), 2, '0', STR_PAD_LEFT)
                              , str_pad(strval($from['d']), 2, '0', STR_PAD_LEFT));
    $to = $today;
} else if ($typ == "TW") {
    //최근 2주
    $from = $dateUtil->calcDate('d', -14);
    $from = sprintf("%s.%s.%s", $from['y']
                              , str_pad(strval($from['m']), 2, '0', STR_PAD_LEFT)
                              , str_pad(strval($from['d']), 2, '0', STR_PAD_LEFT));
    $to = $today;
} else if ($typ == "CM") {
    $last_day = $dateUtil->getLastDayByData();
    //당월
    $base = date("Y.m.");
    $from = $base . "01";
    $to = $base . str_pad(strval($last_day), 2, '0', STR_PAD_LEFT);
} else if ($typ == "PM") {
    //전월
    $dateUtil->setData([
        'y' => $ymd_arr[0],
        'm' => intval($ymd_arr[1]) - 1,
        'd' => 1 
    ]);
    $from = $dateUtil->getDateString('.');

    $last_day = $dateUtil->getLastDayByData();
    $dateUtil->setData([
        'y' => $ymd_arr[0],
        'm' => intval($ymd_arr[1]) - 1,
        'd' => $last_day
    ]);
    $to = $dateUtil->getDateString('.');
}

$period = $from . " ~ " . $to;

if ($member_dvs == "기업") {

    $param = [];
    $param["member_seqno"] = $seqno;

    $rs = $dao->getBuPerSeqno($conn, $param);

    $bu_seqno = $seqno;
    while ($rs && !$rs->EOF) {

        $bu_seqno .= "," . $rs->fields["member_seqno"];
        $rs->moveNext();
    }
}

//전체주문
$param = [];
if ($member_dvs == "기업") {
    $param["member_seqno"] = $bu_seqno;
} else {
    $param["member_seqno"] = $seqno;
}

$state_arr = $session["state_arr"];

$not = $dao->arr2paramStr($conn, [$state_arr["주문취소"],
                                  $state_arr["주문대기"]]);

$param["not"] = $not;
$param["from"] = $from;
$param["to"] = $to;

$rs = $dao->selectOrderStatusCount($conn, $param);

$tot_cnt = $rs->fields["cnt"];

$param["not"] = "";

//상태
$state_rs = $dao->selectStateAdminDvs($conn);
$count_arr = [];
while ($state_rs && !$state_rs->EOF) {
    $dvs = $state_rs->fields["dvs"];

    $range = $dao->selectStateAdminRange($conn, $dvs);

    $param["state_min"] = $range["min"];
    $param["state_max"] = $range["max"];

    $rs = $dao->selectOrderStatusCount($conn, $param);
    $count_arr[$dvs] = $rs->fields["cnt"];

    $state_rs->MoveNext();
}

// 장바구니
$param["state_min"] = $state_arr["주문대기"];
$param["state_max"] = $state_arr["주문대기"];
$rs = $dao->selectOrderStatusCount($conn, $param);
$count_arr["장바구니"] = $rs->fields["cnt"];

/*
$param["dvs"] = "COUNT";
$rs = $dao->selectOrderList($conn, $param);

$param["count"] = $dao->selectFoundRows($conn);

$param["member_dvs"] = $member_dvs;
*/

//주문 상태 HTML
$param = [];
$param["design_dir"] = "/design_template";
$param["tot_cnt"] = $tot_cnt;
$param["state_arr"] = $count_arr;

echo orderStatus22($param) . "♪" . $period . "♪" . $from . "♪" . $to;
$conn->Close();
?>

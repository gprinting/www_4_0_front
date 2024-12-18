<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/eventmall/EventmallDAO.inc");

$frontUtil = new FrontCommonUtil();
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new EventmallDAO();

$cp_seqno = $fb->form("cp_seqno");
$member_seqno = $fb->session("member_seqno");
$issue_date = date("Y-m-d H:i:s");
$use_able_start_date = $fb->form("use_able_start_date");
$use_deadline = $fb->form("use_deadline");
$use_yn = "N";

//랜덤 쿠폰번호 생성
$cp_num = "";

$feed = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
for ($i=0; $i < 10; $i++) {
    $cp_num .= substr($feed, rand(0, strlen($feed)-1), 1);
}

$conn->StartTrans();

//한아이디에서 중복으로 쿠폰 발급 막음
$param = array();
$param["table"] = "cp_issue";
$param["where"]["cp_seqno"] = $cp_seqno;
$param["where"]["member_seqno"] = $member_seqno;
$rs_cnt = $dao->countData($conn, $param);
$cnt = $rs_cnt->fields["cnt"];
if ($cnt > 0) {
    $conn->FailTrans();
    $conn->RollbackTrans();
    goto OverlapErr;
}

$param = array();

$param["table"] = "cp_issue";
$param["col"]["cp_seqno"] = $cp_seqno;
$param["col"]["cp_num"] = $cp_num;
$param["col"]["member_seqno"] = $member_seqno;
$param["col"]["issue_date"] = $issue_date;
if ($use_deadline != '제한없음') {
    $param["col"]["use_deadline"] = $use_deadline. " 23:59:59";
}
$param["col"]["use_yn"] = $use_yn;
$param["col"]["use_able_start_date"] = $use_able_start_date. " 00:00:00";

$dao->insertData($conn, $param);

if ($conn->HasFailedTrans() === true) {
    $conn->FailTrans();
    $conn->RollbackTrans();
    goto ERR;
}
$conn->CompleteTrans();

echo 'T';
$conn->Close();
exit;

ERR:
    echo 'F';
    $conn->Close();
    exit;

OverlapErr:
    echo 'O';
    $conn->Close();
    exit;
?>

<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberInfoDAO();
$check = 1;

//$conn->debug = 1;

$conn->StartTrans();

//회원 탈퇴신청
$param = array();
$param["member_seqno"] = $fb->form("seqno");

$rs = $dao->updateMemberWithdraw($conn, $param);

if (!$rs) {
    $check = 0;
    $conn->FailTrans();
    $conn->RollbackTrans();
}

unset($param);
//회원탈퇴 입력
$param = array();
$param["table"] = "member_withdraw";
$param["col"]["withdraw_code"] = $fb->form("withdraw_code");
$param["col"]["withdraw_dvs"] = "본인탈퇴";
$param["col"]["reason"] = $fb->form("reason");
$param["col"]["withdraw_date"] = date("Y-m-d H:i:s");
$param["col"]["member_seqno"] = $fb->form("seqno");

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
    $conn->FailTrans();
    $conn->RollbackTrans();
}

// 180523 추가 : 회원탈퇴 시 소셜로그인 연동 되어있는 정보 싹다 날림
unset($param);
$param = array();
$param["member_seqno"] = $fb->form("seqno");

$del_rs = $dao->deleteMemberSubId($conn, $param);

if (!$del_rs) {
    $check = 0;
    $conn->FailTrans();
    $conn->RollbackTrans();
}

// 171213 이청산 수정 : 포인트 관련해선 추가로 정책이 나온 후 결정
/*
//회원 쿠폰 삭제
$param = array();
$param["table"] = "cp_issue";
$param["prk"] = "member_seqno";
$param["prkVal"] = $fb->form("seqno");

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

//회원 포인트 내역 삭제
$param = array();
$param["table"] = "member_point_history";
$param["prk"] = "member_seqno";
$param["prkVal"] = $fb->form("seqno");

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}

//회원 포인트 요청 삭제
$param = array();
$param["table"] = "member_point_req";
$param["prk"] = "member_seqno";
$param["prkVal"] = $fb->form("seqno");

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}
*/

echo $check;
$conn->CompleteTrans();
$conn->close();
?>

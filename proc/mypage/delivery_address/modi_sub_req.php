<?
include_once($_SERVER["INC"] . "/define/front/common_config.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/MemberDlvrDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberDlvrDAO();
$conn->StartTrans();

$check = 1;

$param = array();
$param["table"] = "dlvr_friend_main";
$param["col"] = "state";
$param["where"]["member_seqno"] = $fb->session("member_seqno");
$result = $dao->selectData($conn, $param);
if ($result->fields["state"]) {
    $state = $result->fields["state"];
    if ($state == "2") {
        echo "배송친구 메인은 배송친구를 신청할수 없습니다.";
    } else if ($state == "1") {
        echo "이미 배송친구 메인 신청 하셨습니다.\n배송친구 메인 신청시 배송친구 신청은 불가합니다.";
    }
    exit;
}

$param = array();
$param["table"] = "dlvr_friend_sub";
$param["col"] = "state";
$param["where"]["member_seqno"] = $fb->session("member_seqno");
$result = $dao->selectData($conn, $param);
if ($result->fields["state"]) {
    $state = $result->fields["state"];
    if ($state == "2") {
        echo "이미 배송친구가 있습니다. 관리자에게 문의하세요";
    } else if ($state == "1") {
        echo "이미 배송친구를 신청하셨습니다.";
    }
    exit;
}

$param = array();
$param["table"] = "dlvr_friend_sub";
$param["col"]["dlvr_friend_main_seqno"] = $fb->form("main_seqno");
$param["col"]["member_seqno"] = $fb->session("member_seqno");
$param["col"]["state"] = "1";
$param["col"]["regi_date"] = date("Y-m-d", time());

$result = $dao->insertData($conn, $param);
if (!$result) $check = 0;

$param = array();
$param["member_seqno"] = $fb->session("member_seqno");
$param["dlvr_friend_main"] = "N";
$result = $dao->updateMemberDlvrFriend($conn, $param);
if (!$result) $check = 0;

echo $check;
$conn->CompleteTrans();
$conn->close();
?>

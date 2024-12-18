<?
include_once($_SERVER["INC"] . "/define/front/common_config.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/MemberDlvrDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$check = "삭제되었습니다.";

$fb = new FormBean();
$dao = new MemberDlvrDAO();
$conn->StartTrans();

$arr = array();
$arr = explode("&", $fb->form("seq"));
$member_dlvr_seqno = "";

for($i=0; $i<count($arr); $i++) {
    
    $seq = array();
    $seq = explode("=", $arr[$i]);

    $member_dlvr_seqno .= ",".$seq[1];
}

$param = array();
$param["member_dlvr_seqno"] = substr($member_dlvr_seqno, 1);
$result = $dao->deleteDlvr($conn, $param);

if (!$result) {
    $check = "삭제에 실패했습니다.";
    $conn->FailTrans();
    $conn->RollbackTrans();
}

echo $check;
$conn->CompleteTrans();
$conn->close();
?>

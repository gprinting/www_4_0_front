<?
include_once($_SERVER["INC"] . "/define/front/common_config.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/MemberDlvrDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$check = "기본배송지를 변경했습니다.";

$fb = new FormBean();
$dao = new MemberDlvrDAO();
$conn->StartTrans();

$param = array();
$param["member_dlvr_seqno"] = $fb->form("seq");
$param["member_seqno"] = $fb->session("org_member_seqno");
$result = $dao->updateBasicDlvr($conn, $param);

if (!$result)
    $check = "기본배송지 변경에 실패했습니다.";

echo $check;
$conn->CompleteTrans();
$conn->close();
?>

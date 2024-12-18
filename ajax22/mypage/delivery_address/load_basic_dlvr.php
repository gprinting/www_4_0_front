<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberDlvrDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();
$dao = new MemberDlvrDAO();

//$conn->debug = 1;

$param = array();
$param["seqno"] = $fb->session("org_member_seqno");
$rs = $dao->selectBasicDlvr($conn, $param);

//171109 이청산 주석처리(필요없는 내용)
//$rs_dlvr = $dao->selectMemberDlvrDvs($conn, $param);
$rs_dlvr = "";
echo $rs->fields["addr"] . "♪" . $rs->fields["addr_detail"] . "♪" . $fb->session("member_seqno") . "♪" . $fb->session("org_member_seqno") . "♪" . $rs->fields["member_dlvr_seqno"];
$conn->close();
?>

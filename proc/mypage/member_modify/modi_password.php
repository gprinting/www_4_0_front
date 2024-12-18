<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MemberInfoDAO();

$check = 1;

$member_seqno = $fb->form("member_seqno");
$exi_passwd = $fb->form("exi_passwd");
$new_passwd = $fb->form("new_passwd");

$param = array();
$param["member_seqno"] = $member_seqno;
$param["old_password"] = $exi_passwd;
$rs = $dao->selectMemberPw($conn, $param);

$pw = $rs->fields["passwd"];
if ($pw == null) {
    echo "2";
    exit;
}

//$new_passwd = password_hash($new_passwd, PASSWORD_DEFAULT);

$conn->StartTrans();

$param = array();
$param["member_seqno"] = $member_seqno;
$param["passwd"] = $new_passwd;

$rs = $dao->updateMemberPw($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>

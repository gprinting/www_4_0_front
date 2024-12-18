<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MemberInfoDAO();

$check = 1;

$conn->StartTrans();

$param = array();
$param["member_seqno"] = $fb->form("member_seqno");
$param["member_name"] = $fb->form("member_name");
$param["posi"] = $fb->form("posi");
$param["passwd"] = password_hash($fb->form("passwd"), PASSWORD_DEFAULT);
$param["tel_num"] = $fb->form("tel_num");
$param["cell_num"] = $fb->form("cell_num");
$param["mail"] = $fb->form("mail");

$rs = $dao->updateCoOrderMng($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>

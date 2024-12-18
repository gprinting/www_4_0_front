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
$param["table"] = "accting_mng";
$param["col"]["name"] = $fb->form("name");
$param["col"]["posi"] = $fb->form("posi");
$param["col"]["mail"] = $fb->form("mail");
$param["col"]["tel_num"] = $fb->form("tel_num");
$param["col"]["cell_num"] = $fb->form("cell_num");
$param["col"]["member_seqno"] = $fb->session("org_member_seqno");

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>

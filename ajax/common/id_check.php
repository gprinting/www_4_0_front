<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/member/MemberJoinDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MemberJoinDAO();

//$conn->debug= 1;

$member_id = $fb->form("member_id");

if (empty($member_id)) {
    echo "1";
    echo "false";
    exit;
}

$param = [];
$param["member_id"] = $member_id;

$rs = $dao->selectIdCheck($conn, $param);

$cnt = $rs->fields["cnt"];

if ($cnt == 0) {
    echo "true";
} else {
    echo "false";
}
$conn->Close();
?>

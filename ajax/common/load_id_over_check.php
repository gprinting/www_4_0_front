<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/member/MemberJoinDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MemberJoinDAO();

//$conn->debug= 1;

$mail = $fb->form("mail");

if (!$mail) {
    echo "1";
    echo "false";
    exit;
}

$ptrn = "/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i";

if (!preg_match($ptrn, $mail)) {
    echo "false";
    exit;
}

if(strpos($_SERVER["HTTP_REFERER"], "gprinting") !== false){
	$dpgp = 'GP';
} else {
	$dpgp = 'DP';
}

$param = [];
$param["mail"] = $mail;
$param["sell_channel"] = $dpgp;

$rs = $dao->selectIdOverCheck($conn, $param);

$cnt = $rs->fields["cnt"];

if ($cnt == 0) {
    //$fb->addSession("member_join_id_chk", "1");
    $_SESSION["member_join_id_chk"] = "1";
    echo "true";
} else {
    unset($_SESSION["member_join_id_chk"]);
    echo "false";
}
$conn->Close();
?>

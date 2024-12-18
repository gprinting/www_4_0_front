<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . '/com/nexmotion/common22/util/ConnectionPool.inc');
include_once(INC_PATH . '/com/nexmotion/common22/util/front/FrontCommonUtil.inc');
include_once(INC_PATH . "/com/nexmotion/common22/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/service/FileListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new FrontCommonUtil();
$fb = new FormBean();
$dao = new FileListDAO();

$param = array();
$param["table"] = "share_library_file";
$param["col"] = "file_path, save_file_name, origin_file_name";
$param["where"]["share_library_seqno"] = $fb->form("seqno");

$rs = $dao->selectData($conn, $param);

$file_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE . $rs->fields["file_path"] . '/' . $rs->fields["save_file_name"];
$file_size = filesize($file_path);

if (!is_file($file_path)) {
    echo "<script>alert('파일이 존재 하지 않습니다.');</script>";
    exit;
}

$down_file_name = $rs->fields["origin_file_name"];
if ($util->isIe() === true) {
    $down_file_name = $util->utf2euc($down_file_name);
}

header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$down_file_name\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $file_size");

//ob_clean(); // 180509 왠진 모르겠지만 이게 있으면 다운받은 파일이 안 열림 그래서 주석처리
flush();
readfile($file_path);
?>

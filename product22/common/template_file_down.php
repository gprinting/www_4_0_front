<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . '/com/nexmotion/common/util/ConnectionPool.inc');
include_once(INC_PATH . '/common_define/common_config.inc');
include_once(INC_PATH . '/common_lib/CommonUtil.inc');
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new CommonUtil();
$dao = new ProductCommonDAO();

//$conn->debug = 1;

$seqno = $_GET["seqno"];
$dvs   = $_GET["dvs"];

$param = array();
$param["cate_template_seqno"] = $seqno;
$rs = $dao->selectCateTemplateFileInfo($conn, $param)->fields;

$file_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE .
             $rs[$dvs . "_file_path"] .
             $rs[$dvs . "_save_file_name"];

if (!is_file($file_path)) {
    echo "<script>alert('템플릿이 존재하지 않습니다.');</script>";
    exit;
}

$down_file_name = $rs[$dvs . "_origin_file_name"];
$file_size = filesize($file_path);
if ($util->isIe()) {
    $down_file_name = $util->utf2euc($down_file_name);
}

header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . $down_file_name . "\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $file_size");

ob_clean();
readfile($file_path);
?>

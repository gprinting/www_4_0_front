<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . '/com/nexmotion/common22/util/ConnectionPool.inc');
include_once(INC_PATH . "/com/nexmotion/job/front/common22/FrontCommonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common22/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/EstiInfoDAO.inc");
include_once(INC_PATH . '/common_lib/CommonUtil.inc');
include_once(INC_PATH . "/common_define/common_config.inc");
include_once(INC_PATH . "/common_define/cpn_info_define.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new FrontCommonDAO();
$util = new CommonUtil();

$rs = $dao->selectChannelInfo(
    $conn, ["sell_site" => $_SERVER["SELL_SITE"]]
);
$sell_site = $rs["company_name"];

$base_path = $_SERVER["DOCUMENT_ROOT"] . EXCEL_TEMPLATE;
$file_name = $fb->form("filename");
$file_path = $base_path . $file_name . ".xlsx";
$file_size = filesize($file_path);

$down_file_name = $sell_site . '_';

if ($fb->form("dvs") === "esti") {
    $down_file_name .= "견적서.xlsx";
} else if ($fb->form("dvs") === "payment") {
    $down_file_name .= $fb->form("start") . '~';
    $down_file_name .= $fb->form("end") . "_거래명세표.xlsx";
}

if ($util->isIe()) {
    $down_file_name = $util->utf2euc($down_file_name);
}

header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$down_file_name\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $file_size");

flush();
if (readfile($file_path) !== false) {
    unlink($file_path);
}
?>

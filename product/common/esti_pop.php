<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/html/front/product/EstimatePop.inc");
include_once(INC_PATH . "/common_define/prdt_default_info.inc");

// 실제 html 생성부분
include_once($_SERVER["DOCUMENT_ROOT"] . "/product/common/esti_pop_common.php");

$data = $_GET["param"];

print_r($data);

$html_top = getHtmlTop($param);
if ($booklet === 'Y') {
    $html_mid = getBookletHtmlMid($param);
} else {
    $html_mid = getHtmlMid($param);
}
$html_mid_bot = getHtmlMidBot($param, $aft_arr);
$html_bot = getHtmlBot($param);

//echo $html_top . $html_mid . $html_mid_bot . $html_bot;

echo $html_top;

$conn->Close();
exit;
?>

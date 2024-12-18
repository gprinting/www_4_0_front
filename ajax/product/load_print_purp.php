<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ProductCommonDAO();
$fb = new FormBean();

$conn->debug=1;

$sell_site = $fb->session("sell_site");

$cate_sortcode = $fb->form("cate_sortcode");
$print_name    = $fb->form("val");

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["name"]          = $print_name;

echo $dao->selectCatePrintPurpHtml($conn, $param);

$conn->Close();
?>

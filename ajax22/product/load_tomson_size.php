<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/product/ProductSheetTomsonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ProductSheetTomsonDAO();
$fb = new FormBean();

$sell_site = $fb->session("sell_site");

$cate_sortcode = $fb->form("cate_sortcode");
$typ           = $fb->form("typ");

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["typ"]           = $typ;

$temp = array();

echo $dao->selectCateSizeHtml($conn,
                              $param,
                              $temp);

$conn->Close();
?>

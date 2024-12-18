<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/define/front/common_config.inc");
include_once(INC_PATH . "/define/front/message.inc");
include_once(INC_PATH . "/com/nexmotion/html/front/product/QuickEstimate.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/product/ProductNcDAO.inc");
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");

//include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");
//$dao = new ProductCommonDAO();

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ProductNcDAO();
$frontUtil = new FrontCommonUtil();
$fb = new FormBean();

$cate_sortcode = $fb->form("cate_sortcode");

$sortcode_arr = $frontUtil->getTMBCateSortcode($conn, $dao, $cate_sortcode);

$sortcode_t = $sortcode_arr["sortcode_t"];
$sortcode_m = $sortcode_arr["sortcode_m"];
$sortcode_b = $sortcode_arr["sortcode_b"];

// 카테고리 셀렉트박스 생성
$cate_top = $dao->selectCateHtml($conn, $sortcode_t);
$cate_mid = $dao->selectCateHtml($conn, $sortcode_m, $sortcode_t);
$cate_bot = $dao->selectCateHtml($conn, $sortcode_b, $sortcode_m);

echo $cate_bot; 
$conn->Close();
?>

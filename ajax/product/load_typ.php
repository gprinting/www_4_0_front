<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/product/ProductSheetCutDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new CommonUtil();
$dao = new ProductSheetCutDAO();
$fb = new FormBean();

$cate_sortcode = $fb->form("cate_sortcode");
$stan_name     = $fb->form("stan_name");
$pos_yn        = ($fb->form("pos_yn") === 'Y') ? true : false;

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["stan_name"]     = $stan_name;

$temp = [];

$html = $dao->selectCateStanTypHtml($conn,
                                    $param,
                                    $temp,
                                    $pos_yn,
                                    false,
                                    false);

echo $html;

$conn->Close();
?>

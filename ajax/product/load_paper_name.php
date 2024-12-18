<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProductCommonDAO();

$fb = $fb->getForm();

$cate_sortcode = $fb["cate_sortcode"];
$paper_sort    = $fb["sort"];

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["sort"] = $paper_sort;

$temp = array();
$paper_name = $dao->selectCatePaperNameHtml($conn,
                                            $param,
                                            $temp);
echo $paper_name;
?>

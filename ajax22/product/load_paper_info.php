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
$paper_name    = $fb["name"];

$param = [];
$param["cate_sortcode"] = $cate_sortcode;
$param["name"] = $paper_name;
$param["paper_flag"] = [
     "name"        => false
    ,"color"       => true
    ,"dvs"         => true
    ,"basisweight" => true
];

$temp = [];
$paper = $dao->selectCatePaperHtml($conn, $param, $temp)["info"];

echo $paper;
?>

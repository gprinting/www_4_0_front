<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/OptPriceUtil.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new CommonUtil();
$dao = new ProductCommonDAO();
$fb = new FormBean();

$cate_sortcode  = $fb->form("cate_sortcode");
$name           = $fb->form("name");
$mpcode         = $fb->form("mpcode");
$amt            = $fb->form("amt");
$sell_price     = $fb->form("sell_price");
$paper_mpcode   = $fb->form("paper_mpcode");
$paper_info     = $fb->form("paper_info");
$expect_box     = $fb->form("expect_box");
$affil          = $fb->form("affil");

$param = [];
$param["conn"] = $conn;
$param["dao"]  = $dao;
$param["util"] = $util;
$optUtil = new OptPriceUtil($param);

$param["cate_sortcode"] = $cate_sortcode;
$param["name"]          = $name;
$param["mpcode"]        = $mpcode;
$param["amt"]           = $amt;
$param["sell_price"]    = $sell_price;
$param["paper_mpcode"]  = $paper_mpcode;
$param["paper_info"]    = $paper_info;
$param["expect_box"]    = $expect_box;
$param["affil"]         = $affil;

$info_arr = $optUtil->calcOptPrice($param);

unset($opt_info_rs);
unset($param);

// json 생성
$ret  = '{';
$ret .=   "\"price\" :\"%s\",";
$ret .=   "\"mpcode\":\"%s\"";
$ret .= '}';

$price = $util->ceilVal($price);

echo sprintf($ret, $info_arr["price"], $info_arr["mpcode"]);
$conn->Close();
exit;

BLANK:
    echo sprintf($ret, '0', $mpcode);
    $conn->Close();
    exit;
?>

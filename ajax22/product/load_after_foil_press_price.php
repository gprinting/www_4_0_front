<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/AftPriceUtil.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ProductCommonDAO();
$fb = new FormBean();
//$conn->debug = 1;
$util = new AftPriceUtil([
    "conn" => $conn,
    "dao"  => $dao
]);

$fb = $fb->getForm();

$cate_sortcode = $fb["cate_sortcode"];
$aft           = $fb["aft"];
$amt           = $fb["amt"];
$aft_1         = $fb["aft_1"];
$dvs_1         = $fb["dvs_1"];
$aft_2         = $fb["aft_2"];
$dvs_2         = $fb["dvs_2"];
$aft_3         = $fb["aft_3"];
$dvs_3         = $fb["dvs_3"];
$wid_1         = $fb["wid_1"];
$vert_1        = $fb["vert_1"];
$wid_2         = $fb["wid_2"];
$vert_2        = $fb["vert_2"];
$wid_3         = $fb["wid_3"];
$vert_3        = $fb["vert_3"];

$param = [];
$param["cate_sortcode"] = $cate_sortcode;
$param["aft"]           = $aft;
$param["amt"]           = $amt;
$param["aft_1"]         = $aft_1;
$param["dvs_1"]         = $dvs_1;
$param["aft_2"]         = $aft_2;
$param["dvs_2"]         = $dvs_2;
$param["aft_3"]         = $aft_3;
$param["dvs_3"]         = $dvs_3;
$param["wid_1"]         = $wid_1;
$param["vert_1"]        = $vert_1;
$param["wid_2"]         = $wid_2;
$param["vert_2"]        = $vert_2;
$param["wid_3"]         = $wid_3;
$param["vert_3"]        = $vert_3;


$ret = $util->getAfterFoilPressPrice($param);

if (!$ret["success"]) {
    echo -1;
    exit;
}

$json_form = "{\"p1\" : \"%s\", \"p2\" : \"%s\", \"p3\" : \"%s\", \"aft_depth1\" : \"%s\", \"aft_vert\" : \"%s\", \"aft_wid\" : \"%s\", \"aft_depth\" : \"%s\", \"aft_name\" : \"%s\", \"aft_depth1_dvs\" : \"%s\", \"price\" : \"%s\", \"mpcode\" : \"%s\"}";

echo sprintf($json_form, number_format($ret["p1"])
                       , number_format($ret["p2"])
                       , number_format($ret["p3"])
                       , $ret["aft_depth1"]
                       , $ret["aft_vert"]
                       , $ret["aft_wid"]
                       , $ret["aft_depth"]
                       , $ret["aft_name"]
                       , $ret["aft_depth1_dvs"]
                       , $ret["price"]
                       , $ret["mpcode"]);

$conn->Close();
exit;
?>

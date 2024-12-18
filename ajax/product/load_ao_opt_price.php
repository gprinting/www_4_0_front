<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/product/ProductAoDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new FrontCommonUtil();
$dao = new ProductAoDAO();
$fb = new FormBean();

$fb = $fb->getForm();

$name = $fb["name"];
$depth1 = $fb["depth1"];
$depth2 = $fb["depth2"];
$depth3 = $fb["depth3"];
$amt    = intval($fb["amt"]);

$param = [];
$param["opt_name"] = $name;
$param["depth1"] = $depth1;
$param["depth2"] = $depth2;
$param["depth3"] = $depth3;

$unitprice = $dao->selectAoOptUnitprice($conn, $param);

$price  = $unitprice * $amt;
$price *= 1.1;

$ret = "{\"price\" : \"%s\"}";

echo sprintf($ret, $util->ceilVal($price));
$conn->Close();
exit;

BLANK:
    echo "{}";
    $conn->Close();
    exit;

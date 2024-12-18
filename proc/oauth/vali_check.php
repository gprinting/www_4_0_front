<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . '/test-devel/testDAO.php');
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new testDAO();

$fb = $fb->getForm();

$email = $fb["idw"];
$join_path = "통상";

//$conn->debug=1;

$param = array();
$param["mail"] = $email;
$param["join_path"] = $join_path;

$rs = $dao->selectDuplEmail($conn, $param);

if ($rs->EOF) {
    $rs = "검색결과없음";
} else { 
    $rs = "중복결과있음";
} 
echo $rs;

?>

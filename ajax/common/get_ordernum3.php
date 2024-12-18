<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");


$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new FrontCommonDAO();
$fb = new FormBean();


$param = array();
$param["col"]   = "order_num";
$param["table"] = "order_common";
$param["where"]["OPI_Date"] = $fb->form("OPI_Date");
$param["where"]["OPI_Seq"] = $fb->form("OPI_Seq");

$rs = $dao->selectData($conn, $param);

echo $rs->fields["order_num"];
exit;
?>

<?

define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/MoamoaDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MoamoaDAO();
$fb = new FormBean();

$state_code = "200";

$param = [];
$param['machine_id'] = $fb->form("id");
$rs = $dao->UpdateAutoWaitingOrder($conn, $param);


$ret = array();
if($rs == null) {
    $state_code = "400";
}

//while($rs && !$rs->EOF) {
//    array_push($ret,$rs->fields["order_num"]);
//    $rs->MoveNext();
//}
$rs = $dao->selectAutoProductInfo($conn, $param);
//$rs = $dao->selectAutoProductInfo_TEST($conn, $param);

$result = array();
$result["code"] = $state_code;
$result["message"] = "ok";

$data = array();
$data["data"] = $rs;

$ret["result"] = $result;
$ret["data"] = $rs;

echo json_encode($ret);


$conn->Close();
?>

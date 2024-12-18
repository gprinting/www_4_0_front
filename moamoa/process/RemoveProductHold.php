<?

define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/MoamoaDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MoamoaDAO();
$fb = new FormBean();

$param = [];
$param['ordernum'] = $fb->form("OrderNum");

$state_code = "200";$conn->debug = 1;
$rs = $dao->updateProductHold($conn, $param);
if($rs == null) {
    $state_code = "400";
}

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

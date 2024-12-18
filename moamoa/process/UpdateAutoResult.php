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
$param['result'] = $fb->form("result");
$param['order_common_seqno'] = $fb->form("order_common_seqno");
$param['error_list'] = $fb->form("error_list");
if($param['result'] == "success")
    $rs = $dao->UpdateAutoResultSuccess($conn, $param);
else
    $rs = $dao->UpdateAutoResultFail($conn, $param);


$ret = array();
if($rs == null) {
    $state_code = "400";
}

$result = array();
$result["code"] = $state_code;
$result["message"] = "ok";


echo json_encode($result);


$conn->Close();
?>

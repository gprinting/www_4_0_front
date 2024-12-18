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
$param['empl_id'] = $fb->form("id");

$state_code = "200";

$rs = $dao->selectedAllocatedOrder($conn, $param);

$ret = array();
if($rs == null) {
    $state_code = "400";
}
while($rs && !$rs->EOF) {
    array_push($ret,$rs->fields["order_num"]);
    $rs->MoveNext();
}

echo json_encode($ret);


$conn->Close();
?>

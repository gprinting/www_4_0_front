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
$param['state'] = "1385";
$param['receipt_memo'] = $fb->form("Message");

$rs = $dao->updateReceiptMemo($conn, $param);

// 출력완료 이후는 조판대기로 상태변경이 불가함
$rs = $dao->selectProductStatecode($conn, $param);
$state = $rs['order_state'];

if((int)$state <= 2120) {
    $rs = $dao->updateProductStatecode($conn, $param);
    $rs = $dao->insertStateHistory($conn, $param);
}
$result_code = "200";




$result = array();
$result["code"] = $result_code;
$result["message"] = "ok";

$data = array();
$ret["result"] = $result;

echo json_encode($ret);

$conn->Close();
?>
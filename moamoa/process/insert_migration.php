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
$param['OPI_Date'] = $fb->form("opi_date");
$param['OPI_Seq'] = $fb->form("opi_seq");
$param['state3'] = $fb->form("state3");
$param['serialnum'] = $fb->form("serialnum");

if($param['state3'] == "1" || $param['state3'] == "2" || $param['state3'] == "40"
    || $param['state3'] == "46" || $param['state3'] == "99") {
    $rs = $dao->insertMigration($conn, $param);
}

if($param['state3'] == "100") {
    $rs = $dao->updateDlvrInfo($conn, $param);
}


$result = array();
$result["code"] = "200";
$result["message"] = "ok";

$data = array();
$ret["result"] = $result;

echo json_encode($ret);

$conn->Close();
?>
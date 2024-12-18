<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/MoamoaDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MoamoaDAO();
$fb = new FormBean();

$id = $fb->form("id");
$pw = $fb->form("password");

$pw = $dao->selectPassword($conn, $pw);
$rs = $dao->selectEmpl($conn, $id);

$pw_hash = $rs->fields["passwd"];
$name        = $rs->fields["name"];
$result = array();
if ($pw != $pw_hash) {
    $result["code"] = "901";
    $result["message"] = "not ok";
} else {
    $result["code"] = "200";
    $result["message"] = "ok";
    $result["name"] = $name;
}

$data = array();
$ret["result"] = $result;

echo json_encode($ret);

$conn->Close();
?>
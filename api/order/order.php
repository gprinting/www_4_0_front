<?

define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/classes/dprinting/PriceCalculator/Common/DPrintingFactory.php');
include_once(INC_PATH . '/com/dprinting/OrderApiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new OrderApiDAO();
$fb = new FormBean();
$fb = $fb->getForm();

$factory = new DPrintingFactory();
$product = $factory->create($fb["cate_sortcode"]);



$param = [];
$param['ordernum'] = $fb->form("OrderNum");
$param['isaccept'] = $fb->form("IsAccept");
$param['empl_id'] = $fb->form("User");

$rs = $dao->selectProductInfo($conn, $param);

$state_code = "200";

$result = array();
$result["code"] = $state_code;
$result["message"] = "ok";

$ret["result"] = $result;
$ret["data"] = $rs;

echo json_encode($ret);


$conn->Close();
?>
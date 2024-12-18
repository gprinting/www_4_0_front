<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new FrontCommonDAO();

$fb = new FormBean();
$conn->StartTrans();
$check = 1;
$check2 = 0;
$check3 = 0;
$result = "";
$b = array();

// $conn->debug = 1;

$session = $fb->getSession();

$param = array();

$param['chk_array'] = $_POST['check_array'];

foreach($param['chk_array'] as $row) {
	$rs = $dao->checkProduct($conn, $row);
	$check2 = $check2 + $rs->fields["num"];
	$check3++;
}

if($check2 != $check3){
	$check = "0";
}else{
	$param['add_minus_check'] = $_POST['add_minus_check'];
	$param['send_points'] = $_POST['send_points'];
	$param['mb_id_point'] = $session["id"];
	if($_POST['selboxDirect']){
		$param['add_minus_reason'] = $_POST['selboxDirect'];
	} else {
		$param['add_minus_reason'] = $_POST['add_minus_reason'];
	}

	$rs = $dao->selectMemberInfoPoint($conn, $param);


	$result = $dao->updatePoint2($conn, $param, $rs, $dao);
}
/*


*/
echo $check;
$conn->CompleteTrans();
$conn->close();
?>
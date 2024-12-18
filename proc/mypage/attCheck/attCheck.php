<?
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

// $conn->debug = 1;

$param = array();
$param["ae_seqno"] = $_POST['ae_seqno']; // 멤버번호
$mb_email = $_POST['mb_id']; // 멤버 id

$point = $dao->get_point_sum($conn, $param["ae_seqno"]);
// 현재 전체 포인트 내역 가져오기
$param['point_sum'] = $point['sum_point'];


//포인트 세팅값 가져오기
$attendance = $dao->selectAttendanceSetting($conn, $param["ae_seqno"]);

$param["point"] = $attendance['ae_point']; // 출석체크 포인트
$param["reset1"] = $attendance['ae_sequence']; // 10일차 추가포인트
$param["reset2"] = $attendance['ae_sequence2']; // 30일차 추가포인트


$AttCheck = $dao->todayAttendanceCheck($conn, $param);
if($AttCheck) {
	$check = 0; 
	return false;
} else {
	$check = 1;
	// 어제 출석체크 param 값 넘기기
	$ydCheck = $dao->ydAttendanceCheck($conn, $param);
	if($ydCheck){
		$param["ydCheck"] = 1; // 출석체크 포인트
		$PC = 1;
		$param["ae_reset1"] = $ydCheck['ae_reset1']; // 출석체크 포인트
		$param["ae_reset2"] = $ydCheck['ae_reset2']; // 출석체크 포인트
	} // 어제 출석체크 param 종료
	$result = $dao->todayAttendanceInsert($conn, $param);
 }
$conn->CompleteTrans();
$conn->close();
?>
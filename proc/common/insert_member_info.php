<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/member/MemberJoinDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/PasswordEncrypt.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$dao = new MemberJoinDAO();

$mail = $_POST["mail"];
$name = $_POST["name"];
$pw   = $_POST["pw"];
$hp   = $_POST["hp"]; // 전화번호
$cp   = $_POST["cp"]; // 휴대폰번호
$zipcode   = $_POST["zipcode"]; // 우편번호
$addr   = $_POST["addr"]; // 주소1
$addr_detail   = $_POST["addr_detail"]; // 상세주소
$card_pay_tax  = $_POST["card_pay_tax"]; // 세금계산서 동의 여부
$calling       = $_POST["sms_yn"]; // SMS 동의 여부
$mailing       = $_POST["mailing_yn"]; // 메일 동의 여부
$crn   = $_POST["crn"]; // 사업자번호
$member_name   = $_POST["member_name"]; // 대표이사 성함
$bc 	       = $_POST["bc"]; // 업종
$tob 	       = $_POST["tob"]; // 업태
$bemail 	   	   = $_POST["bemail"]; // 회사 이메일
$zipcode2  	   = $_POST["zipcode2"]; // 사업자 우편번호
$addr2 		   = $_POST["addr2"]; // 사업자 주소 
$addr_detail2  = $_POST["addr_detail2"]; // 사업자 주소2


if(strpos($_SERVER["HTTP_REFERER"], "gprinting") !== false){
	$dpgp = 'GP';
} else {
	$dpgp = 'DP';
}

//$pw   = password_hash($pw, PASSWORD_DEFAULT);

// if (empty($_SERVER["HTTP_REFERER"]) 
//         || strpos($_SERVER["HTTP_REFERER"], "goodprinting") === false) {
//     echo "error1";
//     exit;
// }

if (empty($_POST["member_join_id_chk"])) {
    echo "error2";
    exit;
}

// 값이 하나라도 없을 경우 튕김
if (empty($mail) || empty($name) || empty($pw)) {
    echo "error3";
    exit;
}

if($_POST['company'] === 'Y'){
	if (empty($crn) || empty($member_name) || empty($bc) || empty($tob) || empty($bemail) || empty($zipcode2) || empty($addr2) || empty($addr_detail2)) {
		echo "error4";
		exit;
	}
	$mdvs = '기업';
} else {
	$mdvs = '개인';
}

$check = 1;

$conn->StartTrans();
$param = array();
$param["mail"]        = $mail;
$param["name"]        = $name;
$param["pw"]          = $pw;
$param["withdraw_yn"] = "N";
$param["grade"]       = "20";
$param["join_path"]   = "Normal";
$param["member_dvs"]  = $mdvs;
$param["sell_channel"]  = $dpgp;
$param["tel_num"]          = $hp;
$param["cell_num"]          = $cp;
$param["zipcode"]          = $zipcode;
$param["addr"]          = $addr;
$param["addr_detail"]          = $addr_detail;
$param["card_pay_tax"]          = $card_pay_tax;
$param["sms_yn"]          = $calling;
$param["mailing_yn"]          = $mailing;

$rs = $dao->insertJoinerInfo($conn, $param);


if (!$rs) {
    $err_code = "100";
    $check = 0;
    $conn->RollbackTrans();
}

$conn->CompleteTrans();

if ($check == 0) {
    echo "<script>alert('오류 발생. \n에러코드 :"
        . $err_code . 
        " + 관리자에게 문의 바랍니다.')</script>";
} else {
    echo $check;
}


if($_POST['company'] === 'Y'){
	
		$param = array();
		$param["table"] = "member";
		$param["col"] = "member_seqno";
		$param["where"]["mail"] = "$mail";

		$member_seqno = $dao->selectData($conn, $param)->fields["member_seqno"];


		$check = 1;
		
		//$conn->debug = 1;
		$conn->StartTrans();

		$param = array();
		$param["table"] = "licensee_info";
		$param["col"]["corp_name"] = $name;
		$param["col"]["repre_name"] = $member_name;
		$param["col"]["crn"] = $crn;
		$param["col"]["zipcode"] = $zipcode2;
		$param["col"]["addr"] = $addr2;
		$param["col"]["addr_detail"] = $addr_detail2;
		$param["col"]["email"] = $bemail;
		$param["col"]["member_seqno"] = $member_seqno;
		$param["col"]["bc"] = $bc;
		$param["col"]["tob"] = $tob;

		$rs = $dao->insertData($conn, $param);

		if (!$rs) {
			$err_code = "101";
			$check = 0;

		}

		$conn->CompleteTrans();

}

$conn->Close();

?>
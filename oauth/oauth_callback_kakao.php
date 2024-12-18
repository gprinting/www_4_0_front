

<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/LoginUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");

$fb = new FormBean();
$session = $fb->getSession();
$fb = $fb->getForm();


$rest_api_key = "1ecaf2343e0a465dc303eaddb1df5b2f";        // REST API 키
$redirect_uri = "http://dev2.gprinting.co.kr/oauth/oauth_callback_kakao.php";  // Redirect URI
$code = $_GET['code'];
 

// 1) 사용자 토큰받기
$shell_string = "curl -v -X POST https://kauth.kakao.com/oauth/token -d 'grant_type=authorization_code' -d 'client_id={$rest_api_key}' -d 'redirect_uri={$redirect_uri}' -d 'code={$code}'";
$output = shell_exec($shell_string);
$token_json = json_decode($output, true);
 
// 토큰발급 실패
if (!$token_json['access_token']) {
    die("카카오 로그인에 실패했습니다. 다시 시도해 주세요.");
}
 
// 2) 사용자 정보받기
$shell_string = "curl -v -X POST https://kapi.kakao.com/v2/user/me -H 'Authorization: Bearer {$token_json['access_token']}'";
$output2 = shell_exec($shell_string);
$user_info_json = json_decode($output2, true);

//print_r($user_info_json);


$link_dvs = $fb["dvs"];
//$link_dvs = 1;

if ($link_dvs) {
    goto LINK;
}

$res = [
     "email" => $user_info_json['kakao_account']['email']
    ,"name"  => $user_info_json['kakao_account']['profile']["nickname"]
]; 
/*
$res = [
    "email" => "warrenkim86@gmail.com"
   ,"name"  => "슬픈개구리"
]; */


$loginUtil = new LoginUtil([
     "res" => $res
    ,"dvs" => "kakao"
]);

if (!$loginUtil->login()) {
    goto ERR;
}

goto END;

LINK:
    // 소셜계정 링크
    $connectionPool = new ConnectionPool();
    $conn = $connectionPool->getPooledConnection();
    $dao  = new FrontCommonDAO();
    
    //$mail = $fb["kaccount_email"];
    $mail = "warrenkim86@gmail.com";
    $path = "Kakao";

    $member_seqno = $session["member_seqno"];
    
    $param = array();
    $param["mail"] = $mail;
    $param["join_path"] = $path;
    $param["member_seqno"] = $member_seqno;
    
    $check = "";
    //$conn->debug = 1;
    
    // member tbl 확인
    $rs  = $dao->selectDuplSocialAcc($conn, $param);
    $cnt = $rs->fields["cnt"];
    
    // 이미 아이디가 있을 경우 판별
    if ($cnt != "0") {
        $check = "이미 연동된 아이디입니다.";
        goto LINK_ERR;
    }
    
    // 이미 다른 아이디에 연동되어 있을 경우 판별
    $rs_sub  = $dao->selectDuplSocialSub($conn, $param);
    $sub_cnt = $rs_sub->fields["sub_cnt"];
    if ($sub_cnt != "0") {
        $check = "이미 연동된 아이디입니다.";
        goto LINK_ERR;
    }
    
    // 여기까지 올 경우 연동 데이터 입력
    $conn->StartTrans();
    $rs_ins = $dao->insertSocialAccLink($conn, $param);
    
    if (!$rs_ins) {
        $check = "연동아이디 입력에 실패했습니다.";
        $conn->FailTrans();
        $conn->RollbackTrans();
        goto LINK_ERR;
    }
    
    // 카카오는 ajax방식이므로 다르게 처리
    $check = "sus1";
    $conn->CompleteTrans();
    $conn->Close();

    echo $check;
    goto END;

ERR:
    echo $loginUtil->err_msg;
    exit;
LINK_ERR:
    $conn->Close();
    echo $check;
    exit;
END:
    echo $loginUtil->redirPage();
    //echo '';
?>


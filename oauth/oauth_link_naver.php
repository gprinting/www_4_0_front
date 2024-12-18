<?php
// 네이버 연동 콜백
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");

$client_id = "FfUadoF0fkwujmqKpDbT";
$client_secret = "2Zjn2ef9bR";

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$code  = $_GET["code"];
$state = $_GET["state"];
$dao   = new FrontCommonDAO();
$fb    = new FormBean();
$session = $fb->getSession();

$host = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER['HTTP_HOST'];
//$callback_url = urlencode("http://devfront.yesprinting.co.kr/oauth/oauth_callback_naver.php");
$callback_url = urlencode($host . "/oauth/oauth_link_naver.php");
$url  = "https://nid.naver.com/oauth2.0/token?grant_type=authorization_code";
$url .= "&client_id=" . $client_id;
$url .= "&client_secret=" . $client_secret;
$url .= "&redirect_uri=" . $callback_url;
$url .= "&code=" . $code;
$url .= "&state=" . $state;

$is_post = false;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, $is_post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$headers = array();
$response = curl_exec ($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

//echo "status_code:" . $status_code ."";

curl_close ($ch);

if($status_code == 200) {
    //echo $response;
} else {
    echo "Error 내용:".$response;
    exit;
}

$resp_dec = json_decode($response, true);
$token    = $resp_dec["access_token"];
$header   = "Bearer " . $token;
$tok_url  = "https://openapi.naver.com/v1/nid/me";
$is_post  = false;
$ch       = curl_init();
curl_setopt($ch, CURLOPT_URL, $tok_url);
curl_setopt($ch, CURLOPT_POST, $is_post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$headers  = array();
$headers[] = "Authorization: ".$header;
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec ($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//echo "status_code:".$status_code."<br>";
curl_close ($ch);
if ($status_code == 200) {
    //echo $response;
} else {
    echo "Error 내용:".$response;
    exit;
}

// db 확인 후 데이터 집어넣는 로직 작성
$info = json_decode($response, true);
$mail = $info["response"]["email"];
$path = "Naver";

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
    $check = "<script>alert(\"이미 연동된 아이디입니다.\")</script>";
    goto ERR;
}

// 이미 다른 아이디에 연동되어 있을 경우 판별
$rs_sub  = $dao->selectDuplSocialSub($conn, $param);
$sub_cnt = $rs_sub->fields["sub_cnt"];
if ($sub_cnt != "0") {
    $check = "<script>alert(\"이미 연동된 아이디입니다.\")</script>";
    goto ERR;
}

// 여기까지 올 경우 연동 데이터 입력
$conn->StartTrans();
$rs_ins = $dao->insertSocialAccLink($conn, $param);

if (!$rs_ins) {
    $check = "<script>alert(\"연동아이디 입력에 실패했습니다.\")</script>";
    $conn->FailTrans();
    $conn->RollbackTrans();
    goto ERR;
}

// 완료 alert 띄워주고 refresh
$check = "<script>alert(\"연동아이디가 입력 되었습니다.\")</script>";
$conn->CompleteTrans();

goto END;

ERR:
    $conn->Close();
    echo $check;
    echo "<script>self.close();</script>";
    exit;
END:
    $conn->Close();
    echo $check;
    echo "<script>opener.location.reload(); self.close();</script>";
    exit;
?>

<?php
// 네이버 로그인 콜백
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/LoginUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$dao = new ProductCommonDAO();

$rs = $dao->selectChannelInfo(
    $conn, ["sell_site" => $_SERVER["SELL_SITE"]]
);


$naver_info = json_decode($rs["social_naver_info"]);
$client_id = $naver_info[0];
$client_secret = $naver_info[1];

$code = $_GET["code"];
$state = $_GET["state"];

$host = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER['HTTP_HOST'];

//$callback_url = urlencode("http://devfront.yesprinting.co.kr/oauth/oauth_callback_naver.php");
$callback_url = urlencode($host . "/oauth/oauth_callback_naver.php");
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
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec ($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

//echo "status_code:" . $status_code ."";

curl_close ($ch);

if($status_code == 200) {
    echo $response;
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

$loginUtil = new LoginUtil([
    "res" => $response
    ,"dvs" => "naver"
]);

if (!$loginUtil->login()) {
    goto ERR;
}

goto END;

ERR:
echo $loginUtil->err_msg;
exit;
END:
echo $loginUtil->redirPage();
exit;
?>

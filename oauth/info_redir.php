<?php

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$dao = new ProductCommonDAO();

// 소셜아이디 로그인
$dvs = $_GET["dvs"];
$host = "http://" . $_SERVER['HTTP_HOST'];

$rs = $dao->selectChannelInfo(
    $conn, ["sell_site" => $_SERVER["SELL_SITE"]]
);

switch ($dvs) {
    case "google" :
        include_once($_SERVER["DOCUMENT_ROOT"] . '/oauth/google-api-php-client-2.2.0/vendor/autoload.php');

        $json_path = $_SERVER["DOCUMENT_ROOT"] . '/oauth/oauth2_json.json';
        $client = new Google_Client();
        $client->setAuthConfig($json_path);
        $client->addScope(Google_Service_Plus::USERINFO_EMAIL);
        $client->addScope(Google_Service_Plus::USERINFO_PROFILE);
        $auth_url = $client->createAuthUrl();

        break;

    case "naver" :
        $mt = microtime();
        $rand = mt_rand();
        $naver_info = json_decode($rs["social_naver_info"]);
        $client_id = $naver_info[0];
        //$callback_url = urlencode("http://devfront.yesprinting.co.kr/oauth/oauth_callback_naver.php");
        $callback_url = urlencode($host . "/oauth/oauth_callback_naver.php");
        $state = md5($mt . $rand); // 랜덤토큰 생성

        $auth_url  = "https://nid.naver.com/oauth2.0/authorize?response_type=code";
        $auth_url .= "&client_id=" . $client_id;
        $auth_url .= "&redirect_uri=" . $callback_url;
        $auth_url .= "&state=" . $state;
        break;

    case "kakao" :
        $kakao_info = json_decode($rs["social_kakao_info"]);
        $client_id = $kakao_info[0];
        $callback_url = $host . "/oauth/oauth_callback_kakao.php";

        $auth_url  = "https://kauth.kakao.com/oauth/authorize?";
        $auth_url .= "client_id=" . $client_id;
        $auth_url .= "&redirect_uri=" . $callback_url;
        $auth_url .= "&response_type=code";
//echo $auth_url;
        /*
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $auth_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $res = curl_exec($ch);
                $res = json_decode($res, true);
                //curl_close($ch);

                var_dump($res);
                if ($res["result"]["code"] !== "0000") {
                    // 작업 에러시 처리
                } else {

                }
        */
        /*
        $mt = microtime();
        $rand = mt_rand();
        $client_id = "954673864fbb47e441644ddf06996e4f";
        $callback_url = urlencode($host . "/oauth/oauth_callback_kakao.php");

        $auth_url  = "https://kauth.kakao.com/oauth/authorize?response_type=code";
        $auth_url .= "&client_id=" . $client_id;
        $auth_url .= "&redirect_uri=" . $callback_url;
        */
        break;
}

header("Location: " . $auth_url);
?>

<?php

// 소셜아이디 연동
$dvs = $_GET["dvs"];
$host = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER['HTTP_HOST'];

switch ($dvs) {
    case "google" :
        include_once($_SERVER["DOCUMENT_ROOT"] . '/oauth/google-api-php-client-2.2.0/vendor/autoload.php');

        $json_path = $_SERVER["DOCUMENT_ROOT"] . '/oauth/oauth2_link_json.json';
        $client = new Google_Client();
        $client->setAuthConfig($json_path);
        $client->addScope(Google_Service_Plus::USERINFO_EMAIL);
        $client->addScope(Google_Service_Plus::USERINFO_PROFILE);
        $auth_url = $client->createAuthUrl();

        break;

    case "naver" :
        $mt = microtime();
        $rand = mt_rand();
        $client_id = "FfUadoF0fkwujmqKpDbT";
        $callback_url = urlencode($host . "/oauth/oauth_link_naver.php");
        $state = md5($mt . $rand); // 랜덤토큰 생성

        $auth_url  = "https://nid.naver.com/oauth2.0/authorize?response_type=code";
        $auth_url .= "&client_id=" . $client_id;
        $auth_url .= "&redirect_uri=" . $callback_url;
        $auth_url .= "&state=" . $state;
        
        break;
        
}

header("Location: " . $auth_url);
?>

<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/LoginUtil.inc");

$auth_code = $_GET["code"];

$json_path = $_SERVER["DOCUMENT_ROOT"] . '/oauth/oauth2_json.json';
$json_fd = fopen($json_path, 'r');
$json = json_decode(fread($json_fd, filesize($json_path)), true);
fclose($json_fd);
$json = $json["web"];

$data  = "code=" . $auth_code;
$data .= "&client_id=" . $json["client_id"];
$data .= "&client_secret=" . $json["client_secret"];
$data .= "&redirect_uri=" . $json["redirect_uris"][0];
$data .= "&grant_type=authorization_code";

$opts = [
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => $data
    ]
];

# Google oAuth endpoint로 요청한다.
$context  = stream_context_create($opts);
$response = file_get_contents('https://accounts.google.com/o/oauth2/token',
                              false,
                              $context);
$response = json_decode($response, true);

// access_token으로 사용자 정보 추출
$info_url  = "https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token=";
$info_url .= $response["access_token"];

$info = file_get_contents($info_url, false);

$loginUtil = new LoginUtil([
     "res" => $info
    ,"dvs" => "google"
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

<?php
$rest_api_key = "1ecaf2343e0a465dc303eaddb1df5b2f";        // REST API 키
$redirect_uri = "http://dev2.gprinting.co.kr/oauth/kakao_oauth.php";  // Redirect URI
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

print_r($user_info_json);

?>
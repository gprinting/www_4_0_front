<?
require_once 'HTTP/Request2.php'; 

// This will set credentials for basic auth
$request = new HTTP_Request2('http://www.webhard.co.kr/');

// This will set credentials for Digest auth
//user dp123 pw 1234
$request->setAuth('user', 'password', HTTP_Request2::AUTH_DIGEST);

print_r($request);
?>

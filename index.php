<?

error_reporting(E_ALL);

include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");

/*
if (strpos($_SERVER["HTTP_HOST"], "yesprinting") !== false) {
    header("Location: http://www.goodprinting.co.kr");
    exit;
}
*/

/*
function http_digest_parse($txt) {
	$needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
	$data = array();
	preg_match_all('@(\w+)=(?:(?:\'([^\']+)\'|"([^"]+)")|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);
	foreach ($matches as $m) {
		$data[$m[1]] = $m[2]?$m[2]:($m[3]?$m[3]:$m[4]);
		unset($needed_parts[$m[1]]);
	}
	return $needed_parts ? false : $data;
}
function is_auth() {
	$users = array("sitemgr" => "sitemgr");

	if(empty($_SERVER['PHP_AUTH_DIGEST'])) return false; // ����

	$data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST']);
	if($data === false) return false; // ����

	global $username;
	$username = $data['username'];

	if(!isset($users[$username])) return false; // ���̵� Ʋ��

	$ha1 = md5($username.':'.$data['realm'].':'.$users[$username]);
	$ha2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);

	$response = md5($ha1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$ha2);

	if($data['response'] != $response) return false; // �н����� Ʋ��

	return true;
}

$ip_addr = $_SERVER["REMOTE_ADDR"];

if(strpos($ip_addr, "172.16") === false) {
    if (!is_auth()) {
        $realm = 'Digest Auth';
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Digest realm="'.$realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
        echo '<meta charset="utf-8">';
        echo '�α����� �ʿ��մϴ�.';
        exit;
    }
}
*/
if($_SERVER["SELL_SITE"] == "GP" || $_SERVER["SELL_SITE"] == "DP")
    header("Location: /main22/main.html");
else
    header("Location: /main/main.html");
?>
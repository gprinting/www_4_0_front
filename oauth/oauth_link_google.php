<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao   = new FrontCommonDAO();
$fb    = new FormBean();
$session = $fb->getSession();

$auth_code = $_GET["code"];

$json_path = $_SERVER["DOCUMENT_ROOT"] . '/oauth/oauth2_link_json.json';
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

$info = json_decode($info, true);
$mail = $info["email"];
$path = "Google";

$member_seqno = $session["member_seqno"];

$param = array();
$param["mail"] = $mail;
$param["join_path"] = $path;
$param["member_seqno"] = $member_seqno;

$check = "";
$conn->debug = 1;

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

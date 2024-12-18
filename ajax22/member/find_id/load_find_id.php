<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/member/MemberFindIdDAO.inc");
require_once(INC_PATH . "/com/nexmotion/common/util/MailUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MemberFindIdDAO();

$name = $fb->form("name");
$mail = $fb->form("mail");
$join_path = "Normal";

$param = array();
$param["member_name"] = $name;
$param["mail"]        = $mail;

// 위에서 전달받은 파라미터로 아이디를 검색
//$conn->debug = 1;
$rs = $dao->selectFindId($conn, $param);

$check = 1;
// 조건에 맞지 않을 경우
if (empty($rs)) {
    $check = 0;
    goto ERR;
}

// 조건에 맞는 경우 임시비밀번호를 발급한다
$temp_pw = uniqid();
$temp_pw = md5($temp_pw);
$fin_temp_pw = substr($temp_pw, 0, 5);  // 임시 비밀번호(5자리)

$enc_temp_pw = password_hash($fin_temp_pw, PASSWORD_DEFAULT);

$conn->StartTrans();

$param = array();
$param["passwd"]       = $enc_temp_pw;
$param["member_seqno"] = $rs["member_seqno"];
$param["join_path"]    = $join_path;

// 임시비밀번호로 비밀번호 업데이트
$upd_rs = $dao->updateMemberTempPw($conn, $param);

if (!$upd_rs) {
    $check = 2;
    goto ERR;
}

// 임시비밀번호 해당 이메일로 발송
$subject = $name . "님의 임시비밀번호 입니다."; // 메일 제목

// 메일 분문
$content  = $name . "님의 임시비밀번호는 <br />";
$content .= $fin_temp_pw . "<br />";
$content .= "입니다. <br />";
$content .= "접속 후 새로운 비밀번호로 변경 해 주시기 바랍니다. <br />";
$content .= "이 메일은 (주)굿프린팅 에서 발송되었으며, 발신 전용 메일입니다.";

// 받는 사람
$mailto     = $mail;
$mailtoname = "";

sendMail($subject, $content, $mailto, $mailtoname);

goto END;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();
END:
    $conn->CompleteTrans();
    $conn->Close();

?>

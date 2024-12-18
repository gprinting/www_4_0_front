<?php
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
require_once(INC_PATH . "/com/nexmotion/common/util/MailUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();

$mailaddr = $fb->form("mailAddress");        // 메일 주소
$name     = $fb->form("memberName");         // 메일 전송자 이름
$phone    = $fb->form("memberCall");         // 메일 전송자 연락처
$mailtext = $fb->form("mailContent");        // 메일 본문
$subject  = $name . "님의 상담요청 입니다."; // 메일 제목

// 메일 본문
$content  = "내용 : " . $mailtext . "<br />";
$content .= "요청자 : " . $name . "<br />";
$content .= "회신주소 : " . $mailaddr . "<br />";
$content .= "전화번호 : " . $phone;

// 받는 사람(굿프린팅 공식계정으로 입력바람)
$mailto     = "goodprintingweb@gmail.com";
$mailtoname = "굿프린팅 CS센터";

sendMail($subject, $content, $mailto, $mailtoname);

?>

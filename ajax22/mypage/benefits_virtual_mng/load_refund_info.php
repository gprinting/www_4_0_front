<?php
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberInfoDAO();

$session = $fb->getSession();
$member_seqno = $session["member_seqno"];

$conn->debug = 1;

$param = array();
$param["member_seqno"] = $member_seqno;
$check = 1;

$ba_info = $dao->selectMemberVirtBaInfo($conn, $param);

if (empty($ba_info->fields["refund_name"])) {
    $check = 0;
    goto END;
}

$fields  = $ba_info->fields;

$info_text  = "";
$info_text .= "예금주 : " . $fields["refund_name"] . " / ";
$info_text .= "은행명 : " . $fields["refund_bank_name"] . " / ";
$info_text .= "계좌번호 : " . $fields["refund_ba_num"];

END : 
    echo $check . "@" . $info_text;
    exit;

?>

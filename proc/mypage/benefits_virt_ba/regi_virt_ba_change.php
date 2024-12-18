<?php
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberInfoDAO();
$check = 1;
$msg = "가상계좌 입금은행 변경신청이 완료되었습니다.";

$session = $fb->getSession();
$member_seqno = $session["member_seqno"];
$frm = $fb->getForm();

//$conn->debug = 1;
$conn->StartTrans();

// 1. select list of prev change registration
$prev_param = array();
$prev_param["member_seqno"] = $member_seqno;
$prev_param["prog_state"]   = "진행중";

$rs_prev = $dao->selectPrevChangeList($conn, $prev_param);

$now_bank = $frm["bank_name"];

$prev_fields        = $rs_prev->fields;
$change_history_seq = $prev_fields["virt_ba_change_history_seqno"];

if (!empty($change_history_seq)) {
    $check = -1;
    $msg   = "이미 진행중인 신청정보가 있습니다.";
    goto ERR;
}

// 2. select prev virtual bank account
$param = array();
$param["member_seqno"] = $member_seqno;
$param["use_yn"]       = 'Y';

$rs = $dao->selectMemberVirtBa($conn, $param);

$fields = $rs->fields;
$vbas   = $fields["virt_ba_admin_seqno"];
if (empty($vbas)) {
    $check = 0;
    $msg   = "가상계좌를 찾을 수 없습니다.";
    goto ERR;
}

$depo_name = $fields["depo_name"];
$bank_name = $fields["bank_name"];

if ($bank_name == $now_bank) {
    $check = -1;
    $msg   = "이전은행과 같은 은행으로 변경할 수 없습니다.";
    goto ERR;
}

$ins_param = array();
$ins_param["depo_name"]    = $depo_name;
$ins_param["bank_before"]  = $bank_name;
$ins_param["bank_aft"]     = $now_bank;
$ins_param["member_seqno"] = $member_seqno;
$ins_param["prog_state"]   = '진행중';
$ins_param["cancel_yn"]    = 'N';

$ins_rs = $dao->insertVirtBaChangeHistory($conn, $ins_param);

if (!$ins_rs) {
    $check = 2;
    $msg   = "요청에 실패했습니다.";
    goto ERR;
}

goto END;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();

END:
    echo $check . "@" . $msg;
    $conn->CompleteTrans();
    $conn->Close();
?>

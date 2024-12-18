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
$msg = "가상계좌 수정에 성공했습니다.";

$session = $fb->getSession();
$member_seqno = $session["member_seqno"];
$frm = $fb->getForm();

//$conn->debug = 1;
$conn->StartTrans();

$param = array();
$param["member_seqno"] = $member_seqno;
$param["use_yn"]       = 'Y';

$rs = $dao->selectMemberVirtBa($conn, $param);

if (!$rs) {
    $check = 0;
    $msg   = "가상계좌를 찾을 수 없습니다.";
    goto ERR;
}

$fields = $rs->fields;
$vbas   = $fields["virt_ba_admin_seqno"];

$upd_param = array();
$upd_param["depo_name"]    = $frm["depo_name"];
$upd_param["member_seqno"] = $member_seqno;
$upd_param["use_yn"]       = 'Y';
$upd_param["vbas"]         = $vbas;

$upd_rs = $dao->updateMemberVirtBa($conn, $upd_param);

if (!$upd_rs) {
    $check = 2;
    $msg   = "가상계좌 수정에 실패했습니다.";
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

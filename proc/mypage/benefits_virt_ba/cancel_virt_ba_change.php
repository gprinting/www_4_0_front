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
$msg = "취소 되었습니다.";

$session = $fb->getSession();
$member_seqno = $session["member_seqno"];
$frm = $fb->getForm();

//$conn->debug = 1;
$conn->StartTrans();

$upd_param = array();
$upd_param["seqno"]        = $frm["seqno"];
$upd_param["member_seqno"] = $member_seqno;

$rs     = $dao->selectPrevChangeList($conn, $upd_param);
$fields = $rs->fields;

$cancel_yn  = $fields["cancel_yn"];
$prog_state = $fields["prog_state"];

if ($cancel_yn == "Y") {
    $check = -1;    
    $msg   = "이미 취소된 상태입니다.";
    goto ERR;
} else if ($prog_state != "진행중") {
    $check = 2;
    $msg   = "진행중인 요청이 아니면 취소할 수 없습니다.";
    goto ERR;
}

$upd_param["cancel_yn"]  = 'Y';
$upd_param["prog_state"] = "변경취소";

$upd_rs = $dao->updateVirtBaChangeHistory($conn, $upd_param);

if (!$upd_rs) {
    $check = 3;
    $msg   = "취소가 실패했습니다.";
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

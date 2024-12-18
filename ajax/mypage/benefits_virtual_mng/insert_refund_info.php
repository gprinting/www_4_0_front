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
$fb      = $fb->getForm();

$member_seqno = $session["member_seqno"];
$check = 1;

//$conn->debug = 1;

$param = array();
$param["member_seqno"]      = $member_seqno;
$param["refund_name"]       = $fb["refund_name"];
$param["refund_bank_name"]  = $fb["refund_bank_name"];
$param["refund_ba_num"]     = $fb["refund_ba_num"];

$conn->StartTrans();

$upd_rs = $dao->updateRefundAccount($conn, $param);

if (!$upd_rs) {
    $check = 0;
    goto ERR;
}

goto END;

ERR : 
    $conn->FailTrans();
    $conn->RollbackTrans();

END : 
    $conn->CompleteTrans();
    echo $check;
    $conn->Close;

    exit;
?>

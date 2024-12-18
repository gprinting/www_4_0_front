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
$id = $session["id"];
$check = 1;

//$conn->debug = 1;

$form_refund_max = $fb["refund_max"];

$rs     = $dao->selectPrepayPrice($conn, $id);
$fields = $rs->fields;

if ($rs) {
    $refund_max = intval($fields[0]);
}

$refund_amount = $fb["refund_amount"];

$conn->StartTrans();

if ($form_refund_max != $refund_max) {
    $check = 2;
    goto ERR;
} else if ($refund_max < $refund_amount) {
    $check = 3;
    goto ERR;
}

$param["refund_amount"] = $refund_amount;
$param["refund_cont"]   = "";

$ins_rs = $dao->insertRefundAmount($conn, $param);

if (!$ins_rs) {
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

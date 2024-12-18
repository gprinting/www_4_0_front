<?php
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/CartDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new CartDAO();
$util = new CommonUtil();

if (!$is_login) {
    echo "-2";
    exit;
}

$session = $fb->getSession();
$fb = $fb->getForm();

$seqno_arr = $fb["seq"];
$base_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE;

$ret = 'T';
$param = [];
$param["member_seqno"] = $session["org_member_seqno"];

$conn->StartTrans();
foreach ($seqno_arr as $seqno) {
    $param["order_common_seqno"] = $seqno;
    $file_rs = $dao->selectOrderFile($conn, $param);

    if (!empty($path) && !empty($name)) {
        $save_path = $file_rs["file_path"];
        $file_name = $file_rs["save_file_name"];
        $file_path = $base_path . $save_path . $file_name;

        @unlink($file_path);
    }

    $param["order_file_seqno"] = $file_rs["order_file_seqno"];
    $dao->deleteOrderFile($conn, $param);

    if ($conn->HasFailedTrans() === true) {
        $conn->FailTrans();
        $conn->RollbackTrans();
        $ret = 'F';
        goto END;
    }

    // 원파일 그룹정보 삭제
    $dao->deleteOnefileOrderGroup($conn, $param);
}

END:
    echo $ret;
    $conn->CompleteTrans();
    $conn->Close();

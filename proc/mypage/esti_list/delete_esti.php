<?php
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/EstiInfoDAO.inc"); 

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EstiInfoDAO();

$seqno_arr = $fb->form("seqno_arr");
$member_seqno = $fb->session("org_member_seqno");

$success = 'T';

$param = [];
$param["member_seqno"] = $member_seqno;
$param["esti_seqno"]   = $dao->arr2paramStr($conn, $seqno_arr);

$base_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE;

$esti_rs = $dao->selectEstiSeqno($conn, $param);

$conn->StartTrans();
while ($esti_rs && !$esti_rs->EOF) {
    $fields = $esti_rs->fields;

    $param["esti_seqno"] = $fields["esti_seqno"];

    $param["table"] = "esti_detail_brochure";
    $dao->deleteEstiData($conn, $param);

    $param["table"] = "esti_detail";
    $dao->deleteEstiData($conn, $param);

    $param["table"] = "esti_opt_history";
    $dao->deleteEstiData($conn, $param);

    $param["table"] = "esti_after_history";
    $dao->deleteEstiData($conn, $param);

    // 견적_파일 삭제하기 전에 물리파일 삭제
    $file_rs = $dao->selectEstiFile($conn, $param);
    while ($file_rs && !$file_rs->EOF) {
        $path = $file_rs->fields["file_path"];
        $name = $file_rs->fields["save_file_name"];

        if (empty($path) || empty($name)) {
            $file_rs->MoveNext();
            continue;
        }

        @unlink($base_path . DIRECTORY_SEPARATOR . $path . $name);

        $file_rs->MoveNext();
    }

    $param["table"] = "esti_file";
    $dao->deleteEstiData($conn, $param);

    $param["table"] = "esti";
    $dao->deleteEstiData($conn, $param);

    if ($conn->HasFailedTrans() === true) {
        $conn->FailTrans();
        $conn->RollbackTrans();
        goto ERR;
    }

    $conn->CompleteTrans();

    $esti_rs->MoveNext();
}

goto END;


ERR:
    $success = 'F';
END:
    $conn->CompleteTrans();
    echo sprintf("{\"success\" : \"%s\"}", $success);
    $conn->Close();
    exit;

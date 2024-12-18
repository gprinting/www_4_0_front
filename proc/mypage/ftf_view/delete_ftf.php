<?php
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/common_define/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/OtoInqMngDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OtoInqMngDAO();
$util = new CommonUtil();

$check = 1;
$msg   = "삭제에 성공했습니다.";

//$conn->debug = 1;
$member_seqno = $fb->session("org_member_seqno");

$param = array();
$param["oto_inq_seqno"] = $fb->form("seqno");
$param["member_seqno"]  = $member_seqno;

$base_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE;

$conn->StartTrans();
// 잘못된 삭제 방지
$ftf_rs = $dao->selectOtoInqSeqno($conn, $param);

if (empty($ftf_rs)) {
    $check = -1;
    $msg   = "파라미터 오류입니다.";

    goto END;
}

// 실파일 삭제
$file_rs = $dao->selectOtoInqFile($conn, $param);

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

// 파일 데이터 삭제
$file_data_rs = $dao->deleteOtoInqFile($conn, $param);
if (!$file_data_rs) {
    $check = 0;
    $msg   = "파일 데이터 삭제에 실패했습니다.";
    goto ERR;
}

// 게시글 삭제
$inq_data_rs = $dao->deleteOtoInq($conn, $param);
if (!$inq_data_rs) {
    $check = 0;
    $msg   = "문의글 삭제에 실패했습니다.";
    goto ERR;
}

goto END;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();

END:
    $conn->CompleteTrans();
    $conn->Close();
    echo $check . "@" . $msg;
?>

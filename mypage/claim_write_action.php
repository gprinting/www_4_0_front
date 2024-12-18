<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/ClaimInfoDAO.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$check = 1;

$claimDAO = new ClaimInfoDAO(); 
$fileDAO = new FileAttachDAO();
$conn->StartTrans();

$fb = new FormBean();
$fb = $fb->getForm();

// 변수 정리
$order_common_seqno = $fb["order_common_seqno"];// 주문 일련번호
$claim_title        = $fb["claim_title"];       // 클레임 제목
$claim_dvs          = $fb["claim_dvs"];         // 클레임 유형
$claim_cont         = $fb["claim_cont"];        // 클레임 내용
$sample_file        = $_FILES["sample_file"]["name"]; // 클레임 견본파일

// 리다이렉트 페이지
$redir_page = "<script>window.location = \"http://" . $_SERVER["HTTP_HOST"] . "/mypage/claim_list.html \";</script>";

$param = array();
$param["table"] = "order_claim";
$param["col"]["order_common_seqno"] = $order_common_seqno;
$param["col"]["title"]     = $claim_title;
$param["col"]["dvs"]       = $claim_dvs;
$param["col"]["cust_cont"] = $claim_cont;
$param["col"]["regi_date"] = date("Y-m-d H:i:s", time());
$param["col"]["order_yn"]  = "N";
$param["col"]["agree_yn"]  = "N";
$param["col"]["state"]     = "요청";

if ($_FILES["sample_file"]) {

    //클레임 견본파일
    $f_param = array();
    $f_param["file_path"] = SITE_DEFAULT_CLAIM_SAMPLE_FILE;
    $f_param["tmp_name"] = $_FILES["sample_file"]["tmp_name"];
    $f_param["origin_file_name"] = $_FILES["sample_file"]["name"];

    //파일을 업로두 한 후 저장된 경로 리턴
    $result= $fileDAO->upLoadFile($f_param);
    if (!$result) $check = 0;

    //클레임 견본파일 추가
    $param["col"]["sample_origin_file_name"] = $_FILES["sample_file"]["name"];
    $param["col"]["sample_save_file_name"] = $result["save_file_name"];
    $param["col"]["sample_file_path"] = $result["file_path"];

}

$rs = $claimDAO->insertData($conn, $param);
if (!$rs) {
    $check = 0;
    $conn->FailTrans();
    $conn->RollbackTrans();
    echo "<script>alert(\"클레임 등록에 실패했습니다.\")</script>";
    echo $redir_page;
    exit;
}

$conn->CompleteTrans();
$conn->close();

echo "<script>alert(\"클레임을 등록했습니다.\")</script>";
echo $redir_page;

?>

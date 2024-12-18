<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/OtoInqMngDAO.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/file/FileAttachDAO.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$check = "1";

$fb = new FormBean();
$dao = new OtoInqMngDAO();
$fileDAO = new FileAttachDAO();
$orderDAO = new OrderInfoDAO();
$conn->StartTrans();

// 리다이렉트 페이지
//$redir_page = "<script>window.location = \"http://devfront.yesprinting.co.kr/mypage/ftf_list.html \";</script>";
$redir_page = "<script>window.location = \"http://" . $_SERVER["HTTP_HOST"] . "/mypage/ftf_list.html \";</script>";


//$conn->debug = 1;

$param = array();
$param["title"] = $fb->form("title");
$param["inq_typ"] = $fb->form("inq_typ");
/*
if ($fb->form("tel_num"))
    $param["tel_num"] = $fb->form("tel_num");
if ($fb->form("cell_num"))
    $param["cell_num"] = $fb->form("cell_num");
if ($fb->form("mail"))
    $param["mail"] = $fb->form("mail");
*/
$tel_num  = "";
$tel_num  = $fb->form("tel_num");
if ($fb->form("tel_num2")) {
    $tel_num .= "-";
    $tel_num .= $fb->form("tel_num2");
    $tel_num .= "-";
    $tel_num .= $fb->form("tel_num3");
}
$param["tel_num"] = $tel_num;

$cell_num  = "";
$cell_num  = $fb->form("cell_num");
if ($fb->form("cell_num2")) {
    $cell_num .= "-";
    $cell_num .= $fb->form("cell_num2");
    $cell_num .= "-";
    $cell_num .= $fb->form("cell_num3");
}
$param["cell_num"] = $cell_num;

$param["cont"] = $fb->form("cont");
$param["member_seqno"] = $fb->session("org_member_seqno");
$param["group_seqno"] = $fb->session("member_seqno");

$order_common_seqno = $fb->form("order_common_seqno");

if ($order_common_seqno) {
    $param["order_seqno"] = $fb->form("order_common_seqno");
    $order_num = $orderDAO->selectOrderNum($conn, $param);
    $param["order_num"] = $order_num->fields["order_num"];
} else {
    $param["order_num"] = "";
}

$insID = $dao->insertOtoInq($conn, $param);

if (!$insID) {
    $check = "문의사항 등록에 실패하였습니다.";
    $conn->CompleteTrans();
    $conn->Close();
    echo $check;
    exit;
}

if ($_FILES["file"]) {
    //파일 업로드 경로
    $param = array();
    $param["file_path"] = SITE_DEFAULT_OTO_INQ_REPLY_FILE; 
    $param["tmp_name"] = $_FILES["file"]["tmp_name"];
    $param["origin_file_name"] = $_FILES["file"]["name"];
    $param["size"] = $_FILES["file"]["size"];

    //파일을 업로드 한 후 저장된 경로를 리턴한다.
    $rs = $fileDAO->upLoadFile($param);

    $param = array();
    $param["table"] = "oto_inq_file";
    $param["col"]["origin_file_name"] = $_FILES["file"]["name"];
    $param["col"]["save_file_name"] = $rs["save_file_name"];
    $param["col"]["file_path"] = $rs["file_path"];
    $param["col"]["size"] = $_FILES["file"]["size"];
    $param["col"]["oto_inq_seqno"] = $insID;

    $rs = $dao->insertData($conn,$param);

    if (!$rs) {
        $check = 0;
        $conn->FailTrans();
        $conn->RollbackTrans();
        echo "<script>alert(\"문의 등록에 실패했습니다.\")</script>";
        echo $redir_page;
        exit;
    }
}

$conn->CompleteTrans();
$conn->Close();

echo "<script>alert(\"문의를 등록했습니다.\")</script>";
echo $redir_page;
?>

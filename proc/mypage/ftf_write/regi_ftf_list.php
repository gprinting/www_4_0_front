<?
include_once($_SERVER["INC"] . "/define/front/common_config.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/OtoInqMngDAO.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$dao = new OtoInqMngDAO();
$fileDAO = new FileAttachDAO();
$check = "문의사항을 등록하였습니다.";

$param = array();
$param["title"] = $fb->form("title");
$param["inq_typ"] = $fb->form("inq_typ");
if ($fb->form("tel_num"))
    $param["tel_num"] = $fb->form("tel_num");
if ($fb->form("cell_num"))
    $param["cell_num"] = $fb->form("cell_num");
if ($fb->form("mail"))
    $param["mail"] = $fb->form("mail");
$param["cont"] = $fb->form("cont");
//$param["member_seqno"] = $fb->session("org_member_seqno");
$param["member_seqno"] = $fb->session("member_seqno");
$param["group_seqno"] = $fb->session("member_seqno");

$insID = $dao->insertOtoInq($conn, $param);

if (!$insID) {
    $check = "문의사항 등록에 실패하였습니다.";
    $conn->CompleteTrans();
    $conn->Close();
    echo $check;
    exit;
}

if ($fb->form("upload_yn") == "Y") {

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
        $check = "문의사항 등록에 실패하였습니다.";
        $conn->CompleteTrans();
        $conn->Close();
        echo $check;
        exit;
    }
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>

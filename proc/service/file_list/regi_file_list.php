<?
include_once($_SERVER["INC"] . "/define/front/common_config.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/service/FileListDAO.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new FileListDAO();
$fileDAO = new FileAttachDAO();
$check = 1;

$conn->StartTrans();

//공유자료실 등록
$param = array();
$param["table"] = "share_library";
$param["col"]["title"] = $fb->form("title");
$param["col"]["cont"] = $fb->form("cont");
$param["col"]["regi_date"] = date("Y-m-d H:i:s");
$param["col"]["member_seqno"] = $fb->session("org_member_seqno");
$param["col"]["hits"] = 0;

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
}

$seqno = $conn->Insert_ID();

//파일 업로드 경로
$param = array();
$param["file_path"] = SHARE_LIBRARY_FILE; 
$param["tmp_name"] = $_FILES["file"]["tmp_name"];
$param["origin_file_name"] = $_FILES["file"]["name"];

//파일을 업로드 한 후 저장된 경로를 리턴한다.
$rs = $fileDAO->upLoadFileAdmin($param);

$param = array();
$param["table"] = "share_library_file";
$param["col"]["origin_file_name"] = $_FILES["file"]["name"];
$param["col"]["save_file_name"] = $rs["save_file_name"];
$param["col"]["file_path"] = $rs["file_path"];
$param["col"]["share_library_seqno"] = $seqno;

$rs = $dao->insertData($conn,$param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();

echo $check;
?>

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

$seqno = $fb->form("seqno");
$title = $fb->form("title");
$cont = $fb->form("cont");

//공유자료실 등록
$param = array();
$param["table"] = "share_library";
$param["col"]["title"] = $title;
$param["col"]["cont"] = $cont;
$param["prk"] = "share_library_seqno";
$param["prkVal"] = $seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

if ($fb->form("file_yn") === "Y") {

    $param = array();
    $param["table"] = "share_library_file";
    $param["col"] = "file_path, save_file_name";
    $param["where"]["share_library_seqno"] = $seqno;

    $rs = $dao->selectData($conn, $param);

    if (!empty($rs->fields["file_path"])
            && !empty($rs->fields["save_file_name"])) {
        @unlink($_SERVER["SiteHome"] . SITE_NET_DRIVE .
               $rs->fields["file_path"] . 
               $rs->fields["save_file_name"]);
    }

    $param = array();
    $param["table"] = "share_library_file";
    $param["prk"] = "share_library_seqno";
    $param["prkVal"] = $seqno;

    $rs = $dao->deleteData($conn,$param);

    if (!$rs) {
        $check = 0;
    }

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
}

$conn->CompleteTrans();
$conn->Close();

echo $check;
?>

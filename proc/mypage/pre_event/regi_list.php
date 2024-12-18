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
$check = "등록하였습니다.";

$param = array();
$param["title"] = $fb->form("title");
$param["inq_typ"] = $fb->form("inq_typ");
$param["tel_num"] = $fb->form("tel_num") . '-'.
                    $fb->form("tel_num2") . '-' .
                    $fb->form("tel_num3");
$param["cell_num"] = $fb->form("cell_num") . '-'.
                     $fb->form("cell_num2") . '-' .
                     $fb->form("cell_num3");
$param["cont"] = $fb->form("cont");
$param["member_seqno"] = $fb->session("org_member_seqno");
$param["group_seqno"] = $fb->session("member_seqno");

$insID = $dao->insertOtoInq($conn, $param);
if (!$insID) {
    $check = "등록에 실패하였습니다.";
    $conn->FailTrans();
    $conn->RollbackTrans();
    $conn->Close();
    echo $check;
    exit;
}

$file_arr = $_FILES["file"];
$file_count = count($file_arr["name"]);

$success_arr = array();

$file_param = array();
$file_param["file_path"] = SITE_DEFAULT_OTO_INQ_REPLY_FILE; 

$param = array();
$param["table"] = "oto_inq_file";

for ($i = 0; $i < $file_count; $i++) {
    $org_file_name = $file_arr["name"][$i];
    $file_size = $file_arr["size"][$i];

    if (empty($org_file_name)) {
        continue;
    }

    $file_param["tmp_name"]         = $file_arr["tmp_name"][$i];
    $file_param["origin_file_name"] = $org_file_name;
    $file_param["size"]             = $file_size;

    //파일을 업로드 한 후 저장된 경로를 리턴한다.
    $ret = $fileDAO->upLoadFile($file_param);

    if ($ret) {
        $success_arr[] = $ret["file_path"] . DIRECTORY_SEPARATOR .
                         $ret["save_file_name"];
    } else {
        unsetFile($success_arr);

        $check = "등록에 실패하였습니다.";
        $conn->FailTrans();
        $conn->RollbackTrans();
        $conn->Close();
        echo $check;
        exit;
    }

    $param["col"]["origin_file_name"] = $org_file_name;
    $param["col"]["save_file_name"]   = $ret["save_file_name"];
    $param["col"]["file_path"]        = $ret["file_path"];
    $param["col"]["size"]             = $file_size;
    $param["col"]["oto_inq_seqno"]    = $insID;

    $rs = $dao->insertData($conn,$param);

    if (!$rs) {
        $check = "등록에 실패하였습니다.";
        $conn->FailTrans();
        $conn->RollbackTrans();
        $conn->Close();
        echo $check;
        exit;
    }
}

$conn->CompleteTrans();
$conn->Close();

echo "<script>alert('" . $check . "');location.replace('/mypage/pre_event.html');</script>";
exit;

function unsetFile($arr) {
    foreach ($arr as $path) {
        unset($path);
    };
}
?>

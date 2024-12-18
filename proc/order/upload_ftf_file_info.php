<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/common_define/common_config.inc");
include_once($_SERVER["INC"] . "/common_lib/CommonUtil.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/order/SheetDAO.inc");

if ($is_login === false) {
    HandleError("No Login");
    exit(0);
}

// 주문_파일 테이블에 값 입력
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SheetDAO();
$fb = $fb->getForm();

$origin_file_name = "";
$file_path = "attach/". strtolower($_SERVER["SELL_SITE"]) ."/oto_inq_reply_file/" . date('Y/m/d');
$param = array();
$param["file_path"] = "/" . $file_path . "/";
$param["save_file_name"] = "";
$param["origin_file_name"] = $fb["file_name"];
$param["size"] = $fb["file_size"];

$dao->insertOtoFile($conn, $param);
$file_seqno = $conn->Insert_ID("oto_inq_file");
$file_name = $file_seqno;

$file_array = explode('.', $fb["file_name"]);
$insert_file_name = $file_seqno . "." . end($file_array);

$param = array();
$param["table"] = "oto_inq_file";
$param["col"]["save_file_name"] = $insert_file_name;
$param["prk"] = "oto_inq_file_seqno";
$param["prkVal"] = $file_seqno;

$rs = $dao->updateData($conn, $param);


showResult(true, $file_seqno, "", "upload completed", $file_path, $file_name, $origin_file_name);

exit(0);

function showResult($success, $file_seqno, $oper_sys, $message, $file_path, $file_name, $origin_file_name)
{
    $result = array('success'    => $success,
        'file_seqno' => $file_seqno,
        'file_path' => $file_path,
        'oper_sys'   => $oper_sys,
        'file_name'    => $file_name,
        'origin_file_name' => $origin_file_name);
    echo json_encode($result);
}

/* Handles the error output. This error message will be sent to the uploadSuccess event handler.  The event handler
will have to check for any error messages and react as needed. */
function HandleError($message, $code = '') {
    $result = array('success'    => false,
        'file_seqno' => '',
        'code'       => $code,
        'message'    => $message);
    echo json_encode($result);
}
?>

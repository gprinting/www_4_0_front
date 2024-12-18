<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/order/SheetDAO.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/doc/front/order/SheetPopup.inc");

$frontUtil = new FrontCommonUtil();

if ($is_login === false) {
    $frontUtil->errorGoBack("로그인 상태가 아닙니다.");
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SheetDAO();

$session = $fb->getSession();
$fb = $fb->getForm();

$ret = '';

$file_seqno_arr  = explode('|', $fb["file_seqno"]);
$order_seqno_arr = explode('|', $fb["order_seqno"]);
$count_arr = count($order_seqno_arr);

$param = array();
$param["member_seqno"] = $session["org_member_seqno"];

$conn->StartTrans();
for ($i = 0; $i < $count_arr; $i++) {
    $param["order_seqno"] = $order_seqno_arr[$i];
    $param["file_seqno"]  = $file_seqno_arr[$i];
    if($param["file_seqno"] == "") {
        $param["file_seqno"] = $file_seqno_arr[0];
    }

    // 실제 파일 위치 검색 후 삭제
    $file_rs = $dao->selectOrderFile($conn, $param);
    $file_path = $file_rs["file_path"] .
                 $file_rs["save_file_name"];

    if ($rs->EOF) {
        break;
    }

    if (@unlink($file_path) === false) {
        if (@is_file($file_path) === true) {
            $ret = "파일 삭제에 실패했습니다.";
            break;
        }
    }

    // DB 정보 삭제
    $dao->deleteOrderFile($conn, $param);

    if ($conn->HasFailedTrans() === true) {
        $ret = "파일 정보 삭제에 실패했습니다.";
        $conn->FailTrans();
        $conn->RollbackTrans();
        break;
    }
}
$conn->CompleteTrans();

$conn->Close();

echo $ret;
exit;
?>


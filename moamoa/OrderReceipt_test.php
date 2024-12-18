<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/MoamoaDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MoamoaDAO();
$fb = new FormBean();

$param = [];
$param['ordernum'] = $fb->form("OrderNum");
$param['state'] = "1380";
$param['accept_file_path'] = '/attach/gp' . $fb->form("AcceptFilePath") . '/';
$param['accept_file_name'] = $fb->form("AcceptFileName");
$param['preview_file_path'] = '/attach/gp' . $fb->form("PreviewFilePath") . '/';
$param['preview_file_name'] = $fb->form("PreviewFileNames");
$param['empl_id'] = $fb->form("ID");

$rs = $dao->updateProductStatecodeByReceipt($conn, $param);
$rs = $dao->insertStateHistory($conn, $param);

$rs = $dao->select_30OrderNum($conn, $param);
$OPI_Date = $rs["OPI_Date"];
$OPI_Seq = $rs["OPI_Seq"];
$OPI_Inserted = $rs["OPI_Inserted"];
$cate_sortcode = $rs["cate_sortcode"];
$member_seqno = $rs["member_seqno"];
/*
 if(($OPI_Date != "" && $OPI_Inserted == "N" && $param['empl_id'] != "migration"
    && $param['empl_id'] != "dpuser1" && (strpos($param['empl_id'], "auto") === false))
    || ($OPI_Date != "" && $OPI_Inserted == "N" && $param['empl_id'] != "migration"
        && $param['empl_id'] != "dpuser1" && $member_seqno == 6281))
 */
if($OPI_Date != "" && $OPI_Inserted == "N" && $param['empl_id'] != "migration"
    && $param['empl_id'] != "dpuser1") {
    //&& (strpos($param['ordernum'],"EV") !== false || $cate_sortcode == "001002001")) {
    echo request30($OPI_Date, $OPI_Seq);
    /*
    while(true) {
        if(request30($OPI_Date, $OPI_Seq) == "1")
            break;
    }
    */
    $rs = $dao->update_30Insert($conn, $param);
}

$result = array();
$result["code"] = "200";
$result["message"] = "ok";

$data = array();
$ret["result"] = $result;

echo json_encode($ret);

$conn->Close();

function request30($OPI_Date, $OPI_Seq) {
    $post_data = array();
    $post_data["mode"] = "Deliv_End_Direct_40";
    $post_data["Or_Number2"] = "DP-" . $OPI_Date . "-" . $OPI_Seq;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://30.gprinting.co.kr/ISAF/Libs/php/doquery40.php");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    $headers = array();
    $response = curl_exec($ch);
    //$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return $response;
}
?>
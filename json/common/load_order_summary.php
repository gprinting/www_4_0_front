<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/define/front/message.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");

if ($is_login === false) {
    echo "{\"err\" : \"" . NO_LOGIN . "\"}";
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new FrontCommonUtil();
$dao = new FrontCommonDAO();

$session = $fb->getSession();

$dvs = $fb->form("dvs");

$date = date("Y-m-d");

$param = array();
$param["seqno"] = $session["member_seqno"];

if ($dvs === "week") {
    $start_date = date("Y-m-d", strtotime($date . "-7day")) . " 00:00:00";
    $end_date   = $date . " 23:59:59";

    $param["start_date"] = $start_date;
    $param["end_date"]   = $end_date;
} else {
    $start_date = date("Y-m") . "-01 00:00:00";
    $end_date   = $date . " 23:59:59";

    $param["start_date"] = $start_date;
    $param["end_date"]   = $end_date;
}

$summary = $dao->selectOrderSummary($conn, $param);
$summary = $util->makeOrderSummaryArr($summary);

$ret  = '{';
$ret .= " \"wait\" : \"%s\",";
$ret .= " \"rcpt\" : \"%s\",";
$ret .= " \"prdc\" : \"%s\",";
$ret .= " \"rels\" : \"%s\",";
$ret .= " \"dlvr\" : \"%s\",";
$ret .= " \"comp\" : \"%s\"";
$ret .= '}';

echo sprintf($ret, $summary["입금대기"]
                 , $summary["접수"]
                 , $summary["제작"]
                 , $summary["입출고"]
                 , $summary["배송"]
                 , $summary["완료"]);
?>

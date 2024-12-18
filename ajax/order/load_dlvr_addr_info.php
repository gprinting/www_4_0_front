<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/SheetDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$frontUtil = new FrontCommonUtil();

if ($is_login === false) {
    $frontUtil->errorGoBack("로그인 상태가 아닙니다.");
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$commonUtil = new CommonUtil();

$fb = new FormBean();
$dao = new SheetDAO();

$session = $fb->getSession();
$fb = $fb->getForm();

$dvs = $fb["dvs"];
$dlvr_way = $fb["dlvr_way"];

$json  = '{';
$json .= " \"name\"  : \"%s\",";
$json .= " \"recei\" : \"%s\",";
$json .= " \"tel_num1\" : \"%s\",";
$json .= " \"tel_num2\" : \"%s\",";
$json .= " \"tel_num3\" : \"%s\",";
$json .= " \"cell_num1\" : \"%s\",";
$json .= " \"cell_num2\" : \"%s\",";
$json .= " \"cell_num3\" : \"%s\",";
$json .= " \"zipcode\"     : \"%s\",";
$json .= " \"addr\"        : \"%s\",";
$json .= " \"addr_detail\" : \"%s\"";
$json .= '}';

$rs = null;

$name = null;
$tel_num = null;
$cell_num = null;

if ($dvs === "memb") {
    $param = array();
    $param["member_seqno"] = $session["member_seqno"];

    $rs = $dao->selectMembInfo($conn, $param);

    $rs = $rs->fields;

    $dlvr_name = $rs["dlvr_name"];
    $recei = $rs["recei"];

    $tel_num = explode('-', $rs["tel_num"]);
    $cell_num = explode('-', $rs["cell_num"]);
} else if ($dvs === "direct") {
    $rs = $dao->selectDirectDlvrReq($conn, $session["member_seqno"])->fields;

    $dlvr_name = $session["group_name"];
    $recei = $session["name"];
    $tel_num = explode('-', $rs["tel_num"]);
    $cell_num = explode('-', $rs["cell_num"]);

} else if ($dvs === "cpn") {
    $sell_site = $session["sell_site"];

    $rs = $dao->selectCpnAdminInfo($conn, $sell_site);

    $name = $rs["name"];
    $tel_num = explode('-', $rs["tel_num"]);
    $cell_num = explode('-', $rs["cell_num"]);
}

echo sprintf($json, $dlvr_name
                  , $recei
                  , $tel_num[0]
                  , $tel_num[1]
                  , $tel_num[2]
                  , $cell_num[0]
                  , $cell_num[1]
                  , $cell_num[2]
                  , $rs["zipcode"]
                  , $rs["addr"]
                  , $rs["addr_detail"]);

$conn->Close();
exit;
?>

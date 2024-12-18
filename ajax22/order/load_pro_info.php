<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/SheetDAO.inc");
include_once($_SERVER["DOCUMENT_ROOT"] . "/common_lib/CommonUtil.php");

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

$fb = $fb->getForm();

$dvs = $fb["dvs"];
$os  = $fb["os"];
$pro = $fb["pro"];

$param = array();
$param["os"]  = $os;
$param["pro"] = $pro;

if ($dvs === "pro") {
    $rs = $dao->selectProTyp($conn, $param);
    $param["pro"] = $rs->fields["pro"];

    $pro = makeProInfoOption($rs, "pro");
    $pro = $commonUtil->convJsonStr($pro);


    $rs = $dao->selectProTyp($conn, $param);
    $pro_ver = makeProInfoOption($rs, "pro_ver");
    $pro_ver = $commonUtil->convJsonStr($pro_ver);

    $json  = '{';
    $json .= " \"pro\" : \"%s\",";
    $json .= " \"ver\" : \"%s\"";
    $json .= '}';

    echo sprintf($json, $pro, $pro_ver);
} else {
    $rs = $dao->selectProTyp($conn, $param);
    $pro_ver = makeProInfoOption($rs, "pro_ver");
    $pro_ver = $commonUtil->convJsonStr($pro_ver);

    $json = "{\"ver\" : \"%s\"}";

    echo sprintf($json, $pro_ver);
}

$conn->Close();
exit;
?>

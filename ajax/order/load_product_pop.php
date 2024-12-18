<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/SheetDAO.inc");
include_once(INC_PATH . '/com/nexmotion/doc/front/order/SheetPopup.inc');
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

$sell_site    = $fb->session("sell_site");
$member_seqno = $fb->session("org_member_seqno");

$fb = $fb->getForm();

$param = array();
$param["order_common_seqno"] = str_replace('|', ',', $fb["seq"]);

$rs = $dao->selectProductList($conn, $param);
echo productListPopup($rs, $fb["to"], $fb["selected"]);
$conn->Close();
exit;
?>

<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/eventmall/EventmallDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new EventmallDAO();

$fb = new FormBean();
$sortcode = $fb->form("sortcode");
$rs = $dao->selectPopularList($conn, $sortcode);

$html = makePopularListHTML($conn, $rs);
$conn->Close();

echo $html;
exit;
?>

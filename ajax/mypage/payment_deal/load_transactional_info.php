<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/PaymentInfoDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$dao = new PaymentInfoDAO();

//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("list_num"); 

//현재 페이지
$page = $fb->form("page");

//리스트 보여주는 갯수 설정
if (!$fb->form("list_num")) $list_num = 10;
// 페이지가 없으면 1 페이지
if (!$page) $page = 1; 

$type = $fb->form("type");
$s_num = $list_num * ($page-1);
$session = $fb->getSession();
$seqno = $session["org_member_seqno"];
$member_dvs = $session["member_dvs"];
$state_arr = $session["state_arr"];

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["from"] = $fb->form("from");
$param["to"] = $fb->form("to");
$param["dvs"] = $fb->form("dvs");
$param["member_seqno"] = $seqno;
$param["order_state"] = $state_arr["주문취소"];
$param["type"] = "SEQ";

//$conn->debug = 1;
$rs = $dao->selectTransactionalInfoList($conn, $param);

$rsCount = $dao->selectFoundRows($conn);

$list = makeTransactionalInfoHtml($rs);
$paging = mkDotAjaxPage($rsCount, $page, $list_num, "movePage");

$html = "";
if ($fb->form("from") && $fb->form("to")) {
    $html .= "<strong>" . $param["from"] . "</strong>부터 <strong>";
    $html .= $param["to"] . "</strong>까지 ";
}
$html .= "<em>" . $rsCount . "</em>건의 검색결과가 있습니다.";

echo $list . "♪" . $paging . "♪" . $html;

$conn->Close();
?>

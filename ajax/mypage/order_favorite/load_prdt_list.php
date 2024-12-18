<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/InterestDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$orderDAO = new OrderInfoDAO();

$session = $fb->getSession();
$fb = $fb->getForm();

//한페이지에 출력할 게시물 갯수
$list_num = $fb["list_num"]; 

//$conn->debug =1;

//현재 페이지
$page = $fb["page"];

//리스트 보여주는 갯수 설정
if (empty($fb["list_num"])) {
    $list_num = 10;
}
// 페이지가 없으면 1 페이지
if (empty($page)) {
    $page = 1; 
}

$s_num = $list_num * ($page - 1);
$seqno = $session["org_member_seqno"];

$param = [];
$param["s_num"]        = $s_num;
$param["list_num"]     = $list_num;
$param["title"]        = $fb["title"];
$param["member_seqno"] = $session["org_member_seqno"];

//$conn->debug = 1;
$rs = $orderDAO->selectPrdtList($conn, $param);

$count = $orderDAO->selectFoundRows($conn);

$list = makePrdtListHtml($conn, $rs, $param);
$paging = mkDotAjaxPage($count, $page, $list_num, "movePage");

$html = "";

$html .= "<em>" . $count . "</em>건의 검색결과가 있습니다.";

echo $list . "♪" . $paging . "♪" . $html;
$conn->close();
?>

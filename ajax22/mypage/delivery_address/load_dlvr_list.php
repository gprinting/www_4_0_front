<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberDlvrDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberDlvrDAO();

//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("list_num"); 

//현재 페이지
$page = $fb->form("page");

//$conn->debug = 1;

//리스트 보여주는 갯수 설정
if (!$fb->form("list_num")) $list_num = 10;
// 페이지가 없으면 1 페이지
if (!$page) $page = 1; 

$type = $fb->form("type");
$s_num = $list_num * ($page-1);
$session = $fb->getSession();
$seqno = $session["member_seqno"];
$org_seqno = $session["org_member_seqno"];

// 기업회원과 일반회원은 구조가 다름
$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["from"] = $fb->form("from");
$param["to"] = $fb->form("to");
$param["category"] = $fb->form("category");
$param["searchkey"] = str_replace('-', '', $fb->form("searchkey"));
$param["seqno"] = $seqno;
$param["type"] = "SEQ";

$rs = $dao->selectDlvrList($conn, $param);
$list = makeDlvrListHtml($rs, $param);

$rsCount = $dao->selectFoundRows($conn);
$param["count"] = $rsCount;

$paging = mkDotAjaxPage($rsCount, $page, $list_num, "movePage");
$html = "총<em> " . $rsCount . "</em>건의 배송지가 있습니다.";

echo $list . "♪" . $paging . "♪" . $html;
$conn->close();
?>

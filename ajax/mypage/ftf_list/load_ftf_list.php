<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/OtoInqMngDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OtoInqMngDAO();

//$conn->debug = 1;

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

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["from"] = $fb->form("from");
$param["to"] = $fb->form("to");
$param["title"] = $fb->form("title");
$param["answ_yn"] = $fb->form("answ_yn");
$param["inq_typ"] = $fb->form("inq_typ");
$param["dvs"] = "SEQ";

/* 이부분 수정여지 있음
if ($session["member_dvs"] == "기업") {
    $param["group_seqno"] = $session["member_seqno"];
} else {
    $param["member_seqno"] = $session["org_member_seqno"];
}
*/
$param["member_seqno"] = $session["org_member_seqno"];

$rs = $dao->selectOtoInquireList($conn, $param);
$list = makeOtoInquireListHtml($rs, $param);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectOtoInquireList($conn, $param);
$rsCount = $count_rs->fields["cnt"];

$paging = mkDotAjaxPage($rsCount, $page, $list_num, "movePage");

$html = "";
if ($fb->form("from") && $fb->form("to")) {
    $html .= "<strong>" . $param["from"] . "</strong>부터 <strong>";
    $html .= $param["to"] . "</strong>까지 ";
}
$html .= "<em>" . $rsCount . "</em>건의 검색결과가 있습니다.";

echo $list . "♪" . $paging . "♪" . $html;
$conn->close();
?>

<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/cscenter/NoticeDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new NoticeDAO();

//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("list_num"); 

//현재 페이지
$page = $fb->form("page");

//$conn->debug = 1;

//리스트 보여주는 갯수 설정
if (!$fb->form("list_num")) {
    $list_num = 10;
}

// 페이지가 없으면 1 페이지
if (!$page) {
    $page = 1; 
}

$s_num = $list_num * ($page-1);

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
if ($fb->form("from")) {
    $param["from"] = $fb->form("from") . " 00:00:00";
}
if ($fb->form("to")) {
    $param["to"] = $fb->form("to") . " 23:59:59";
}
$param["search_txt"] = $fb->form("search_txt");
$param["dvs"] = "SEQ";
$param["noti_dvs"] = 0;

$rs = $dao->selectNoticeList($conn, $param);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectFoundRows($conn);
$rsCount = $count_rs;

$param["count"] = $rsCount;
$list = makeNoticeListHtml($rs, $param);
$paging = mkDotAjaxPage($rsCount, $page, $list_num, "movePage");

$html = "";
if ($fb->form("from") && $fb->form("to")) {
    $html .= "<strong>" . $fb->form("from") . "</strong>부터 <strong>" . $fb->form("to") . "</strong>까지 ";
}

$html .= "<em>" . $rsCount . "</em>건의 검색결과가 있습니다.";

echo $list . "♪" . $paging . "♪" . $html;
$conn->close();
?>

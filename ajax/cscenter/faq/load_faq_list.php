<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/cscenter/FaqDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new FaqDAO();

//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("list_num"); 

//현재 페이지
$page = $fb->form("page");

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
$param["cont"] = $fb->form("cont");
$param["type"] = $fb->form("type");
$param["dvs"] = "SEQ";
$rs = $dao->selectFaqList($conn, $param);

$rsCount = $dao->selectFoundRows($conn);

$param["count"] = $rsCount;
$list = makeFAQListHtml($rs, $param);
$paging = mkDotAjaxPage($rsCount, $page, $list_num, "movePage");

echo $list . "♪" . $paging . "♪" . $rsCount;
$conn->close();
?>

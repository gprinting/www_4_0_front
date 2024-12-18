<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/service/FileListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new FileListDAO();

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
$param["search_cnd"] = $fb->form("search_cnd");
$param["search_txt"] = $fb->form("search_txt");
$param["dvs"] = "SEQ";

$rs = $dao->selectFileList($conn, $param);

$param["dvs"] = "COUNT";
$count_rs = $dao->selectFileList($conn, $param);
$rsCount = $count_rs->fields["cnt"];

$param["count"] = $rsCount;

$list = makeFileListHtml($rs, $param);
$paging = mkDotAjaxPage($rsCount, $page, $list_num, "movePage");

echo $list . "♪" . $paging . "♪" . $rsCount;
$conn->close();
?>

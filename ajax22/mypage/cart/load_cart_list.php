<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common22/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/CartDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$template = new Template();
$dao = new CartDAO();
$util = new CommonUtil();
$commonDAO = $dao;

// 로그인 상태인지 체크하는부분 include
include_once($_SERVER["DOCUMENT_ROOT"] . "/common22/login_check.php");

//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("list_num"); 

//리스트 보여주는 갯수 설정
if (!$fb->form("list_num")) {
    $list_num = 10;
}

//현재 페이지
$page = $fb->form("page");

// 페이지가 없으면 1 페이지
if (!$fb->form("page")) {
    $page = 1; 
}

//블록 갯수
$scrnum = 5; 
$s_num = $list_num * ($page-1);

if (!$s_num) {
    $s_num = 0;
}

$session = $fb->getSession();

//$conn->debug=1;

$price_info_arr = [];
$price_info_arr["state"] = $state_arr["주문대기"];

$state_arr = $session["state_arr"];
$param = [];
$param["member_seqno"] = $session["org_member_seqno"];
$param["order_state"]  = $state_arr["주문대기"];
// 페이징 기능이 빠졌으므로 주석처리 만약 다시 생긴다면 이부분 주석 해제
/*
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
*/

// 30개까지만 보여줌
$param["s_num"] = 0;
$param["list_num"] = 30;

$cart_list = $dao->selectCartOrderList($conn, $param);
$count = $cart_list->recordCount();
if ($is_mobile) {
    $cart_list = makeMobileCartOrderListHtml($conn, $dao, $cart_list, $price_info_arr);
} else {
    $cart_list = makeCartOrderListHtml($conn, $dao, $cart_list, $price_info_arr);
}

$paging = "";
/*
if ($is_mobile) {
    $paging = mkDotAjaxPageMobile($rsCount, $page, $list_num, "movePage");
} else {
    $paging = mkDotAjaxPage($rsCount, $page, $list_num, "movePage");
}
*/

$json  = '{';
$json .=     "\"cart_list\"    : \"%s\",";
$json .=     "\"paging\"       : \"%s\",";
$json .=     "\"rsCount\"      : \"%s\",";
$json .=     "\"price_sell\"   : \"%s\",";
$json .=     "\"price_grade\"  : \"%s\",";
$json .=     "\"price_member\" : \"%s\",";
$json .=     "\"price_sum\"    : \"%s\"";
$json .= '}';

echo sprintf($json, $util->convJsonStr($cart_list)
                  , $util->convJsonStr($paging)
                  , $count
                  , number_format($price_info_arr["sell"])
                  , number_format($price_info_arr["grade"])
                  , number_format($price_info_arr["member"])
                  , number_format($price_info_arr["sum"]));

$conn->Close();
?>

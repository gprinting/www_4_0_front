<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/left_menu.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/PaymentInfoDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/LeftMenuSetting.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/PointMngDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/OrderMngUtil.inc');

include_once(INC_PATH . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");


$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$template = new Template();
$leftSetting = new LeftMenuSetting();
$pointDAO = new PointMngDAO();
$util = new OrderMngUtil();


//공통 footer 사용을 위한 변수 선언
$commonDAO = $pointDAO; 

//메뉴 세팅 변수 선언 - 여기만 세팅하면 됩니다.
$top = "mkt"; //기초관리
$left = "mkt_mng"; //레프트 메뉴
$left_sub = "point_mng"; //레프트하위 메뉴, script 주소

$session = $fb->getSession();
$param = array();
$param["id"] = "search_cnd";
$param["flag"] = false;
$param["from_id"] = "from";
$param["to_id"] = "to";

//포인트 세팅값 가져오기
$dao = new FrontCommonDAO();
//$attendance = $dao->selectAttendanceSetting($conn, $param);
$rs = $dao->get_point_list($conn, $session["member_seqno"]);


// 판매채널 검색

//print_r($rs);
$list = makePointListHtml($rs, $util);
//$paging = mkDotAjaxPage($rsCount, $page, $list_num, "movePage");

//$html = "";
//if ($fb->form("from") && $fb->form("to")) {
//    $html .= "<strong>" . $param["from"] . "</strong>부터 <strong>";
//    $html .= $param["to"] . "</strong>까지 ";
//}
$html .= "<em>" . $rsCount . "</em>건의 검색결과가 있습니다.";

echo $list  . "♪" . $paging . "♪" . $html;

$conn->Close();
?>

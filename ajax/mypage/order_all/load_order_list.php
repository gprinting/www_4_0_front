<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');

if (!$is_login) {
    header("Location: /main/main.html");
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderInfoDAO();

// 주문번호
$order_seqno = $fb->form("order_seqno");

//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("list_num");

//현재 페이지
$page = $fb->form("page");

//리스트 보여주는 갯수 설정
if (!$fb->form("list_num")) $list_num = 10;
// 페이지가 없으면 1 페이지
if (!$page) $page = 1;

$session = $fb->getSession();
$member_dvs = $session["member_dvs"];
$seqno = $session["org_member_seqno"];

if ($member_dvs == "기업") {
    $param = array();
    $param["member_seqno"] = $seqno;
    $rs = $dao->getBuPerSeqno($conn, $param);
    $bu_seqno = $seqno;
    while ($rs && !$rs->EOF) {

        $bu_seqno .= "," . $rs->fields["member_seqno"];
        $rs->moveNext();
    }
}

$param = [];
$state = $fb->form("state");
if (!empty($state)) {
    switch ($state) {
        case "주문" :
            $state_arr = $session["state_arr"];
            $param["state_min"] = $state_arr["입금대기"];
            $param["state_max"] = $state_arr["입금대기"];
            break;
        case "후가공" :
            $state = "후공정";
            $state_arr = $dao->selectStateAdminRange($conn, $state);
            $param["state_min"] = $state_arr["min"];
            $param["state_max"] = $state_arr["max"];
            break;
        case "완료" :
            $state = "구매확정";
            $state_arr = $dao->selectStateAdminRange($conn, $state);
            $param["state_min"] = $state_arr["min"];
            $param["state_max"] = $state_arr["max"];
            break;
        default :
            $state_arr = $dao->selectStateAdminRange($conn, $state);
            $param["state_min"] = $state_arr["min"];
            $param["state_max"] = $state_arr["max"];
            break;
    }
}

$type = $fb->form("type");
$s_num = $list_num * ($page-1);
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["from"] = $fb->form("from");
$param["to"] = $fb->form("to");
$param["title"] = $fb->form("title");
$param["dlvr_way"] = $fb->form("dlvr_way");
$param["type"] = $fb->form("type");

if ($member_dvs == "aaaaa") {
    /*
    $param["member_seqno"] = $bu_seqno;
    $rs = $dao->selectBusiOrderList($conn, $param);

    $param["dvs"] = "COUNT";
    $count_rs = $dao->selectBusiOrderList($conn, $param);
    $rsCount = $count_rs->fields["cnt"];

    $param["count"] = $rsCount;
    $list = makeBusiOrderListHtml($rs, $param);
    */
} else {
    $param["order_common_seqno"] = $order_seqno;
    $param["member_seqno"] = $seqno;

    $rs = $dao->selectOrderList($conn, $param);

    $rsCount = $dao->selectFoundRows($conn);
    $param["count"] = $rsCount;

    if ($is_mobile) {
        $list = makeOrderListHtmlMobile($conn, $rs, $param);
    } else {
        $list = makeOrderListHtml($conn, $rs, $param);
    }
}

$paging = mkDotAjaxPage($rsCount, $page, $list_num, "movePage");

$html = "";
if ($is_mobile) {
    $html = $rsCount;
} else {
    if ($fb->form("from") && $fb->form("to")) {
        $html .= "<strong>" . $param["from"] . "</strong>부터 <strong>";
        $html .= $param["to"] . "</strong>까지 ";
    }
    $html .= "<em>" . $rsCount . "</em>건의 검색결과가 있습니다.";
}

echo $list . "♪" . $paging . "♪" . $html;
$conn->close();
?>

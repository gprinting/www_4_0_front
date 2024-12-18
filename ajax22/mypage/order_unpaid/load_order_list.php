<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderInfoDAO();

//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("list_num"); 

//$conn->debug = 1;

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

$type = $fb->form("type");
$s_num = $list_num * ($page-1);
$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["from"] = $fb->form("from");
$param["to"] = $fb->form("to");
$param["title"] = $fb->form("title");
$param["dlvr_way"] = $fb->form("dlvr_way");
$param["state_dvs"] = "N";
$param["state"] = $session["state_arr"]["입금대기"];
$param["type"] = $fb->form("type");
if ($member_dvs == "기업") {
    $param["member_seqno"] = $bu_seqno;
    $rs = $dao->selectBusiOrderList($conn, $param);

    $param["dvs"] = "COUNT";
    $count_rs = $dao->selectBusiOrderList($conn, $param);
    $rsCount = $count_rs->fields["cnt"];

    $param["count"] = $rsCount;
    $list = makeBusiOrderUnpaidListHtml($rs, $param);

} else {
    $param["member_seqno"] = $seqno;
    $rs = $dao->selectOrderList($conn, $param);

    $param["dvs"] = "COUNT";
    $count_rs = $dao->selectOrderList($conn, $param);
    $rsCount = $count_rs->fields["cnt"];

    $param["count"] = $rsCount;

    $list = makeOrderUnpaidListHtml($rs, $param);
}

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

<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberDlvrDAO.inc");
include_once(INC_PATH . "/com/nexmotion/doc/front/mypage/DeliveryDOC.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberDlvrDAO();

$dvs = $fb->form("dvs");

if ($dvs == 1) {

    //한페이지에 출력할 게시물 갯수
    $list_num = 10;
    //현재 페이지
    $page = 1;
    $s_num = $list_num * ($page-1);
    $session = $fb->getSession();
    $seqno = $session["member_seqno"];

    $param = array();
    $param["s_num"] = $s_num;
    $param["list_num"] = $list_num;
    $param["from"] = $fb->form("from");
    $param["to"] = $fb->form("to");
    $param["category"] = $fb->form("category");
    $param["searchkey"] = $fb->form("searchkey");
    $param["seqno"] = $seqno;
    $param["type"] = "SEQ";
    $param["org_member_seqno"] = $session["org_member_seqno"]; 
    $rs = $dao->selectDlvrList($conn, $param);

    $rsCount = $dao->selectFoundRows($conn);
    $param["count"] = $rsCount;

    $list = makeDlvrListHtml($rs, $param);
    $paging = mkDotAjaxPage($rsCount, $page, $list_num, "movePage");
    $html = "총<em> " . $rsCount . "</em>건의 배송지가 있습니다.";

    $param = array();
    $param["list"] = $list;
    $param["paging"] = $paging;
    $param["html"] = $html;

    echo getAddressMain($param);

} else {

    echo getAddressTypeMain();
}
?>


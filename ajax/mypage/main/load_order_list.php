<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MainDAO.inc"); 

if ($is_login === false) {
    header("Location: /main/main.html");
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$template = new Template();
$frontUtil = new FrontCommonUtil();
$dao = new MainDAO();

$session = $fb->getSession();
//$seqno = $session["org_member_seqno"];
$seqno = $session["member_seqno"];
$member_dvs = $session["member_dvs"];
$state_dvs = $fb->form("dvs");

$state_arr = $dao->selectStateAdminRange($conn, $state_dvs);

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

//전체주문
$param = array();
$param["state_min"] = $state_arr["min"];
$param["state_max"] = $state_arr["max"];
$param["from"] = $fb->form("from");
$param["to"] = $fb->form("to");

if ($member_dvs == "기업") {
    $param["member_seqno"] = $bu_seqno;
    $rs = $dao->selectMainBusiOrderList($conn, $param);

    $param["dvs"] = "COUNT";
    $count_rs = $dao->selectMainBusiOrderList($conn, $param);
    $rsCount = $count_rs->fields["cnt"];

    $param["count"] = $rsCount;
    $list = makeBusiOrderListHtml($rs, $param);
 
    if (!$list) {
        $list = "<tr><td colspan=\"10\">검색한 결과가 없습니다.</td></tr>";
    }

} else {
    $param["member_seqno"] = $seqno;
    $rs = $dao->selectMainOrderList($conn, $param);

    $param["dvs"] = "COUNT";
    $count_rs = $dao->selectMainOrderList($conn, $param);
    $rsCount = $count_rs->fields["cnt"];

    $param["count"] = $rsCount;

    $list = makeOrderListMainHtml($rs, $param);

    if (!$list) {
        $list = "<tr><td colspan=\"9\">검색한 결과가 없습니다.</td></tr>";
    }
}

$html = <<<HTML
            <colgroup>
                <col style="width:40px">
                <col style="width:100px">
                <col>
                <col style="width:150px">
                <col style="width:75px">
                <col style="width:80px">
                <col style="width:80px">
                <col style="width:40px">
            </colgroup>
            <thead>
                <tr>
                    <th>번호</th>
                    <th>주문일</th>
                    <th>상품정보</th>
                    <th>후가공</th>
                    <th>수량(건)</th>
                    <th>결제금액</th>
                    <th>진행상태</th>
                    <th>관리</th>
                </tr>
            </thead>
HTML;

if ($member_dvs == "기업") {
    $html = <<<HTML
                <colgroup>
                    <col width="40">
                    <col width="70">
                    <col width="120">
                    <col width="120">
                    <col>
                    <col>
                    <col width="80">
                    <col width="80">
                    <col width="80">
                    <col width="40">
                </colgroup>
                <thead>
                    <tr>
                        <th>번호</th>
                        <th>주문일</th>
                        <th>주문번호</th>
                        <th>주문담당자</th>
                        <th>인쇄물제목</th>
                        <th>상품정보</th>
                        <th>수량(건)</th>
                        <th>결제금액</th>
                        <th>진행상태</th>
                        <th>관리</th>
                    </tr>
                </thead>
HTML;
}

echo $html . $list;
$conn->Close();
?>

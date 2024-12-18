<?php
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberInfoDAO();

//$conn->debug = 1;

$session = $fb->getSession();

// 한페이지에 출력한 페이지 갯수
$list_num = $fb->form("list_num");

// 현재 페이지
$page = $fb->form("page");

// 리스트 보여주는 갯수 설정
if (!$fb->form("showPage")) {
    $list_num = 10;
}

// 블록 갯수
$scrnum = 5;

// 페이지가 없으면 1페이지
if (!$page) {
    $page = 1;
}

$s_num        = $list_num * ($page-1);
$member_seqno = $session["member_seqno"];

$param = [];
$param["s_num"]        = $s_num;
$param["list_num"]     = $list_num;
$param["member_seqno"] = $member_seqno;

$rs = $dao->selectRefundReq($conn, $param);

$rsCount = $dao->selectFoundRows($conn);
$param["count"] = $rsCount;

$list = makeRefundReqListHtml($conn, $rs, $param);

$paging = mkDotAjaxPage($rsCount, $page, $list_num, "movePage");

echo $list . "♪" . $paging;
exit;

/**************** 함수 영역 *****************/

function makeRefundReqListHtml($conn, $rs, $param) {

    $ret = "";
    $html  = "\n            <tr>";
    $html .= "\n                <td>%s</td>"; // #1 번호
    $html .= "\n                <td>%s</td>"; // #2 요청일자
    $html .= "\n                <td>%s</td>"; // #3 요청금액
    $html .= "\n                <td>%s</td>"; // #4 요청내용
    $html .= "\n                <td>%s</td>"; // #5 환불여부
    $html .= "\n                <td>%s</td>"; // #6 환불일자
    $html .= "\n            </tr>";

    $i = $param["count"] - $param["s_num"];
    $j = 1;

    while ($rs && !$rs->EOF) {

        $ret .= sprintf($html, $i // #1 번호
                             , $rs->fields["req_date"]   // #2 주문일
                             , number_format($rs->fields["req_price"]) . "\\"  // #3 요청금액
                             , $rs->fields["req_cont"]   // #4 요청내용
                             , $rs->fields["refund_yn"]  // #5 환불여부
                             , $rs->fields["refund_date"]); // #6 환불일자
        $i--;
        $rs->moveNext();
    }

    if ($rs->recordCount() == 0) {
        $ret  = "\n            <tr>";
        $ret .= "\n                <td colspan=\"6\">요청이 없습니다.</td>";
        $ret .= "\n            </tr>";
    }

    return $ret;
}



?>

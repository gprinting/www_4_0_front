<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MemberInfoDAO();

$param = array();
$param["member_seqno"] = $fb->form("member_seqno");

$rs = $dao->selectCoPerMember($conn, $param);
$i = 1;

$temp_html  = "\n<tr>";
$temp_html .= "\n    <td class=\"center\">%s</td>";
$temp_html .= "\n    <td>%s</td>";
$temp_html .= "\n    <td>%s</td>";
$temp_html .= "\n    <td>%s</td>";
$temp_html .= "\n    <td>%s</td>";
$temp_html .= "\n    <td>%s</td>";
$temp_html .= "\n    <td class=\"center\"><button onclick=\"layerPopup('l_officer', 'popup/l_orderofficer_modify.html?seq=%s');\">수정/삭제</button></td>";
$temp_html .= "\n</tr>";

$order_mng_html = "";
while ($rs && !$rs->EOF) {

    $order_mng_html .= sprintf($temp_html, 
            $i,
            $rs->fields["member_name"],
            $rs->fields["id"],
            $rs->fields["tel_num"],
            $rs->fields["cell_num"],
            $rs->fields["mail"],
            $rs->fields["member_seqno"]);
    $i++;
    $rs->moveNext();
}

$conn->Close();
echo $order_mng_html;
?>

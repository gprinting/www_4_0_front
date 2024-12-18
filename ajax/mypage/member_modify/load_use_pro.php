<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MemberInfoDAO();

//회원 상세정보
$param = array();
$param["table"] = "member_detail_info";
$param["col"] = "use_pro";
$param["where"]["member_seqno"] = $fb->form("member_seqno");

$detail_rs = $dao->selectData($conn, $param);

//OS 별 사용프로그램
$param = array();
$param["table"] = "pro_typ";
$param["col"] = "pro";
$param["where"]["oper_sys"] = $fb->form("use_oper_sys");

$rs = $dao->selectData($conn, $param);

$pro_html = "\n<label><input type=\"radio\" name=\"use_pro\" value=\"%s\"%s>%s</label>";
$html = "";

while ($rs && !$rs->EOF) {

    $checked = "";
    if ($detail_rs->fields["use_pro"] == $rs->fields["pro"]) {
        $checked = " checked=\"checked\"";
    }
    $html .= sprintf($pro_html
            , $rs->fields["pro"]
            , $checked
            , $rs->fields["pro"]);
    $rs->moveNext();
}
$conn->Close();
echo $html;
?>

<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$template = new Template();
$frontUtil = new FrontCommonUtil();
$dao = new MemberInfoDAO();

$td_html .= "\n  <td rowspan=\"%s\">%s</td>";

$html  = "\n<tr>";
$html .= "%s";
$html .= "\n  <td>%s</td>";
$html .= "\n  <td>%s%%</td>";
$html .= "\n</tr>";

$tb_html = "";

$param = array();
$param["cate_level"] = "2";
$param["cate_sortcode"] = $fb->form("cate_sortcode");

$mid_rs = $dao->selectCateTable($conn, $param);

while ($mid_rs && !$mid_rs->EOF) {

    $param = array();
    $param["cate_level"] = "3";
    $param["cate_sortcode"] = $mid_rs->fields["sortcode"];

    $btm_rs = $dao->selectCateTable($conn, $param);

    $param["type"] = "COUNT";
    $btm_cnt_rs = $dao->selectCateTable($conn, $param);
    $btm_cnt = $btm_cnt_rs->fields["cnt"];
        
    $rs_td_html = sprintf($td_html, $btm_cnt, $mid_rs->fields["cate_name"]);

    $i = 1;
    while ($btm_rs && !$btm_rs->EOF) {

        if ($i != 1) {
            $rs_td_html = "";
        }

        $param = array();
        $param["cate_sortcode"] = $btm_rs->fields["sortcode"];
        $param["grade"] = $fb->session("grade");

        $rate_rs = $dao->selectCateGradeInfo($conn, $param);
        $rate = $rate_rs->fields["rate"];
        if (!$rate) {
            $rate = "0";
        }

        $tb_html .= sprintf($html, $rs_td_html
                , $btm_rs->fields["cate_name"]
                , $rate);
        $i++;
        $btm_rs->moveNext();
    }

    $mid_rs->moveNext();
}

echo $tb_html;
$conn->Close();
?>

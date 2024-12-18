<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/SheetDAO.inc");
include_once(INC_PATH . "/com/nexmotion/doc/front/order/SheetPopup.inc");
include_once($_SERVER["DOCUMENT_ROOT"] . "/common_lib/CommonUtil.php");

$frontUtil = new FrontCommonUtil();

if ($is_login === false) {
    $frontUtil->errorGoBack("로그인 상태가 아닙니다.");
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$commonUtil = new CommonUtil();

$fb = new FormBean();
$dao = new SheetDAO();

$sell_site    = $fb->session("sell_site");
$member_seqno = $fb->session("org_member_seqno");

$fb = $fb->getForm();
$seqno_arr = explode('|', $fb["seq"]);
$order_rs = $dao->selectOrderCateSortcode($conn, $seqno_arr);

$tbody_base  = "<tr>";
$tbody_base .= "    <td class=\"btn\"><input type=\"checkbox\" name=\"cp_seqno[]\" value=\"%s\" class=\"_individual\"></td>";
$tbody_base .= "    <th scope=\"row\" class=\"subject\">%s</th>";
$tbody_base .= "    <td>%s</td>";
$tbody_base .= "    <td>%s</td>";
$tbody_base .= "    <input type=\"hidden\" id=\"cp_val_%s\" value=\"%s\">";
$tbody_base .= "    <input type=\"hidden\" id=\"cp_unit_%s\" value=\"%s\">";
$tbody_base .= "    <input type=\"hidden\" id=\"cp_max_discount_price_%s\" value=\"%s\">";
$tbody_base .= "    <input type=\"hidden\" id=\"cp_min_order_price_%s\" value=\"%s\">";
$tbody_base .= "    <input type=\"hidden\" id=\"categories_%s\" value=\"%s\">";
$tbody_base .= "</tr>";

$param = array();
$tbody = '';

$param["member_seqno"] = $member_seqno;
$cp_rs = $dao->selectValidCpSeqno($conn, $param);

unset($param);
$param["sell_site"] = $sell_site;
while ($cp_rs && !$cp_rs->EOF) {
    $param["coupon_seqno"] = $cp_rs->fields["coupon_seqno"];
    $rs = $dao->selectValidCpInfo($conn, $param);

    while($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $conpon_seqno = $fields["coupon_seqno"];
        $discount_rate = json_decode($fields["discount_rate"]);
        $categories = json_decode($fields["categories"]);
        $str_categories = implode( ',', $categories );

        $unit = $discount_rate->sale_dvs;

        $sale_val = null;

        if ($unit === '%') {
            $sale_val = $discount_rate->per_val . "%";
            $val = $discount_rate->per_val;
        } else {
            $sale_val = number_format($discount_rate->won_val) . "원";
            $val = $discount_rate->won_val;
        }

        $start_date = $fields["release_date"];
        $end_date   = $fields["expired_date"];

        $period = "%s ~ %s";

        if ($end_date === "0000-00-00 00:00:00") {
            $period = sprintf($period, $start_date, "무기한");
        } else {
            $period = sprintf($period, $start_date, $end_date);
        }

        $tbody .= sprintf($tbody_base, $conpon_seqno
            , $fields["name"]
            , $sale_val
            , $period
            , $conpon_seqno
            , $val
            , $conpon_seqno
            , $unit
            , $conpon_seqno
            , $discount_rate->min_order_price
            , $conpon_seqno
            , $discount_rate->max_discount_price
            , $conpon_seqno
            , $str_categories);

        $rs->MoveNext();
    }

    $cp_rs->MoveNext();
}

echo couponPopup($tbody);

$conn->Close();
exit;
?>

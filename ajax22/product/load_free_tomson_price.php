<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/AftPriceUtil.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ProductCommonDAO();
$fb = new FormBean();
$util = new AftPriceUtil(["conn" => $conn, "dao" => $dao]);

$sell_site    = $fb->session("sell_site");
$member_seqno = $fb->session("org_member_seqno");

$fb = $fb->getForm();

// $Pp_id -> 종이관련 정보
$paper_name = $fb["paper_name"];
// $S_Code -> 사이즈 유형 정보
$stan_name  = $fb["stan_name"];
// $Qty -> 수량
$amt        = intval($fb["amt"]);
// 도무송 수량
$amt_ts     = intval($fb["amt_ts"]);
// 도무송 유형별 수량
$amt_f1     = intval($fb["amt_f1"]);
$amt_f2     = intval($fb["amt_f2"]);
$amt_f3     = intval($fb["amt_f3"]);
$amt_f4     = intval($fb["amt_f4"]);
// $chkW, chkH
$size_width = doubleval($fb["size_width"]);
$size_vert  = doubleval($fb["size_vert"]);

//$conn->debug = 1;

$param = [];
$param["paper_name"] = $paper_name;
$param["stan_name"]  = $stan_name;
$param["amt"]        = $amt;
$param["amt_ts"]     = $amt_ts;
$param["amt_f1"]     = $amt_f1;
$param["amt_f2"]     = $amt_f2;
$param["amt_f3"]     = $amt_f3;
$param["amt_f4"]     = $amt_f4;
$param["size_width"] = $size_width;
$param["size_vert"]  = $size_vert;

$calc_price = $util->getFreeTomsonPrice($param);

$json  = '{';
$json .=    "\"price\" : \"%s\"";
$json .=   ",\"amt_rate\" : \"%s\"";
$json .=   ",\"amt_aplc\" : \"%s\"";
$json .= '}';

// 수량별 할인 검색
$price_tb = "ply_price";

unset($param);
$param["table_name"]    = $price_tb;
$param["member_seqno"]  = $member_seqno;
$param["cate_sortcode"] = $fb["cate_sortcode"];
$param["paper_mpcode"]  = $fb["paper_mpcode"];
$param["bef_print_mpcode"]     = intval($fb["bef_print_mpcode"]);
$param["aft_print_mpcode"]     = intval($fb["aft_print_mpcode"]);
$param["bef_add_print_mpcode"] = intval($fb["bef_add_print_mpcode"]);
$param["aft_add_print_mpcode"] = intval($fb["aft_add_print_mpcode"]);
$param["stan_mpcode"]   = $fb["stan_mpcode"];
$param["amt"]           = $amt;

$rs = $dao->selectAmtMemberCateSale($conn, $param);

$amt_rate = doubleval($rs["rate"]);
$amt_aplc = doubleval($rs["aplc_price"]);

echo sprintf($json, $calc_price, $amt_rate, $amt_aplc);
?>

<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/product/ProductNcDAO.inc");
include_once($_SERVER["DOCUMENT_ROOT"] . "/product/common/calc_opt_price.php");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new FrontCommonUtil();
$dao = new ProductNcDAO();
$fb = new FormBean();

$sell_site = $fb->session("sell_site");

$cate_sortcode = $fb->form("cate_sortcode");
$name          = $fb->form("name");
$amt           = $fb->form("amt");
$sell_price    = $fb->form("sell_price");

//$conn->debug = 1;

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["name"]          = $name;
$param["basic_yn"]      = 'N';

// 카테고리와 옵션명으로 맵핑코드 검색
$opt_info_rs = $dao->selectCateOptInfo($conn, $param);

if ($opt_info_rs->EOF) {
    goto BLANK;
}

$mpcode_arr = $util->rs2arr($opt_info_rs);

unset($opt_info_rs);
unset($param);

// 가격 검색
$mpcode = $util->arr2delimStr($mpcode_arr);

$param["sell_site"] = $sell_site;
$param["mpcode"]    = $mpcode;

$price_rs = $dao->selectCateOptPrice($conn, $param);

if ($price_rs->EOF) {
    goto BLANK;
}

$price_info_arr = array();
while ($price_rs && !$price_rs->EOF) {
    $amt        = $price_rs->fields["amt"];
    $sell_price = $price_rs->fields["sell_price"];
    $mpcode     = $price_rs->fields["mpcode"];

    $price_info_arr[$mpcode][$amt] = $sell_price;

    $price_rs->MoveNext();
}

unset($price_rs);

// json 생성
$outer_form = "\"%s\":{";
$inner_form = "\"%s\":\"%s\",";

$ret = '{';

foreach ($price_info_arr as $mpcode => $price_arr) {
    $crtr_unit = $crtr_unit_arr[$mpcode];

    $ret .= sprintf($outer_form, $mpcode);

    $temp = "";
    foreach ($price_arr as $amt => $price) {
        $temp .= sprintf($inner_form, $amt, $price);
    }

    $ret .= substr($temp, 0, -1); 
    $ret .= "},";
}

$ret  = substr($ret, 0, -1); 
$ret .= '}';

echo $ret;
$conn->Close();
exit;

BLANK:
    echo "{}";
    $conn->Close();
    exit;
?>

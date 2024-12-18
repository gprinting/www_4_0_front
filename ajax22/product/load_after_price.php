<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new FrontCommonUtil();
$dao = new ProductCommonDAO();
$fb = new FormBean();

$sell_site = $fb->session("sell_site");

$cate_sortcode = $fb->form("cate_sortcode");
$after_name    = $fb->form("after_name");
$depth1        = $fb->form("depth1");
$depth2        = $fb->form("depth2");
$depth3        = $fb->form("depth3");
$size          = $fb->form("size");

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["after_name"]    = $after_name;
$param["depth1"]        = $depth1;
$param["depth2"]        = $depth2;
$param["depth3"]        = $depth3;
$param["size"]          = $size;

//$conn->debug = 1;
// 카테고리와 후공정명으로 맵핑코드, 기준단위 검색
$after_info_rs = $dao->selectCateAfterInfo($conn, $param);

if ($after_info_rs->EOF) {
    goto BLANK;
}

$mpcode_arr = $util->rs2arr($after_info_rs);
$after_info_rs->MoveFirst();
$crtr_unit_arr = $util->rs2arr($after_info_rs, "crtr_unit");

// 기준단위 배열 맵핑코드랑 매칭
$mpcode_arr_count = count($mpcode_arr);

$temp_arr = array();
for ($i = 0; $i < $mpcode_arr_count; $i++) {
    $mpcode = $mpcode_arr[$i];
    $crtr_unit = $crtr_unit_arr[$i];

    $temp_arr[$mpcode] = $crtr_unit;
}

$crtr_unit_arr = $temp_arr;
unset($temp_arr);
unset($after_info_rs);
unset($param);

// 가격 검색
$mpcode = $util->arr2delimStr($mpcode_arr);

$param["mpcode"]    = $mpcode;
$param["sell_site"] = $sell_site;

$price_rs = $dao->selectCateAfterPrice($conn, $param);

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
    $ret .= sprintf($inner_form, "crtr_unit", $crtr_unit);

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

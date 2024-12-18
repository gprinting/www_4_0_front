<?php
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/product/ProductAoDAO.inc");
include_once(INC_PATH . '/define/front/product_ao_rack_info.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ProductAoDAO();
$fb = new FormBean();

$fb = $fb->getForm();

$min_max_arr = RACK_MIN_MAX_SIZE;

$cate_sortcode = $fb["cate_sortcode"];
$opt_name      = $fb["opt_name"];
$depth1        = $fb["depth1"];
$depth2        = $fb["depth2"];

$flag          = $fb["flag"];

if ($flag === 'Y') {
    $flag = true;
} else {
    $flag = false;
}

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["opt_name"]      = $opt_name;
$param["depth1"]        = $depth1;
$param["depth2"]        = $depth2;

// 카테고리와 후공정명으로 후공정 하위 depth html 검색
$rs = $dao->selectCateAoOptLower($conn, $param, $flag);

$ret = '';
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $mpcode = $fields["mpcode"] ?? $fields["lower_depth"];

    $attr = '';
    if (!empty($fields["mpcode"])) {
        $size_arr = $min_max_arr[$depth1][$depth2][$fields["lower_depth"]];

        $attr  = "min_wid=\"%s\" min_vert=\"%s\"";
        $attr .= " max_wid=\"%s\" max_vert=\"%s\"";
        $attr = sprintf($attr, $size_arr["MIN_WID"]
                             , $size_arr["MIN_VERT"]
                             , $size_arr["MAX_WID"]
                             , $size_arr["MAX_VERT"]);
    }

    $ret .= option($fields["lower_depth"], $mpcode, $attr);

    $rs->MoveNext();
}

echo $ret;
$conn->Close();

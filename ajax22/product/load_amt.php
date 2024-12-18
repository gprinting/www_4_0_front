<?php
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");
include_once(INC_PATH . "/common_define/prdt_default_info.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ProductCommonDAO();
$fb = new FormBean();
$fb = $fb->getForm();

//$conn->debug = 1;

$cate_sortcode = $fb["cate_sortcode"];
$paper_mpcode  = $fb["paper_mpcode"];
$stan_mpcode   = $fb["stan_mpcode"];
$mono_dvs      = $fb["mono_yn"];
$amt_unit      = $fb["amt_unit"];
$def_amt       = $fb["def_amt"];

$amt_arr = PrdtDefaultInfo::AMT[$cate_sortcode][$fb["size_name"]];
if (empty($amt_arr)) {
    $amt_arr = PrdtDefaultInfo::AMT[$cate_sortocde];
}

if (empty($amt_arr)) {
    $price_tb = "ply_price";

    $temp = [];

    $param = [];
    $param["table_name"]    = $price_tb;
    $param["cate_sortcode"] = $cate_sortcode;
    $param["paper_mpcode"]  = $paper_mpcode;
    $param["stan_mpcode"]   = $stan_mpcode;
    $param["amt_unit"]      = $amt_unit;
    if (!empty($def_amt)) {
        $param["def_amt"] = doubleval($def_amt);
    }

    echo $dao->selectCateAmtHtml($conn, $param, $temp);
    $conn->Close();
} else {
    $amt_arr_count = count($amt_arr);

    $amt = '';

    for ($i = 0; $i < $amt_arr_count; $i++) {
        $val = $amt_arr[$i];
        $attr = '';

        if (doubleval($val) === $def_amt) {
            $attr = "selected=\"selected\"";
            $price_info_arr["amt"] = $val;
        }

        if ($val < 1) {
            $tmp = $val;
        } else {
            $tmp = number_format($val);
        }

        $amt .= option($tmp . ' ' . $amt_unit, $val, $attr);
    }

    echo $amt;
}

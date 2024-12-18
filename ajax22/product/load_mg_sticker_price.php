<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$util = new CommonUtil();
$fb = new FormBean();
$fb = $fb->getForm();

$wid  = doubleval($fb["work_wid_size"]) / 10.0;
$vert = doubleval($fb["work_vert_size"]) / 10.0;

$amt   = doubleval($fb["amt"]);
$count = doubleval($fb["count"]);

if($amt < 6000){
    $price = $wid * $vert * $amt * 0.95 * $count;
}else {
    $price = $wid * $vert * $amt * 0.9 * $count;
}

if ($wid <= 3.8 && $vert <= 5.5) {
    $price_arr = [
        1000 => 18700
        ,2000 => 37400
        ,3000 => 56100
        ,4000 => 74800
        ,5000 => 93500
        ,6000 => 105600
        ,7000 => 123200
        ,8000 => 140800
        ,9000 => 158400
        ,10000 => 176000
    ];

    $result = $price_arr[$amt];
} else {
    $result = round($price, -2);
}

$ret  = "{\"mg\" : {";
$ret .=              "\"sell_price\" : \"%s\",";
$ret .=              "\"amt_rate\"   : \"%s\",";
$ret .=              "\"amt_aplc\"   : \"%s\"";
$ret .=            "}";
$ret .= "}";


echo sprintf($ret, $util->ceilVal($result)
                 , '0'
                 , '0');
exit;

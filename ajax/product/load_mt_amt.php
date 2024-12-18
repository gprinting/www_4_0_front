
<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/common_define/prdt_default_info.inc");
include_once(INC_PATH . "/com/nexmotion/html/MakeCommonHtml.inc");

$fb = new FormBean();

$cate_sortcode = $fb->form("cate_sortcode");
$stan_name = $fb->form("stan_name");
$amt_unit  = $fb->form("amt_unit");

$amt_arr = PrdtDefaultInfo::AMT[$cate_sortcode][$stan_name];

$amt_arr_count = count($amt_arr);

$default_amt = doubleval($default_sel_arr["amt"]);

$amt = '';

for ($i = 0; $i < $amt_arr_count; $i++) {
    $val = $amt_arr[$i];
    $attr = '';

    if (doubleval($val) === $default_amt) {
        $attr = "selected=\"selected\"";
    }

    if ($val < 1) {
        $tmp = $val;
    } else {
        $tmp = number_format($val);
    }

    $amt .= option($tmp . ' ' . $amt_unit, $val, $attr);
}

echo $amt;

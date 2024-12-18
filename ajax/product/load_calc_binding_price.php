<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/front/BindingPriceUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/common_define/prdt_default_info.inc");

$bindingUtil = new BindingPriceUtil();
$fb = new FormBean();
$fb = $fb->getForm();

$cate_sortcode = $fb["cate_sortcode"];
$stan_name     = $fb["stan_name"];

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["amt"]           = $fb["amt"];
$param["page"]          = $fb["page"];
$param["price"]         = $fb["price"];
$param["coating_yn"]    = ($fb["coating_yn"] === "true") ? true : false;
$param["depth1"]        = $fb["depth1"];
$param["stan_name"]     = $stan_name;
$param["pos_num"] = PrdtDefaultInfo::POSITION_NUMBER[$cate_sortcode][$stan_name];

$bindingUtil->setData($param);
$price = $bindingUtil->calcBindingPrice();

echo sprintf("{\"price\":\"%s\"}", $price);
?>

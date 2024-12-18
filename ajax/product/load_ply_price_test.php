<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . '/classes/dprinting/PriceCalculator/Common/DPrintingFactory.php');

$fb = new FormBean();
$session = $fb->getSession();
$fb = $fb->getForm();
$fb["grade"] = $session["level"];

$cate_sortcode = $fb["cate_sortcode"];
$stan_mpcode   = $fb["stan_mpcode"];
$amt           = $fb["amt"];
$paper_mpcode  = $fb["paper_mpcode"];
$after_name   = explode("|", $fb["after_name_list"]);
$after_mpcode   = explode("|", $fb["after_mp_list"]);

$factory = new DPrintingFactory();

$product = $factory->create($fb["cate_sortcode"]);
$product = $product->setInfo($fb);

$result = $product->cost();
$ret  = "{\"%s\" : {";
$ret .=              "\"sell_price\" : \"%s\",";
foreach($result as $key => $value) {
    if(is_array($value)) {
        $ret .=              "\"". $key . "\":" . json_encode($value) .",";
    } else {
        $ret .=              "\"". $key . "\":\"" . $value ."\",";
    }
}
$ret .=              "\"amt_rate\"   : \"%s\",";
$ret .=              "\"amt_aplc\"   : \"%s\",";
$ret .=              "\"jarisu\"   : \"%s\"";
$ret .=            "}";
$ret .= "}";

echo sprintf($ret, $fb["dvs"]
    , $result["sell_price"]
    , 0
    , 0
    , $result["jarisu"]);
?>

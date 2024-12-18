<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/define/front/product_default_sel.inc");
include_once(INC_PATH . "/common_define/prdt_default_info.php");
include_once(INC_PATH . "/common_lib/CommonUtil.php");

$fb = new FormBean();
$fb = $fb->getForm();

$commonUtil = new CommonUtil();

$cate_sortcode = $fb["cate_sortcode"];
$size_dvs      = $fb["size_dvs"];

$default_sel_arr = ProductDefaultSel::DEFAULT_SEL[$cate_sortcode];
$position_num_arr = PrdtDefaultInfo::POSITION_NUMBER[$cate_sortcode];

$manu_pos_num = $fb["manu_pos_num"];

$size_name = $fb["size_name"];
// 비규격 사이즈일 때 기본 자리수 가져오기용 사이즈명으로 변경
if ($size_dvs === "manu") {
    $size_name = $default_sel_arr["size"];
}

$pos_num = $position_num_arr[$size_name];
// 비규격 사이즈 일 때 계산된 자리수로 나눠줌
if ($size_dvs === "manu") {
    $pos_num /= intval($manu_pos_num);
}

$amt      = $fb["amt"];
$page_num = 2;
$amt_unit = $fb["amt_unit"];

$param = array();
$param["amt"]      = $amt;
$param["pos_num"]  = $pos_num;
$param["page_num"] = $page_num;
$param["amt_unit"] = $amt_unit;

echo $commonUtil->getPaperRealPrintAmt($param);
?>

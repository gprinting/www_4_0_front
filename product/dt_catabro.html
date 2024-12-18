<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/product/info/BookletInfo.php");
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");
include_once(INC_PATH . '/common_define/common_info.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$template = new Template();
$dao = new ProductCommonDAO();

$dvs = "dt";

// 사진, 배너, 카테고리 셀렉트박스
include_once($_SERVER["DOCUMENT_ROOT"] . "/product/info/common_info.php");

// 로그인 상태인지 체크하는부분 include
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/login_check.php");

$flag_arr = array(
    "affil_yn"      => true
,"pos_yn"        => true
,"mix_yn"        => true
,"paper_sort_yn" => true
,"size_typ_yn"   => false
,"calc_flag"     => true
,"common_flag"   => false
,"after_flag"    => false
,"mobile_flag"   => $is_mobile
);
// 표지
$cover = new BookletInfo($conn,
    $template,
    $sortcode_b,
    "cover",
    $flag_arr,
    $def_arr);
$cover->init();

$amt         = $cover->getAmt();
$pos_num     = $cover->getPosNum();
$affil       = $cover->getAffil();
$stan_mpcode = $cover->getStanMpcode();
$size_name   = $cover->getSizeName();
$flag_arr["common_flag"] = true;
// 내지1
$inner1 = new BookletInfo($conn,
    $template,
    $sortcode_b,
    "inner1",
    $flag_arr,
    $def_arr);
$inner1->setAmt($amt);
$inner1->setPosNum($pos_num);
$inner1->setAffil($affil);
$inner1->setStanMpcode($stan_mpcode);
$inner1->setSizeName($size_name);
$inner1->init();
// 내지2
$flag_arr["after_flag"] = true;
$inner2 = new BookletInfo($conn,
    $template,
    $sortcode_b,
    "inner2",
    $flag_arr,
    $def_arr);
$inner2->setPosNum($pos_num);
$inner2->setAffil($affil);
$inner2->setSizeName($size_name);
$inner2->init();
// 내지3
$inner3 = new BookletInfo($conn,
    $template,
    $sortcode_b,
    "inner3",
    $flag_arr,
    $def_arr);
$inner3->setPosNum($pos_num);
$inner3->setAffil($affil);
$inner3->setSizeName($size_name);
$inner3->init();
/*
*/

$cover_tax          = $cover->getTax();
$cover_supply_price = $cover->getSupplyPrice();
$cover_sell_price   = $cover->getSellPrice();
$cover_sale_price   = $cover->getSalePrice();

$inner1_tax          = $inner1->getTax();
$inner1_supply_price = $inner1->getSupplyPrice();
$inner1_sell_price   = $inner1->getSellPrice();
$inner1_sale_price   = $inner1->getSalePrice();

$tax          = $cover_tax + $inner1_tax;
$supply_price = $cover_supply_price + $inner1_supply_price;
$sell_price   = $cover_sell_price + $inner1_sell_price;
$sale_price   = $cover_sale_price + $inner1_sale_price;

$template->reg("ad_tax"         , number_format($tax));
$template->reg("ad_supply_price", number_format($supply_price));
$template->reg("ad_sell_price"  , number_format($sell_price));
$template->reg("ad_sale_price"  , number_format($sale_price));

// 옵션 html 생성
$opt_def_arr = $def_arr["opt"];

$param = [];
$param["cate_sortcode"] = $sortcode_b;
$param["dvs"]           = $dvs;
$opt = $dao->selectCateOptHtml($conn, $param, $opt_def_arr);
$template->reg("opt", $opt["html"]);

$template->reg("add_opt", '');
if (empty($opt["info_arr"]) === false) {
    $add_opt = $opt["info_arr"]["name"];
    $add_opt = $dao->parameterArrayEscape($conn, $add_opt);
    $add_opt = $frontUtil->arr2delimStr($add_opt);

    $param["opt_name"] = $add_opt;
    $param["opt_idx"]  = $opt["info_arr"]["idx"];
    $param["mobile_yn"] = $is_mobile;
    $add_opt = $dao->selectCateAddOptInfoHtml($conn, $param, $opt_def_arr);
    unset($param);
    $template->reg("add_opt", $add_opt);
}

// 등급할인 html 생성
$cover_grade_sale  = $cover->getGradeSalePrice();
$cover_member_sale = $cover->getMemberSalePrice();

$inner1_grade_sale  = $inner1->getGradeSalePrice();
$inner1_member_sale = $inner1->getMemberSalePrice();

$grade_sale  = $cover_grade_sale + $inner1_grade_sale;
$member_sale = $cover_memer_sale + $inner1_member_sale;

unset($param);
if (!$is_login) {
    $dscr = "로그인시 할인받으실 수 있는 금액입니다.";
}
unset($param);

$grade = empty($_SESSION["grade"]) ? '10' : $_SESSION["grade"];

$param["dscr"]             = $dscr;
$param["rate"]             = $cover->getGradeSaleRate();
$param["member_sale_rate"] = $cover->getMemberSaleRate();
$param["grade"]            = $fb->session("grade");
$param["price"]            = $frontUtil->ceilVal($grade_sale + $member_sale);
$param["mobile_yn"]        = $is_mobile;
$grade_sale_html = makeGradeSaleDl($param);
$template->reg("ad_grade_sale", $grade_sale_html);

// 이벤트할인 html 생성
unset($param);
$param["dscr"]  = NO_EVENT;
$template->reg("ad_event_sale", makeEventSaleDl($param));

// 견적서 html 생성
$cover_paper_price  = $cover->getPaperPrice();
$cover_output_price = $cover->getOutputPrice();
$cover_print_price  = $cover->getPrintPrice();

$inner1_paper_price  = $inner1->getPaperPrice();
$inner1_output_price = $inner1->getOutputPrice();
$inner1_print_price  = $inner1->getPrintPrice();

$paper_price  = $cover_paper_price + $inner1_paper_price;
$output_price = $cover_output_price + $inner1_output_price;
$print_price  = $cover_print_price + $inner1_print_price;

unset($param);
$param["esti_paper"]   = $paper_price;
$param["esti_output"]  = $output_price;
$param["esti_print"]   = $print_price;
$param["esti_binding"] = $cover->getBindingPrice();
$param["esti_opt"]     = $opt_price;
$param["esti_sell_price"] = $sell_price;
$param["esti_sale_price"] = $sale_price;
$template->reg("quick_esti", getQuickEstimateHtml(
    $param,
    $frontUtil,
    ProductInfoClass::AFTER_ARR
));

// 기본사용 자바스크립트, css 파일 불러오는 용
$template->reg("dir", "product");
$template->reg("page", "ad_catabro");

// 상세 탭
$tab = "
    <table width=\"1180\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" class=\"detail_doc product_detail_doc\" id=\"tab1\" style=\"display: table;\">
        <tbody>
            <tr>
                <td>
                    <p><img border=\"0\" src=\"" . $design_path . "/images/product/tab/info_ad01.jpg\" class=\"tab_detail_img\"></p>
                    <p><img border=\"0\" src=\"" . $design_path . "/images/product/tab/caution_ad01.jpg\" class=\"tab_detail_img\"></p>
                </td>
            </tr>
        </tbody>
    </table>
";
$template->reg("tab", $tab);

//design_dir 경로
$template->reg("root_design_dir", $root_design_dir);
$template->reg("design_dir", $design_path);
$template->htmlPrint($html_path);

$conn->Close();
?>

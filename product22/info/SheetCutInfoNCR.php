<?
/*
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016-11-22 엄준현 추가(NCR 관련이라 독립)
 *============================================================================
 *
 */
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/product/info/CommonInfo.php");
include_once(INC_PATH . "/define/front/product_info_class.inc");
include_once(INC_PATH . "/define/front/common_config.inc");
include_once(INC_PATH . "/define/front/message.inc");
include_once(INC_PATH . "/common_define/prdt_default_info.inc");
include_once(INC_PATH . "/com/nexmotion/html/front/product/QuickEstimate.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/product/ProductSheetCutDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");

class SheetCutInfoNCR extends CommonInfo {
    // 확정형
    var $sell_price        = null;
    var $grade_sale_rate   = null;
    var $grade_sale_price  = null;
    var $member_sale_rate  = null;
    var $member_sale_price = null;
    var $sale_price        = null;
    // 계산형
    var $paper_price       = null;
    var $print_price       = null;
    var $output_price      = null;
    // 공통
    var $tax               = null;
    var $supply_price      = null;
    var $flattyp_yn        = null;

    /**
     * @brief 클래스 생성자
     *
     * @detail $flag_arr에 들어가는 flag는 아래와 같다
     * $flag_arr["pos_yn"]      = 사이즈 자리수 노출여부
     * $flag_arr["mix_yn"]      = 책자형 여부
     * $flag_arr["size_typ_yn"] = 사이즈명 종류 노출여부
     *
     * @param &$conn         = db 커넥션
     * @param &$template     = 주문페이지에 값을 표현할 템플릿 객체
     * @param $cate_sortcode = 카테고리 분류코드
     * @param $dvs           = 고유값을 만들기위한 구분값
     * @param $flag_arr      = 플래그 여부 배열
     */
    function __construct(&$conn, &$template,
                         $cate_sortcode, $dvs, $flag_arr, $def_arr = []) {
        $this->conn          = $conn;
        $this->template      = $template;
        $this->cate_sortcode = $cate_sortcode;
        $this->dvs           = $dvs;
        $this->affil_yn      = $flag_arr["affil_yn"];
        $this->pos_yn        = $flag_arr["pos_yn"];
        $this->mix_yn        = $flag_arr["mix_yn"];
        $this->size_typ_yn   = $flag_arr["size_typ_yn"];
        $this->mobile_yn     = $flag_arr["mobile_flag"];
        $this->def_arr       = $def_arr;

        $this->init();
    }

    /**
     * @brief 정보를 초기화 하는 함수
     */
    function init() {
        $dao = new ProductSheetCutDAO();
        $util = new FrontCommonUtil();

        $price_info_arr = array();
        $param = array();

        $conn        = $this->conn;
        $template    = $this->template;
        $sortcode_b  = $this->cate_sortcode;
        $dvs         = $this->dvs;
        $affil_yn    = $this->affil_yn;
        $pos_yn      = $this->pos_yn;
        $mix_yn      = $this->mix_yn;
        $size_typ_yn = $this->size_typ_yn;
        $is_login    = empty($_SESSION["id"]) ? false : true;
        $mobile_yn   = $this->mobile_yn;

        $default_sel_arr = $this->def_arr;

        if (empty($default_sel_arr)) {
            $default_sel_arr = ProductDefaultSel::DEFAULT_SEL[$sortcode_b];
        }

        $prefix = '';
        if (empty($dvs) === false) {
            $prefix = $dvs . '_';
        }

        //-2 제품별 카테고리 정보 생성
        if ($mix_yn === true) {
            $cate_bot = $dao->selectMixCateHtml($conn, $sortcode_b);
            $template->reg($prefix . "cate_bot", $cate_bot);
        }

        //-1 카테고리 독판여부, 수량단위 검색
        $cate_info_arr = $dao->selectCateInfo($conn, $sortcode_b);
        $mono_dvs   = $cate_info_arr["mono_dvs"];
        $amt_unit   = $cate_info_arr["amt_unit"];
        $tmpt_dvs   = $cate_info_arr["tmpt_dvs"];
        $flattyp_yn = $cate_info_arr["flattyp_yn"];
        $template->reg($prefix . "tmpt_dvs", $tmpt_dvs);
        $template->reg($prefix . "flattyp_yn", $flattyp_yn);

        // $mono_dvs : 1->전체, 2->확정, 3->계산
        $mono_dvs = ($mono_dvs === '1' || $mono_dvs === '2') ? 0 : 1;

        if ($mobile_yn) {
            $template->reg($prefix . "mono_dvs", $mono_dvs);
        } else {
            $template->reg($prefix . "mono_dvs",
                           makeMonoDvsOption($cate_info_arr["mono_dvs"]));
        }
        unset($cate_info_arr);

        // 가격 테이블명 검색
        $price_tb  = "ply_price";

        // 종이 장수(상하-2, 상중하-3, 상중중하-4)
        $sheet_count = 2;

        //0 종이 정보 생성
        // 007001001 -> 상하
        // 007001002 -> 상중1하
        // 007001003 -> 상중1중2하
        $top_paper_mpcode = null;
        $mid_paper_mpcode = null;
        $bot_paper_mpcode = null;

        $param["cate_sortcode"] = $sortcode_b;
        $param["dvs"]     = "상지";
        $param["default"] = $default_sel_arr["paper_top"];
        $top_paper = $dao->selectCatePaperHtml($conn,
                                               $param,
                                               $price_info_arr);
        $template->reg($prefix . "top_paper", $top_paper["info"]);
        $top_paper_mpcode = $price_info_arr["paper_mpcode"];
        if ($sortcode_b === "007001002") {
            // 상중하
            $temp = array();
            $param["dvs"]     = "중지";
            $param["default"] = $default_sel_arr["paper_mid1"];
            $mid_paper = $dao->selectCatePaperHtml($conn, $param, $temp);

            $template->reg($prefix . "mid1_paper", $mid_paper["info"]);
            $mid_paper_mpcode = $temp["paper_mpcode"];

            $sheet_count = 3;
        }
        if ($sortcode_b === "007001003") {
            // 상중중하
            $temp = array();
            $param["dvs"] = "중지";
            $param["default"] = $default_sel_arr["paper_mid1"];
            $mid1_paper = $dao->selectCatePaperHtml($conn, $param, $temp);
            $param["default"] = $default_sel_arr["paper_mid2"];
            $mid2_paper = $dao->selectCatePaperHtml($conn, $param, $temp);

            $template->reg($prefix . "mid1_paper", $mid1_paper["info"]);
            $template->reg($prefix . "mid2_paper", $mid2_paper["info"]);
            $mid_paper_mpcode = $temp["paper_mpcode"];

            $sheet_count = 4;
        }
        $temp = array();
        $param["dvs"] = "하지";
        $param["default"] = $default_sel_arr["paper_bot"];
        $bot_paper = $dao->selectCatePaperHtml($conn, $param, $temp);

        $template->reg($prefix . "bot_paper", $bot_paper["info"]);
        $bot_paper_mpcode = $temp["paper_mpcode"];

        //1 종이에 물린 사이즈 검색
        $param["table_name"]    = $price_tb;
        $param["paper_mpcode"]  = $price_info_arr["paper_mpcode"];
        $stan_mpcode_rs = $dao->selectCateStanMpcodeByPrice($conn, $param);

        $stan_mpcode_arr = array();
        while ($stan_mpcode_rs && !$stan_mpcode_rs->EOF) {
            $stan_mpcode_arr[] = $stan_mpcode_rs->fields["cate_stan_mpcode"];

            $stan_mpcode_rs->MoveNext();
        }
        unset($stan_mpcode_rs);
        unset($param);

        //2 사이즈 정보 생성
        $param["cate_sortcode"] = $sortcode_b;
        $param["typ"]           = $default_sel_arr["size_typ"];
        $param["cate_mpcode"]   = $dao->arr2paramStr($conn, $stan_mpcode_arr);
        $param["def_arr"] = $default_sel_arr;
        $size = $dao->selectCateSizeHtml($conn,
                                         $param,
                                         $price_info_arr,
                                         $affil_yn,
                                         $pos_yn,
                                         $size_typ_yn);

        $template->reg($prefix . "size", $size);
        $template->reg($prefix . "def_stan_mpcode", $price_info_arr["stan_mpcode"]);
        $template->reg($prefix . "def_cut_wid"    , $price_info_arr["def_cut_wid"]);
        $template->reg($prefix . "def_cut_vert"   , $price_info_arr["def_cut_vert"]);

        //3 재단, 작업사이즈간 차이 정보 생성
        $size_gap = " _gap%s";
        $val = ProductInfoClass::SIZE_GAP[$sortcode_b];
        if (empty($val)) {
            $val = $price_info_arr["size_gap"];
        }
        $val = $price_info_arr["size_gap"];
        $template->reg($prefix . "size_gap", sprintf($size_gap, $val));

        //4 인쇄도수 정보 생성
        $param["affil"]         = $price_info_arr["affil"];
        $param["default_print"] = $default_sel_arr["print"];
        $param["default_purp"]  = $default_sel_arr["print_purp"];

        $print_tmpt = $dao->selectCatePrintTmptHtml($conn,
                                                    $param,
                                                    $price_info_arr);
        if ($tmpt_dvs === '0') {
            $tmpt = $print_tmpt["단면"] . $print_tmpt["양면"];
            $template->reg($prefix . "print_tmpt", $tmpt);
        } else {
            $template->reg($prefix . "bef_print_tmpt", $print_tmpt["전면"]);
            $template->reg($prefix . "aft_print_tmpt", $print_tmpt["후면"]);
            $template->reg($prefix . "bef_add_print_tmpt", $print_tmpt["전면추가"]);
            $template->reg($prefix . "aft_add_print_tmpt", $print_tmpt["후면추가"]);
        }
        $template->reg($prefix . "print_purp", $print_tmpt["purp_dvs"]);

        unset($print_tmpt);
        unset($param);

        if ($mix_yn === false) {
            $opt_def_arr = $default_sel_arr["opt"];

            //5 옵션 체크박스 생성
            $param["cate_sortcode"] = $sortcode_b;
            $param["dvs"]           = $dvs;
            $opt = $dao->selectCateOptHtml($conn, $param, $opt_def_arr);
            $template->reg($prefix . "opt", $opt["html"]);

            //6 옵션 가격 레이어 생성
            $template->reg($prefix . "add_opt", '');
            if (empty($opt["info_arr"]) === false) {
                $add_opt = $opt["info_arr"]["name"];
                $add_opt = $dao->parameterArrayEscape($conn, $add_opt);
                $add_opt = $util->arr2delimStr($add_opt);

                $param["opt_name"] = $add_opt;
                $param["opt_idx"]  = $opt["info_arr"]["idx"];
                $param["mobile_yn"] = $mobile_yn;
                $param["dvs"]       = $dvs;
                $add_opt = $dao->selectCateAddOptInfoHtml($conn,
                                                          $param,
                                                          $opt_def_arr);
                unset($param);
                $template->reg($prefix . "add_opt", $add_opt);
            }
        }

        //7 후공정 체크박스 생성
        $aft_def_arr = $default_sel_arr["after"];

        $param["cate_sortcode"] = $sortcode_b;
        $param["dvs"]           = $dvs;

        $after = $dao->selectCateAfterHtml($conn, $param, $aft_def_arr);
        $template->reg($prefix . "after", $after["html"]);
        unset($param);

        //8 기본 후공정 내역에 표시할 정보 생성
        $template->reg($prefix . "basic_after", '');
        if (empty($after["info_arr"]["basic"]) === false) {
            $basic_after = $after["info_arr"]["basic"];
            $basic_after = $util->arr2delimStr($basic_after, '|');
            $template->reg($prefix . "basic_after", $basic_after);
        }

        //9 추가 후공정 가격 레이어 생성
        $template->reg($prefix . "after", '');
        $template->reg($prefix . "add_after", '');
        if (empty($after["info_arr"]["add"]) === false) {
            $add_after = $after["info_arr"]["add"];
            $template->reg($prefix . "after", $after["html"]);

            $param["cate_sortcode"] = $sortcode_b;
            $param["dvs"]           = $dvs;

            $add_after_html = '';
            foreach ($add_after as $after_name) {
                $param["size"]       = $price_info_arr["size_name"];
                $param["after_name"] = $after_name;
                $param["mobile_yn"] = $mobile_yn;

                $add_after_html .= $dao->selectCateAddAfterInfoHtml($conn,
                                                                    $param,
                                                                    $aft_def_arr);
                unset($param["affil"]);
                unset($param["subpaper"]);
            }
            unset($param);
            $template->reg($prefix . "add_after", $add_after_html);
        }

        //11 지질느낌 검색
        $paper_sense = $dao->selectPaperDscr($conn, $price_info_arr["paper_mpcode"]);
        $template->reg($prefix . "paper_sense", $paper_sense);

        //12 수량 정보 생성
        $amt_arr =
            PrdtDefaultInfo::AMT[$sortcode_b][$price_info_arr["size_name"]];
        $amt_arr_count = count($amt_arr);

        $default_amt = doubleval($default_sel_arr["amt"]);
        $amt = '';

        for ($i = 0; $i < $amt_arr_count; $i++) {
            $val = $amt_arr[$i];
            $attr = '';

            if (trim($val) === trim($default_amt)) {
                $attr = "selected=\"selected\"";
                $price_info_arr["amt"] = $val;
            }

            $amt .= option($val . ' ' . $amt_unit, $val, $attr);
        }

        if (empty($price_info_arr["amt"])) {
            $price_info_arr["amt"] = $amt_arr[0];
        }

        unset($amt_arr);

        $template->reg($prefix . "amt", $amt);
        $template->reg($prefix . "amt_unit", $amt_unit);

        //13 종이, 출력, 인쇄가격 계산
        $class_path = INC_PATH . 
                      "/common_lib/CalcPriceUtil.inc";

        include_once($class_path);

        $temp = array();
        $temp["cate_sortcode"] = $sortcode_b;
        $temp["amt_unit"]      = $amt_unit;
        $temp["flattyp_yn"]    = 'N';

        $amt = PrdtDefaultInfo::MST_GROUP * $price_info_arr["amt"];
        $temp["amt"]     = $amt;
        $temp["page"]    = 2;
        $temp["pos_num"] = $price_info_arr["pos_num"];
        $temp["affil"]   = $price_info_arr["affil"];

        $print_mpcode = $price_info_arr["print_mpcode"];
        $temp["paper_mpcode"]  = $top_paper_mpcode;
        $temp["cate_output_mpcode"] = $price_info_arr["stan_mpcode"];

        $temp["bef_print_mpcode"]     = $print_mpcode;
        $temp["aft_print_mpcode"]     = null;
        $temp["bef_add_print_mpcode"] = null;
        $temp["aft_add_print_mpcode"] = null;

        $calcUtil = new CalcPriceUtil($temp);

        unset($temp);
        $temp["bef_print_name"] = $print_info_arr["print_name"];
        $temp["aft_print_name"] = null;
        $temp["bef_add_print_name"] = null;
        $temp["aft_add_print_name"] = null;

        $sum_paper_price  = 0;
        $sum_print_price  = 0;
        $sum_output_price = 0;

        //######################### 상지
        // 상지 종이가격
        $top_paper_price = $util->ceilVal($calcUtil->calcPaperPrice($temp));
        // 상지 인쇄가격, 앞뒤 같음이므로 수량, 실제인쇄수량 변경
        $calcUtil->setAmt($amt * $sheet_count);
        $calcUtil->calcRealPaperAmt();
        $top_print_price  = $util->ceilVal($calcUtil->calcSheetPrintPrice());
        // 상지 출력가격
        $top_output_price = $util->ceilVal($calcUtil->calcSheetOutputPrice());
        // 종이수량 원복
        $calcUtil->setAmt($amt);

        $sum_paper_price  += $top_paper_price;
        $sum_print_price  += $top_print_price;
        $sum_output_price += $top_output_price;

        //echo "top : $top_paper_price / $top_print_price / $top_output_price\n";
        //echo "top : $sum_paper_price / $sum_print_price / $sum_output_price\n";

        //######################### 중지
        if ($sortcode_b === "007001002" || $sortcode_b === "007001003") {
            $calcUtil->setCatePaperMpcode($mid_paper_mpcode);
            // 중지 종이가격
            $mid_paper_price =
                $util->ceilVal($calcUtil->calcPaperPrice($temp));
            // 중지 인쇄가격
            /*
            $mid_print_price =
                $util->ceilVal($calcUtil->calcSheetPrintPrice());
            // 중지 출력가격
            $mid_output_price =
                $util->ceilVal($calcUtil->calcSheetOutputPrice());
            */

            $sum_paper_price  += $mid_paper_price;
            /*
            $sum_print_price  += $mid_print_price;
            $sum_output_price += $mid_output_price;
            */

            //echo "mid : $mid_paper_price / $mid_print_price / $mid_output_price\n";
            //echo "mid1 : $sum_paper_price / $sum_print_price / $sum_output_price\n";

            if ($sortcode_b === "007001003") {
                $sum_paper_price  += $mid_paper_price;
                /*
                $sum_print_price  += $mid_print_price;
                $sum_output_price += $mid_output_price;
                */

                //echo "mid2 : $sum_paper_price / $sum_print_price / $sum_output_price\n";
            }
        }
        //######################### 하지
        // 하지 종이가격
        $calcUtil->setCatePaperMpcode($bot_paper_mpcode);
        $bot_paper_price  = $util->ceilVal($calcUtil->calcPaperPrice($temp));
        // 하지 인쇄가격
        /*
        $bot_print_price  = $util->ceilVal($calcUtil->calcSheetPrintPrice());
        // 하지 출력가격
        $bot_output_price = $util->ceilVal($calcUtil->calcSheetOutputPrice());
        */

        $sum_paper_price  += $bot_paper_price;
        /*
        $sum_print_price  += $bot_print_price;
        $sum_output_price += $bot_output_price;
        */

        //echo "bot : $bot_paper_price / $bot_print_price / $bot_output_price\n";
        //echo "bot : $sum_paper_price / $sum_print_price / $sum_output_price\n";

        $sell_price = $sum_paper_price + $sum_print_price + $sum_output_price;

        $template->reg($prefix . "paper_price" , $sum_paper_price);
        $template->reg($prefix . "print_price" , $sum_print_price);
        $template->reg($prefix . "output_price", $sum_output_price);
        $template->reg($prefix . "prdt_price"  , $sell_price);
        //$template->reg($prefix . "sell_price"  , number_format($sell_price));
        unset($param);

        $page = 2;
        $page_dvs = "표지";

        $template->reg($prefix . "page"    , $page);
        $template->reg($prefix . "page_dvs", $page_dvs);

        //13-1 제본 인쇄가격
        $binding_html = '';

        $param["cate_sortcode"] = $sortcode_b;
        $param["after_name"]    = "제본";
        $param["depth1"]        = "떡제본";
        $param["size"]          = $price_info_arr["size_name"];
        $binding_rs = $dao->selectCateAfterInfo($conn, $param);

        while ($binding_rs && !$binding_rs->EOF) {
            $default = '';
            $fields = $binding_rs->fields;
            $depth2 = $fields["depth2"];
            $mpcode = $fields["mpcode"];

            if ($depth2 === "상철") {
                $default = "selected=\"selected\"";
                $binding_mpcode = $binding_rs->fields["mpcode"];
            }

            $binding_html .= option($depth2, $mpcode, $default);

            $binding_rs->MoveNext();
        }
        $template->reg($prefix . "binding_html", $binding_html);

        unset($binding_rs);
        unset($param);

        $param["mpcode"]    = $binding_mpcode;
        $param["amt"]       = $price_info_arr["amt"];

        $binding_price = $dao->selectBindingPrice($conn, $param);
        $binding_price = intval($binding_price);
        $template->reg($prefix . "binding_price", $binding_price);

        //14 회원등급 할인, 회원 할인 정보 생성
        if ($is_login) {
            $param["cate_sortcode"] = $sortcode_b;
            $param["member_seqno"]  = $_SESSION["member_seqno"];
            $member_sale_rate = $dao->selectCateMemberSaleRate($conn, $param);

            $param["paper_mpcode"] = $price_info_arr["paper_mpcode"];
            $param["bef_print_mpcode"] = $price_info_arr["print_mpcode"];
            $param["aft_print_mpcode"] = '0';
            $param["bef_add_print_mpcode"] = '0';
            $param["aft_add_print_mpcode"]  = '0';
            $param["stan_mpcode"] = $price_info_arr["stan_mpcode"];
            $param["amt"] = $price_info_arr["amt"];

            //$rs = $dao->selectAmtMemberCateSale($conn, $param);
            //$amt_member_sale_rate       = $rs["rate"];
            //$amt_member_sale_aplc_price = $rs["aplc_price"];
            $amt_member_sale_rate       = 0;
            $amt_member_sale_aplc_price = 0;

            unset($param);
        } else {
            $dscr = "로그인시 할인받으실 수 있는 금액입니다.";
            $member_sale_rate           = 0;
            $amt_member_sale_rate       = 0;
            $amt_member_sale_aplc_price = 0;

            $sale_price = $sell_price;
        }
        unset($param);

        // 14-1 등급할인, 카테고리 회원할인 적용
        $grade = empty($_SESSION["level"]) ?
                    count($_SESSION["grade_arr"])  : $_SESSION["level"];

        $param["cate_sortcode"] = $sortcode_b;
        $param["grade"]         = $grade;
        $grade_sale_rate = $dao->selectGradeSaleRate($conn, $param);
        $grade_sale = $util->calcPrice($grade_sale_rate, $sell_price);
        $grade_sale = $util->ceilVal($grade_sale);

        $sale_price   = $sell_price + $grade_sale;
        $sale_price   = $util->ceilVal($sale_price);
        $member_sale  = $util->calcPrice($member_sale_rate, $sale_price);
        $member_sale  = $util->ceilVal($member_sale);

        $sale_price  += $member_sale + $binding_price;

        $template->reg($prefix . "sell_price", number_format($sell_price +
                                                             $binding_price));
        $arr = array(
            "dscr"             => $dscr,
            "rate"             => $grade_sale_rate,
            "member_sale_rate" => $member_sale_rate,
            "grade"            => $grade,
            "price"            => $util->ceilVal($grade_sale + $member_sale),
            "mobile_yn"        => $mobile_yn
        );

        $grade_sale_html = makeGradeSaleDl($arr);
        unset($arr);

        $template->reg($prefix . "member_sale_rate", $member_sale_rate);
        $template->reg($prefix . "grade_sale_rate", $grade_sale_rate);

        // 14-2 등급할인이 적용된 가격에 추가적으로 수량별 할인 적용
        if (!empty($amt_member_sale_rate) || !empty($amt_member_sale_aplc_price)) {
            $amt_member_sale_price  = $util->calcPrice($amt_member_sale_rate,
                                                       $sale_price);
            $amt_member_sale_price += $amt_member_sale_aplc_price;
            $amt_member_sale_price  = $util->ceilVal($amt_member_sale_price);
            $sale_price += $amt_member_sale_price;

            $arr["price"] = $amt_member_sale_price;
            $amt_member_sale_html = makeAmtMemberSale($arr);
        }

        $template->reg($prefix . "grade_sale",
                       $grade_sale_html . $amt_member_sale_html);

        //15 이벤트 할인 정보 생성
        $param["dscr"]  = NO_EVENT;
        $template->reg($prefix . "event_sale", makeEventSaleDl($param));
        unset($param);

        //16 결제금액 계산
        $sale_price = $util->ceilVal($sale_price);
        $template->reg($prefix . "sale_price", number_format($sale_price));

        // 공급가 계산
        //$tax = $sale_price - ceil($sale_price / 1.1);
        $tax = $util->ceilVal($sale_price / 11);
        $template->reg($prefix . "tax", number_format($tax));
        // 부가세 계산
        $supply_price = $sale_price - $tax;
        $template->reg($prefix . "supply_price", number_format($supply_price));

        $this->sell_price        = $sell_price;
        $this->grade_sale_rate   = $grade_sale_rate;
        $this->grade_sale_price  = $grade_sale;
        $this->member_sale_rate  = $member_sale_rate;
        $this->member_sale_price = $member_sale;
        $this->sale_price        = $sale_price;
        $this->paper_price       = $sum_paper_price;
        $this->print_price       = $sum_print_price;
        $this->output_price      = $sum_output_price;
        $this->tax               = $tax;
        $this->supply_price      = $supply_price;
        $this->flattyp_yn        = $flattyp_yn;

        //17 견적서 html 생성
        if ($mix_yn === false) {
            $param["esti_paper"]    = $sum_paper_price;
            $param["esti_output"]   = $sum_output_price;
            $param["esti_print"]    = $sum_print_price;
            $param["esti_binding"]  = $binding_price;
            $param["esti_opt"]      = $opt_price;
            $param["esti_sell_price"] = $sell_price + $binding_price;
            $param["esti_sale_price"] = $sale_price;
            $template->reg("quick_esti", getQuickEstimateHtml(
                                             $param,
                                             $util,
                                             ProductInfoClass::AFTER_ARR
                                         ));
        }

        //18 재질미리보기 정보 생성
        $param["name"]  = $price_info_arr["paper_name"];
        $param["dvs"]   = $price_info_arr["paper_dvs"];
        $param["color"] = $price_info_arr["paper_color"];

        $rs = $dao->selectPaperPreviewInfo($conn, $param);
        $rs = $rs->fields;

        $save_file_arr = explode('.', $rs["save_file_name"]);

        $zoom = $rs["file_path"] . DIRECTORY_SEPARATOR . $rs["save_file_name"];
        //$thumb = $rs["file_path"] . DIRECTORY_SEPARATOR .
        //         $save_file_arr[0] . "_400_313." . $save_file_arr[1];

        $template->reg("preview_org", $zoom);
        $template->reg("preview_thumb", $zoom);
        //$template->reg("preview_thumb", $thumb);
    }
}
?>

<?
/*
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016-11-25 엄준현 추가(책자형 관련 로직 수정구현)
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
include_once(INC_PATH . "/com/nexmotion/job/front/product/ProductAdBookDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/BindingPriceUtil.inc");

class BookletInfo extends CommonInfo {
    // 확정형
    var $grade_sale_rate   = null;
    var $grade_sale_price  = null;
    var $member_sale_rate  = null;
    var $member_sale_price = null;
    // 계산형
    var $paper_price       = null;
    var $print_price       = null;
    var $output_price      = null;
    var $binding_price     = null;
    // 공통
    var $tax               = null;
    var $supply_price      = null;
    var $flattyp_yn        = null;
    var $sell_price        = null;
    var $sale_price        = null;
    // 표지부분 처리 후(공통정보 포함) 내지처리시 필요정보
    var $amt         = null;
    var $size_name   = null;
    var $pos_num     = null;
    var $affil       = null;
    var $stan_mpcode = null;
    // 플래그 변수
    var $calc_flag   = null;
    var $common_flag = null;
    var $after_flag  = null;
    var $esti_flag   = null;

    /**
     * @brief 클래스 생성자
     *
     * @detail $flag_arr에 들어가는 flag는 아래와 같다
     * $flag_arr["pos_yn"]      = 사이즈 자리수 노출여부
     * $flag_arr["mix_yn"]      = 책자형 여부
     * $flag_arr["paper_sort_yn"] = 종이분류 노출여부
     * $flag_arr["size_typ_yn"] = 사이즈명 종류 노출여부
     * $flag_arr["calc_flag"]   = 가격계산 진행여부
     * $flag_arr["common_flag"] = 공통정보 진행여부
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
        $this->paper_sort_yn = $flag_arr["paper_sort_yn"];
        $this->size_typ_yn   = $flag_arr["size_typ_yn"];
        $this->calc_flag     = $flag_arr["calc_flag"];
        $this->common_flag   = $flag_arr["common_flag"];
        $this->after_flag    = $flag_arr["after_flag"];
        $this->esti_flag     = $flag_arr["esti_flag"];
        $this->mobile_yn     = $flag_arr["mobile_flag"];
        $this->def_arr       = $def_arr;
    }

    /**
     * @brief 정보를 초기화 하는 함수
     */
    function init() {
        $dao = new ProductAdBookDAO();
        $util = new FrontCommonUtil();

        $price_info_arr = [];
        $param = [];

        $conn          = $this->conn;
        $template      = $this->template;
        $sortcode_b    = $this->cate_sortcode;
        $dvs           = $this->dvs;
        $affil_yn      = $this->affil_yn;
        $pos_yn        = $this->pos_yn;
        $mix_yn        = $this->mix_yn;
        $paper_sort_yn = $this->paper_sort_yn;
        $calc_flag     = $this->calc_flag;
        $size_typ_yn   = $this->size_typ_yn;
        $common_flag   = $this->common_flag;
        $after_flag    = $this->after_flag;
        $esti_flag     = $this->esti_flag;
        $is_login      = empty($_SESSION["id"]) ? false : true;
        $mobile_yn     = $this->mobile_yn;

        $default_sel_arr = $this->def_arr;

        if (empty($default_sel_arr)) {
            $default_sel_arr = ProductDefaultSel::DEFAULT_SEL[$sortcode_b];
        }
        $default_dvs_arr = $default_sel_arr[$dvs];

        $prefix = '';
        if (empty($dvs) === false) {
            $prefix = $dvs . '_';
        }

        if ($common_flag) {
            $price_info_arr["amt"]         = $this->getAmt();
            $price_info_arr["size_name"]   = $this->getSizeName();
            $price_info_arr["pos_num"]     = $this->getPosNum();
            $price_info_arr["affil"]       = $this->getAffil();
            $price_info_arr["stan_mpcode"] = $this->getStanMpcode();
        }

        //-2 제품별 카테고리 정보 생성
        /*
        if ($mix_yn === true) {
            $cate_bot = $dao->selectMixCateHtml($conn, $sortcode_b);
            $template->reg($prefix . "cate_bot", $cate_bot);
        }
        */

        //-1 카테고리 독판여부, 수량단위 검색
        $cate_info_arr = $dao->selectCateInfo($conn, $sortcode_b);
        $mono_dvs   = $cate_info_arr["mono_dvs"];
        $amt_unit   = $cate_info_arr["amt_unit"];
        $tmpt_dvs   = $cate_info_arr["tmpt_dvs"];
        $flattyp_yn = $cate_info_arr["flattyp_yn"];
        $tot_name_arr = explode("|", $cate_info_arr["tot_name"]);

        $template->reg("depth1", $tot_name_arr[0]);
        $template->reg("depth2", $tot_name_arr[1]);
        $template->reg("product_name", $tot_name_arr[2]);
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

        //!! 공통정보 아닐 때 바로 종이정보 검색
        if ($common_flag) goto PAPER;
        //!! 후공정 셀렉트박스 생성
        if ($after_flag) goto AFTER_CHKBOX;

        // 가격 테이블명 검색
        $price_tb = "ply_price";

        //0 종이 정보 생성
PAPER:
        $param["cate_sortcode"] = $sortcode_b;
        if ($paper_sort_yn) {
            $param["default"] = $default_dvs_arr["paper_sort"];
            $paper_sort = $dao->selectCatePaperSortHtml($conn,
                                                        $param,
                                                        $price_info_arr);
            $template->reg($prefix . "paper_sort", $paper_sort);

            $param["sort"] = $price_info_arr["paper_sort"];
        }
        $param["default"] = $default_dvs_arr["paper_name"];
        //0-1 종이 이름 distinct 검색
        $paper_name = $dao->selectCatePaperNameHtml($conn,
                                                    $param,
                                                    $price_info_arr);
        $template->reg($prefix . "paper_name", $paper_name);

        //0-2 종이 html 생성
        $param["name"]       = $price_info_arr["paper_name"];
        $param["default"]    = $default_dvs_arr["paper"];
        $param["paper_flag"] = [
             "name"        => false
            ,"color"       => true
            ,"dvs"         => true
            ,"basisweight" => true
        ];
        $paper = $dao->selectCatePaperHtml($conn, $param, $price_info_arr);
        $template->reg($prefix . "paper", $paper["info"]);
        unset($paper);

        //!! 공통정보 아닐 때 바로 인쇄도수 검색
        if ($common_flag) goto TMPT;

        //1 종이에 물린 사이즈 검색
        $param["table_name"]    = $price_tb;
        $param["paper_mpcode"]  = $price_info_arr["paper_mpcode"];
        $stan_mpcode_rs = $dao->selectCateStanMpcodeByPrice($conn, $param);

        $stan_mpcode_arr = [];
        while ($stan_mpcode_rs && !$stan_mpcode_rs->EOF) {
            $stan_mpcode_arr[] = $stan_mpcode_rs->fields["cate_stan_mpcode"];

            $stan_mpcode_rs->MoveNext();
        }
        unset($stan_mpcode_rs);
        unset($param);

        //2 사이즈 정보 생성
        $param["cate_sortcode"] = $sortcode_b;
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
TMPT:
        $param["affil"]         = $price_info_arr["affil"];
        $param["default_print"] = $default_dvs_arr["print"];
        $param["default_purp"]  = $default_dvs_arr["print_purp"];

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

        // 제본 depth1 정보 생성
        $param["default"] = $default_sel_arr["binding_d1"];
        $param["cate_sortcode"] = $sortcode_b;
        if (!$esti_flag) {
            $param["size"]    = $price_info_arr["size_name"];
        }

        $binding_depth1 = $dao->selectBindingHtml($conn,
                                                  "depth1",
                                                  $param,
                                                  $price_info_arr);

        //!! 공통정보 아닐 때 바로 후공정 체크박스 생성
        if ($common_flag) goto AFTER_CHKBOX;

        $template->reg($prefix . "binding_depth1", $binding_depth1);

        // 제본 depth2 정보 생성
        $param["default"]  = $default_sel_arr["binding_d2"];
        $param["depth1"]   = $price_info_arr["binding_depth1"];

        $binding_depth2 = $dao->selectBindingHtml($conn,
                                                  "depth2",
                                                  $param,
                                                  $price_info_arr);
        unset($param);
        $template->reg($prefix . "binding_depth2", $binding_depth2);

        if ($mix_yn === false) {
            //5 옵션 체크박스 생성
            $opt_def_arr = $default_sel_arr["opt"];

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
                $add_opt = $dao->selectCateAddOptInfoHtml($conn,
                                                          $param,
                                                          $opt_def_arr);
                unset($param);
                $template->reg($prefix . "add_opt", $add_opt);
            }
        }

AFTER_CHKBOX:
        //7 후공정 체크박스 생성
        $aft_def_arr = $default_dvs_arr["after"];

        $param["cate_sortcode"] = $sortcode_b;
        $param["dvs"]           = $dvs;
        $except_arr = ["제본" => true];

        $after = $dao->selectCateAfterHtml($conn, $param,
                                           $aft_def_arr, $except_arr);
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
        if (empty($after["info_arr"]["add"]) === false && $prefix == "cover_") {
            $add_after = $after["info_arr"]["add"];
            $template->reg($prefix . "after", $after["html"]);

            $param["cate_sortcode"] = $sortcode_b;
            $param["dvs"]           = $dvs;

            $add_after_html = '';
            foreach ($add_after as $after_name) {
                $param["size"]       = $price_info_arr["size_name"];
                $param["after_name"] = $after_name;
                $param["mobile_yn"]  = $mobile_yn;

                $add_after_html .= $dao->selectCateAddAfterInfoHtml($conn,
                                                                    $param,
                                                                    $aft_def_arr);
                unset($param["affil"]);
                unset($param["subpaper"]);
            }
            unset($param);
            $template->reg($prefix . "add_after", $add_after_html);
        }

        //!! 후공정 정보 생성 후 탈출
        if ($after_flag) goto OUT;

        //11 지질느낌 검색
        $paper_sense = $dao->selectPaperDscr($conn, $price_info_arr["paper_mpcode"]);
        $template->reg($prefix . "paper_sense", $paper_sense);

        //!! 공통정보 아닐 때 바로 페이지 검색
        if ($common_flag) goto PAGE;

        //12 수량 정보 생성
        $amt_arr = PrdtDefaultInfo::AMT[$sortcode_b];
        $amt_arr_count = count($amt_arr);

        $default_amt = $default_sel_arr["amt"];

        $amt = '';

        for ($i = 0; $i < $amt_arr_count; $i++) {
            $val = $amt_arr[$i];
            $attr = '';

            if ($val === $default_amt) {
                $attr = "selected=\"selected\"";
                $price_info_arr["amt"] = $val;
            }

            $amt .= option(number_format($val) . ' ' . $amt_unit, $val, $attr);
        }

        if (empty($price_info_arr["amt"])) {
            $price_info_arr["amt"] = $amt_arr[0];
        }

        unset($amt_arr);

        $template->reg($prefix . "amt", $amt);
        $template->reg($prefix . "amt_unit", $amt_unit);

        // 12-1 페이지 정보 생성
PAGE:
        $page_dvs = '';
        if (strpos($dvs, "cover") > -1) {
            $page_dvs = "표지";
        } else if (strpos($dvs, "inner") > -1) {
            $page_dvs = "내지";
        }

        $page_arr = PrdtDefaultInfo::PAGE_INFO[$sortcode_b][$page_dvs];
        $page_arr_count = count($page_arr);

        $page = "";
        $page_default = $default_dvs_arr["page"];

        if (empty($page_default)) {
            $page_default = true;
        }

        for ($i = 0; $i < $page_arr_count; $i++) {
            $val = $page_arr[$i];
            $page_val = number_format(doubleval($val)) . 'p';
            $attr = "";

            if ($page_default === true || $val === $page_default) {
                $attr = "selected=\"selected\"";
                $price_info_arr["page"][$page_dvs]        = $val;
                $price_info_arr["page_detail"][$page_dvs] = '';

                $page_default = false;
            }
            $page .= option($page_val, $val, $attr);
        }
        $template->reg($prefix . "page", $page);

        if (!$esti_flag && $calc_flag) {
            $class_path = INC_PATH . 
                          "/common_lib/CalcPriceUtil.inc";

            include_once($class_path);

            $temp = [];
            $temp["cate_sortcode"] = $sortcode_b;
            $temp["amt_unit"]      = $amt_unit;
            $temp["flattyp_yn"]    = $flattyp_yn;

            $temp["amt"]     = $price_info_arr["amt"];
            $temp["page"]    = $price_info_arr["page"][$page_dvs];
            $temp["pos_num"] = $price_info_arr["pos_num"];
            $temp["affil"]   = $price_info_arr["affil"];

            $print_mpcode = $price_info_arr["print_mpcode"];

            $temp["cate_paper_mpcode"]  = $price_info_arr["paper_mpcode"];
            $temp["cate_output_mpcode"] = $price_info_arr["stan_mpcode"];

            if (is_array($print_mpcode)) {
                $temp["bef_print_mpcode"]     =
                    $print_mpcode["전면"]["mpcode"];
                $temp["aft_print_mpcode"]     =
                    $print_mpcode["후면"]["mpcode"];
                $temp["bef_add_print_mpcode"] =
                    $print_mpcode["전면추가"]["mpcode"];
                $temp["aft_add_print_mpcode"] =
                    $print_mpcode["후면추가"]["mpcode"];
            } else {
                $temp["bef_print_mpcode"]     = $print_mpcode;
                $temp["aft_print_mpcode"]     = null;
                $temp["bef_add_print_mpcode"] = null;
                $temp["aft_add_print_mpcode"] = null;
            }

            $calcUtil = new CalcPriceUtil($temp);

            unset($temp);
            if (is_array($print_mpcode)) {
                $temp["bef_print_name"] = $print_mpcode["전면"]["name"];
                $temp["aft_print_name"] = $print_mpcode["후면"]["name"];
                $temp["bef_add_print_name"] = $print_mpcode["전면추가"]["name"];
                $temp["aft_add_print_name"] = $print_mpcode["후면추가"]["name"];
            } else {
                $temp["bef_print_name"] = $print_info_arr["print_name"];
                $temp["aft_print_name"] = null;
                $temp["bef_add_print_name"] = null;
                $temp["aft_add_print_name"] = null;
            }

            $temp["amt_paper_sale_flag"] = false;

            $paper_price  = $util->ceilVal($calcUtil->calcPaperPrice($temp));
            $print_price  = $util->ceilVal($calcUtil->calcBookletPrintPrice());
            $output_price = $util->ceilVal($calcUtil->calcBookletOutputPrice());
            $sell_price   = $paper_price + $print_price + $output_price;

            $template->reg($prefix . "paper_price" , $paper_price);
            $template->reg($prefix . "print_price" , $print_price);
            $template->reg($prefix . "output_price", $output_price);
            $template->reg($prefix . "org_sell_price"  , $sell_price);
        }

        unset($param);
        $template->reg($prefix . "page"    , $page);
        $template->reg($prefix . "page_dvs", $page_dvs);

        if (!$esti_flag) {
            //19-1 제본가격 검색용 종이수량 공통 정보 생성
            $param["mpcode"] = $price_info_arr["paper_mpcode"];
            $param["col"]    = "crtr_unit";
            $param["affil"]  = $price_info_arr["affil"];
            $paper_crtr_unit =
                $dao->selectPrdtPaperInfo($conn, $param)["crtr_unit"];
            unset($param);

            //19-2 제본가격 검색용 종이수량 계산
            $param["amt"]     = $price_info_arr["amt"];
            $param["pos_num"] = $price_info_arr["pos_num"];
            $param["amt_unit"]  = $amt_unit;
            $param["page_num"]  = $price_info_arr["page"][$page_dvs];
            $param["crtr_unit"] = $paper_crtr_unit;

            $real_paper_amt = $util->getPaperRealPrintAmt($param);
            unset($param);

            //20 제본 가격 계산
            $param["mpcode"]    = $price_info_arr["binding_mpcode"];
            $param["amt"]       = $price_info_arr["amt"];

            $binding_price = $dao->selectBindingPrice($conn, $param);
            $binding_price = intval($binding_price);
            unset($param);

            $param["cate_sortcode"] = $sortcode_b;
            $param["amt"]           = $price_info_arr["amt"];
            $param["page"]          = $price_info_arr["page"][$page_dvs];
            $param["price"]         = $binding_price;
            $param["coating_yn"]    = false;
            $param["depth1"]        = $price_info_arr["binding_depth1"];
            $param["stan_name"]     = $price_info_arr["size_name"];
            $param["pos_num"]       = $price_info_arr["pos_num"];

            $bindingPriceUtil = new BindingPriceUtil($param);
            $binding_price = $bindingPriceUtil->calcBindingPrice();

            $template->reg($prefix . "binding_price", $binding_price);
            unset($param);

            // 제본가격 판매가격에 합산
            $sell_price += $binding_price;
        }
        $template->reg($prefix . "sell_price", $sell_price);

        //14 회원등급 할인, 회원 할인 정보 생성
        if ($is_login) {
            $param["cate_sortcode"] = $sortcode_b;
            $param["member_seqno"]  = $_SESSION["member_seqno"];
            $member_sale_rate = $dao->selectCateMemberSaleRate($conn, $param);
            unset($param);
        } else {
            $dscr = "로그인시 할인받으실 수 있는 금액입니다.";
            $grade_sale_rate  = 0;
            $grade_sale       = 0;
            $member_sale_rate = 0;
            $member_sale      = 0;

            $sale_price = $sell_price;
        }
        unset($param);

        $grade = empty($_SESSION["level"]) ?
                    count($_SESSION["grade_arr"]) : $_SESSION["level"];

        $param["cate_sortcode"] = $sortcode_b;
        $param["grade"]         = $grade;
        $grade_sale_rate = $dao->selectGradeSaleRate($conn, $param);
        $grade_sale = $util->calcPrice($grade_sale_rate,
                                       $sell_price - $binding_price);
        $grade_sale = $util->ceilVal($grade_sale);

        $sale_price   = $sell_price + $grade_sale;
        $sale_price   = $util->ceilVal($sale_price);
        $member_sale  = $util->calcPrice($member_sale_rate, $sale_price);
        $member_sale  = $util->ceilVal($member_sale);

        $sale_price  += $member_sale;

        //!! 공통정보 아닐 때 정보 필드에 입력
        if ($common_flag) goto TAX;

        $arr = array(
            "dscr"             => $dscr,
            "rate"             => $grade_sale_rate,
            "member_sale_rate" => $member_sale_rate,
            "grade"            => $grade,
            "price"            => $util->ceilVal($grade_sale + $member_sale)
        );

        $grade_sale_html = makeGradeSaleDl($arr);
        unset($arr);

        $template->reg($prefix . "member_sale_rate", $member_sale_rate);
        $template->reg($prefix . "grade_sale"     , $grade_sale_html);
        $template->reg($prefix . "grade_sale_rate", $grade_sale_rate);

        //15 이벤트 할인 정보 생성
        $param["dscr"]  = NO_EVENT;
        $template->reg($prefix . "event_sale", makeEventSaleDl($param));
        unset($param);

        //16 결제금액 계산
        $sale_price = $util->ceilVal($sale_price);
        $template->reg($prefix . "sale_price", number_format($sale_price));

TAX:
        // 공급가, 부가세 계산
        // 반올림 때문에 10원씩 오차생기는 것 때문에 수정
        $paper   = $util->ceilVal($paper_price / 1.1);
        $output  = $util->ceilVal($output_price / 1.1);
        $print   = $util->ceilVal($print_price / 1.1);
        $binding = $util->ceilVal($binding_price / 1.1);

        $supply_price = $paper + $output + $print + $binding;
        $tax = $util->ceilVal($supply_price / 10);

        $temp = $sale_price - $tax;

        if ($supply_price !== $temp) {
            $tax -=
                ($supply_price < $temp) ? $temp - $supply_price : $supply_price - $temp;
        }

        if ($common_flag) goto REGI;

        $template->reg($prefix . "tax", number_format($tax));
        $template->reg($prefix . "supply_price", number_format($supply_price));

REGI:
        $this->sell_price        = $sell_price;
        $this->grade_sale_rate   = $grade_sale_rate;
        $this->grade_sale_price  = $grade_sale;
        $this->member_sale_rate  = $member_sale_rate;
        $this->member_sale_price = $member_sale;
        $this->sale_price        = $sale_price;
        $this->paper_price       = $paper_price;
        $this->print_price       = $print_price;
        $this->output_price      = $output_price;
        $this->binding_price     = $binding_price;
        $this->tax               = $tax;
        $this->supply_price      = $supply_price;
        $this->flattyp_yn        = $flattyp_yn;

        if (!$common_flag) {
            $this->amt         = $price_info_arr["amt"];
            $this->size_name   = $price_info_arr["size_name"];
            $this->pos_num     = $price_info_arr["pos_num"];
            $this->affil       = $price_info_arr["affil"];
            $this->stan_mpcode = $price_info_arr["stan_mpcode"];
        }

        //17 견적서 html 생성
        if ($mix_yn === false) {
            $param["esti_paper"]    = $paper_price;
            $param["esti_output"]   = $output_price;
            $param["esti_print"]    = $print_price;
            $param["esti_binding"]  = $binding_price;
            $param["esti_opt"]      = $opt_price;
            $param["esti_sell_price"] = $sell_price;
            $param["esti_sale_price"] = $sale_price;
            $template->reg("quick_esti", getQuickEstimateHtml(
                                             $param,
                                             $util,
                                             ProductInfoClass::AFTER_ARR
                                         ));
        }

        //18 재질미리보기 정보 생성
        if (!$common_flag) {
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
OUT:
    }

    /**************************************************************************
     ** 가격관련 getter
     **************************************************************************/
    function getPaperPrice() {
        return $this->paper_price;
    }
    function getOutputPrice() {
        return $this->output_price;
    }
    function getPrintPrice() {
        return $this->print_price;
    }
    function getBindingPrice() {
        return $this->binding_price;
    }
    function getTax() {
        return $this->tax;
    }
    function getSupplyPrice() {
        return $this->supply_price;
    }
    function getSellPrice() {
        return $this->sell_price;
    }
    function getSalePrice() {
        return $this->sale_price;
    }
    function getGradeSalePrice() {
        return $this->grade_sale_price;
    }
    function getMemberSalePrice() {
        return $this->member_sale_price;
    }
    function getGradeSaleRate() {
        return $this->grade_sale_rate;
    }
    function getMemberSaleRate() {
        return $this->member_sale_rate;
    }

    /**************************************************************************
     ** 추가정보 getter/setter
     **************************************************************************/

    function getAmt() {
        return $this->amt;
    }
    function getSizeName() {
        return $this->size_name;
    }
    function getPosNum() {
        return $this->pos_num;
    }
    function getAffil() {
        return $this->affil;
    }
    function getStanMpcode() {
        return $this->stan_mpcode;
    }

    function setAmt($amt) {
        $this->amt = $amt;
    }
    function setSizeName($size_name) {
        $this->size_name = $size_name;
    }
    function setPosNum($pos_num) {
        $this->pos_num = $pos_num;
    }
    function setAffil($affil) {
        $this->affil = $affil;
    }
    function setStanMpcode($stan_mpcode) {
        $this->stan_mpcode = $stan_mpcode;
    }
}
?>

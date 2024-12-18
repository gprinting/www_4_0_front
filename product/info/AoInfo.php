<?php
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2017-11-08 엄준현 생성
 *============================================================================
 *
 */
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/product/info/CommonInfo.php");
include_once(INC_PATH . "/common_define/prdt_default_info.inc");
include_once(INC_PATH . "/com/nexmotion/html/front/product/QuickEstimate.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/product/ProductAoDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/ActualPriceUtil.inc");

class AoInfo extends CommonInfo {

    function __construct(&$conn, &$template,
                         $cate_sortcode, $dvs, $flag_arr, $def_arr = []) {
        $this->conn          = $conn;
        $this->template      = $template;
        $this->cate_sortcode = $cate_sortcode;
        $this->dvs           = $dvs;
        $this->mobile_yn     = $flag_arr["mobile_flag"];
        $this->def_arr       = $def_arr;

        $this->init();
    }

    public function init() {
        $dao = new ProductAoDAO();
        $util = new FrontCommonUtil();
        $actualUtil = new ActualPriceUtil();

        $conn          = $this->conn;
        $template      = $this->template;
        $sortcode_b    = $this->cate_sortcode;
        $dvs           = $this->dvs;
        $is_login      = empty($_SESSION["id"]) ? false : true;
        $mobile_yn     = $this->mobile_yn;

        $default_sel_arr = $this->def_arr;

        if (empty($default_sel_arr)) {
            $default_sel_arr = ProductDefaultSel::DEFAULT_SEL[$sortcode_b];
        }

        $price_info_arr = [];
        $param = [];

        $prefix = '';
        if (empty($dvs) === false) {
            $prefix = $dvs . '_';
        }

        //-1 카테고리 독판여부, 수량단위 검색
        $cate_info_arr = $dao->selectCateInfo($conn, $sortcode_b);
        $mono_dvs   = $cate_info_arr["mono_dvs"];
        $amt_unit   = $cate_info_arr["amt_unit"];
        $tmpt_dvs   = $cate_info_arr["tmpt_dvs"];
        $flattyp_yn = $cate_info_arr["flattyp_yn"];
        $tot_name_arr = explode("|", $cate_info_arr["tot_name"]);
        unset($cate_info_arr);

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

        //1 용지 검색
        unset($param);
        $param["cate_sortcode"] = $sortcode_b;
        $param["default"] = $default_sel_arr["paper"];
        $paper = $dao->selectCatePaperHtml($conn, $param, $price_info_arr);
        $template->reg($prefix . "paper", $paper["info"]);

        //2 인쇄도수 검색
        unset($param);
        $param["cate_sortcode"] = $sortcode_b;
        $param["default_print"] = $default_sel_arr["print"];
        $param["default_purp"]  = $default_sel_arr["print_purp"];

        $print_tmpt = $dao->selectCatePrintTmptHtml($conn,
                                                    $param,
                                                    $price_info_arr);
        $tmpt = $print_tmpt["단면"] . $print_tmpt["양면"];
        $template->reg($prefix . "print_tmpt", $tmpt);

        //3 사이즈 검색
        unset($param);
        $param["cate_sortcode"] = $sortcode_b;
        $param["cate_mpcode"]   = $dao->arr2paramStr($conn, $stan_mpcode_arr);
        $param["def_arr"] = $default_sel_arr;
        $size = $dao->selectCateSizeHtml($conn,
                                         $param,
                                         $price_info_arr,
                                         false, false, false);

        $template->reg($prefix . "def_stan_mpcode", $price_info_arr["stan_mpcode"]);
        if ($sortcode_b === "002005003") {
            // 현수막이면 회원할인 검색용 더미사이즈 검색
            // 나중에 실제 장바구니에 들어갈 때는 -1로 들어감
        } else {
            // 현수막이 아닐 경우 생성한 항목 템플릿에 등록
            $template->reg($prefix . "size", $size);
            $template->reg($prefix . "def_cut_wid"    , $price_info_arr["def_cut_wid"]);
            $template->reg($prefix . "def_cut_vert"   , $price_info_arr["def_cut_vert"]);
        }

        //4 수량 검색
        unset($param);
        $param["table_name"]    = "ply_price";
        $param["cate_sortcode"] = $sortcode_b;
        $param["paper_mpcode"]  = $price_info_arr["paper_mpcode"];
        $param["stan_mpcode"]   = $price_info_arr["stan_mpcode"];
        $param["amt_unit"]      = $amt_unit;
        $param["def_amt"]       = doubleval($default_sel_arr["amt"]);
        $amt = $dao->selectCateAmtHtml($conn, $param, $price_info_arr);

        $template->reg($prefix . "amt", $amt);
        $template->reg($prefix . "amt_unit", $amt_unit);


        //5 옵션(추가물품) 검색
        $opt_def_arr = $default_sel_arr["opt"];

        $param["cate_sortcode"] = $sortcode_b;
        $param["dvs"]           = $dvs;
        $opt = $dao->selectCateAoOptHtml($conn, $param, $opt_def_arr);
        $template->reg($prefix . "opt", $opt["html"]);

        //6 옵션(추가물품) 가격 레이어 생성
        if (!empty($opt["info_arr"])) {
            $add_opt = $opt["info_arr"]["name"];
            $add_opt = $dao->parameterArrayEscape($conn, $add_opt);
            $add_opt = $util->arr2delimStr($add_opt);

            $param["opt_name"]  = $add_opt;
            $param["opt_idx"]   = $opt["info_arr"]["idx"];
            $param["mobile_yn"] = $mobile_yn;
            $add_opt = $dao->selectCateAddAoOptInfoHtml($conn,
                                                        $param,
                                                        $opt_def_arr);
            unset($param);
            $template->reg($prefix . "add_opt", $add_opt);
        }

        //7 후공정 체크박스 생성
        $aft_def_arr = $default_sel_arr["after"];

        unset($param);
        $param["cate_sortcode"] = $sortcode_b;
        $param["dvs"]           = $dvs;

        $after = $dao->selectCateAoAfterHtml($conn, $param, $aft_def_arr);
        $template->reg($prefix . "after", $after["html"]);

        //8 기본 후공정 내역에 표시할 정보 생성
        $basic_after = $after["info_arr"]["basic"];
        $basic_after_html = '';
        if (!empty($basic_after)) {
            $param["basic_yn"] = 'Y';

            foreach ($basic_after as $after_name) {
                $param["after_name"] = $after_name;

                // 단가검색 후 가격 계산해서 입력
                $rs = $dao->selectCateAoAfter($conn, $param);
                $basic_after_html .= makeCateAoBasicAfter($rs, $dvs, $mobile_yn);

                if ($mobile_yn) {
                    $basic_after_html .= "<hr class=\"after_section\">";
                }
            }

            $template->reg($prefix . "basic_after", $basic_after_html);
        }

        //9 추가 후공정 가격 레이어 생성
        $template->reg($prefix . "add_after", '');
        if (empty($after["info_arr"]["add"]) === false) {
            unset($param);
            $add_after = $after["info_arr"]["add"];
            $template->reg($prefix . "after", $after["html"]);

            $param["cate_sortcode"] = $sortcode_b;
            $param["dvs"]           = $dvs;

            $add_after_html = '';
            foreach ($add_after as $after_name) {
                $param["after_name"] = $after_name;
                $param["mobile_yn"]  = $mobile_yn;

                $add_after_html .= $dao->selectCateAddAoAfterInfoHtml($conn,
                                                                      $param);
            }
            $template->reg($prefix . "add_after", $add_after_html);
        }

        //10 기준가격(정상판매가) 검색, 부가세 계산
        $sell_price   = 0;
        $paper_price  = 0;
        $print_price  = 0;
        $output_price = 0;
        $tax          = 0;

        if ($sortcode_b !== "002005003") {
            // 현수막 아닐경우 디비에서 검색
            unset($param);
            $param["table_name"]           = $price_tb;
            $param["cate_sortcode"]        = $sortcode_b;
            $param["paper_mpcode"]         = $price_info_arr["paper_mpcode"];
            $param["bef_print_mpcode"]     = $price_info_arr["print_mpcode"];
            $param["bef_add_print_mpcode"] = '0';
            $param["aft_print_mpcode"]     = '0';
            $param["aft_add_print_mpcode"] = '0';
            $param["stan_mpcode"]          = $price_info_arr["stan_mpcode"];
            $param["amt"]                  = $price_info_arr["amt"];

            $price_rs = $dao->selectPrdtPlyPrice($conn, $param);

            $page = $price_rs["page"];
            $page_dvs = $price_rs["page_dvs"];

            if (empty($page)) {
                $page = 2;
            }

            if (empty($page_dvs)) {
                $page_dvs = "표지";
            }

            $sell_price  = doubleval($price_rs["new_price"]);
            $sell_price  = $util->ceilVal($sell_price);

            $print_price  = $sell_price;

        } else {
            // 현수막일 경우 헤베당 단가로 계산
            $wid = defined("PLACARD_DEF_WID") ? PLACARD_DEF_WID : 5000;
            $vert = defined("PLACARD_DEF_VERT") ? PLACARD_DEF_VERT : 900;

            $sell_price  = $actualUtil->calcPrice($wid, $vert);
            $sell_price  = $util->ceilVal($sell_price);

            $print_price = $sell_price;

            $page = 2;
            $page_dvs = "표지";
        }

        $template->reg($prefix . "prdt_price"  , $sell_price);
        $template->reg($prefix . "sell_price"  , number_format($sell_price));

        $template->reg($prefix . "page"    , $page);
        $template->reg($prefix . "page_dvs", $page_dvs);

        //11 할인정보 생성
        unset($param);
        if ($is_login) {
            // 로그인한 상태라면 카테고리 회원 할인과 회원 수량별 할인정보 가져옴
            $param["cate_sortcode"] = $sortcode_b;
            $param["member_seqno"]  = $_SESSION["org_member_seqno"];
            $member_sale_rate = $dao->selectCateMemberSaleRate($conn, $param);

            $param["paper_mpcode"] = $price_info_arr["paper_mpcode"];
            $param["bef_print_mpcode"] = $price_info_arr["print_mpcode"];
            $param["aft_print_mpcode"] = '0';
            $param["bef_add_print_mpcode"] = '0';
            $param["aft_add_print_mpcode"]  = '0';
            $param["stan_mpcode"] = $price_info_arr["stan_mpcode"];
            $param["amt"] = $price_info_arr["amt"];

            if (is_array($print_mpcode)) {
                $temp["bef_print_name"] = $print_mpcode["전면"]["name"];
                $temp["aft_print_name"] = $print_mpcode["후면"]["name"];
                $temp["bef_add_print_name"] = $print_mpcode["전면추가"]["name"];
                $temp["aft_add_print_name"] = $print_mpcode["후면추가"]["name"];
            } else {
                $temp["bef_print_name"] = $print_info_arr["print_name"];
                $temp["aft_print_name"] = '0';
                $temp["bef_add_print_name"] = '0';
                $temp["aft_add_print_name"] = '0';
            }

            $rs = $dao->selectAmtMemberCateSale($conn, $param);
            $amt_member_sale_rate       = doubleval($rs["rate"]);
            $amt_member_sale_aplc_price = doubleval($rs["aplc_price"]);

            $template->reg($prefix . "amt_sale_rate", $amt_member_sale_rate);
            $template->reg($prefix . "amt_sale_aplc", $amt_member_sale_aplc_price);

            unset($param);
        } else {
            $dscr = "로그인시 할인받으실 수 있는 금액입니다.";
            $member_sale_rate           = 0;
            $amt_member_sale_rate       = 0;
            $amt_member_sale_aplc_price = 0;

            $sale_price = $sell_price;
        }

        //12 등급할인, 카테고리 회원할인
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

        $sale_price  += $member_sale;

        $arr = [
            "dscr"             => $dscr,
            "rate"             => $grade_sale_rate,
            "member_sale_rate" => $member_sale_rate,
            "grade"            => $grade,
            "price"            => $util->ceilVal($grade_sale + $member_sale),
            "mobile_yn"        => $mobile_yn
        ];

        $grade_sale_html = makeGradeSaleDl($arr);
        unset($arr);

        $template->reg($prefix . "member_sale_rate", $member_sale_rate);
        $template->reg($prefix . "grade_sale_rate", $grade_sale_rate);

        //13 등급할인이 적용된 가격에 추가적으로 수량별 할인 적용
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

        //14 결제금액
        $template->reg($prefix . "sale_price", number_format($sale_price));
        //15 부가세
        $tax = $util->ceilVal($sale_price / 11);
        $template->reg($prefix . "tax", number_format($tax));
        //16 공급가
        $supply_price = $sale_price - $tax;
        $template->reg($prefix . "supply_price", number_format($supply_price));

        //17 견적서 html 생성
        unset($param);
        $param["esti_paper"]  = $paper_price;
        $param["esti_output"] = $output_price;
        $param["esti_print"]  = $print_price;
        $param["esti_opt"]    = $opt_price;
        $param["esti_sell_price"] = $sell_price;
        $param["esti_sale_price"] = $sale_price;
        $template->reg("quick_esti", getQuickEstimateHtml(
                                         $param,
                                         $util,
                                         ProductInfoClass::AFTER_ARR
                                     ));

        //18 재질미리보기 정보 생성
        unset($param);
        $param["name"]  = $price_info_arr["paper_name"];
        $param["dvs"]   = $price_info_arr["paper_dvs"];
        $param["color"] = $price_info_arr["paper_color"];

        $rs = $dao->selectPaperPreviewInfo($conn, $param);
        $rs = $rs->fields;

        $save_file_arr = explode('.', $rs["save_file_name"]);

        $zoom = $rs["file_path"] . DIRECTORY_SEPARATOR . $rs["save_file_name"];

        $template->reg("preview_org", $zoom);
        $template->reg("preview_thumb", $zoom);
    }
}

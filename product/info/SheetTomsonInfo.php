<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/product/info/CommonInfo.php");
include_once(INC_PATH . "/define/front/product_info_class.inc");
include_once(INC_PATH . "/define/front/common_config.inc");
include_once(INC_PATH . "/define/front/message.inc");
include_once(INC_PATH . "/common_define/prdt_default_info.inc");
include_once(INC_PATH . "/com/nexmotion/html/front/product/QuickEstimate.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/product/ProductSheetTomsonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");

class SheetTomsonInfo extends CommonInfo {
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
        $dao = new ProductSheetTomsonDAO();
        $util = new FrontCommonUtil();

        $price_info_arr = [];
        $param = [];

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
            $cate_bot = $dao->selectMixCateHtml($conn, $sortcode_b, $sortcode_m);
            $template->reg($prefix . "cate_bot", $cate_bot); 
        }

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

        // 가격 테이블명 검색
        $price_tb = "ply_price";

        //0 종이 정보 생성(##사이즈의 계열에 따라서 처리)
        $param["cate_sortcode"] = $sortcode_b;
        $param["default"] = $default_sel_arr["paper"];
        $paper = $dao->selectCatePaperHtml($conn, $param, $price_info_arr);
        $template->reg($prefix . "paper", $paper["info"]); 

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
        $param["typ"]           = $default_sel_arr["size_typ"];
        $param["cate_mpcode"]   = $dao->arr2paramStr($conn, $stan_mpcode_arr);
        $param["def_arr"] = $default_sel_arr;


        $order_size = "";
        if($default_sel_arr != null) {
            $template->reg("order_cut_wid", $default_sel_arr["cut_size_wid"]);
            $template->reg("order_cut_vert", $default_sel_arr["cut_size_vert"]);
            $template->reg("order_count", $default_sel_arr["order_count"]);
            $template->reg("order_size_name", $default_sel_arr["size"]);
            $order_size = $default_sel_arr["size"];
        } else {
            $template->reg("order_cut_wid", 50);
            $template->reg("order_cut_vert", 50);
        }

        $size_typ = $dao->selectTomsonSizeTyp($conn,
                                              $sortcode_b,
                                              $price_info_arr,
                                              $affil_yn,
                                              $size_typ_yn,
                                              $order_size);

        $template->reg($prefix . "size", $size_typ); 
        $template->reg($prefix . "def_stan_mpcode", $price_info_arr["stan_mpcode"]); 
        $template->reg($prefix . "def_cut_wid"    , $price_info_arr["def_cut_wid"]); 
        $template->reg($prefix . "def_cut_vert"   , $price_info_arr["def_cut_vert"]); 


        //3 재단, 작업사이즈간 차이 정보 생성
        $size_gap = " _gap%s";
        $val = 6;
        if (empty($val)) {
            $val = $price_info_arr["size_gap"];
        }
        $template->reg($prefix . "size_gap", sprintf($size_gap, $val));

        //4 인쇄도수 정보 생성
        if ($mono_dvs === 1) {
            $param["affil"] = $price_info_arr["affil"];
        }

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
            $template->reg($prefix . "aft_print_tmpt", $print_tmpt["전면추가"]); 
            $template->reg($prefix . "bef_add_print_tmpt", $print_tmpt["후면"]); 
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

                $param["cate_sortcode"] = $sortcode_b;
                $param["opt_name"]      = $add_opt;
                $param["opt_idx"]       = $opt["info_arr"]["idx"];
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

        //11 지질느낌 검색
        $paper_sense = $dao->selectPaperDscr($conn, $price_info_arr["paper_mpcode"]);
        $template->reg($prefix . "paper_sense", $paper_sense); 

        //12 수량 정보 생성
        $param["table_name"]    = $price_tb;
        $param["cate_sortcode"] = $sortcode_b;
        $param["paper_mpcode"]  = $price_info_arr["paper_mpcode"];
        $param["amt_unit"]      = $amt_unit;
        $param["def_amt"]       = doubleval($default_sel_arr["amt"]);
        $amt = $dao->selectCateAmtHtml($conn, $param, $price_info_arr);
        unset($param);
        $template->reg($prefix . "amt", $amt); 
        $template->reg($prefix . "amt_unit", $amt_unit); 

        //13 기준가격(정상판매가) 검색, 부가세 계산
        $param["table_name"]           = $price_tb;
        $param["cate_sortcode"]        = $sortcode_b;
        $param["paper_mpcode"]         = $price_info_arr["paper_mpcode"];
        $param["bef_print_mpcode"]     = $price_info_arr["print_mpcode"];
        $param["bef_add_print_mpcode"] = '0';
        $param["aft_print_mpcode"]     = '0';
        $param["aft_add_print_mpcode"] = '0';
        $param["stan_mpcode"]          = $price_info_arr["stan_mpcode"];
        $param["amt"]                  = $price_info_arr["amt"];

        $sell_price   = 0;
        $paper_price  = 0;
        $print_price  = 0;
        $output_price = 0;
        $tax          = 0;

        $page = 2;
        $page_dvs = "표지";

        if ($mono_dvs === 0) {
            $price_rs = $dao->selectPrdtPlyPrice($conn, $param);

            $page = $price_rs["page"];
            $page_dvs = $price_rs["page_dvs"];
            
            $sell_price  = doubleval($price_rs["new_price"]);
            $sell_price  = $util->ceilVal($sell_price);

            $print_price = $sell_price;
        } else {
            // 계산형 들어가면 그 때 구현
        }

        unset($param);
        $template->reg($prefix . "paper_price" , $paper_price); 
        $template->reg($prefix . "print_price" , $print_price); 
        $template->reg($prefix . "output_price", $output_price); 
        $template->reg($prefix . "prdt_price"  , $sell_price);
        $template->reg($prefix . "sell_price"  , number_format($sell_price));

        $template->reg($prefix . "page"     , $page); 
        $template->reg($prefix . "page_dvs" , $page_dvs); 

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

            $rs = $dao->selectAmtMemberCateSale($conn, $param);
            $amt_member_sale_rate       = doubleval($rs["rate"]);
            $amt_member_sale_aplc_price = doubleval($rs["aplc_price"]);

            unset($param);
        } else {
            $dscr = "로그인시 할인받으실 수 있는 금액입니다.";
            $member_sale_rate           = 0;
            $amt_member_sale_rate       = 0;
            $amt_member_sale_aplc_price = 0;

            $template->reg($prefix . "amt_sale_rate", $amt_member_sale_rate);
            $template->reg($prefix . "amt_sale_aplc", $amt_member_sale_aplc_price);

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

        $sale_price  += $member_sale;

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
        $this->paper_price       = $paper_price;
        $this->print_price       = $print_price;
        $this->output_price      = $output_price;
        $this->tax               = $tax;
        $this->supply_price      = $supply_price;

        //17 견적서 html 생성
        if ($mix_yn === false) {
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

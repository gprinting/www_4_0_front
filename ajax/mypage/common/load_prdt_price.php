<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_define/prdt_default_info.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/CartDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/SheetDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/AftPriceUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/OptPriceUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/BindingPriceUtil.inc");
include_once(INC_PATH . "/classes/dprinting/PriceCalculator/Common/DPrintingFactory.php");

$util = new FrontCommonUtil();

if ($is_login === false) {
    $util->errorGoBack("로그인 후 확인 가능합니다.");
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new CartDAO();
$orderDAO = new OrderInfoDAO();
$sheetDAO = new SheetDAO();
$prdtDAO  = new ProductCommonDAO();
$calcUtil = new CalcPriceUtil();
$aftUtil  = new AftPriceUtil([
    "conn" => $conn,
    "dao"  => $prdtDAO,
    "util" => $util
]);
$optUtil = new OptPriceUtil([
    "conn" => $conn,
    "dao"  => $prdtDAO,
    "util" => $util
]);
$bindingUtil = new BindingPriceUtil();

$session = $fb->getSession();
$fb = $fb->getForm();

$order_seqno = $fb["order_seqno"];

$param = [];
$param["member_seqno"] = $session["org_member_seqno"];
$param["order_common_seqno"] = $order_seqno;

$order_common_col  = "\n  member_seqno";
$order_common_col .= "\n ,order_detail";
$order_common_col .= "\n ,mono_yn";
$order_common_col .= "\n ,title";
$order_common_col .= "\n ,expec_weight";
$order_common_col .= "\n ,cate_sortcode";
$order_common_col .= "\n ,opt_use_yn";
$order_common_col .= "\n ,group_seqno";
$order_common_col .= "\n ,amt";
$order_common_col .= "\n ,amt_unit_dvs";
$order_common_col .= "\n ,page_cnt";
$order_common_col .= "\n ,count";
$order_common_col .= "\n ,dlvr_produce_dvs";
$order_common_col .= "\n ,commerce_dvs";
$order_common_col .= "\n ,oper_sys";
$order_common_col .= "\n ,cust_memo";
$order_common_col .= "\n ,point_use_yn";
$order_common_col .= "\n ,use_point_price";
$order_common_col .= "\n ,receipt_dvs";
$order_common_col .= "\n ,bun_group";

$rs = $dao->selectOrderCommon($conn, $param, $order_common_col);
$fields = $rs->fields;

$title = $fields["title"];
$cate_sortcode = $fields["cate_sortcode"];
$member_seqno  = $fields["member_seqno"];
$amt  = $fields["amt"];
if ($amt > 1) {
    $amt = intval($amt);
}
$count = $fields["count"];
$sheet_count = $fields["page_cnt"];

$order_detail = $fields["order_detail"];

$cate_info = $dao->selectCateInfo($conn, $cate_sortcode);

$sell_price = 0;
$grade_sale = 0;
$amt_sale   = 0;
$sum_after_price = 0;
$sum_opt_price   = 0;

if ($cate_info["mono_dvs"] === '2') {
    // 확정형
    $price_tb = "ply_price";
    // order_detail
    $rs = $dao->selectOrderDetail($conn, $param);

    $i = 0;
    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $bef_print_mpcode = $fields["cate_beforeside_print_mpcode"];
        $aft_print_mpcode = $fields["cate_aftside_print_mpcode"];
        $bef_add_print_mpcode = $fields["cate_beforeside_add_print_mpcode"];
        $aft_add_print_mpcode = $fields["cate_aftside_add_print_mpcode"];
        $paper_mpcode = $fields["cate_paper_mpcode"];
        $stan_mpcode  = $fields["cate_output_mpcode"];

        $amt      = $fields["amt"];
        if ($amt > 1) {
            $amt = intval($amt);
        }
        $amt_unit = $fields["amt_unit_dvs"];

        // 확정형 가격 검색
        $temp = [];
        $temp["table_name"]    = $price_tb;
        $temp["member_seqno"]  = $member_seqno;
        $temp["cate_sortcode"] = $cate_sortcode;
        $temp["paper_mpcode"]  = $paper_mpcode;
        $temp["bef_print_mpcode"]     = $bef_print_mpcode;
        $temp["aft_print_mpcode"]     = $aft_print_mpcode;
        $temp["bef_add_print_mpcode"] = $bef_add_print_mpcode;
        $temp["aft_add_print_mpcode"] = $aft_add_print_mpcode;
        $temp["stan_mpcode"]   = $stan_mpcode;
        $temp["amt"]           = $amt;

        $price = $prdtDAO->selectPrdtPlyPrice($conn, $temp);
        $sell_price = $util->ceilVal($price["new_price"]);

        // 180510 건수 추가
        $sell_price = $sell_price * $count;

        // 주문 수량할인정보 검색
        $amt_sale = $dao->selectAmtMemberCateSale($conn, $temp);
        $amt_sale_rate       = doubleval($amt_sale["rate"]);
        $amt_sale_aplc_price = doubleval($amt_sale["aplc_price"]);
        unset($amt_sale);

        // 회원 등급할인율 검색
        unset($temp);
        $temp["cate_sortcode"] = $cate_sortcode;
        $temp["grade"] = $grade;

        $grade_sale_rate = $prdtDAO->selectGradeSaleRate($conn, $temp);

        // 등급할인액 계산
        $grade_sale = $util->calcPrice($grade_sale_rate, $sell_price);
        $grade_sale = $util->ceilVal($grade_sale);
        $sale_price = $sell_price + $grade_sale;

        // 수량할인액 계산
        $amt_sale  = $util->calcPrice($amt_sale_rate, $sale_price);
        $amt_sale += $amt_sale_aplc_price;
        $amt_sale  = $util->ceilVal($amt_sale);

        // 후가공 내역 검색
        unset($temp);
        $org_detail_dvs_num = $fields["order_detail_dvs_num"];
        $temp["order_detail_dvs_num"] = $org_detail_dvs_num;
        $after_rs = $dao->selectOrderAfterHistory($conn, $temp);

        $sum_after_price = 0;
        while ($after_rs && !$after_rs->EOF) {
            $after = $after_rs->fields;

            $after_name = $after["name"];
            $mpcode = $after["mpcode"];
            $info   = $after["info"];

            // 후가공 가격 검색
            $price = 0;
            if ($after_name === "박"
                    || $after_name === "형압"
                    || $after_name === "엠보싱") {
                // 계산형 후가공
                $info_arr = explode('|', $info);

                $aft_dvs = ProductInfoClass::AFTER_ARR[$after_name];

                $price = $aftUtil->getAfterFoilPressPrice([
                    "cate_sortcode" => $cate_sortcode,
                    "aft"           => $after_dvs,
                    "amt"           => $amt,
                    "sheet_count"   => $sheet_count,
                    "aft_1"  => $info_arr[0],
                    "dvs_1"  => $info_arr[1],
                    "wid_1"  => $info_arr[2],
                    "vert_1" => $info_arr[3],
                    "aft_2"  => $info_arr[4],
                    "dvs_2"  => $info_arr[5],
                    "wid_2"  => $info_arr[6],
                    "vert_2" => $info_arr[7]
                ])["price"];
            } else {
                if ($after_name === "라미넥스") {
                    $info_arr = explode('|', $info);
                    $amt = $info_arr[0];
                }

                // 확정형 후가공
                $price = $aftUtil->getAfterPrice([
                    "sell_site" => $cpn_admin_seqno,
                    "mpcode"    => $mpcode,
                    "amt"       => $amt
                ]);
            }
            // 180510 추가 : 건수에 따른 후가공 가격
            $price = $price * $count;
            $sum_after_price += $price;

            $after_rs->MoveNext();
        }


        $rs->MoveNext();
    }

} else if ($cate_info["mono_dvs"] === '3'
        && $cate_info["flattyp_yn"] === 'Y') {
    // 낱장 - 계산형, 마스터 양식지
    $paper_price  = 0;
    $output_price = 0;
    $print_price  = 0;

    // order_detail
    $rs = $dao->selectOrderDetail($conn, $param);

    $i = 0;
    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $detail_num = str_pad(strval(++$i), 2, '0', STR_PAD_LEFT);
        $order_detail_dvs_num = 'S' . $order_num . $detail_num;

        $bef_print_mpcode = $fields["cate_beforeside_print_mpcode"];
        $aft_print_mpcode = $fields["cate_aftside_print_mpcode"];
        $bef_add_print_mpcode = $fields["cate_beforeside_add_print_mpcode"];
        $aft_add_print_mpcode = $fields["cate_aftside_add_print_mpcode"];

        $paper_mpcode = $fields["cate_paper_mpcode"];
        $stan_mpcode  = $fields["cate_output_mpcode"];
        $stan_name    = $fields["stan_name"];

        $amt_unit = $fields["amt_unit_dvs"];

        $temp = [];
        $temp["mpcode"] = $stan_mpcode;

        $affil = $prdtDAO->selectSizeNameAffil($conn, $temp)["affil"];

        unset($temp);
        $temp["sell_site"]     = $cpn_admin_seqno;
        $temp["cate_sortcode"] = $cate_sortcode;
        $temp["amt_unit"]      = $amt_unit;
        $temp["flattyp_yn"]    = $cate_info["flattyp_yn"];

        $temp["amt"]     = $amt;
        $temp["page"]    = $fields["page_amt"];
        $temp["pos_num"] = PrdtDefaultInfo::POSITION_NUMBER[$cate_sortcode][$stan_name];
        $temp["affil"]   = $affil;

        $temp["cate_paper_mpcode"]  = $paper_mpcode;
        $temp["cate_output_mpcode"] = $stan_mpcode;

        $temp["bef_print_mpcode"]     = $bef_print_mpcode;
        $temp["aft_print_mpcode"]     = $aft_print_mpcode;
        $temp["bef_add_print_mpcode"] = $bef_add_print_mpcode;
        $temp["aft_add_print_mpcode"] = $aft_add_print_mpcode;

        // 마스터 인쇄일 때, 수량 낱장여부 별도처리
        if (substr($cate_sortcode, 0, 3) === "007") {
            $temp["flattyp_yn"] = 'N';

            if (substr($cate_sortcode, 0, 6) === "007001") {
                $temp["amt"] = PrdtDefaultInfo::MST_GROUP * $amt;
            } else {
                $temp["amt"] = PrdtDefaultInfo::MST_GROUP * 2 * $amt;
            }
        }

        $calcUtil->setData($temp);

        unset($temp);
        $temp["bef_print_name"] =
            $dao->selectCatePrintName($conn, ["mpcode" => $bef_print_mpcode]);
        $temp["aft_print_name"] =
            $dao->selectCatePrintName($conn, ["mpcode" => $aft_print_mpcode]);
        $temp["bef_add_print_name"] =
            $dao->selectCatePrintName($conn, ["mpcode" => $bef_add_print_mpcode]);
        $temp["aft_add_print_name"] =
            $dao->selectCatePrintName($conn, ["mpcode" => $aft_add_print_mpcode]);

        if (substr($cate_sortcode, 0, 6) === "007001") {
            // 마스터 NCR만 별도처리

            $diff_yn = false;
            if (strpos($order_detail, "내용 같음") === false) {
                $diff_yn = true;
            }

            $paper_mpcode_arr = explode('|', $fields["cate_paper_tot_mpcode"]);
            $paper_mpcode_arr_count = count($paper_mpcode_arr);
            $is_fst = true;
            for ($i = 0; $i < $paper_mpcode_arr_count ; $i++) {
                $paper_mpcode = $paper_mpcode_arr[$i];

                $calcUtil->setCatePaperMpcode($paper_mpcode);

                $paper_price += $util->ceilVal($calcUtil->calcPaperPrice($temp));

                if ($diff_yn) {
                    // 내용 다름
                    $print_price  += $util->ceilVal($calcUtil->calcSheetPrintPrice());
                    $output_price += $util->ceilVal($calcUtil->calcSheetOutputPrice());

                } else {
                    // 내용 같음
                    if ($is_fst) {
                        $calcUtil->setAmt(PrdtDefaultInfo::MST_GROUP
                                          * $amt
                                          * $paper_mpcode_arr_count);
                        $calcUtil->calcRealPaperAmt();

                        $print_price  += $util->ceilVal($calcUtil->calcSheetPrintPrice());
                        $output_price += $util->ceilVal($calcUtil->calcSheetOutputPrice());

                        $is_fst = false;

                        $calcUtil->setAmt(PrdtDefaultInfo::MST_GROUP * $amt);
                    }
                }
            }

            $sell_price = $paper_price
                          + $print_price
                          + $output_price;
        } else {
            $paper_price  = $util->ceilVal($calcUtil->calcPaperPrice($temp));
            $print_price  = $util->ceilVal($calcUtil->calcSheetPrintPrice());
            $output_price = $util->ceilVal($calcUtil->calcSheetOutputPrice());

            $sell_price   = $paper_price + $print_price + $output_price;
            $sell_price   = $sell_price * $count;
        }

        // 회원 등급할인율 검색
        unset($temp);
        $temp["cate_sortcode"] = $cate_sortcode;
        $temp["grade"] = $grade;

        $grade_sale_rate = $prdtDAO->selectGradeSaleRate($conn, $temp);

        // 등급할인액 계산
        $grade_sale = $util->calcPrice($grade_sale_rate, $sell_price);
        $grade_sale = $util->ceilVal($grade_sale);
        $sale_price = $sell_price + $grade_sale;

        // 후가공 내역 검색
        $org_detail_dvs_num = $fields["order_detail_dvs_num"];

        unset($temp);
        $temp["order_detail_dvs_num"] = $org_detail_dvs_num;
        $after_rs = $dao->selectOrderAfterHistory($conn, $temp);

        $sum_after_price = 0;
        while ($after_rs && !$after_rs->EOF) {
            $after = $after_rs->fields;

            $after_name = $after["name"];
            $mpcode = $after["mpcode"];
            $info   = $after["info"];

            // 후가공 가격 검색
            $price = 0;
            if ($after_name === "박"
                    || $after_name === "형압"
                    || $after_name === "엠보싱") {
                // 계산형 후가공
                $info_arr = explode('|', $info);

                $aft_dvs = ProductInfoClass::AFTER_ARR[$after_name];

                $aft_param = [
                    "cate_sortcode" => $cate_sortcode,
                    "aft"           => $after_dvs,
                    "amt"           => $amt,
                    "sheet_count"   => $sheet_count,
                    "aft_1"  => $info_arr[0],
                    "dvs_1"  => $info_arr[1],
                    "wid_1"  => $info_arr[2],
                    "vert_1" => $info_arr[3],
                    "aft_2"  => $info_arr[4],
                    "dvs_2"  => $info_arr[5],
                    "wid_2"  => $info_arr[6],
                    "vert_2" => $info_arr[7]
                ];
                print_r($aft_param);
                $price = $aftUtil->getAfterFoilPressPrice($aft_param)["price"];
            } else {
                if ($after_name === "라미넥스") {
                    $info_arr = explode('|', $info);
                    $amt = $info_arr[0];
                }

                // 확정형 후가공
                $price = $aftUtil->getAfterPrice([
                    "sell_site" => $cpn_admin_seqno,
                    "mpcode"    => $mpcode,
                    "amt"       => $amt
                ]);
                $price = $util->ceilVal($price);

            }
            // 180515 추가 : 건수에 따른 후가공 가격
            $price = $price * $count;
            $sum_after_price += $price;

            $after_rs->MoveNext();
        }

        $rs->MoveNext();
    }

} else if ($cate_info["mono_dvs"] === '3'
        && $cate_info["flattyp_yn"] === 'N') {
    // 책자 - 계산형
    // order_detail_brochure
    $rs = $dao->selectOrderDetailBrochure($conn, $param);

    $i = 0;
    $sum_page = 0;
    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $detail_num = str_pad(strval(++$i), 2, '0', STR_PAD_LEFT);
        $order_detail_dvs_num = 'B' . $order_num . $detail_num;

        $bef_print_mpcode = $fields["cate_beforeside_print_mpcode"];
        $aft_print_mpcode = $fields["cate_aftside_print_mpcode"];
        $bef_add_print_mpcode = $fields["cate_beforeside_add_print_mpcode"];
        $aft_add_print_mpcode = $fields["cate_aftside_add_print_mpcode"];

        $paper_mpcode = $fields["cate_paper_mpcode"];
        $stan_mpcode  = $fields["cate_output_mpcode"];
        $stan_name    = $fields["stan_name"];

        $amt_unit = $fields["amt_unit_dvs"];

        $typ  = $fields["typ"];
        $page = $fields["page_amt"];
        $sum_page += intval($page);

        $temp = [];
        $temp["mpcode"] = $stan_mpcode;

        $affil = $prdtDAO->selectSizeNameAffil($conn, $temp)["affil"];

        unset($temp);
        $temp["sell_site"]     = $cpn_admin_seqno;
        $temp["cate_sortcode"] = $cate_sortcode;
        $temp["amt_unit"]      = $amt_unit;
        $temp["flattyp_yn"]    = $cate_info["flattyp_yn"];

        $temp["amt"]     = $amt;
        $temp["page"]    = $page;
        $temp["pos_num"] = PrdtDefaultInfo::POSITION_NUMBER[$cate_sortcode][$stan_name];
        $temp["affil"]   = $affil;

        $temp["cate_paper_mpcode"]  = $paper_mpcode;
        $temp["cate_output_mpcode"] = $stan_mpcode;

        $temp["bef_print_mpcode"]     = $bef_print_mpcode;
        $temp["aft_print_mpcode"]     = $aft_print_mpcode;
        $temp["bef_add_print_mpcode"] = $bef_add_print_mpcode;
        $temp["aft_add_print_mpcode"] = $aft_add_print_mpcode;

        $calcUtil->setData($temp);

        unset($temp);
        $temp["bef_print_name"] =
            $dao->selectCatePrintName($conn, ["mpcode" => $bef_print_mpcode]);
        $temp["aft_print_name"] =
            $dao->selectCatePrintName($conn, ["mpcode" => $aft_print_mpcode]);
        $temp["bef_add_print_name"] =
            $dao->selectCatePrintName($conn, ["mpcode" => $bef_add_print_mpcode]);
        $temp["aft_add_print_name"] =
            $dao->selectCatePrintName($conn, ["mpcode" => $aft_add_print_mpcode]);

        $paper_price  = $util->ceilVal($calcUtil->calcPaperPrice($temp));
        $print_price  = $util->ceilVal($calcUtil->calcBookletPrintPrice());
        $output_price = $util->ceilVal($calcUtil->calcBookletOutputPrice());
        $sell_price += $paper_price + $print_price + $output_price;

        // 회원 등급할인율 검색
        unset($temp);
        $temp["cate_sortcode"] = $cate_sortcode;
        $temp["grade"] = $grade;

        $grade_sale_rate = $prdtDAO->selectGradeSaleRate($conn, $temp);

        // 등급할인액 계산
        $grade_sale = $util->calcPrice($grade_sale_rate, $sell_price);
        $grade_sale = $util->ceilVal($grade_sale);
        $sale_price = $sell_price + $grade_sale;

        // 후가공 내역 검색
        $org_detail_dvs_num = $fields["order_detail_dvs_num"];

        unset($temp);
        $temp["order_detail_dvs_num"] = $org_detail_dvs_num;
        $after_rs = $dao->selectOrderAfterHistory($conn, $temp);

        $sum_after_price = 0;
        while ($after_rs && !$after_rs->EOF) {
            $after = $after_rs->fields;

            $after_name = $after["name"];
            $mpcode = $after["mpcode"];
            $info   = $after["info"];

            // 후가공 가격 검색
            $price = 0;
            if ($after_name === "박"
                    || $after_name === "형압"
                    || $after_name === "엠보싱") {
                // 계산형 후가공
                $info_arr = explode('|', $info);

                $aft_dvs = ProductInfoClass::AFTER_ARR[$after_name];

                $price = $aftUtil->getAfterFoilPressPrice([
                    "cate_sortcode" => $cate_sortcode,
                    "aft"           => $after_dvs,
                    "amt"           => $amt,
                    "sheet_count"   => $sheet_count,
                    "aft_1"  => $info_arr[0],
                    "dvs_1"  => $info_arr[1],
                    "wid_1"  => $info_arr[2],
                    "vert_1" => $info_arr[3],
                    "aft_2"  => $info_arr[4],
                    "dvs_2"  => $info_arr[5],
                    "wid_2"  => $info_arr[6],
                    "vert_2" => $info_arr[7]
                ])["price"];
            } else if ($after_name === "제본") {
                $binding_mpcode = $mpcode;
                $price = 0;
            } else {
                if ($after_name === "라미넥스") {
                    $info_arr = explode('|', $info);
                    $amt = $info_arr[0];
                }

                if ($amt > 1) {
                    $amt = intval($amt);
                }

                // 확정형 후가공
                $price = $aftUtil->getAfterPrice([
                    "sell_site" => $cpn_admin_seqno,
                    "mpcode"    => $mpcode,
                    "amt"       => $amt
                ]);
            }
            $sum_after_price += $price;

            $after_rs->MoveNext();
        }

        $rs->MoveNext();
    }

    // 후가공 중 제본은 detail 다 돌고 page 합산해서 검색
    $binding_price = $aftUtil->getAfterPrice([
        "sell_site" => $cpn_admin_seqno,
        "mpcode"    => $binding_mpcode,
        "amt"       => $amt
    ]);

    $bindingUtil->setData([
        "cate_sortcode" => $cate_sortcode,
        "amt"           => $amt,
        "page"          => $sum_page,
        "price"         => $binding_price,
        "coating_yn"    => false,
        "depth1"        => $after["depth1"],
        "stan_name"     => $stan_name,
        "pos_num"       => PrdtDefaultInfo::POSITION_NUMBER[$cate_sortcode][$stan_name]
    ]);

    $binding_price = $bindingUtil->calcBindingPrice();
    $sum_after_price += $binding_price;
}

// 옵션 내역 검색
unset($temp);
$temp["order_common_seqno"] = $order_seqno;
$opt_rs = $dao->selectOrderOptHistory($conn, $temp);
while ($opt_rs && !$opt_rs->EOF) {
    $fields = $opt_rs->fields;

    $opt_name = $fields["name"];
    $mpcode   = $fields["mpcode"];
    $info     = $fields["info"];

    $paper_info_arr = $dao->selectCatePaperInfo($conn, $paper_mpcode);
    $paper_info = [
        $paper_info_arr["name"],
        $paper_info_arr["basisweight"]
    ];

    unset($temp);
    $temp["mpcode"] = $stan_mpcode;
    $size_info = $prdtDAO->selectSizeNameAffil($conn, $temp);

    unset($temp);
    $temp["sell_site"]     = $cpn_admin_seqno;
    $temp["cate_sortcode"] = $cate_sortcode;
    $temp["name"]          = $opt_name;
    $temp["mpcode"]        = $mpcode;
    $temp["amt"]           = $amt;

    $temp["sell_price"]    = $sell_price;

    $temp["paper_mpcode"]  = $paper_mpcode;
    $temp["paper_info"]    = $paper_info;

    $temp["affil"]         = $size_info["affil"];
    $lc                    = $util->getLumpCount($cate_sortcode,
                                                 $order_common_param["expec_weight"]);

    $temp["expect_box"]    = $lc["lump_count"];
    $temp["dlvr_price"]    = $lc["price"];

    $info_arr = $optUtil->calcOptPrice($temp);

    $price = intval($info_arr["price"]);

    $sum_opt_price += $price;

    $opt_rs->MoveNext();
}

/*********** 배송 정보 START ************/
$flag = "발신";
// 배송정보(발신) 검색
$dlvr_info_send = $orderDAO->selectDlvrInfoDup($conn, $param, $flag);

$flag = "수신";
// 배송정보(수신) 검색
$dlvr_info_recei = $orderDAO->selectDlvrInfoDup($conn, $param, $flag);

// 직배확인
$dlvr_way = $dlvr_info_recei["dlvr_way"];

// 배송비 수납방식(선불,착불) 판별
$dlvr_sum_way = $dlvr_info_recei["dlvr_sum_way"];


$dlvr_param = [];
$dlvr_param["member_seqno"]       = $member_seqno;
$dlvr_param["order_common_seqno"] = $order_common_seqno;
$dlvr_param["zipcode"]            = $dlvr_info_recei["zipcode"];

// 01이면 선불. 이 경우 택배비를 재계산해야 한다.
if ($dlvr_sum_way == "01") {
    /***************** 배송비 재계산 START *****************/
    $expec_weight  = $fields["expec_weight"];
    $zipcode       = $dlvr_param["zipcode"];
    
    $factory         = new DPrintingFactory();
    $dlvr_cost_nc    = 0;
    $dlvr_cost_bl    = 0;
    $weight_leaflet  = 0;
    $weight_namecard = 0;
    $seq_leaflet     = "";
    $seq_namecard    = "";
    $boxCount        = 0;
    $island_cost     = 0;
    
    // 택배의 경우 도서지방 배송료 계산 필요
    // 화물의 경우도 계산 필요한지 확인 필요($dlvr_way == "03")
    if ($dlvr_way == "01") {
    	$rs_dlvr = $sheetDAO->selectIslandParcelCost($conn, $dlvr_param);
    	while ($rs_dlvr && !$rs_dlvr->EOF) {
    		$island_cost = $rs_dlvr->fields["price"];
    		$rs_dlvr->MoveNext();
    	}
    }
    
    $product = $factory->create($cate_sortcode);
    $sort    = $product->getSort();

    // 명함은 주문건의 모든 상품을 합쳐서 배송비를 받아야함
    if ($sort == "namecard") {
    	$weight_namecard += $expec_weight;
    	$seq_namecard = $order_seqno;
    
    // 전단은 건당으로 배송비를 받아야함
    } else if ($sort == "leaflet") {
    	$param['sort'] = $sort;
    	$param['expec_weight'] = $expec_weight;
    	$dlvr_cost_bl += $product->getDlvrCost($param, $dlvr_way);
    	$seq_leaflet = $order_seqno;
    	$blBoxCount = $util->getLeafletBoxcount($expec_weight);
    	$boxCount += $blBoxCount;
    	$dlvr_cost_bl += $blBoxCount * $island_cost;
    	$weight_leaflet += $expec_weight;
    
    // 모든 상품들이 전단 / 명함으로 구분지어지면 삭제해야한다.
    } else { 
    	$weight_leaflet += $expec_weight;
    	$seq_leaflet = $order_seqno;
    }
    
    if ($weight_namecard != 0) {
    	$ncBoxCount = (int)($weight_namecard / 12) + 1;
    	$boxCount += $ncBoxCount;
    	$dlvr_cost_nc += $ncBoxCount * $island_cost;
    }
    
    if ($seq_leaflet != "") {
    	$seq_leaflet = substr($seq_leaflet , 0, -1);
    }
    
    if ($seq_namecard != "") {
    	$seq_namecard = substr($seq_namecard , 0, -1);
    }
    
    if ($weight_namecard != 0) {
    	$product = $factory->create("003001001");
    	$param_namecard = array();
    	$param_namecard['zipcode'] = $zipcode;
    	$param_namecard['expec_weight'] = $weight_namecard;
    	$dlvr_cost_nc += $product->getDlvrCost($param_namecard, $dlvr_way);
    }
    
    /*
    if ($weight_leaflet != 0) {
    	$product = $factory->create("005001001");
    	$param_leaflet = array();
    	$param_leaflet['zipcode'] = $zipcode;
    	$param_leaflet['expec_weight'] = $weight_leaflet;
    	//$dlvr_cost_bl += $product->getDlvrCost($param, $dlvr_way);
    }
    */
    
    /***************** 배송비 재계산 END *****************/

    $dlvr_cost = !empty($dlvr_cost_nc) ? $dlvr_cost_nc : $dlvr_cost_bl;

// 착불의 경우 택배비 재계산이 필요 없으므로 이전 데이터를 그대로 사용
} else {
    $dlvr_cost = $dlvr_info_recei["dlvr_price"];
}

$json = "{\"title\" : \"%s\", \"pay_price\" : \"%s\"}";

$pay_price = $sell_price - $grade_sale - $amt_sale
             + $sum_after_price + $sum_opt_price + $dlvr_cost;

echo sprintf($json, $title, $pay_price);

$conn->Close();


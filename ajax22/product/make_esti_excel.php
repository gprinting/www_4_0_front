<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/excel/PHPExcel/IOFactory.php");
include_once(INC_PATH . "/common_define/common_config.inc");
include_once(INC_PATH . "/define/front/product_info_class.inc");


// 여기서 $param 처리
include_once($_SERVER["DOCUMENT_ROOT"] . "/product/common/esti_pop_common.php");

$base_path = $_SERVER["DOCUMENT_ROOT"] . EXCEL_TEMPLATE;
$file_name = null;

$is_booklet = false;
$is_calc    = false;
$is_ply     = false;

$cate_name = $param["cate_name_arr"][0];

if ((strpos($cate_name, "카다로그") !== false) || // 카다로그
        (strpos($cate_name, "NCR") !== false) ||  // 마스터 NCR
        (strpos($cate_name, "책자") !== false)) { // 책자
    $file_name = "esti_sample_catabro";
    $is_booklet = true;
} else if ((strpos($cate_name, "독판전단") !== false) || // 독판전단
        (strpos($cate_name, "양식지") !== false)) { // 마스터 양식지
    $file_name = "esti_sample_calc";
    $is_calc = true;
} else {
    $file_name = "esti_sample_ply";
    $is_ply = true;
}

$input_file = $base_path . $file_name .".xlsx";

$objPHPExcel = PHPExcel_IOFactory::load($input_file);

$sheet = $objPHPExcel->getActiveSheet();

$paper_arr     = $param["paper_arr"];
$size_arr      = $param["size_arr"];
$tmpt_arr      = $param["tmpt_arr"];
$page_arr      = $param["page_arr"];
$amt_arr       = $param["amt_arr"];
$amt_unit_arr  = $param["amt_unit_arr"];
$count_arr     = $param["count_arr"];
$after_arr     = $param["after_arr"];
$after_det_arr = $param["after_det_arr"];

//! 최상단 공통정보
// 견적일
$sheet->setCellValue("C4", sprintf("%s년 %s월 %s일", $param["year"]
                                                   , $param["month"]
                                                   , $param["day"]));
// 회원명
$sheet->setCellValue("C5", $param["member_name"] . " 귀하");

// 공급가액, 부가세
$sheet->setCellValue("B11", "합계금액 \\" . $param["supply_price"] . " 원 + 부가세 \\" . $param["tax"] . " 원 + 배송비 별도");

// 합계금액(오른쪽 정렬 적용)
$sheet->setCellValue("I11", "총 합계금액 : \\" . $param["pay_price"])
      ->getStyle("I11")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


if ($is_booklet) {
    //! 카다로그 브로셔, NCR(각각에 대해 분리되어 있음)

    // 공통 품명
    $sheet->setCellValue("D14", $cate_name);
    // 공통 규격(사이즈)
    $sheet->setCellValue("D15", $size_arr[0]);
    // 공통 수량
    $sheet->setCellValue("D16", $amt_arr[0] . $amt_unit_arr[0] . " x " . $count_arr[0] . "건");
    // 종이비
    $sheet->setCellValue("H14", "\\" . $param["paper_price"]);
    // 출력비
    $sheet->setCellValue("H15", "\\" . $param["output_price"]);
    // 인쇄비
    $sheet->setCellValue("H16", "\\" . $param["print_price"]);

    $paper_arr_cnt = count($paper_arr);
    $tmpt_arr_cnt  = count($tmpt_arr);
    $row_lc        = 3 + $paper_arr_cnt + $tmpt_arr_cnt;  // 좌변 갯수
    $row_rc        = 3;

    $after_arr = ProductInfoClass::AFTER_ARR;

    foreach ($after_arr as $after_ko => $after_en) {
        $price = $param[$after_en . "_price"];

        if (!empty($price)) {
            $row_rc++;
        }
    }

    // 우변 갯수
    $row_rc = intval($row_rc) + 7;

    $row = 17;

    $paper_ncr = "";
    if ((strpos($cate_name, "NCR") !== false)) {
        $paper_ncr     = explode(",", $paper_arr[0]);
        $paper_ncr_cnt = count($paper_ncr);

        $paper_name = array();
        switch ($paper_ncr_cnt) {
            case 2 : 
                $paper_name[0] = "상";
                $paper_name[1] = "하";
                break;
            case 3:
                $paper_name[0] = "상";
                $paper_name[1] = "중";
                $paper_name[2] = "하";
                break;
            case 4:
                $paper_name[0] = "상";
                $paper_name[1] = "중";
                $paper_name[2] = "중";
                $paper_name[3] = "하";
                break;
            default:
                $paper_name[0] = "오류";
                break;
        } 

    } else {
        $paper_name = array();
        $paper_name[0] = "표지";
        $paper_name[1] = "내지1";
        $paper_name[2] = "내지2";
        $paper_name[3] = "내지3";
    }

    // 좌변의 갯수가 더 크거나 같을 때 
    if ($row_lc >= $row_rc) {

        /*********** 좌변 시작 ************/
        $sheet->insertNewRowBefore($row);

        // 표지 재질
        $sheet->mergeCells('B' . $row . ':' . 'C' . $row); 
        $sheet->mergeCells('D' . $row . ':' . 'E' . $row);
        $sheet->mergeCells('H' . $row . ':' . 'J' . $row);
        for ($i = 0; $i < $paper_arr_cnt; $i++) {
            $sheet->setCellValue('B' . $row, "재질(".$paper_name[$i].")");
            $sheet->setCellValue('D' . $row, $paper_arr[$i]);
            $row++;
            // 표지 인쇄도수
            $sheet->setCellValue('B' . $row, "인쇄도수(".$paper_name[$i].")");
            $sheet->setCellValue('D' . $row, $tmpt_arr[$i]);
            $row++;
        }
        /*********** 좌변 끝 ************/

        /*********** 우변 시작 ************/
        // 후공정비
        $row_after = 17;
        $after_arr = ProductInfoClass::AFTER_ARR;

        foreach ($after_arr as $after_ko => $after_en) {
            $price = $param[$after_en . "_price"];

            if (!empty($price)) {
                $sheet->setCellValue('G' . $row_after, $after_ko . '비');
                $sheet->setCellValue('H' . $row_after, "\\" . $price);
                $row_after++;
            }
        }
        // 옵션비
        $sheet->setCellValue('H' . $row_after++, "\\" . $param["opt_price"]);
        // 주문건수
        $sheet->setCellValue('H' . $row_after++, $count_arr[0] . "건");
        // 공급가
        $sheet->setCellValue('H' . $row_after++, "\\" . $param["supply_price"]);
        // 부가세
        $sheet->setCellValue('H' . $row_after++, "\\" . $param["tax"]);
        // 정상판매가
        $sheet->setCellValue('H' . $row_after++, "\\" . $param["sell_price"]);
        // 할인금액
        $sheet->setCellValue('H' . $row_after++, "\\" . $param["sale_price"]);
        // 결제금액
        $sheet->setCellValue('H' . $row_after++, "\\ " . $param["pay_price"]);

        /*********** 우변 끝 ************/

        // 후가공 세부내역

    // 우변의 갯수가 더 클 때
    } else {

        // 좌변 시작지점
        $row_left = $row;
        
        /****************** 우변 시작 ********************/
        // 후공정비
        foreach ($after_arr as $after_ko => $after_en) {
            $price = $param[$after_en . "_price"];
            if (!empty($price)) {
                $sheet->insertNewRowBefore($row);
                $sheet->mergeCells('B' . $row . ':' . 'C' . $row);
                $sheet->mergeCells('D' . $row . ':' . 'E' . $row);
                $sheet->mergeCells('H' . $row . ':' . 'J' . $row);
                $sheet->setCellValue('G' . $row, $after_ko . '비');
                $sheet->setCellValue('H' . $row, "\\" . $price);
                $row++;
                
            }
        }

        // 옵션비
        $sheet->setCellValue('H' . $row++, "\\" . $param["opt_price"]);
        // 주문건수
        $sheet->setCellValue('H' . $row++, $count_arr[0] . "건");
        // 공급가
        $sheet->setCellValue('H' . $row++, "\\" . $param["supply_price"]);
        // 부가세
        $sheet->setCellValue('H' . $row++, "\\" . $param["tax"]);
        // 정상판매가
        $sheet->setCellValue('H' . $row++, "\\" . $param["sell_price"]);
        // 할인금액
        $sheet->setCellValue('H' . $row++, "\\" . $param["sale_price"]);
        // 결제금액
        $sheet->setCellValue('H' . $row++, "\\ " . $param["pay_price"]);
        /****************** 우변 끝 ********************/

        // 후공정 세부내역 시작줄
        $after_det_row = $row + 2;

        /****************** 좌변 시작 ********************/
        // NCR의 경우
        if ((strpos($cate_name, "NCR") !== false)) {
            for ($i = 0; $i < $paper_ncr_cnt; $i++) {
                $sheet->setCellValue('B' . $row_left, "재질(".$paper_name[$i].")");
                $sheet->setCellValue('D' . $row_left, trim($paper_ncr[$i]));
                $row_left++;
            }
            
            $ncr_tmpt = explode('-', $tmpt_arr[0])[0];
            
            $sheet->setCellValue('B' . $row_left, "인쇄도수");
            $sheet->setCellValue('D' . $row_left, $ncr_tmpt);

            // 후공정 세부내역
            $after_det  = $after_det_arr[0]; 
            $after_text = explode(" ", $after_det);

            if ($after_text[0] != "후가공을") {
                $after_text_cnt = count($after_text); 
                for ($i = 0; $i < $after_text_cnt; $i) {
                    $sheet->setCellValue('B'. $after_det_row++, 
                                         $after_text[$i++] . ":" . $after_text[$i++]);
                }
            }
        // 카다로그 일 경우
        } else {

            for ($i = 0; $i < $paper_arr_cnt; $i++) {
                $sheet->setCellValue('B' . $row_left, "재질(".$paper_name[$i].")");
                $sheet->setCellValue('D' . $row_left, $paper_arr[$i]);
                $row_left++;
                // 표지 인쇄도수
                $sheet->setCellValue('B' . $row_left, "인쇄도수(".$paper_name[$i].")");
                $sheet->setCellValue('D' . $row_left, $tmpt_arr[$i]);
                $row_left++;
            }

            // 후공정 세부내역
            $after_det = $after_det_arr[0];
            for ($i = 0; $i < $paper_arr_cnt; $i++) {
            
                if ($after_det[$i] == "후가공을 입력해주세요.") {
                    continue;
                }
                $sheet->setCellValue('B' . $after_det_row++, 
                                     "후공정(".$paper_name[$i]."):".$after_det[$i]);
            }
        }
        /****************** 좌변 끝 ********************/
    }

} else if ($is_calc) {
    //! 계산형 카테고리

    // 공통 품명
    $sheet->setCellValue("D14", $cate_name);
    // 공통 규격(사이즈)
    $sheet->setCellValue("D15", $size_arr[0]);
    // 공통 수량
    $sheet->setCellValue("D16", $amt_arr[0] . $amt_unit_arr[0] . " x " . $count_arr[0] . "건");
    // 종이비
    $sheet->setCellValue("H14", "\\" . $param["paper_price"]);
    // 출력비
    $sheet->setCellValue("H15", "\\" . $param["output_price"]);
    // 인쇄비
    $sheet->setCellValue("H16", "\\" . $param["print_price"]);

    $row_lc = 5; // 좌변 갯수

    // 후공정비
    $row = 17;
    $row_rc = 1;
    $after_arr = ProductInfoClass::AFTER_ARR;

    foreach ($after_arr as $after_ko => $after_en) {
        $price = $param[$after_en . "_price"];

        if (!empty($price)) {
            $row_rc++;
        }

    }
    $row_rc = intval($row_rc) + 7;

    // 좌변의 갯수가 더 클 때 
    if ($row_lc > $row_rc) {

        // 이럴 상황이 없음
        echo "error";

    // 우변의 갯수가 더 클때
    } else {

        /****************** 우변 ******************/
        foreach ($after_arr as $after_ko => $after_en) {
            $price = $param[$after_en . "_price"];
            if (!empty($price)) {
                $sheet->insertNewRowBefore($row);
                $sheet->mergeCells('B' . $row . ':' . 'C' . $row);
                $sheet->mergeCells('D' . $row . ':' . 'E' . $row);
                $sheet->mergeCells('H' . $row . ':' . 'J' . $row);
                $sheet->setCellValue('G' . $row, $after_ko . '비');
                $sheet->setCellValue('H' . $row, "\\" . $price);
                $row++;
            }
        }

        // 옵션비
        $sheet->setCellValue('H'. $row++, "\\" . $param["opt_price"]);
        // 주문건수
        $sheet->setCellValue('H'. $row++, $count_arr[0] . "건");
        // 공급가
        $sheet->setCellValue('H'. $row++, "\\" . $param["supply_price"]);
        // 부가세
        $sheet->setCellValue('H'. $row++, "\\" . $param["tax"]);
        // 정상판매가
        $sheet->setCellValue('H'. $row++, "\\" . $param["sell_price"]);
        // 할인금액
        $sheet->setCellValue('H'. $row++, "\\" . $param["sale_price"]);
        // 결제금액
        $sheet->setCellValue('H'. $row++, "\\ " . $param["pay_price"]);
        /****************** 우변 끝 ***************/

        /****************** 좌변 ******************/
        // 재질
        $sheet->setCellValue("B17", "재질");
        $sheet->setCellValue("D17", $paper_arr[0]);
        // 인쇄도수
        $sheet->setCellValue("B18", "인쇄도수");
        // 양식지의 경우
        if ((strpos($cate_name, "양식지") !== false)) {
            $ncr_tmpt = explode('-', $tmpt_arr[0])[0];
            $sheet->setCellValue('D18', $ncr_tmpt);
        // 독판전단의 경우
        } else {
            $sheet->setCellValue("D18", $tmpt_arr[0]);
        }
        /****************** 좌변 끝 ***************/

    }

    $after_row = $row + 2;

    // 후공정 세부내역
    $after_det  = $after_det_arr[0]; 
    $after_text = explode(" ", $after_det);

    if ($after_text[0] != "후가공을") {
        $after_text_cnt = count($after_text); 
        for ($i = 0; $i < $after_text_cnt; $i) {
            $sheet->setCellValue('B'. $after_row++, 
                                 $after_text[$i++] . ":" . $after_text[$i++]);
        }
    }
   
} else {
    //! 그 외

    $row_lc = 5; // 좌변 갯수

    // 후공정비
    $row = 15;
    $row_rc = 1;
    $after_arr = ProductInfoClass::AFTER_ARR;

    foreach ($after_arr as $after_ko => $after_en) {
        $price = $param[$after_en . "_price"];

        /*
        if (!empty($price)) {
            $sheet->insertNewRowBefore($row);
            $sheet->mergeCells('C' . $row . ":D" . $row);
            $sheet->mergeCells('E' . $row . ":K" . $row);
            $sheet->setCellValue('C' . $row, $after_ko . '비');
            $sheet->setCellValue('E' . $row, "\\" . $price);
            $row++;
        }

        if (!empty($price)) {
            $sheet->insertNewRowBefore($row);
            $sheet->setCellValue('G' . $row, $after_ko . '비');
            $sheet->setCellValue('J' . $row, "\\" . $price);
            $row++;
        }
        */

        if (!empty($price)) {
            $row_rc++;
        }

    }

    $row_rc = intval($row_rc) + 7;

    // 좌변의 갯수가 더 클 때 
    if ($row_lc > $row_rc) {

        /****************** 좌변 ******************/
        // 품명
        $sheet->setCellValue("D14", $cate_name);
        // 사이즈(규격)
        $sheet->setCellValue("B15", "규격");
        $sheet->setCellValue("D15", $size_arr[0]);
        // 수량
        $sheet->setCellValue("B16", "수량");
        $sheet->setCellValue("D16", $amt_arr[0] . $amt_unit_arr[0] . " x " . $count_arr[0] . "건");
        // 재질
        $sheet->setCellValue("B17", "재질");
        $sheet->setCellValue("D17", $paper_arr[0]);
        // 인쇄도수
        $sheet->setCellValue("B18", "인쇄도수");
        $sheet->setCellValue("D18", $tmpt_arr[0]);
        /****************** 좌변 끝 ***************/

        /****************** 우변 ******************/
        // 인쇄비
        $sheet->setCellValue("H14", "\\" . $param["print_price"]);
        foreach ($after_arr as $after_ko => $after_en) {
            $price = $param[$after_en . "_price"];
            if (!empty($price)) {
                $sheet->insertNewRowBefore($row);
                $sheet->setCellValue('G' . $row, $after_ko . '비');
                $sheet->setCellValue('H' . $row, "\\" . $price);
                $row++;
            }
        }

        // 옵션비
        $sheet->setCellValue('G'. $row, "옵션비");
        $sheet->setCellValue('H'. $row++, "\\" . $param["opt_price"]);
        // 주문건수
        $sheet->setCellValue('H'. $row++, $count_arr[0] . "건");
        // 공급가
        $sheet->setCellValue('H'. $row++, "\\" . $param["supply_price"]);
        // 부가세
        $sheet->setCellValue('H'. $row++, "\\" . $param["tax"]);
        // 정상판매가
        $sheet->setCellValue('H'. $row++, "\\" . $param["sell_price"]);
        // 할인금액
        $sheet->setCellValue('H'. $row++, "\\" . $param["sale_price"]);
        // 결제금액
        $sheet->setCellValue('H'. $row++, "\\ " . $param["pay_price"]);
        /****************** 우변 끝 ***************/

    // 우변의 갯수가 더 클때
    } else {

        /****************** 우변 ******************/
        // 인쇄비
        $sheet->setCellValue("H14", "\\" . $param["print_price"]);
        foreach ($after_arr as $after_ko => $after_en) {
            $price = $param[$after_en . "_price"];
            if (!empty($price)) {
                $sheet->insertNewRowBefore($row);
                $sheet->mergeCells('B' . $row . ':' . 'C' . $row);
                $sheet->mergeCells('D' . $row . ':' . 'E' . $row);
                $sheet->mergeCells('H' . $row . ':' . 'J' . $row);
                $sheet->setCellValue('G' . $row, $after_ko . '비');
                $sheet->setCellValue('H' . $row, "\\" . $price);
                $row++;
                
            }
        }

        // 옵션비
        $sheet->setCellValue('H'. $row++, "\\" . $param["opt_price"]);
        // 주문건수
        $sheet->setCellValue('H'. $row++, $count_arr[0] . "건");
        // 공급가
        $sheet->setCellValue('H'. $row++, "\\" . $param["supply_price"]);
        // 부가세
        $sheet->setCellValue('H'. $row++, "\\" . $param["tax"]);
        // 정상판매가
        $sheet->setCellValue('H'. $row++, "\\" . $param["sell_price"]);
        // 할인금액
        $sheet->setCellValue('H'. $row++, "\\" . $param["sale_price"]);
        // 결제금액
        $sheet->setCellValue('H'. $row++, "\\ " . $param["pay_price"]);
        /****************** 우변 끝 ***************/

        /****************** 좌변 ******************/
        // 품명
        $sheet->setCellValue("D14", $cate_name);
        // 사이즈(규격)
        $sheet->setCellValue("B15", "규격");
        $sheet->setCellValue("D15", $size_arr[0]);
        // 수량
        $sheet->setCellValue("B16", "수량");
        $sheet->setCellValue("D16", $amt_arr[0] . $amt_unit_arr[0] . " x " . $count_arr[0] . "건");
        // 재질
        $sheet->setCellValue("B17", "재질");
        $sheet->setCellValue("D17", $paper_arr[0]);
        // 인쇄도수
        $sheet->setCellValue("B18", "인쇄도수");
        $sheet->setCellValue("D18", $tmpt_arr[0]);
        /****************** 좌변 끝 ***************/

    }

    $after_row = $row + 2;

    // 후공정 세부내역
    $after_det  = $after_det_arr[0]; 
    $after_text = explode(" ", $after_det);

    if ($after_text[0] != "후가공을") {
        $after_text_cnt = count($after_text); 
        for ($i = 0; $i < $after_text_cnt; $i) {
            $sheet->setCellValue('B'. $after_row++, 
                                 $after_text[$i++] . ":" . $after_text[$i++]);
        }
    }
}

$save_name = uniqid();

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save($base_path . $save_name . ".xlsx");

$objPHPExcel->disconnectWorksheets();
unset($objPHPExcel);

echo $save_name;
//echo $row_rc;
exit;

/******************************************************************************
 * 엑셀 함수영역
 ******************************************************************************/

/**
 * 셀의 경계선 스타일을 상하좌우 전체 변경 하는 함수
 */
function setCellBorder(&$sheet, $cells) {
    $thick = PHPExcel_Style_Border::BORDER_THIN;

    $style_arr = array();
    $style_arr["borders"]["top"]["style"] = $thick;
    $style_arr["borders"]["bottom"]["style"] = $thick;
    $style_arr["borders"]["left"]["style"] = $thick;
    $style_arr["borders"]["right"]["style"] = $thick;

    $sheet->getStyle($cells)->applyFromArray($style_arr);
}

/**
 * 셀의 문자서식을 숫자형으로 변경하는 함수(1,111,111...)
 */
function setCellNumberFormatting(&$sheet, $cells) {
    $sheet->getStyle($cells)
          ->getNumberFormat()
          ->setFormatCode('₩#,##0;[Red]-₩#,##0');
}

/**
 * 셀의 수평정렬을 설정하는 함수
 *
 * PHPExcel_Style_Alignment::HORIZONTAL_CENTER : 가운데 정렬
 * PHPExcel_Style_Alignment::HORIZONTAL_RIGHT  : 오른쪽 정렬
 */
function setCellHAlign(&$sheet, $cells, $style) {
    $sheet->getStyle($cells)
          ->getAlignment()
          ->setHorizontal($style);
}

/**
 * 셀의 수직정렬을 설정하는 함수
 *
 * PHPExcel_Style_Alignment::VERTICAL_TOP    : 상단 정렬
 * PHPExcel_Style_Alignment::VERTICAL_BOTTOM : 하단 정렬
 * PHPExcel_Style_Alignment::VERTICAL_CENTER : 가운데 정렬
 */
function setCellVAlign(&$sheet, $cells) {
    $sheet->getStyle($cells)
          ->getAlignment()
          ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
}

/**
 * 셀의 글자크기 변경
 */
function setCellFontSize(&$sheet, $cells) {
    $sheet->getStyle($cells)
          ->getFont()
          ->setSize(9);
}

?>


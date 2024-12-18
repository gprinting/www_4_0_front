<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/common/excel/PHPExcel/IOFactory.php');
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/PaymentInfoDAO.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/calcul_mng/settle/IncomeDataDAO.inc');
include_once(INC_PATH . "/common_define/common_config.inc");
include_once(INC_PATH . "/common_define/cpn_info_define.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaymentInfoDAO();
$incomeDAO = new IncomeDataDAO();

if (!$is_login) {
    exit;
}

if (!$fb->form("from") || !$fb->form("to")) {
    exit;
}

// 엑셀관련 초기화
$base_path = $_SERVER["DOCUMENT_ROOT"] . EXCEL_TEMPLATE;
$objPHPExcel = PHPExcel_IOFactory::load($base_path . "payment_deal_sample.xlsx");
$sheet = $objPHPExcel->getActiveSheet();

// 엑셀 문서 설정
$sheet->getDefaultStyle()->getFont()->setName("맑은 고딕");
//$sheet->getDefaultStyle()->getFont()->setSize(9);

// 입력정보 초기화
$session = $fb->getSession();
$seqno = $session["org_member_seqno"];

$cpn_admin_seqno = $session["sell_site"];
$member_dvs = $session["member_dvs"];
$state_arr = $session["state_arr"];

$param = array();

//1 공급자 정보 : 현재 이 쿼리는 쓰지 않음.
//$rs = $dao->selectCpnAdmin($conn, $cpn_admin_seqno, true);

/*
$sell_site    = $rs["sell_site"]; 
$licensee_num = $rs["licensee_num"]; 
$repre_name   = $rs["repre_name"]; 
$repre_num    = $rs["repre_num"]; 
$addr         = $rs["addr"]; 
$addr_detail  = $rs["addr_detail"]; 
$bc           = $rs["bc"]; 
$tob          = $rs["tob"]; 
*/

$sell_site    = BASIC_COM_INFO["name"];
$licensee_num = BASIC_COM_INFO["licensee_num"];
$repre_name   = BASIC_COM_INFO["repre_name"];
$repre_num    = BASIC_COM_INFO["repre_num"];
$addr         = BASIC_COM_INFO["addr"];

//2 공급받는자 정보
$ed_sell_site    = '';
$ed_licensee_num = ''; 
$ed_repre_name   = ''; 
$ed_addr         = '';
$ed_addr_detail  = '';
$ed_bc           = ''; 
$ed_tob          = ''; 

if ($member_dvs == "개인" /* || $member_dvs == "외국인" */) {
    $ed_sell_site    = $session["member_name"]; 
    $ed_repre_name   = $session["member_name"]; 
    $ed_addr         = $session["addr"]; 
    $ed_addr_detail  = $session["addr_detail"]; 

} else {
    unset($param);
    $param["member_seqno"] = $session["member_seqno"];

    $rs = $dao->selectLicenseInfo($conn, $param);

    $ed_sell_site    = $rs["corp_name"]; 
    $ed_licensee_num = $rs["crn"]; 
    $ed_repre_name   = $rs["repre_name"]; 
    $ed_addr         = $rs["addr"]; 
    $ed_addr_detail  = $rs["addr_detail"]; 
    $ed_bc           = $rs["bc"]; 
    $ed_tob          = $rs["tob"]; 
}

$date_str = $fb->form("from") . " ~ " . $fb->form("to");

$origin_from = $fb->form("from");
$origin_to = $fb->form("to");

unset($param);
$param["from"] = $origin_from;
$param["to"] = $origin_to;
$param["member_seqno"] = $seqno;
$param["order_state"] = $state_arr["주문취소"];
$param["type"] = "";
$param["date_from"] = $origin_from;
$param["date_to"] = $origin_to;
//총매출액, 에누리, 순매출액
$p_rs = $dao->selectTransactionPrice($conn, $param);
//$p_re2 = $dao->selectTransactionPrice2($conn, $param);
//입금액, 일일잔액
$d_rs = $dao->selectDepoBalancePrice($conn, $param);
$param["type"] = "BALANCE";
$b_rs = $dao->selectDepoBalancePrice($conn, $param);
$info = $incomeDAO->selectByDayIncomeList($conn, $param);
$t_rs = $incomeDAO->selectPrepayPriceUntilDate($conn, $info->fields["id"], $info->fields["date_range"]);
$fields = $t_rs->fields;
//$b_rs2 = $dao->selectDepoBalancePrice2($conn, $param);

$total_sell_price = $p_rs->fields["sell_price"];
$total_sale_price = $p_rs->fields["adjust_price"];
$total_sell_price2 = $p_rs->fields["sell_price2"];
$total_pay_price  = $p_rs->fields["pay_price"];
$total_depo_price = $d_rs->fields["depo_price"];
//$total_prepay_bal = $b_rs->fields["prepay_bal"];
$total_prepay_bal = intval($fields[0]);

if($total_sell_price2 < 0){
    $total_sell_price2 =  abs($total_sell_price2);
}else{
    $total_sell_price2 =  $total_sell_price2 * (-1);
}

$date = explode("-" , $origin_from);

unset($p_rs);
unset($d_rs);
unset($b_rs);

// 고정값 부분 입력
$sheet->setCellValue("D4", $date_str); // 상단날짜
$sheet->setCellValue("O5", $repre_num); // 전화번호
//$sheet->setCellValue("O6", $param[""]); // 팩스번호

$sheet->setCellValue("E8", $licensee_num); // 공급자 등록번호
$sheet->setCellValue("E9", $sell_site); // 공급자 상호
$sheet->setCellValue("H9", $repre_name); // 공급자 대표자명
//$sheet->setCellValue("E10", $addr . ' ' . $addr_detail); // 공급자 업장주소
$sheet->setCellValue("E10", $addr); // 공급자 업장주소
//$sheet->setCellValue("E11", $bc); // 공급자 업태
//$sheet->setCellValue("H11", $tob); // 공급자 종목

$sheet->setCellValue("K8", $ed_licensee_num); // 공급받는자 등록번호
$sheet->setCellValue("K9", $ed_sell_site); // 공급받는자 상호
$sheet->setCellValue("P9", $ed_repre_name); // 공급받는자 대표자명
$sheet->setCellValue("K10", $ed_addr . ' ' . $ed_addr_detail); // 공급받는자 업장주소
$sheet->setCellValue("K11", $ed_bc); // 공급받는자 업태
$sheet->setCellValue("P11", $ed_tob); // 공급받는자 종목

$sheet->setCellValue("D12", $date_str); // 중단날짜
$sheet->setCellValue("B14", $total_pay_price + $total_sell_price2); // 총매출액
$sheet->setCellValue("F14", $total_sale_price); // 에누리
$sheet->setCellValue("I14", ($total_pay_price + $total_sell_price2) - $total_sale_price ); // 순매출액
$sheet->setCellValue("M14", $total_depo_price); // 입금액
$sheet->setCellValue("P14", $total_prepay_bal); // 총잔액
setCellNumberFormatting($sheet, "B12:P14");

// 시작일에서 끝일까지 하루단위로 끊어서 검색
//$summary_str = "총매출액 : %s원/ 에누리 : %s원/ 순매출액 : %s원/ 입금액 : %s원/ 일일잔액 : %s원";
$summary_str = "총매출액 : %s원/ 에누리 : %s원/ 순매출액 : %s원/ 입금액 : %s원";
$row_pos = 16; // 일별 요약 셀 시작위치

$int_to = intval(str_replace('-', '', $origin_to));
while(1) {
    $from = date("Y-m-d",
                 mktime(0, 0, 0, $date[1], $date[2] + $i++, $date[0])); 
    $int_from = intval(str_replace('-', '', $from));

    if ($int_from > $int_to) {
        break;
    }

    unset($param);
    $param["from"] = $from;
    $param["to"] = $from;
    $param["member_seqno"] = $seqno;
    $param["order_state"] = $state_arr["주문취소"];
 
    $param["type"] = "";

    // 내역 리스트
    $rs = $dao->selectTransactionalInfoList($conn, $param);
    $rs2 = $dao->selectTransactionalInfoList2($conn, $param);
    if ($rs->EOF) {
        continue;
    }

    //! 일별 요약 먼저 입력
    // 총매출액, 에누리, 순매출액
    $p_rs = $dao->selectTransactionPrice($conn, $param);
    //$p_rs2 = $dao->selectTransactionPrice2($conn, $param);
    // 일일잔액
    $d_rs = $dao->selectDepoBalancePrice($conn, $param);
    // 입금액
    $param["type"] = "BALANCE";
    $b_rs = $dao->selectDepoBalancePrice($conn, $param);

    $sell_price = $p_rs->fields["sell_price"];
    $sell_price2 = $p_rs->fields["sell_price2"];
    $sale_price = $p_rs->fields["adjust_price"];    
    $pay_price  = $p_rs->fields["pay_price"];
    $depo_price = $d_rs->fields["depo_price"];
    $prepay_bal = $b_rs->fields["prepay_bal"];

    if($sell_price2 < 0){
        $sell_price2 =  abs($sell_price2);
    }else{
        $sell_price2 =  $sell_price2 * (-1);
    }

    $range = 'B' . $row_pos . ':Q' . $row_pos;
    $sheet->mergeCells($range);

    $summary_cell = 'B' . $row_pos++;
    $summary = sprintf($summary_str, number_format($pay_price+$sell_price2)
                                   , number_format($sale_price)
                                   , number_format(($pay_price+$sell_price2)-$sale_price)
                                   , number_format($depo_price));
                                   //, number_format($prepay_bal));
    $sheet->setCellValue($summary_cell, $summary);
    setCellHAlign($sheet,
                  $summary_cell,
                  PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    setCellFontSize($sheet, $summary_cell);

    //! 일별 상세내역 입력
    // 일별요약 제목
    $range = 'B' . $row_pos . ':' . 'Q' . $row_pos;
    setCellVAlign($sheet, $range);
    setCellFontSize($sheet, $range);

    $sheet->mergeCells('B' . $row_pos . ':C' . $row_pos);
    $sheet->mergeCells('F' . $row_pos . ':I' . $row_pos);
    $sheet->mergeCells('L' . $row_pos . ':M' . $row_pos);
    $sheet->mergeCells('N' . $row_pos . ':O' . $row_pos);
    $sheet->mergeCells('P' . $row_pos . ':Q' . $row_pos);

    setCellBorder($sheet, 'B' . $row_pos . ':C' . $row_pos);
    setCellBorder($sheet, 'D' . $row_pos);
    setCellBorder($sheet, 'E' . $row_pos);
    setCellBorder($sheet, 'F' . $row_pos . ':I' . $row_pos);
    setCellBorder($sheet, 'J' . $row_pos);
    setCellBorder($sheet, 'K' . $row_pos);
    setCellBorder($sheet, 'L' . $row_pos . ':M' . $row_pos);
    setCellBorder($sheet, 'N' . $row_pos . ':O' . $row_pos);
    setCellBorder($sheet, 'P' . $row_pos . ':Q' . $row_pos);

    setCellHAlign($sheet,
                  $range,
                  PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->setCellValue('B' . $row_pos, "No");
    $sheet->setCellValue('D' . $row_pos, "일자");
    $sheet->setCellValue('E' . $row_pos, "제작물내용");
    $sheet->setCellValue('F' . $row_pos, "재질 및 규격");
    $sheet->setCellValue('J' . $row_pos, "수량");
    $sheet->setCellValue('K' . $row_pos, "건");
    $sheet->setCellValue('L' . $row_pos, "총매출");
    $sheet->setCellValue('N' . $row_pos, "에누리");
    $sheet->setCellValue('P' . $row_pos, "순매출액");

    $row_pos++;

    // 일별요약 내용
    $j = 1;
    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $range = 'B' . $row_pos . ':' . 'Q' . $row_pos;
        setCellVAlign($sheet, $range);
        setCellFontSize($sheet, $range);

        $sheet->mergeCells('B' . $row_pos . ':' . 'C' . $row_pos);
        $sheet->mergeCells('F' . $row_pos . ':' . 'I' . $row_pos);
        $sheet->mergeCells('L' . $row_pos . ':' . 'M' . $row_pos);
        $sheet->mergeCells('N' . $row_pos . ':' . 'O' . $row_pos);
        $sheet->mergeCells('P' . $row_pos . ':' . 'Q' . $row_pos);

        setCellBorder($sheet, 'B' . $row_pos . ':' . 'C' . $row_pos);
        setCellBorder($sheet, 'D' . $row_pos);
        setCellBorder($sheet, 'E' . $row_pos);
        setCellBorder($sheet, 'F' . $row_pos . ':' . 'I' . $row_pos);
        setCellBorder($sheet, 'J' . $row_pos);
        setCellBorder($sheet, 'K' . $row_pos);
        setCellBorder($sheet, 'L' . $row_pos . ':' . 'M' . $row_pos);
        setCellBorder($sheet, 'N' . $row_pos . ':' . 'O' . $row_pos);
        setCellBorder($sheet, 'P' . $row_pos . ':' . 'Q' . $row_pos);

        $no_cell     = 'B' . $row_pos;
        $date_cell   = 'D' . $row_pos;
        $title_cell  = 'E' . $row_pos;
        $detail_cell = 'F' . $row_pos;
        $amt_cell    = 'J' . $row_pos;
        $count_cell  = 'K' . $row_pos;
        $sell_cell   = 'L' . $row_pos;
        $sale_cell   = 'N' . $row_pos;
        $pay_cell    = 'P' . $row_pos;

        // 택배비 일경우 수량에 공란으로 표기 처리 
        if(number_format(doubleval($fields["amt"])) == 0 ){
            $amt_cell2 = "";
        }else{
            $amt_cell2 = number_format(doubleval($fields["amt"])) . $fields["amt_unit_dvs"];
        }
        

        $sheet->setCellValue($no_cell, $j++);
        $sheet->setCellValue($date_cell, substr($fields["deal_date"], 5, 5));
        $sheet->setCellValue($title_cell, $fields["title"]);
        $sheet->setCellValue($detail_cell, $fields["order_detail"]);
        $sheet->setCellValue($amt_cell, $amt_cell2 );
        $sheet->setCellValue($count_cell, $fields["count"]);
        $sheet->setCellValue($sell_cell, $fields["sell_price"]);
        $sheet->setCellValue($sale_cell, $fields["sale_price"]);
        $sheet->setCellValue($pay_cell, $fields["pay_price"]);
        setCellNumberFormatting($sheet, 'L' . $row_pos . ':P' . $row_pos);

        setCellHAlign($sheet,
                      $no_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        setCellHAlign($sheet,
                      $date_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        setCellHAlign($sheet,
                      $title_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        setCellHAlign($sheet,
                      $detail_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        setCellHAlign($sheet,
                      $amt_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        setCellHAlign($sheet,
                      $count_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        setCellHAlign($sheet,
                      $sell_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        setCellHAlign($sheet,
                      $sale_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        setCellHAlign($sheet,
                      $pay_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        $row_pos++;
        $rs->MoveNext();
    }

   while ($rs2 && !$rs2->EOF) {
        $fields = $rs2->fields;

        $range = 'B' . $row_pos . ':' . 'Q' . $row_pos;
        setCellVAlign($sheet, $range);
        setCellFontSize($sheet, $range);

        $sheet->mergeCells('B' . $row_pos . ':' . 'C' . $row_pos);
        $sheet->mergeCells('F' . $row_pos . ':' . 'I' . $row_pos);
        $sheet->mergeCells('L' . $row_pos . ':' . 'M' . $row_pos);
        $sheet->mergeCells('N' . $row_pos . ':' . 'O' . $row_pos);
        $sheet->mergeCells('P' . $row_pos . ':' . 'Q' . $row_pos);

        setCellBorder($sheet, 'B' . $row_pos . ':' . 'C' . $row_pos);
        setCellBorder($sheet, 'D' . $row_pos);
        setCellBorder($sheet, 'E' . $row_pos);
        setCellBorder($sheet, 'F' . $row_pos . ':' . 'I' . $row_pos);
        setCellBorder($sheet, 'J' . $row_pos);
        setCellBorder($sheet, 'K' . $row_pos);
        setCellBorder($sheet, 'L' . $row_pos . ':' . 'M' . $row_pos);
        setCellBorder($sheet, 'N' . $row_pos . ':' . 'O' . $row_pos);
        setCellBorder($sheet, 'P' . $row_pos . ':' . 'Q' . $row_pos);

        $no_cell     = 'B' . $row_pos;
        $date_cell   = 'D' . $row_pos;
        $title_cell  = 'E' . $row_pos;
        $detail_cell = 'F' . $row_pos;
        $amt_cell    = 'J' . $row_pos;
        $count_cell  = 'K' . $row_pos;
        $sell_cell   = 'L' . $row_pos;
        $sale_cell   = 'N' . $row_pos;
        $pay_cell    = 'P' . $row_pos;

        if($fields["sell_price"] < 0){ 
            $fields["sell_price"] = abs($fields["sell_price"]);
        }else{
            $fields["sell_price"] = "-".$fields["sell_price"];
        }


        $sheet->setCellValue($no_cell, $j++);
        $sheet->setCellValue($date_cell, substr($fields["deal_date"], 5, 5));
        $sheet->setCellValue($title_cell, $fields["cont"]);
        $sheet->setCellValue($detail_cell, $fields["order_detail"]);
        $sheet->setCellValue($amt_cell, number_format(doubleval($fields["amt"])) . $fields["amt_unit_dvs"]);
        $sheet->setCellValue($count_cell, $fields["count"]);
        $sheet->setCellValue($sell_cell, $fields["sell_price"]);
        $sheet->setCellValue($sale_cell, $fields["adjust_price"]);
        $sheet->setCellValue($pay_cell, $fields["pay_price"]);
        setCellNumberFormatting($sheet, 'L' . $row_pos . ':P' . $row_pos);

        setCellHAlign($sheet,
                      $no_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        setCellHAlign($sheet,
                      $date_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        setCellHAlign($sheet,
                      $title_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        setCellHAlign($sheet,
                      $detail_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        setCellHAlign($sheet,
                      $amt_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        setCellHAlign($sheet,
                      $count_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        setCellHAlign($sheet,
                      $sell_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        setCellHAlign($sheet,
                      $sale_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        setCellHAlign($sheet,
                      $pay_cell,
                      PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        $row_pos++;
        $rs2->MoveNext();
    } 
}

$save_name = uniqid();

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save($base_path . $save_name . ".xlsx");

$objPHPExcel->disconnectWorksheets();
unset($objPHPExcel);

echo $save_name;

$conn->Close();
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

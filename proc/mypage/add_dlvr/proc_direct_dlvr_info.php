<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberDlvrDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/common_lib/DateUtil.inc");

//define("DIRECT_PRICE", 55000);

if (!$is_login) {
    echo "<script>alert('로그인이 필요합니다.'); return false;</script>";
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao      = new MemberDlvrDAO();
$info_dao = new MemberInfoDAO();
$util     = new CommonUtil();
$DateUtil = new DateUtil();

$member_seqno = $fb->session("member_seqno");
//$conn->debug = 1;

$success = "1";
$msg = "월배송 신청이 완료되었습니다.";

$start_period = date("Y-m-d");
$ymd_arr = explode('-', $start_period);

$year = $ymd_arr[0];
if ($ymd_arr[2] > 0 && $ymd_arr[2] < 25) {
    // 직전달 첫날 ~ 말일까지의 매출 확인 
    $last_month = $ymd_arr[1] - 1;
    if ($last_month < 1) {
        $last_month = 12;
        $year = $year - 1;
    }
    $last_month_last_day = $DateUtil->getLastDay($year, $last_month);
    $month_start = $year ."-". $last_month ."-01";
    $month_end   = $year ."-". $last_month ."-". $last_month_last_day;

// 25일부터는 이번달 매출을 기준(첫날부터 오늘까지)으로 잡는다.
} else {
    $month_start = $year ."-". $ymd_arr[1] ."-01";    
    $month_end   = $start_period;   
}

$param = array();
$param["member_seqno"] = $member_seqno;
$param["from"] = $month_start;
$param["to"]   = $month_end;

$fields = $info_dao->selectMemberMonthlySales($conn, $param);

$net_sales      = intval($fields["net"]);
$card_net_sales = intval($fields["card_net"]);

$sum_net_sales  = $net_sales + $card_net_sales;

if ($sum_net_sales < 330000) {
    $direct_price = 55000;
} else {
    $direct_price = 0;
}
unset($param);

// 직배 사용중인지 확인
$fields = $dao->selectDirectDlvrReq($conn, $member_seqno)->fields;
if (!empty($fields)) {
    if ($start_period >= $fields["start_period"] && $start_period <= $fields["end_period"]) {
        $success = "-1";
        $msg = "이미 월배송을 사용하고 계십니다.";
        goto END;
    }
}

/* 180719 로직변경으로 인한 주석처리
//#0 이미 직배 사용중인지 연장인지 확인
// 만료일 -5일부터 연장신청 가능
$next_month_flag = false;
$fields = $dao->selectDirectDlvrReq($conn, $member_seqno)->fields;

// 기존 데이터가 있는경우
if (!empty($fields)) {
    $end_period = explode('-', $fields["end_period"]);

    $start_stamp = mktime(0, 0, 0, $ymd_arr[1], $ymd_arr[2], $ymd_arr[0]);
    $end_stamp = mktime(0, 0, 0, $end_period[1], $end_period[2], $end_period[0]);

    $gap = ((($end_stamp - $start_stamp) / 60) / 60) / 24;

    // $gap이 0보다 작으면 이용기간 종료 이후
    if ($gap > 0) {
        // 남은 기간이 5일 이상일 경우
        if ($gap > 5) {
            $success = "-1";
            $msg = "이미 월배송을 사용하고 계십니다.";
            goto END;
        }

        // 5일 이하는 다음달로 추가
        $next_month_flag = true;
    }
}
*/

$conn->StartTrans();

//#1 선입금 체크
$prepay_info = $dao->selectMemberPrepayLock($conn, $member_seqno);
$prepay_money = intval($prepay_info["prepay_price_money"]);
$prepay_card  = intval($prepay_info["prepay_price_card"]);

$prepay_price = $prepay_money + $prepay_card;

if ($prepay_price < $direct_price) {
    $success = "-1";
    $msg = "잔액이 부족합니다.";
    goto END;
}

$pay_money = $direct_price;
$pay_card  = 0;
if ($prepay_money < $direct_price) {
    $pay_money = $prepay_money;
    $pay_card  = $direct_price - $prepay_money;
}

//#2 결제내역 입력
$param = [];
$param["member_seqno"]    = $member_seqno;
$param["order_num"]       = '';
$param["dvs"]             = "매출증가";
$param["sell_price"]      = $direct_price;
$param["sale_price"]      = '0';
$param["pay_price"]       = $pay_money;
$param["card_pay_price"]  = $pay_card;
$param["depo_price"]      = '0';
$param["card_depo_price"] = '0';
$param["exist_prepay"]    = $prepay_price;
$param["prepay_bal"]      = $prepay_price - $direct_price;
$param["state"]           = '';
$param["deal_num"]        = '';
$param["order_cancel_yn"] = '';
$param["pay_year"]        = date('Y');
$param["pay_mon"]         = date('m');
$param["prepay_use_yn"]   = 'Y';
$param["input_typ"]       = $util->selectInputType("제품구입");
$param["cont"]            = "월배송 신청";

$dao->insertMemberPayHistory($conn, $param);

//#3 회원 선입금액 수정
unset($param);
$param["prepay_price_money"] = $pay_money * -1;
$param["prepay_price_card"]  = $pay_card * -1;
$param["member_seqno"] = $member_seqno;

$dao->updateMemberPrepay($conn, $param);

//#4 회원 직배요청 테이블에 값 입력
$frm = $fb->getForm();

$zipcode     = $frm["zipcode"];
$addr        = $frm["addr"];
$addr_detail = $frm["addr_detail"];
$add_info = $frm["add_info"];
$tel_num  = implode('-', [$frm["tel_num1"], $frm["tel_num2"], $frm["tel_num3"]]);
$cell_num = implode('-', [$frm["cell_num1"], $frm["cell_num2"], $frm["cell_num3"]]);

$end_period = date("Y-m-t");
if ($next_month_flag) {
    $end_period = date("Y-m-t", mktime(0, 0, 0,
                                       intval($ymd_arr[1]) + 1,
                                       1, $ymd_arr[0]));
}

unset($param);
$param["member_seqno"] = $member_seqno;
$param["direct_dlvr_info_seqno"] = $direct_dlvr_info_seqno;
$param["zipcode"]     = $zipcode;
$param["addr"]        = $addr;
$param["addr_detail"] = $addr_detail;
$param["add_info"] = $add_info;
$param["start_period"] = $start_period;
$param["end_period"]   = $end_period;
$param["tel_num"]  = $tel_num;
$param["cell_num"] = $cell_num;

$dao->insertDirectDlvrReq($conn, $param);

//#5 회원 직배여부 수정
unset($param);
$param["table"] = "member";
$param["col"]["direct_dlvr_yn"] = 'Y';
$param["prk"]    = "member_seqno";
$param["prkVal"] = $member_seqno;
$dao->updateData($conn, $param);

//$conn->FailTrans();
//$conn->RollbackTrans();

if ($conn->HasFailedTrans()) {
    $success = "-1";
    $msg = "월배송 신청에 실패했습니다.";
    $conn->FailTrans();
    $conn->RollbackTrans();
    goto END;
}

END :
    $conn->CompleteTrans();
    $conn->Close();

    $json = "{\"success\" : \"%s\", \"msg\" : \"%s\"}";
    $json = sprintf($json, $success, $msg);
    echo $json;

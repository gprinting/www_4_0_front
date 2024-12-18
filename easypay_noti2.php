<?php

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/virt_ba_mng/VirtBaListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/pageLib.inc");
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$virtDAO = new VirtBaListDAO();


$fb = new FormBean();

@extract($_GET);
@extract($_POST);
@extract($_SERVER);


$TEMP_IP = getenv("REMOTE_ADDR");
$PG_IP = substr($TEMP_IP, 0, 10);

if ($PG_IP == "203.238.37" || $PG_IP == "39.115.212" || $PG_IP == "183.109.71") {  //PG에서 보냈는지 IP로 체크
    $msg_id = $msg_id;             //메세지 타입
    $no_tid = $no_tid;             //거래번호
    $no_oid = $no_oid;             //상점 주문번호
    $id_merchant = $id_merchant;   //상점 아이디
    $cd_bank = $cd_bank;           //거래 발생 기관 코드
    $cd_deal = $cd_deal;           //취급 기관 코드
    $dt_trans = $dt_trans;         //거래 일자
    $tm_trans = $tm_trans;         //거래 시간
    $no_msgseq = $no_msgseq;       //전문 일련 번호
    $cd_joinorg = $cd_joinorg;     //제휴 기관 코드

    $dt_transbase = $dt_transbase; //거래 기준 일자
    $no_transeq = $no_transeq;     //거래 일련 번호
    $type_msg = $type_msg;         //거래 구분 코드
    $cl_close = $cl_close;         //마감 구분코드
    $cl_kor = $cl_kor;             //한글 구분 코드
    $no_msgmanage = $no_msgmanage; //전문 관리 번호
    $no_vacct = $no_vacct;         //가상계좌번호
    $amt_input = $amt_input;       //입금금액
    $amt_check = $amt_check;       //미결제 타점권 금액
    $nm_inputbank = $nm_inputbank; //입금 금융기관명
    $nm_input = $nm_input;         //입금 의뢰인
    $dt_inputstd = $dt_inputstd;   //입금 기준 일자
    $dt_calculstd = $dt_calculstd; //정산 기준 일자
    $flg_close = $flg_close;       //마감 전화
    //가상계좌채번시 현금영수증 자동발급신청시에만 전달
    $dt_cshr = $dt_cshr;       //현금영수증 발급일자
    $tm_cshr = $tm_cshr;       //현금영수증 발급시간
    $no_cshr_appl = $no_cshr_appl;  //현금영수증 발급번호
    $no_cshr_tid = $no_cshr_tid;   //현금영수증 발급TID

    $insert_param = array();
    $insert_param["deal_date"] = $tm_trans;
    $insert_param["dvs"] = "입금증가";
    $insert_param["sell_price"] = 0;
    $insert_param["depo_price"] = $amt_input;
    $insert_param["deal_num"] = $no_tid;
    $insert_param["pay_year"] = substr($tm_trans,0,4);
    $insert_param["pay_mon"] = substr($tm_trans,4,2);
    $insert_param["prepay_use_yn"] = "N";
    $insert_param["cont"] = "가상계좌 입금[" . number_format($amt_input) . "원]";
    $insert_param["depo_bank_code"] = $cd_bank;
    $insert_param["depo_bank_name"] = iconv("EUC-KR", "UTF-8", $nm_inputbank);
    $insert_param["account_no"] = $no_vacct;
    $insert_param["bank_nm"] = getBankName($cd_bank);
    $insert_param["member_seqno"] = $virtDAO->selectMemberByBaNum($conn, $insert_param)->fields["member_seqno"];

    $cnt_rs = $virtDAO->selectDealNum($conn, $insert_param);
//
    if($cnt_rs->fields["cnt"] == 0) {
        $rs = $virtDAO->insertVirtAmount($conn, $insert_param);
    }


    echo "OK";                        // 절대로 지우지마세요
//      }
//*************************************************************************************
}

function getBankName($bank_code) {
    switch ($bank_code) {
        case "003" :
            return "기업은행";
        case "004" :
            return "국민은행";
        case "011" :
            return "농협중앙회";
        case "020" :
        case "022" :
            return "우리은행";
        case "023" :
            return "SC제일은행";
        case "021" :
        case "026" :
        case "088" :
            return "신한은행";
        case "032" :
            return "부산은행";
        case "071" :
            return "우체국";
        case "081" :
        case "082" :
            return "하나은행";
        default :
            return "";
    }
}
?>

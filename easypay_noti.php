<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-10-28
 * Time: 오후 2:58
 */

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

$insert_param = array();
$insert_param["deal_date"] = $fb->form("tran_date");
$insert_param["dvs"] = "입금증가";
$insert_param["sell_price"] = 0;
$insert_param["depo_price"] = $fb->form("amount");
$insert_param["deal_num"] = $fb->form("cno");
$insert_param["pay_year"] = substr($insert_param["deal_date"],0,4);
$insert_param["pay_mon"] = substr($insert_param["deal_date"],4,2);
$insert_param["prepay_use_yn"] = "N";
$insert_param["cont"] = "가상계좌 입금[" . number_format($fb->form("amount")) . "원]";
$insert_param["depo_bank_code"] = $fb->form("depo_bkcd");
$insert_param["depo_bank_name"] = iconv("EUC-KR", "UTF-8", $fb->form("depo_bknm"));
$insert_param["account_no"] = $fb->form("account_no");
$insert_param["bank_nm"] = getBankName($fb->form("bank_cd"));
$insert_param["member_seqno"] = $virtDAO->selectMemberByBaNum($conn, $insert_param)->fields["member_seqno"];

$wpath = "./tmp/virt_".date("Y_m_d") . ".log";

$cnt_rs = $virtDAO->selectDealNum($conn, $insert_param);
//
if($cnt_rs->fields["cnt"] == 0) {
    $rs = $virtDAO->insertVirtAmount($conn, $insert_param);
    FileWrite($wpath, "","a+");
}

$path = "./test.log";
$date = date("Y_m_d H:i:s");
FileWrite($path, $date."|","a+");


echo "res_cd=0000^res_msg=SUCCESS";


function FileWrite($fileName, $content, $mode) {
    if (!$fileName || !$content) {
        echo "파일명 또는 내용을 입력하세요!!";
    } else {
        $fp = @fopen($fileName, $mode);
        if(!$fp) echo "파일을 여는데 실패했습니다. 다시 확인하시길 바랍니다.";
        @fwrite($fp, $content);
        @fclose($fp);
    }
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
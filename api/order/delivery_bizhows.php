<?

define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/CompleteDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new CompleteDAO();
$fb = new FormBean();
$fb = $fb->getForm();

unset($param);
$param["tsrs_dvs"]           = "송신";
$param["name"]               = "구로비즈하우스";
$param["recei"]              = "구로비즈하우스";
$param["tel_num"]            = $fb["or_hp"][0] . "-" . $fb["or_hp"][1] . "-" . $fb["or_hp"][2];
$param["cell_num"]           = $fb["or_hp"][0] . "-" . $fb["or_hp"][1] . "-" . $fb["or_hp"][2];
//$param["tel_num"]            = $fb["or_hp"][0] . "-" . $fb["or_hp"][1] . "-" . $fb["or_hp"][2];
$param["zipcode"]            = $fb["or_zip1"] . "-" . $fb["or_zip2"];
$param["sms_yn"]             = "N";
$param["dlvr_way"]           = "01";
$param["dlvr_sum_way"]       = "01";
$param["addr"]               = $fb["or_addr1"];
$param["addr_detail"]        = $fb["or_addr2"];
$param["dlvr_price"]         = 0;
$param["invo_cpn"]           = '';
$param["order_common_seqno"] = $fb["Secure_Id"][0];
$param["bun_dlvr_order_num"] = $fb["BunGroupNum"];
$param["bun_group"]          = $fb["BunGroup"];
$param["lump_count"]         = 0;
$param["dlvr_req"]           = '';
$proc_ret = $dao->insertOrderDlvr($conn, $param);


//! 주문 배송 받는 사람 정보 insert
unset($param);

$add2 = explode("고객요청[", $fb["re_addr2"]);
$param["tsrs_dvs"]           = "수신";
$param["name"]               = $fb["re_name"];
$param["recei"]              = $fb["re_name"];
$param["tel_num"]            = $fb["re_tel"][0] . "-" . $fb["re_tel"][1] . "-" . $fb["re_tel"][2];;
$param["cell_num"]           = $fb["re_hp"][0] . "-" . $fb["re_hp"][1] . "-" . $fb["re_hp"][2];;
$param["zipcode"]            = $fb["re_zip1"] . "-" . $fb["re_zip2"];
$param["sms_yn"]             = "N";
$param["dlvr_way"]           = "01";
$param["dlvr_sum_way"]       = "01";
$param["addr"]               = $fb["re_addr1"];
$param["addr_detail"]        = $add2[0];
$param["dlvr_price"]         =  $fb["DlvrPrice"];
$param["invo_cpn"]           = '롯데택배';
$param["order_common_seqno"] = $fb["Secure_Id"][0];
$param["bun_dlvr_order_num"] = $fb["BunGroupNum"];
$param["bun_group"]          = $fb["BunGroup"];
$param["lump_count"]         = 0;
$param["dlvr_req"]           = count($add2) == 1 ? "" : "고객요청[" . $add2[1];

$proc_ret = $dao->insertOrderDlvr($conn, $param);



$param1 = array();
$param1["order_common_seqno"] = $fb["Secure_Id"][0];

$param = array();
$rs = $dao->selectOrderInfo($conn, $param1);
while ($rs && !$rs->EOF) {
    $member_seqno = $rs->fields["member_seqno"];
    $order_num = $rs->fields["order_num"];
    $title = $rs->fields["title"];
    $rs->moveNext();
}


$param["member_seqno"] = $member_seqno;
$param["order_num"]    = $order_num;
$param["exist_prepay"] = '0';
$param["cur_date"]     = date('Y-m-d');
$param["dvs"]             = "배송비";
$param["sell_price"]      = $fb["DlvrPrice"];
$param["sale_price"]      = '0';
$param["pay_price"]       = $fb["DlvrPrice"];
$param["card_pay_price"]  = '0';
$param["depo_price"]      = '0';
$param["card_depo_price"] = '0';
$param["input_typ"]       = '000';
$param["prepay_bal"]      = '0';
$param["state"]           = '';
$param["deal_num"]        = '';
$param["order_cancel_yn"] = 'N';
$param["pay_year"]        = date('Y');
$param["pay_mon"]         = date('m');
$param["cont"]            = $title . '(' . $order_num . ") 배송비";
$param["prepay_use_yn"]   = 'Y';
$param["public_dvs"]         = '세금계산서';
$param["public_state"]         = '대기';
$param["dvs_detail"]   = '';
$dao->insertMemberPayHistory($conn, $param);

$state_code = "200";

$result = array();
$result["code"] = $state_code;
$result["message"] = "ok";

$ret["result"] = $result;
$ret["data"] = $rs;

echo json_encode($ret);


$conn->Close();
?>
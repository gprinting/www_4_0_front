<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/OrderMngUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$dao = new FrontCommonDAO();

$url = "https://pgapi.easypay.co.kr/api/trades/approval";

$order_no   = "card_%s_%s";
$shopTransactionId = sprintf($order_no, $_POST["shopOrderNo"], uniqid());

$rs = $dao->selectChannelInfo(
    $conn, ["sell_site" => $_SERVER["SELL_SITE"]]
);

$mallId = $rs["pay_easypay_recharge"];


$headers = array( "content-type: application/json" );
$post_data = array(
    "mallId"         => $mallId,          // <!-- KICC에서 발급한 상점ID -->
    "shopTransactionId"         => $shopTransactionId,          // <!-- KICC에서 발급한 상점ID -->
    "authorizationId"         => $_POST["authorizationId"],          // <!-- 결제수단 코드 -->
    "shopOrderNo"         => $_POST["shopOrderNo"],          // <!-- 통화코드 -->
    "approvalReqDate"         => date('Ymd'),          // <!-- 결제요청 금액 -->
);


$ch  = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));


$data = curl_exec($ch);
/*
for ($i = 0; $i < $count; $i++) {
    $post_data["EP_order_no"] = sprintf($order_no, $bank_code, uniqid());
    $post_data["EP_product_nm"] = sprintf($product_nc, $bank_name);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    $data = curl_exec($ch);

    var_dump($data);
}
*/
$decoded = json_decode($data);

curl_close($ch);

if ( $decoded->resCd == "0000" ) {
    // 카드일 경우에는 바로 성공이 떨어지므로
    // 세션 및 DB에 저장되어있는 선입금액 수정
    // 회원_결제_내역에 입력
    $connectionPool = new ConnectionPool();
    $conn = $connectionPool->getPooledConnection();
    $dao = new FrontCommonDAO();
    $util = new OrderMngUtil();

    $bDBProc = true;
    $debug_code = null;
    $debug_msg  = null;

    if ($decoded->paymentInfo->payMethodTypeCode === "11") {
        $member_seqno = $_SESSION["member_seqno"];

        $param = [];
        $param["seqno"] = $member_seqno;

        $conn->StartTrans();
        $prepay_info = $dao->selectMemberPrepayLock($conn, $member_seqno);
        $prepay_price_money = intval($prepay_info["prepay_price_money"]);
        $prepay_price_card  = intval($prepay_info["prepay_price_card"]);
        $prepay_price = $prepay_price_money + $prepay_price_card;

        unset($param);
        $param["prepay_price_money"] = $decoded->amount;
        $param["prepay_price_card"] = 0;
        $param["member_seqno"] = $member_seqno;
        $ret = $dao->updateMemberPrepay($conn, $param);


        unset($param);
        $param["member_seqno"] = $member_seqno;
        $param["order_num"]    = '';
        $param["exist_prepay"] = $prepay_price_money;

        $param["dvs"]             = "입금증가";
        $param["sell_price"]      = '0';
        $param["sale_price"]      = '0';
        $param["pay_price"]       = '0';
        $param["card_pay_price"]  = '0';
        $param["depo_price"]      = '0';
        $param["card_depo_price"] = $decoded->amount;
        $param["input_typ"]       = $util->selectInputType("카드");
        $param["prepay_bal"]      = intval($prepay_price_money) + intval($decoded->amount);
        $param["state"]           = '';
        $param["deal_num"]        = $decoded->pgCno;
        $param["order_cancel_yn"] = 'N';
        $param["pay_year"]        = date('Y');
        $param["pay_mon"]         = date('m');
        $param["cont"]            = "선입금 카드충전";
        $param["prepay_use_yn"]   = 'N';
        $param["public_dvs"]   = '미발행';
        $param["public_state"]   = '';
        $param["dvs_detail"]   = '';

        $ret = $dao->insertMemberPayHistory($conn, $param);

        $pay_seqno = $conn->Insert_ID("member_pay_history");

        unset($param);
        $param["member_pay_history_seqno"] = $pay_seqno;
        $param["card_cpn"]  = $decoded->paymentInfo->cardInfo->acquirerName;
        $param["aprvl_num"] = $decoded->paymentInfo->approvalNo;
        $param["card_num"] = $decoded->paymentInfo->cardInfo->cardNo;

        $ret = $dao->insertMemberPayHistoryCard($conn, $param);
        $conn->CompleteTrans();

    }

    if (!empty($debug_code)) {
        $res_cd = $debug_code;
    }
    if (!empty($debug_code)) {
        $res_msg = $debug_msg;
    }
}
?>
<html>
<meta name="robots" content="noindex, nofollow">
<body onload="window.parent.goCharge();">
<input type="hidden" id="res_cd"           name="res_cd"          value="<?=$decoded->resCd?>">              <!-- 결과코드 //-->
<input type="hidden" id="res_msg"          name="res_msg"         value="<?=$decoded->resMsg?>">             <!-- 결과메시지 //-->
<input type="hidden" id="cno"              name="cno"             value="<?=$decoded->pgCno?>">               <!-- PG거래번호 //-->
<input type="hidden" id="amount"           name="amount"          value="<?=$decoded->amount?>">            <!-- 총 결제금액 //-->
<input type="hidden" id="order_no"         name="order_no"        value="<?=$decoded->shopOrderNo?>">          <!-- 주문번호 //-->
<input type="hidden" id="auth_no"          name="auth_no"         value="<?=$decoded->paymentInfo->approvalNo?>">           <!-- 승인번호 //-->
<input type="hidden" id="tran_date"        name="tran_date"       value="<?=$decoded->transactionDate?>">         <!-- 승인일시 //-->
<input type="hidden" id="escrow_yn"        name="escrow_yn"       value="<?=$decoded->escrowUsed?>">         <!-- 에스크로 사용유무 //-->
<input type="hidden" id="complex_yn"       name="complex_yn"      value="N">        <!-- 복합결제 유무 //-->
<input type="hidden" id="stat_cd"          name="stat_cd"         value="<?=$decoded->statusCode?>">           <!-- 상태코드 //-->
<input type="hidden" id="stat_msg"         name="stat_msg"        value="<?=$decoded->statusMessage?>">          <!-- 상태메시지 //-->
<input type="hidden" id="pay_type"         name="pay_type"        value="<?=$decoded->paymentInfo->payMethodTypeCode?>">          <!-- 결제수단 //-->
<input type="hidden" id="mall_id"          name="mall_id"         value="<?=$decoded->mallId?>">           <!-- 가맹점 Mall ID //-->
<input type="hidden" id="card_num"          name="card_num"         value="<?=$decoded->paymentInfo->cardInfo->cardNo?>">           <!-- 카드번호 //-->
<input type="hidden" id="issuer_cd"        name="issuer_cd"       value="<?=$decoded->paymentInfo->cardInfo->issuerCode?>">         <!-- 발급사코드 //-->
<input type="hidden" id="issuer_nm"        name="issuer_nm"       value="<?=$decoded->paymentInfo->cardInfo->issuerName?>">         <!-- 발급사명 //-->
<input type="hidden" id="acquirer_cd"      name="acquirer_cd"     value="<?=$decoded->paymentInfo->cardInfo->acquirerCode?>">       <!-- 매입사코드 //-->
<input type="hidden" id="acquirer_nm"      name="acquirer_nm"     value="<?=$decoded->paymentInfo->cardInfo->acquirerName?>">       <!-- 매입사명 //-->
<input type="hidden" id="install_period"   name="install_period"  value="<?=$decoded->paymentInfo->cardInfo->installmentMonth?>">    <!-- 할부개월 //-->
<input type="hidden" id="noint"            name="noint"           value="<?=$decoded->paymentInfo->cardInfo->freeInstallmentTypeCode?>">             <!-- 무이자여부 //-->
<input type="hidden" id="join_no"          name="join_no"         value="">           <!-- 가맹점 번호 //-->
<input type="hidden" id="part_cancel_yn"   name="part_cancel_yn"  value="<?=$decoded->paymentInfo->cardInfo->partCancelUsed?>">    <!-- 부분취소 가능여부 //-->
<input type="hidden" id="card_gubun"       name="card_gubun"      value="<?=$decoded->paymentInfo->cardInfo->cardGubun?>">        <!-- 신용카드 종류 //-->
<input type="hidden" id="card_biz_gubun"   name="card_biz_gubun"  value="<?=$decoded->paymentInfo->cardInfo->cardBizGubun?>">    <!-- 신용카드 구분 //-->
<input type="hidden" id="cpon_flag"        name="cpon_flag"       value="">         <!-- 쿠폰사용유무 //-->
<input type="hidden" id="van_tid"          name="van_tid"         value="">           <!-- VAN Tid //-->
<input type="hidden" id="cc_expr_date"     name="cc_expr_date"    value="">      <!-- 신용카드 유효기간 //-->
<input type="hidden" id="bank_cd"          name="bank_cd"         value="<?=$decoded->paymentInfo->virtualAccountInfo->bankCode?>">           <!-- 은행코드 //-->
<input type="hidden" id="bank_nm"          name="bank_nm"         value="<?=$decoded->paymentInfo->virtualAccountInfo->bankName?>">           <!-- 은행명 //-->
<input type="hidden" id="account_no"       name="account_no"      value="<?=$decoded->paymentInfo->virtualAccountInfo->accountNo?>">        <!-- 계좌번호 //-->
<input type="hidden" id="deposit_nm"       name="deposit_nm"      value="<?=$decoded->paymentInfo->virtualAccountInfo->depositName?>">        <!-- 입금자명 //-->
<input type="hidden" id="expire_date"      name="expire_date"     value="<?=$decoded->paymentInfo->virtualAccountInfo->expiryDate?>">       <!-- 계좌사용만료일 //-->
<input type="hidden" id="cash_res_cd"      name="cash_res_cd"     value="<?=$decoded->paymentInfo->cashReceiptInfo->resCd?>">       <!-- 현금영수증 결과코드 //-->
<input type="hidden" id="cash_res_msg"     name="cash_res_msg"    value="<?=$decoded->paymentInfo->cashReceiptInfo->resMsg?>">      <!-- 현금영수증 결과메세지 //-->
<input type="hidden" id="cash_auth_no"     name="cash_auth_no"    value="<?=$decoded->paymentInfo->cashReceiptInfo->approvalNo?>">      <!-- 현금영수증 승인번호 //-->
<input type="hidden" id="cash_tran_date"   name="cash_tran_date"  value="<?=$decoded->paymentInfo->cashReceiptInfo->approvalDate?>">    <!-- 현금영수증 승인일시 //-->
<input type="hidden" id="cash_issue_type"  name="cash_issue_type" value="">   <!-- 현금영수증발행용도 //-->
<input type="hidden" id="cash_auth_type"   name="cash_auth_type"  value="">    <!-- 인증구분 //-->
<input type="hidden" id="cash_auth_value"  name="cash_auth_value" value="">   <!-- 인증번호 //-->
<input type="hidden" id="auth_id"          name="auth_id"         value="<?=$decoded->paymentInfo->mobInfo->authId?>">           <!-- PhoneID //-->
<input type="hidden" id="billid"           name="billid"          value="<?=$decoded->paymentInfo->mobInfo->billId?>">            <!-- 인증번호 //-->
<input type="hidden" id="mobile_no"        name="mobile_no"       value="<?=$decoded->paymentInfo->mobInfo->mobileNo?>">         <!-- 휴대폰번호 //-->
<input type="hidden" id="mob_ansim_yn"     name="mob_ansim_yn"    value="<?=$decoded->paymentInfo->mobInfo->mobileAnsimUsed?>">      <!-- 안심결제 사용유무 //-->
<input type="hidden" id="ars_no"           name="ars_no"          value="">            <!-- 전화번호 //-->
<input type="hidden" id="cp_cd"            name="cp_cd"           value="<?=$decoded->paymentInfo->cpCode?>">             <!-- 포인트사/쿠폰사 //-->
<input type="hidden" id="pnt_auth_no"      name="pnt_auth_no"     value="">       <!-- 포인트승인번호 //-->
<input type="hidden" id="pnt_tran_date"    name="pnt_tran_date"   value="">     <!-- 포인트승인일시 //-->
<input type="hidden" id="used_pnt"         name="used_pnt"        value="">          <!-- 사용포인트 //-->
<input type="hidden" id="remain_pnt"       name="remain_pnt"      value="">        <!-- 잔여한도 //-->
<input type="hidden" id="pay_pnt"          name="pay_pnt"         value="0">           <!-- 할인/발생포인트 //-->
<input type="hidden" id="accrue_pnt"       name="accrue_pnt"      value="0">        <!-- 누적포인트 //-->
<input type="hidden" id="deduct_pnt"       name="deduct_pnt"      value="0">        <!-- 총차감 포인트 //-->
<input type="hidden" id="payback_pnt"      name="payback_pnt"     value="0">       <!-- payback 포인트 //-->
<input type="hidden" id="cpon_auth_no"     name="cpon_auth_no"    value="">      <!-- 쿠폰승인번호 //-->
<input type="hidden" id="cpon_tran_date"   name="cpon_tran_date"  value="">    <!-- 쿠폰승인일시 //-->
<input type="hidden" id="cpon_no"          name="cpon_no"         value="">           <!-- 쿠폰번호 //-->
<input type="hidden" id="remain_cpon"      name="remain_cpon"     value="">       <!-- 쿠폰잔액 //-->
<input type="hidden" id="used_cpon"        name="used_cpon"       value="">         <!-- 쿠폰 사용금액 //-->
<input type="hidden" id="rem_amt"          name="rem_amt"         value="">           <!-- 잔액 //-->
<input type="hidden" id="bk_pay_yn"        name="bk_pay_yn"       value="">         <!-- 장바구니 결제여부 //-->
<input type="hidden" id="canc_acq_date"    name="canc_acq_date"   value="">     <!-- 매입취소일시 //-->
<input type="hidden" id="canc_date"        name="canc_date"       value="">         <!-- 취소일시 //-->
<input type="hidden" id="refund_date"      name="refund_date"     value="">       <!-- 환불예정일시 //-->
</body>
</html>

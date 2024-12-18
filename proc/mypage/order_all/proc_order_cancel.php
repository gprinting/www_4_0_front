<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/classes/dprinting/PriceCalculator/Common/DPrintingFactory.php");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");


if ($is_login === false) {
    echo "<script>alert('로그인이 필요합니다.'); return false;</script>";
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderInfoDAO();
$new_dao = new FrontCommonDAO();
$util = new FrontCommonUtil();
$factory = new DPrintingFactory();

$session = $fb->getSession();
$check = 1;

$state_arr = $session["state_arr"];

$order_seqno = $fb->form("seqno");

// order_common - order_state 상태 1180로 변경
// 묶음배송비 재계산
// 카드결제시 환불
$param = array();
$param["order_seqno"] = $order_seqno;


// 주문 정보 select
$order_result = $dao->selectOrderInfo($conn, $param);


$pay_way = "";
$pay_price = 0;
$delivery_price = 0;
$cno = "";
$dlvr_dvs = "";
$order_num = "";
$exist_together_dlvr = "N";
$checked_bun = false;
$i = 0 ;
while($order_result && !$order_result->EOF) {
    if($order_result->fields["order_state"] != "1320") {
        $check = -1;
        goto ERR;
    }
    

    if($order_result->fields['use_point_price'] != 0  && $i == 0){

       
        $param['add_minus_check'] = "add";
        $param['send_points'] = $order_result->fields['use_point_price'];
        $param['add_minus_reason'] = "주문취소 포인트 환불";
        $param['order_num'] = $order_result->fields["order_num"];

        $re_id = $new_dao->selectMemberInfo($conn,$order_result->fields['member_seqno'] );

        $param['mb_id_point'] = $re_id['id'];

        $rs = $new_dao->selectMemberInfoPoint($conn, $param);
        $result = $new_dao->updatePoint2($conn, $param, $rs, $new_dao);
        $i++;
       
    }

   
    $member_seqno = $order_result->fields['member_seqno'];
    $pay_way = $order_result->fields['pay_way'];
    $cno = $order_result->fields['deal_num'];
    $dlvr_dvs = $order_result->fields['dlvr_dvs'];
    $bun_dlvr_order_num = $order_result->fields['bun_dlvr_order_num'];
    $order_num = $order_result->fields['order_num'];
    $sell_channel = $order_result->fields['sell_channel'];
    // 묶음존재 여부
    if($dlvr_dvs == "namecard") {
        if($order_result->fields['dvs'] != "배송비")
            $pay_price += $order_result->fields['sell_price'];

        //함께배송인 제품이 있는지 확인
        if($checked_bun == false) {
            $tmp_param = array();
            $tmp_param["bun_dlvr_order_num"] = $bun_dlvr_order_num;
            $tmp_param["member_seqno"] = $member_seqno;
            $tmp_param["order_num"] = $order_num;
            $cnt = $dao->selectCountBunDelivery($conn,$tmp_param);
            if($cnt >= 1) {
                $exist_together_dlvr = "Y";
                $dao->updateDeliveryFeeToAnotherRow($conn,$tmp_param);
            }
            if($cnt == 0) {
                // 묶음배송중 상품 한개만 남아있는경우 배송비조회하여 취소금액에 추가
                $delivery_price = $dao->selectBunDeliveryPrice($conn,$tmp_param);
                $pay_price += $delivery_price;
            }
            $checked_bun = true;
        }
    } else {
        $pay_price += $order_result->fields['sell_price'];
        $exist_together_dlvr = "N";
    }

    $order_result->MoveNext();
}
if($pay_way == "카드") {
    //취소로직
    $url = "https://pgapi.easypay.co.kr/api/trades/revise";

    $fb = new FormBean();

    $headers = array( "content-type: application/json" );

    $mall_id = '05562982';
    $secret_key = "easypay!wJ8YFOFW"; // 암복호화키

    if($sell_channel == "DP") {
        $mall_id = "05574480";
        $secret_key = "easypay!84NaUZh4"; // 암복호화키
    }

   
    $id = uniqid();
    $hash_val = hash_hmac( 'sha256', $cno . "|" . $id, $secret_key, false);


    $post_data = array(
        "mallId"         => $mall_id,
        "shopTransactionId"         => $id,
        "pgCno"         => $cno,
        "reviseTypeCode"         => $exist_together_dlvr == "N" ? "32" : "32",// // 32 - 부분취소, 40 - 즉시취소
        "amount"    => $pay_price, // 부분취소금액
        "clientIp"         => "211.110.168.85", // 요청자IP
        "clientId"         => "고객취소", // 요청자ID
        "msgAuthValue"         => $hash_val,
        "cancelReqDate"         => date('Ymd')
    );
    var_dump($post_data);
    $ch  = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

    $data = curl_exec($ch);
    curl_close($ch);
    $decoded = json_decode($data);
}


$conn->StartTrans();


$param = array();
$param["member_name"] = "고객취소";
$param["order_state"] = "1180";
$param["order_seqno"] = $order_seqno;

//주문취소 상태 변경 및 삭제자 이름 UPDATE
$result = $dao->updateOrderState($conn, $param);
if (!$result) $check = 0;


ERR:
    echo $check;
    
    $conn->CompleteTrans();
    $conn->Close();
    exit;
?>

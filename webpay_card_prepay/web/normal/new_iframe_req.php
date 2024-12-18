<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/OrderMngUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$dao = new FrontCommonDAO();

//$url = "https://testpgapi.easypay.co.kr/api/trades/webpay";
$url = "https://pgapi.easypay.co.kr/api/trades/webpay";

$customerInfo = [
    "customerId"   => urldecode($_POST["EP_user_id"]),
    "customerName"   => urldecode($_POST["EP_user_nm"]),
    "customerMail"   => urldecode($_POST["EP_user_mail"])
];

$orderInfo = [
    "goodsName"   => urldecode($_POST["EP_product_nm"]),
    "customerInfo" => $customerInfo
];

$rs = $dao->selectChannelInfo(
    $conn, ["sell_site" => $_SERVER["SELL_SITE"]]
);

$mallId = $rs["pay_easypay_recharge"];


$headers = array( "content-type: application/json" );

$post_data = array(
    "mallId"         => $mallId,          // <!-- KICC에서 발급한 상점ID -->
    "mallName"         => urldecode($_POST["EP_mall_nm"]),          // <!-- KICC에서 발급한 상점ID -->
    "payMethodTypeCode"         => "11",          // <!-- 결제수단 코드 -->
    "currency"         => "00",          // <!-- 통화코드 -->
    "amount"         => $_POST["EP_product_amt"],          // <!-- 결제요청 금액 -->
    "clientTypeCode"         => "00",          // <!-- 결제창 종류 코드 -->
    "returnUrl"         => $_POST["EP_return_url"],          // <!-- 인증응답URL -->
    "shopOrderNo"         => $_POST["EP_order_no"],          // <!-- 상점 주문번호 -->
    "deviceTypeCode"         => "pc",          // <!-- 고객 결제 단말 -->
    "orderInfo"         => $orderInfo,          // <!-- KICC에서 발급한 상점ID -->
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

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko" lang="ko">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=10" />
    <meta name="robots" content="noindex, nofollow" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script>
        window.onload = function() {
            document.frm.submit();
        }
    </script>
    <title></title>
</head>
<body>
<!-- 테스트 -->
<form name="frm" method="post" action="<?=$decoded->authPageUrl?>">

</form>
</body>
</html>

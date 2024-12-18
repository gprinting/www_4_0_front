<html>
<title>KICC EASYPAY 8.0 SAMPLE</title>
<meta name="robots" content="noindex, nofollow"> 
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<link href="./css/style.css" rel="stylesheet" type="text/css">
<script language="javascript" src="./js/default.js" type="text/javascript"></script>
<?
$res_cd          = $_POST["res_cd"];
$res_msg         = $_POST["res_msg"];
$cno             = $_POST["cno"];
$amount          = $_POST["amount"];
$msg_type        = $_POST["msg_type"];
$order_no        = $_POST["order_no"];
$auth_no         = $_POST["auth_no"];
$tran_date       = $_POST["tran_date"];
$pnt_auth_no     = $_POST["pnt_auth_no"];
$pnt_tran_date   = $_POST["pnt_tran_date"];
$cpon_auth_no    = $_POST["cpon_auth_no"];
$cpon_tran_date  = $_POST["cpon_tran_date"];
$card_no         = $_POST["card_no"];
$issuer_cd       = $_POST["issuer_cd"];
$issuer_nm       = $_POST["issuer_nm"];
$acquirer_cd     = $_POST["acquirer_cd"];
$acquirer_nm     = $_POST["acquirer_nm"];
$install_period  = $_POST["install_period"];
$noint           = $_POST["noint"];
$bank_cd         = $_POST["bank_cd"];
$bank_nm         = $_POST["bank_nm"];
$account_no      = $_POST["account_no"];
$deposit_nm      = $_POST["deposit_nm"];
$expire_date     = $_POST["expire_date"];
$vacct_rt_val    = $_POST["vacct_rt_val"];
$cash_res_cd     = $_POST["cash_res_cd"];
$cash_res_msg    = $_POST["cash_res_msg"];
$cash_auth_no    = $_POST["cash_auth_no"];
$cash_tran_date  = $_POST["cash_tran_date"];
$auth_id         = $_POST["auth_id"];
$billid          = $_POST["billid"];
$mobile_no       = $_POST["mobile_no"];
$ars_no          = $_POST["ars_no"];
$cp_cd           = $_POST["cp_cd"];
$used_pnt        = $_POST["used_pnt"];
$remain_pnt      = $_POST["remain_pnt"];
$pay_pnt         = $_POST["pay_pnt"];
$accrue_pnt      = $_POST["accrue_pnt"];
$remain_cpon     = $_POST["remain_cpon"];
$used_cpon       = $_POST["used_cpon"];
$mall_nm         = $_POST["mall_nm"];
$escrow_yn       = $_POST["escrow_yn"];
$complex_yn      = $_POST["complex_yn"];
$canc_acq_date   = $_POST["canc_acq_date"];
$canc_date       = $_POST["canc_date"];
$refund_date     = $_POST["refund_date"];
$pay_type        = $_POST["pay_type"];
$gw_url          = $_POST["gw_url"];
$gw_port         = $_POST["gw_port"];
  
$gw_name = "";
if( "testgw.easypay.co.kr" == gw_url )
{
    $gw_name = "테스트";
}
else if( "gw.easypay.co.kr" == gw_url )
{
    $gw_name = "리얼";
}
?>
<body> 
<table border="0" width="910" cellpadding="10" cellspacing="0">
<tr>
    <td>
    <table border="0" width="900" cellpadding="0" cellspacing="0">
    <tr>
        <td height="30" bgcolor="#FFFFFF" align="left">&nbsp;<img src="./img/arow3.gif" border="0" align="absmiddle">&nbsp;<b>결과</b></td>
    </tr>
    <tr>
        <td height="2" bgcolor="#2D4677"></td>
    </tr>
    </table>
    <table border="0" width="900" cellpadding="0" cellspacing="0">
    <tr>
        <td height="5"></td>
    </tr>
    </table>
    <table border="0" width="900" cellpadding="0" cellspacing="1" bgcolor="#DCDCDC">
    <tr height="25">
        <td bgcolor="#EDEDED" width="150">&nbsp;서버</td>
        <td bgcolor="#FFFFFF" width="300">&nbsp;<?=$gw_name?>[<?=$gw_url?>]</td>
        <td bgcolor="#EDEDED" width="150">&nbsp;서버PORT</td>
        <td bgcolor="#FFFFFF" width="300">&nbsp;<?=$gw_port?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED" width="150">&nbsp;응답코드</td>
        <td bgcolor="#FFFFFF" width="300">&nbsp;<?=$res_cd?></td>
        <td bgcolor="#EDEDED" width="150">&nbsp;응답메시지</td>
        <td bgcolor="#FFFFFF" width="300">&nbsp;<?=$res_msg?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;PG거래번호</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$cno?></td>
        <td bgcolor="#EDEDED">&nbsp;총 결제금액</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$amount?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;거래구분</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$msg_type?></td>
        <td bgcolor="#EDEDED">&nbsp;주문번호</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$order_no?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;승인번호</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$auth_no?></td>
        <td bgcolor="#EDEDED">&nbsp;승인일시</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$tran_date?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;포인트승인번호</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$pnt_auth_no?></td>
        <td bgcolor="#EDEDED">&nbsp;포인트승인일시</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$pnt_tran_date?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;쿠폰승인번호</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$cpon_auth_no?></td>
        <td bgcolor="#EDEDED">&nbsp;쿠폰승인일시</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$cpon_tran_date?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;카드번호</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$card_no?></td>
        <td bgcolor="#EDEDED">&nbsp;발급사코드</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$issuer_cd?></td>
    </tr>
    <tr>
        <td bgcolor="#EDEDED">&nbsp;발급사명</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$issuer_nm?></td>
        <td bgcolor="#EDEDED">&nbsp;매입사코드</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$acquirer_cd?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;매입사명</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$acquirer_nm?></td>
        <td bgcolor="#EDEDED">&nbsp;할부개월</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$install_period?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;무이자여부</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$noint?></td>
        <td bgcolor="#EDEDED">&nbsp;은행코드</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$bank_cd?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;은행명</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$bank_nm?></td>
        <td bgcolor="#EDEDED">&nbsp;계좌번호</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$account_no?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;입금자명</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$deposit_nm?></td>
        <td bgcolor="#EDEDED">&nbsp;계좌사용만료일</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$expire_date?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;입금통보용 업체 사용영역</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$vacct_rt_val?></td>
        <td bgcolor="#EDEDED">&nbsp;현금영수증 결과코드</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$cash_res_cd?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;현금영수증 결과메세지</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$cash_res_msg?></td>
        <td bgcolor="#EDEDED">&nbsp;현금영수증 승인번호</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$cash_auth_no?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;현금영수증 승인일시</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$cash_tran_date?></td>
        <td bgcolor="#EDEDED">&nbsp;PhoneID</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$auth_id?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;인증번호</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$billid?></td>
        <td bgcolor="#EDEDED">&nbsp;휴대폰번호</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$mobile_no?></td>
    </tr>
    <tr>
        <td height="25" bgcolor="#EDEDED">&nbsp;전화번호</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$ars_no?></td>
        <td bgcolor="#EDEDED">&nbsp;포인트사</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$cp_cd?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;사용포인트</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$used_pnt?></td>
        <td bgcolor="#EDEDED">&nbsp;잔여한도</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$remain_pnt?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;할인/발생포인트</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$pay_pnt?></td>
        <td bgcolor="#EDEDED">&nbsp;누적포인트</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$accrue_pnt?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;쿠폰잔액</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$remain_cpon?></td>
        <td bgcolor="#EDEDED">&nbsp;쿠폰 사용금액</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$used_cpon?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;제휴사명칭</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$mall_nm?></td>
        <td bgcolor="#EDEDED">&nbsp;에스크로 사용유무</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$escrow_yn?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;복합결제 유무</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$complex_yn?></td>
        <td bgcolor="#EDEDED">&nbsp;매입취소일시</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$canc_acq_date?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;취소일시</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$canc_date?></td>
        <td bgcolor="#EDEDED">&nbsp;환불예정일시</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$refund_date?></td>
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED">&nbsp;결제수단</td>
        <td bgcolor="#FFFFFF">&nbsp;<?=$pay_type?></td>
        <td bgcolor="#EDEDED">&nbsp;</td>
        <td bgcolor="#FFFFFF">&nbsp;</td>
    </tr>
    </table>
    </td>
</tr>
</table>
</form>
</body>
</html>

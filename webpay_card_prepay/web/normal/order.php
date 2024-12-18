<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>KICC EASYPAY 8.0 SAMPLE</title>
<meta name="robots" content="noindex, nofollow"> 
<meta http-equiv="content-type" content="text/html; charset=euc-kr">
<meta http-equiv="X-UA-Compatible" content="requiresActiveX=true">
<meta http-equiv="cache-control" content="no-cache"/>
<meta http-equiv="Expires" content="0"/>
<meta http-equiv="Pragma" content="no-cache"/>
<link href="../css/style.css" rel="stylesheet" type="text/css">
<script language="javascript" src="../js/default.js" type="text/javascript"></script>
<!-- Test -->
<!--script type="text/javascript" src="http://testpg.easypay.co.kr/webpay/EasypayCard_Web.js"></script-->
<!-- Real -->
<script type="text/javascript" src="https://pg.easypay.co.kr/webpay/EasypayCard_Web.js"></script>

<script type="text/javascript">
    
    /* 입력 자동 Setting */
    function f_init()
    {
        var frm_pay = document.frm_pay;
        
        var today = new Date();
        var year  = today.getFullYear();
        var month = today.getMonth() + 1;
        var date  = today.getDate();
        var time  = today.getTime();
        
        if(parseInt(month) < 10) 
        {
            month = "0" + month;
        }

        if(parseInt(date) < 10) 
        {
            date = "0" + date;
        }
        
        
        frm_pay.EP_mall_id.value = "T5102001";
        frm_pay.EP_mall_nm.value = "한국정보통신(주) 테스트";
        frm_pay.EP_vacct_end_date.value = "" + year + month + date;
        frm_pay.EP_vacct_end_time.value = "235959";
        frm_pay.EP_order_no.value = "ORDER_" + year + month + date + time;   //가맹점주문번호
        frm_pay.EP_user_id.value = "USER_" + time;                           //고객ID
        frm_pay.EP_user_nm.value = "천송이";
        frm_pay.EP_user_mail.value = "test@kicc.co.kr";
        frm_pay.EP_user_phone1.value = "0212344567";
        frm_pay.EP_user_phone2.value = "01012344567";
        frm_pay.EP_user_addr.value = "서울 금천구 가산동 459-9 ";
        frm_pay.EP_product_nm.value = "테스트 상품";
        frm_pay.EP_product_amt.value = "50000";
        frm_pay.EP_return_url.value = "http://1.234.88.134/easypay80_webpay_php/web/normal/order_res.php";
    }
    
    function f_start_pay() 
    {
        var frm_pay = document.frm_pay;

        /* UTF-8 사용가맹점의 경우 EP_charset 값 셋팅 필수 */
        if( frm_pay.EP_charset.value == "UTF-8" ) 
        {
            console.log(encodeURIComponent(frm_pay.EP_mall_nm.value));
            console.log(encodeURIComponent(frm_pay.EP_product_nm.value));
            // 한글이 들어가는 값은 모두 encoding 필수.
            frm_pay.EP_mall_nm.value      = encodeURIComponent( frm_pay.EP_mall_nm.value );
            frm_pay.EP_product_nm.value   = encodeURIComponent( frm_pay.EP_product_nm.value );
        }
        
        
        /* 가맹점에서 원하는 인증창 호출 방법을 선택 */

        if( frm_pay.EP_window_type.value == "iframe" ) 
        {
            easypay_webpay(frm_pay,"./iframe_req.php","hiddenifr","0","0","iframe",30);
        }
        else if( frm_pay.EP_window_type.value == "popup" ) 
        {
            easypay_webpay(frm_pay,"./popup_req.php","hiddenifr","","","popup",30);
        }
    }
    
    function f_submit() 
    {
        var frm_pay = document.frm_pay;
        frm_pay.target = "_self";
        frm_pay.action = "../easypay_request.php";
        frm_pay.submit();
    }
    
</script>
</head>
<body onload="f_init();">
<form name="frm_pay" method="post" action="">

<!--------------------------->
<!-- ::: 공통 인증 요청 값 -->
<!--------------------------->

<input type="hidden" id="EP_mall_nm"       name="EP_mall_nm"          value="">         <!-- 가맹점 이름 // -->
<input type="hidden" id="EP_order_no"       name="EP_order_no"          value="">         <!-- 가맹점 주문번호 // -->
<input type="hidden" id="EP_currency"       name="EP_currency"          value="00">       <!-- 통화코드 // 00 : 원화-->
<input type="hidden" id="EP_return_url"     name="EP_return_url"        value="">         <!-- 가맹점 CALLBACK URL // -->
<input type="hidden" id="EP_ci_url"         name="EP_ci_url"            value="">         <!-- CI LOGO URL // -->
<input type="hidden" id="EP_lang_flag"      name="EP_lang_flag"         value="">         <!-- 언어 // -->
<input type="hidden" id="EP_charset"        name="EP_charset"           value="UTF-8">   <!-- 가맹점 CharSet // -->
<input type="hidden" id="EP_user_type"      name="EP_user_type"         value="">         <!-- 사용자구분 // -->
<input type="hidden" id="EP_user_id"        name="EP_user_id"           value="">         <!-- 가맹점 고객ID // -->
<input type="hidden" id="EP_memb_user_no"   name="EP_memb_user_no"      value="">         <!-- 가맹점 고객일련번호 // -->
<input type="hidden" id="EP_user_nm"        name="EP_user_nm"           value="">         <!-- 가맹점 고객명 // -->
<input type="hidden" id="EP_user_mail"      name="EP_user_mail"         value="">         <!-- 가맹점 고객 E-mail // -->
<input type="hidden" id="EP_user_phone1"    name="EP_user_phone1"       value="">         <!-- 가맹점 고객 연락처1 // -->
<input type="hidden" id="EP_user_phone2"    name="EP_user_phone2"       value="">         <!-- 가맹점 고객 연락처2 // -->
<input type="hidden" id="EP_user_addr"      name="EP_user_addr"         value="">         <!-- 가맹점 고객 주소 // -->
<input type="hidden" id="EP_user_define1"   name="EP_user_define1"      value="">         <!-- 가맹점 필드1 // -->
<input type="hidden" id="EP_user_define2"   name="EP_user_define2"      value="">         <!-- 가맹점 필드2 // -->
<input type="hidden" id="EP_user_define3"   name="EP_user_define3"      value="">         <!-- 가맹점 필드3 // -->
<input type="hidden" id="EP_user_define4"   name="EP_user_define4"      value="">         <!-- 가맹점 필드4 // -->
<input type="hidden" id="EP_user_define5"   name="EP_user_define5"      value="">         <!-- 가맹점 필드5 // -->
<input type="hidden" id="EP_user_define6"   name="EP_user_define6"      value="">         <!-- 가맹점 필드6 // -->
<input type="hidden" id="EP_product_type"   name="EP_product_type"      value="">         <!-- 상품정보구분 // -->
<input type="hidden" id="EP_product_expr"   name="EP_product_expr"      value="">         <!-- 서비스 기간 // (YYYYMMDD) -->


<!--------------------------->
<!-- ::: 카드 인증 요청 값 -->
<!--------------------------->

<input type="hidden" id="EP_usedcard_code"      name="EP_usedcard_code"     value="">      <!-- 사용가능한 카드 LIST // FORMAT->카드코드:카드코드: ... :카드코드 EXAMPLE->029:027:031 // 빈값 : DB조회-->
<input type="hidden" id="EP_quota"              name="EP_quota"             value="">      <!-- 할부개월 (카드코드-할부개월) -->
<input type="hidden" id="EP_os_cert_flag"       name="EP_os_cert_flag"      value="2">     <!-- 해외안심클릭 사용여부(변경불가) // -->
<input type="hidden" id="EP_noinst_flag"        name="EP_noinst_flag"       value="">      <!-- 무이자 여부 (Y/N) // -->
<input type="hidden" id="EP_noinst_term"        name="EP_noinst_term"       value="">      <!-- 무이자 기간(카드코드-더할할부개월) // -->
<input type="hidden" id="EP_set_point_card_yn"  name="EP_set_point_card_yn" value="">      <!-- 카드사포인트 사용여부 (Y/N) // -->
<input type="hidden" id="EP_point_card"         name="EP_point_card"        value="">      <!-- 포인트카드 LIST  // -->
<input type="hidden" id="EP_join_cd"            name="EP_join_cd"           value="">      <!-- 조인코드 // -->
<input type="hidden" id="EP_kmotion_useyn"      name="EP_kmotion_useyn"     value="">      <!-- 국민앱카드 사용유무 // -->

<!------------------------------->
<!-- ::: 가상계좌 인증 요청 값 -->
<!------------------------------->

<input type="hidden" id="EP_vacct_bank"      name="EP_vacct_bank"     value="">      <!-- 가상계좌 사용가능한 은행 LIST // -->
<input type="hidden" id="EP_vacct_end_date"  name="EP_vacct_end_date" value="">      <!-- 입금 만료 날짜 // -->
<input type="hidden" id="EP_vacct_end_time"  name="EP_vacct_end_time" value="">      <!-- 입금 만료 시간 // -->

<!------------------------------->
<!-- ::: 선불카드 인증 요청 값 -->
<!------------------------------->

<input type="hidden" id="EP_prepaid_cp"    name="EP_prepaid_cp"     value="">      <!-- 선불카드 CP // FORMAT->코드:코드: ... :코드 EXAMPLE->CCB:ECB // 빈값 : DB조회-->

<!--------------------------------->
<!-- ::: 인증응답용 인증 요청 값 -->
<!--------------------------------->

<input type="hidden" id="EP_res_cd"          name="EP_res_cd"         value="">      <!--  응답코드 // -->
<input type="hidden" id="EP_res_msg"         name="EP_res_msg"        value="">      <!--  응답메세지 // -->
<input type="hidden" id="EP_tr_cd"           name="EP_tr_cd"          value="">      <!--  결제창 요청구분 // -->
<input type="hidden" id="EP_ret_pay_type"    name="EP_ret_pay_type"   value="">      <!--  결제수단 // -->
<input type="hidden" id="EP_ret_complex_yn"  name="EP_ret_complex_yn" value="">      <!--  복합결제 여부 (Y/N) // -->
<input type="hidden" id="EP_card_code"       name="EP_card_code"      value="">      <!--  카드코드 (ISP:KVP카드코드 MPI:카드코드) // -->
<input type="hidden" id="EP_eci_code"        name="EP_eci_code"       value="">      <!--  MPI인 경우 ECI코드 // -->
<input type="hidden" id="EP_card_req_type"   name="EP_card_req_type"  value="">      <!--  거래구분 // -->
<input type="hidden" id="EP_save_useyn"      name="EP_save_useyn"     value="">      <!--  카드사 세이브 여부 (Y/N) // -->
<input type="hidden" id="EP_trace_no"        name="EP_trace_no"       value="">      <!--  추적번호 // -->
<input type="hidden" id="EP_sessionkey"      name="EP_sessionkey"     value="">      <!--  세션키 // -->
<input type="hidden" id="EP_encrypt_data"    name="EP_encrypt_data"   value="">      <!--  암호화전문 // -->
<input type="hidden" id="EP_pnt_cp_cd"       name="EP_pnt_cp_cd"      value="">      <!--  포인트 CP 코드 // -->
<input type="hidden" id="EP_spay_cp"         name="EP_spay_cp"        value="">      <!--  간편결제 CP 코드 // -->
<input type="hidden" id="EP_card_prefix"     name="EP_card_prefix"    value="">      <!--  신용카드prefix // -->
<input type="hidden" id="EP_card_no_7"       name="EP_card_no_7"      value="">      <!--  신용카드번호 앞7자리 // -->


<table border="0" width="910" cellpadding="10" cellspacing="0">
<tr>
    <td>
    <!-- title start -->
    <table border="0" width="900" cellpadding="0" cellspacing="0">
    <tr>
        <td height="30" bgcolor="#FFFFFF" align="left">&nbsp;<img src="../img/arow3.gif" border="0" align="absmiddle">&nbsp;일반 > <b>결제</b></td>
    </tr>
    <tr>
        <td height="2" bgcolor="#2D4677"></td>
    </tr>
    </table>
    <!-- title end -->
       
    <!-- mallinfo start -->
    <table border="0" width="900" cellpadding="0" cellspacing="0">
    <tr>
        <td height="30" bgcolor="#FFFFFF">&nbsp;<img src="../img/arow2.gif" border="0" align="absmiddle">&nbsp;<b>가맹점정보</b>(*필수)</td>
    </tr>
    </table>
    
    <table border="0" width="900" cellpadding="0" cellspacing="1" bgcolor="#DCDCDC">
    <tr height="25">
        <td bgcolor="#EDEDED" width="150">&nbsp; *가맹점아이디</td>
        <td bgcolor="#FFFFFF" width="300">&nbsp;<input type="text" id="EP_mall_id" name="EP_mall_id" value="" size="50" maxlength="8" class="input_F"></td>
        <td bgcolor="#EDEDED" width="150">&nbsp; 윈도우 타입</td>
        <td bgcolor="#FFFFFF" width="300">&nbsp;
            <select id="EP_window_type" name="EP_window_type" class="input_F">
                <option value="iframe" selected>iframe</option>
                <option value="popup" >popup</option>
            </select>
       </td>
    </tr>
    </table>
    <!-- mallinfo end -->
    
    <!-- webpay start -->
    <table border="0" width="900" cellpadding="0" cellspacing="0">
    <tr>
        <td height="30" bgcolor="#FFFFFF">&nbsp;<img src="../img/arow2.gif" border="0" align="absmiddle">&nbsp;<b>결제창 정보</b>(*필수)</td>
    </tr>
    </table>

    <table border="0" width="900" cellpadding="0" cellspacing="1" bgcolor="#DCDCDC">
    <tr height="25">
        <td bgcolor="#EDEDED" width="150">&nbsp; *결제수단</td>
        <td bgcolor="#FFFFFF" width="300">&nbsp;
            <select id="EP_pay_type" name="EP_pay_type" class="input_F">
                <option value="11" selected>신용카드</option>            
                <option value="21">계좌이체</option>
                <option value="22">무통장입금</option>
                <option value="31">휴대폰</option>
                <option value="50">선불결제</option>
                <option value="60">간편결제</option>
            </select>
        </td>
        <td bgcolor="#EDEDED" width="150">&nbsp;</td>        
        <td bgcolor="#FFFFFF" width="300">&nbsp;</td>      
    </tr>
    <tr height="25">
        <td bgcolor="#EDEDED" width="150">&nbsp; *상품명</td>        
        <td bgcolor="#FFFFFF" width="300">&nbsp;<input type="text" id="EP_product_nm" name="EP_product_nm" value="테스트상품" size="50" class="input_F"></td>      
        <td bgcolor="#EDEDED" width="150">&nbsp; *상품금액</td>        
        <td bgcolor="#FFFFFF" width="300">&nbsp;<input type="text" id="EP_product_amt" name="EP_product_amt" value="10" size="50" class="input_F"></td>      
    </tr>
    </table>
    <!-- webpay end -->

    <table border="0" width="900" cellpadding="0" cellspacing="0">
    <tr>
        <td height="30" align="center" bgcolor="#FFFFFF"><input type="button" value="결 제" class="input_D" style="cursor:hand;" onclick="javascript:f_start_pay();"></td>
    </tr>
    </table>
    </td>
</tr>
</table>
</form>
</body>
</html>

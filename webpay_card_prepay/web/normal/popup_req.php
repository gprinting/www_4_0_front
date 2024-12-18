<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko" lang="ko">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
<meta name="robots" content="noindex, nofollow" />
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr" />
<script type="text/javascript">
    function change(url)
    {
        document.getElementById("main").src = url;
    }

    function resizeHeight(fr)
    {
        var new_height = window.frames[0].document.body.scrollHeight;
        if(new_height < 500)
        {
            fr.height = 500;
        }
        else
        {
            fr.height=new_height;
        }
    }

    function f_submit()
    {
        var frm = document.frm;
        frm.target = "hiddenifr";
        frm.submit();
    }
</script>
<title>webpay 가맹점 test page</title>
</head>
<body onload="f_submit();">
    <form name="frm" method="post" action="http://testpg.easypay.co.kr/webpay/MainAction.do"> <!-- 테스트 -->
    <!--form name="frm" method="post" action="https://pg.easypay.co.kr/webpay/MainAction.do"--> <!-- 운영 -->
		<input type="hidden" id="EP_mall_id"           name="EP_mall_id"            value="<?=$_POST["EP_mall_id"] ?>" />
		<input type="hidden" id="EP_mall_nm"           name="EP_mall_nm"            value="<?=$_POST["EP_mall_nm"] ?>" />
		<input type="hidden" id="EP_order_no"          name="EP_order_no"           value="<?=$_POST["EP_order_no"] ?>" />
		<input type="hidden" id="EP_pay_type"          name="EP_pay_type"           value="<?=$_POST["EP_pay_type"] ?>" />
		<input type="hidden" id="EP_currency"          name="EP_currency"           value="<?=$_POST["EP_currency"] ?>" />
		<input type="hidden" id="EP_product_nm"        name="EP_product_nm"         value="<?=$_POST["EP_product_nm"] ?>" />
		<input type="hidden" id="EP_product_amt"       name="EP_product_amt"        value="<?=$_POST["EP_product_amt"] ?>" />
		<input type="hidden" id="EP_return_url"        name="EP_return_url"         value="<?=$_POST["EP_return_url"] ?>" />
		<input type="hidden" id="EP_ci_url"            name="EP_ci_url"             value="<?=$_POST["EP_ci_url"] ?>" />
		<input type="hidden" id="EP_lang_flag"         name="EP_lang_flag"          value="<?=$_POST["EP_lang_flag"] ?>" />
		<input type="hidden" id="EP_charset"           name="EP_charset"            value="<?=$_POST["EP_charset"] ?>" />
		<input type="hidden" id="EP_user_type"         name="EP_user_type"          value="<?=$_POST["EP_user_type"] ?>" />
		<input type="hidden" id="EP_user_id"           name="EP_user_id"            value="<?=$_POST["EP_user_id"] ?>" />
		<input type="hidden" id="EP_memb_user_no"      name="EP_memb_user_no"       value="<?=$_POST["EP_memb_user_no"] ?>" />
		<input type="hidden" id="EP_user_nm"           name="EP_user_nm"            value="<?=$_POST["EP_user_nm"] ?>" />
		<input type="hidden" id="EP_user_mail"         name="EP_user_mail"          value="<?=$_POST["EP_user_mail"] ?>" />
		<input type="hidden" id="EP_user_phone1"       name="EP_user_phone1"        value="<?=$_POST["EP_user_phone1"] ?>" />
		<input type="hidden" id="EP_user_phone2"       name="EP_user_phone2"        value="<?=$_POST["EP_user_phone2"] ?>" />
		<input type="hidden" id="EP_user_addr"         name="EP_user_addr"          value="<?=$_POST["EP_user_addr"] ?>" />
		<input type="hidden" id="EP_user_define1"      name="EP_user_define1"       value="<?=$_POST["EP_user_define1"] ?>" />
		<input type="hidden" id="EP_user_define2"      name="EP_user_define2"       value="<?=$_POST["EP_user_define2"] ?>" />
		<input type="hidden" id="EP_user_define3"      name="EP_user_define3"       value="<?=$_POST["EP_user_define3"] ?>" />
		<input type="hidden" id="EP_user_define4"      name="EP_user_define4"       value="<?=$_POST["EP_user_define4"] ?>" />
		<input type="hidden" id="EP_user_define5"      name="EP_user_define5"       value="<?=$_POST["EP_user_define5"] ?>" />
		<input type="hidden" id="EP_user_define6"      name="EP_user_define6"       value="<?=$_POST["EP_user_define6"] ?>" />
		<input type="hidden" id="EP_product_type"      name="EP_product_type"       value="<?=$_POST["EP_product_type"] ?>" />
		<input type="hidden" id="EP_product_expr"      name="EP_product_expr"       value="<?=$_POST["EP_product_expr"] ?>" />
		<input type="hidden" id="EP_usedcard_code"     name="EP_usedcard_code"      value="<?=$_POST["EP_usedcard_code"] ?>" />
		<input type="hidden" id="EP_quota"             name="EP_quota"              value="<?=$_POST["EP_quota"] ?>" />
		<input type="hidden" id="EP_os_cert_flag"      name="EP_os_cert_flag"       value="<?=$_POST["EP_os_cert_flag"] ?>" />
		<input type="hidden" id="EP_noinst_flag"       name="EP_noinst_flag"        value="<?=$_POST["EP_noinst_flag"] ?>" />
		<input type="hidden" id="EP_noinst_term"       name="EP_noinst_term"        value="<?=$_POST["EP_noinst_term"] ?>" />
		<input type="hidden" id="EP_set_point_card_yn" name="EP_set_point_card_yn"  value="<?=$_POST["EP_set_point_card_yn"] ?>" />
		<input type="hidden" id="EP_point_card"        name="EP_point_card"         value="<?=$_POST["EP_point_card"] ?>" />
		<input type="hidden" id="EP_join_cd"           name="EP_join_cd"            value="<?=$_POST["EP_join_cd"] ?>" />
		<input type="hidden" id="EP_kmotion_useyn"     name="EP_kmotion_useyn"      value="<?=$_POST["EP_kmotion_useyn"] ?>" />
		<input type="hidden" id="EP_vacct_bank"        name="EP_vacct_bank"         value="<?=$_POST["EP_vacct_bank"] ?>" />
		<input type="hidden" id="EP_vacct_end_date"    name="EP_vacct_end_date"     value="<?=$_POST["EP_vacct_end_date"] ?>" />
		<input type="hidden" id="EP_vacct_end_time"    name="EP_vacct_end_time"     value="<?=$_POST["EP_vacct_end_time"] ?>" />
        
    </form>
    <iframe id="hiddenifr" name="hiddenifr" width="100%" frameborder="0" scrolling="0" onload="resizeHeight(this)" />
</body>
</html> 

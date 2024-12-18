<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko" lang="ko">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=10" />
<meta name="robots" content="noindex, nofollow" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script>
    window.onload = function() {

        /* UTF-8 사용가맹점의 경우 한글이 들어가는 값은 모두 decoding 필수 */
        var res_msg = "<?=urldecode($_POST["EP_res_msg"]); ?>";

        window.parent.document.getElementById("P_EP_res_cd").value         = "<?=$_POST["EP_res_cd"] ?>";
        window.parent.document.getElementById("P_EP_res_msg").value        = res_msg;
        window.parent.document.getElementById("P_EP_tr_cd").value          = "<?=$_POST["EP_tr_cd"] ?>";
        window.parent.document.getElementById("P_EP_ret_pay_type").value   = "<?=$_POST["EP_ret_pay_type"] ?>";
        window.parent.document.getElementById("P_EP_ret_complex_yn").value = "<?=$_POST["EP_ret_complex_yn"] ?>";
        window.parent.document.getElementById("P_EP_card_code").value      = "<?=$_POST["EP_card_code"] ?>";
        window.parent.document.getElementById("P_EP_eci_code").value       = "<?=$_POST["EP_eci_code"] ?>";
        window.parent.document.getElementById("P_EP_card_req_type").value  = "<?=$_POST["EP_card_req_type"] ?>";
        window.parent.document.getElementById("P_EP_save_useyn").value     = "<?=$_POST["EP_save_useyn"] ?>";
        window.parent.document.getElementById("P_EP_trace_no").value       = "<?=$_POST["EP_trace_no"] ?>";
        window.parent.document.getElementById("P_EP_sessionkey").value     = "<?=$_POST["EP_sessionkey"] ?>";
        window.parent.document.getElementById("P_EP_encrypt_data").value   = "<?=$_POST["EP_encrypt_data"] ?>";
        window.parent.document.getElementById("P_EP_pnt_cp_cd").value      = "<?=$_POST["EP_pnt_cp_cd"] ?>";
        window.parent.document.getElementById("P_EP_spay_cp").value        = "<?=$_POST["EP_spay_cp"] ?>";
        window.parent.document.getElementById("P_EP_card_prefix").value    = "<?=$_POST["EP_card_prefix"] ?>";
        window.parent.document.getElementById("P_EP_card_no_7").value      = "<?=$_POST["EP_card_no_7"] ?>";
        
        if( "<?=$_POST["EP_res_cd"] ?>" == "0000" ) {
            window.parent.prepaySubmit();
        } else {
            alert( "<?=$_POST["EP_res_cd"] ?> : " + res_msg);
        }
        
        window.parent.kicc_popup_close();
    }
    
    function urldecode( str ) {
        // 공백 문자인 + 를 처리하기 위해 +('%20') 을 공백으로 치환
        return decodeURIComponent((str + '').replace(/\+/g, '%20'));
    }
    
</script>
<title></title>
</head>
<body>
</body>
</html>

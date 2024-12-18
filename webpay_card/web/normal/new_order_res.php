<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko" lang="ko">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=10" />
    <meta name="robots" content="noindex, nofollow" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script>
        window.onload = function() {

            /* UTF-8 사용가맹점의 경우 한글이 들어가는 값은 모두 decoding 필수 */
            //var res_msg = urldecode( "<?=$_POST["resMsg"] ?>" );
            var res_msg = "<?=urldecode($_POST["resMsg"]); ?>";

            window.parent.document.getElementById("resCd").value         = "<?=$_POST["resCd"] ?>";
            window.parent.document.getElementById("resMsg").value        = res_msg;
            window.parent.document.getElementById("shopOrderNo").value          = "<?=$_POST["shopOrderNo"] ?>";
            window.parent.document.getElementById("authorizationId").value   = "<?=$_POST["authorizationId"] ?>";
            window.parent.document.getElementById("shopValue1").value = "<?=$_POST["shopValue1"] ?>";
            window.parent.document.getElementById("shopValue2").value      = "<?=$_POST["shopValue2"] ?>";
            window.parent.document.getElementById("shopValue3").value       = "<?=$_POST["shopValue3"] ?>";
            window.parent.document.getElementById("shopValue4").value  = "<?=$_POST["shopValue4"] ?>";
            window.parent.document.getElementById("shopValue5").value     = "<?=$_POST["shopValue5"] ?>";
            window.parent.document.getElementById("shopValue6").value       = "<?=$_POST["shopValue6"] ?>";
            window.parent.document.getElementById("shopValue7").value     = "<?=$_POST["shopValue7"] ?>";

            if( "<?=$_POST["resCd"] ?>" == "0000" ) {
                window.parent.reqSubmit();
            } else {
                alert( "<?=$_POST["resCd"] ?> : " + res_msg);
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

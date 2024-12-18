<html>
<head>
<title>KICC EASYPAY 8.0 SAMPLE</title>
<meta name="robots" content="noindex, nofollow"> 
<meta http-equiv="content-type" content="text/html; charset=euc-kr">
<link href="./css/style.css" rel="stylesheet" type="text/css">
<script language="javascript" src="./js/default.js" type="text/javascript"></script>
<script language="javascript">
<!--
    function f_timer( url,s_subMenuNo,showNo){
        setTimeout( "f_mainload('"+ url + "','" + s_subMenuNo + "','" + showNo + "')", 500 );
    }
    
    function f_showTimer(showNo){
        setTimeout( "clickshow('" + showNo + "')", 500 );
    }
    
    function f_mainload(url,s_subMenuNo,showNo){
        window.parent.main.location=url + "?s_subMenuNo=" + s_subMenuNo;
      
        clickshow(showNo);
    }
    
    function f_gotomain(url,s_subMenuNo){
        var frm = document.frm_main;
        
        frm.action = url;
        frm.s_subMenuNo.value = s_subMenuNo;
        frm.submit();
    }
//-->
</script>
</head>
<body topmargin="0" leftmargin="0">
<form name="frm_main" method="post" target="main">
<input type="hidden" name="s_subMenuNo">
</form>
<table bgcolor="#FFFFFF" width="180" cellpadding="0" cellspacing="0" align="left" border="0">
<tr>
    <td>
    <table cellpadding="0" cellspacing="0" border="0" width="180">
    <tr>
        <td height="50" align="center" class="menuTitle">EASYPAY 8.0</td>
    </tr>
    <tr>
        <td height="2" bgcolor="#DCDCDC"></td>
    </tr>
    <tr>
        <td height="3"></td>
    </tr>
    </table>
    </td>
</tr>
<tr>
    <td height="25" onClick="clickshow(1)" style="cursor:hand;">&nbsp;<img src="./img/go_menu_open.gif" border="0">&nbsp;<b>일반</b></td>
</tr>
<tr>
    <td bgcolor="#FFFFFF">
    <span id="block1" style="DISPLAY:none; MARGIN-LEFT:0px; CURSOR:hand">
        <table width="180" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td background="./img/dot0001.gif"></td>
        </tr>
        <tr>
            <td height="20">&nbsp;&nbsp;<img src="./img/go_menu_minus2.gif" border="0">&nbsp;<a href="javascript:f_gotomain('normal/order.php');">결제</a></td>
        </tr>
        <tr>
            <td background="./img/dot0001.gif"></td>
        </tr>
        <tr>
            <td height="20">&nbsp;&nbsp;<img src="./img/go_menu_minus2.gif" border="0">&nbsp;<a href="javascript:f_gotomain('normal/mgr.php');">변경</a></td>
        </tr>
        <tr>
            <td background="./img/dot0001.gif"></td>
        </tr>
        </table>
    </span>
    </td>
</tr>
<tr>
    <td height="4"></td>
</tr>
<tr>
    <td height="25" onClick="clickshow(2)" style="cursor:hand;">&nbsp;<img src="./img/go_menu_open.gif" border="0">&nbsp;<b>에스크로</b></td>
</tr>
<tr>
    <td bgcolor="#FFFFFF">
    <span id="block2" style="DISPLAY:none; MARGIN-LEFT:0px; CURSOR:hand">
        <table width="180" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td background="./img/dot0001.gif"></td>
        </tr>
        <tr>
            <td height="20">&nbsp;&nbsp;<img src="./img/go_menu_minus2.gif" border="0">&nbsp;<a href="javascript:f_gotomain('escrow/order.php');">결제</a></td>
        </tr>
        <tr>
            <td background="./img/dot0001.gif"></td>
        </tr>
        <tr>
            <td height="20">&nbsp;&nbsp;<img src="./img/go_menu_minus2.gif" border="0">&nbsp;<a href="javascript:f_gotomain('escrow/mgr.php');">변경</a></td>
        </tr>
        <tr>
            <td background="./img/dot0001.gif"></td>
        </tr>
        </table>
    </span>
    </td>
</tr>
<tr>
    <td height="4"></td>
</tr>
</table>
<script>f_showTimer(1);</script>
<script>
    JavaScript:setConfig(2);
</script> 
</body>
</html>


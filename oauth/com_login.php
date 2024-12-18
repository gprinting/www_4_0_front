<?php

$script  = "<script type=\"text-javascript\">";
$script .= "var welcome = function() {";
$script .= "";
$script .= "";
$script .= "}";

echo $script;



$html  = "아이디 : <input type=\"text\" id=\"user_id\" /><br />";
$html .= "패스워드 : <input type=\"text\" id=\"user_pw\" /><br />";
$html .= "<button type=\"button\" onclick=\"\">로그인</button><br />";
$html .= "<button type=\"button\" onclick=\"welcome()\">회원가입</button><br />";

echo $html;

?>

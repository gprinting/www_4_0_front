<?php
$host        = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER['HTTP_HOST'];
$email       = $_SESSION["email"];
$group_name  = $_SESSION["group_name"];
$member_name = $_SESSION["name"];

$user_nm    = $group_name;
if (!empty($user_nm)) {
    $user_nm = urlencode($user_nm . ' ' . $member_name);
} else {
    $user_nm = urlencode($member_name);
}

$template->reg("mall_nm", urlencode("(주)굿프린팅"));
$template->reg("host", $host);
$template->reg("user_nm", $user_nm);
$template->reg("member_mail", $email);

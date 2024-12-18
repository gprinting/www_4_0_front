<?php

session_start();
define("INC_PATH", $_SERVER["INC"]);

if (strpos($_SERVER["SCRIPT_NAME"], "ajax") !== false
        || strpos($_SERVER["SCRIPT_NAME"], "proc") !== false) {
    /*
    if (empty($_SERVER["HTTP_REFERER"]) 
            || strpos($_SERVER["HTTP_REFERER"], "goodprinting") === false) {
        // ajax, proc 포함된 주소는 레퍼러 참조해서 접근제한
        echo "error";
        exit;
    }
    */
}


/*
*/

include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
$fb = new FormBean();

$is_login = false;
// 로그인 여부 체크
if (!empty($fb->session("id"))) {
    $is_login = true;
}

if (!empty($fb->session("login_time"))) {
    $login_time = $fb->session("login_time");
    $curr_time  = time();

    if (($curr_time - $login_time) > 1800) {
        //$fb->removeAllSession();
        //exit;
    } else {
        $fb->addSession("login_time", time());
    }
}



$is_ba = "true";
// 가상계좌 여부
if ($is_login && empty($fb->session("bank_name")) === true) {
    $is_ba = "false";
}

$design_path = "/design_template";
$html_path   = $_SERVER["PHP_SELF"];

$root_design_dir = "/design_template"; 

$is_mobile = isMobile();

if ($is_mobile) {
    //$design_path =  $design_path . "/m";
    //$html_path   = "/m" . $html_path;
}

// 모바일 접속인지 판단
function isMobile() {
    // 모바일 기종(배열 순서 중요, 대소문자 구분 안함)
    $ary_m = [
        "iPhone","iPod","IPad","Android","Blackberry"
        ,"SymbianOS|SCH-M\d+","Opera Mini","Windows CE"
        ,"Nokia","Sony","Samsung","LGTelecom","SKT","Mobile","Phone"
    ];

    for($i = 0; $i < count($ary_m); $i++){
        if(preg_match("/$ary_m[$i]/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
            break;
        }
    }

    return false;
}

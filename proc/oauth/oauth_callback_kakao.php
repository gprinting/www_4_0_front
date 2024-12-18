<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/LoginUtil.inc");

$fb = new FormBean();
$fb = $fb->getForm();

$res = [
     "email" => $fb["kaccount_email"]
    ,"name"  => $fb["properties"]["nickname"]
];

$loginUtil = new LoginUtil([
     "res" => $res
    ,"dvs" => "kakao"
]);

if (!$loginUtil->login()) {
    goto ERR;
}

goto END;

ERR:
    echo $loginUtil->err_msg;
    exit;
END:
    //echo $loginUtil->redirPage();
    echo '';
?>

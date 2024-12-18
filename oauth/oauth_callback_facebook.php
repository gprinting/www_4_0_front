<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/LoginUtil.inc");

$fb = new FormBean();
$fb = $fb->getForm();

$res = [
     "email" => $fb["email"]
    ,"name"  => $fb["name"]
];

$loginUtil = new LoginUtil([
     "res" => $response
    ,"dvs" => "fb"
]);

if (!$loginUtil->login()) {
    goto ERR;
}

goto END;

ERR:
    echo $loginUtil->err_msg;
    exit;
END:
    echo $loginUtil->redirPage();
?>

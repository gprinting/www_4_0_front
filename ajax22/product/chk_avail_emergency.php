<?php
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");

$util = new FrontCommonUtil();
$fb = new FormBean();

$depth1 = $fb->form("emergency");

if (!$util->chkAvailEmergency($depth1)) {
    $msg = $depth1 . " 당일판 시간이 초과되었습니다.";
    echo "{\"ret\" : -1, \"msg\" : \"" . $msg . "\"}";
    exit;
}

echo "{\"ret\" : 1}";
exit;

<?php
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");

$util = new FrontCommonUtil();
$fb = new FormBean();

$depth1_arr = $fb->getForm("emergency_arr");

foreach ($depth1_arr as $depth1) {
    if (empty($depth1)) {
        continue;
    }

    if (!$util->chkAvailEmergency($depth1)) {
        $msg = "당일판 시간이 초과된 주문이 있습니다. 다시 작성해주세요.";
        echo "{\"ret\" : -1, \"msg\" : \"" . $msg . "\"}";
        exit;
    }
}

echo "{\"ret\" : 1}";

exit;

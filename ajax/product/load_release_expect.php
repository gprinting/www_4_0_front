<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/front/ReleaseExpectUtil.inc");

// $fb include sess_common
$fb = $fb->getForm();

$util = new ReleaseExpectUtil([
    "cate_sortcode" => $fb["cate_sortcode"]
    ,"paper_info"   => $fb["paper_info"]
    ,"size_info"    => $fb["size_info"]
    ,"amt"          => $fb["amt"]
    ,"page_arr"     => $fb["page_arr"]
    ,"after_info"   => $fb["after_info"]
    ,"emergency"    => boolval($fb["emergency"])
]);

$ret = $util->calcReleaseExpectTime();

if (empty($ret)) {
    echo "{\"month\" : \"-1\", \"day\" : \"-1\", \"hour\" : \"-1\"}";
} else {
    echo json_encode($ret);
}


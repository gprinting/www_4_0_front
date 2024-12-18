<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$commonUtil = new CommonUtil();
$util = new FrontCommonUtil();
$dao = new FrontCommonDAO();

// $fb include sess_common
$fb = $fb->getForm();

$ret = 0;

$param = [];
$param["util"] = $commonUtil;

$dvs_arr = $fb["dvs_arr"];
$page_arr = $fb["page_arr"];
$paper_arr = $fb["paper_arr"];

// 예상무게
$expect_weight = 0.0;
// 덩어리
$box_count = 1;

$cate_sortcode = $fb["cate_sortcode"];
$amt           = $fb["amt"];
$pos_num       = $fb["pos_num"];
$amt_unit      = $fb["amt_unit"];
$count         = $fb["count"];
$cut_wid_size  = $fb["cut_wid_size"];
$cut_vert_size = $fb["cut_vert_size"];
$basisweight   = $fb["basisweight"];

//1. 예상무게
$length = count($dvs_arr);
for ($i = 0; $i < $length; $i++) {
    $dvs = $dvs_arr[$i];
    $page = $page_arr[$i];

    if ($page === '0') {
        continue;
    }

    $param["prefix"] = $dvs . '_';
    $param["fb"] = [
         $dvs . "_cate_sortcode" => $cate_sortcode
        ,$dvs . "_amt"           => $amt
        ,$dvs . "_pos_num"       => $pos_num
        ,$dvs . "_amt_unit"      => $amt_unit
        ,$dvs . "_count"         => $count
        ,$dvs . "_page"          => $page
        ,$dvs . "_paper"         => $paper_arr[$i]
        ,$dvs . "_cut_wid_size"  => $cut_wid_size
        ,$dvs . "_cut_vert_size" => $cut_vert_size
        ,$dvs . "_basisweight"   => $basisweight
    ];

    $expect_weight += $util->calcExpectWeight($conn, $dao, $param);
}

//2. 덩어리 수
$lump_info = $util->getLumpCount($cate_sortcode, $expect_weight);

$json = "{\"weight\" : \"%s\", \"box\" : \"%s\", \"price\" : \"%s\"}";
echo sprintf($json, $expect_weight
                  , $lump_info["lump_count"]
                  , $lump_info["price"]);

$conn->Close();

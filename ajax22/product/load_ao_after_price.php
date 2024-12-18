<?php
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/ActualPriceUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/product/ProductAoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new FrontCommonUtil();
$aoUtil = new ActualPriceUtil();
$dao = new ProductAoDAO();
$fb = new FormBean();

$fb = $fb->getForm();

//$conn->debug=1;

$cate_sortcode = $fb["cate_sortcode"];

$after_name = $fb["after_name"];
$depth1 = $fb["depth1"];
$depth2 = $fb["depth2"];
$depth3 = $fb["depth3"];
$amt    = $fb["amt"];

$wid  = $fb["wid"];
$vert = $fb["vert"];

// 미싱류 위치
$t_pos = $fb["t_pos"] ?? -1;
$b_pos = $fb["b_pos"] ?? -1;
$l_pos = $fb["l_pos"] ?? -1;
$r_pos = $fb["r_pos"] ?? -1;
// 고리류 위치별 개수
$t_cnt = $fb["t_cnt"];
$b_cnt = $fb["b_cnt"];
$l_cnt = $fb["l_cnt"];
$r_cnt = $fb["r_cnt"];

$param = [];
$param["after_name"] = $after_name;
$param["depth1"] = $depth1;
$param["depth2"] = $depth2;
$param["depth3"] = $depth3;

//! 게릴라 현수막이면서 미싱이면 값 0원으로 처리
if ($cate_sortcode === "002005001" && $after_name === "미싱") {
    $price = 0;
    goto END;
}

//! 페트배너면서 고리-사방쇠고리면 값 0원으로 처리
$cate_sortcode_m = substr($cate_sortcode, 0, 6);
if ($cate_sortcode_m === "002002") {
    if ($after_name === "고리" && $depth1 === "사방쇠고리") {
        $price = 0;
    } else if ($after_name === "쿨코팅") {
        if ($depth1 === "무광") {
            $price = intval($amt) * 7000;
        } else if ($depth1 === "유광") {
            $price = intval($amt) * 8000;
        }
    }
    goto END;
}

// 해당 후공정명에 해당하는 단가 검색
$unitprice = $dao->selectAoAfterUnitprice($conn, $param);

$param = [];
$param["unitprice"] = $unitprice;
$param["pos_arr"] = [
     "t" => $t_pos
    ,"b" => $b_pos
    ,"l" => $l_pos
    ,"r" => $r_pos
];
$param["cnt_arr"] = [
     "t" => $t_cnt
    ,"b" => $b_cnt
    ,"l" => $l_cnt
    ,"r" => $r_cnt
];
$param["amt"] = $amt;
$param["wid"] = $wid;
$param["vert"] = $vert;

$price = $aoUtil->calcAfterPrice($param);
$price *= 1.1;

goto END;

BLANK:
    echo "{}";
    $conn->Close();
    exit;
END:
    $ret = "{\"price\" : \"%s\"}";

    echo sprintf($ret, $util->ceilVal($price));
    $conn->Close();
    exit;

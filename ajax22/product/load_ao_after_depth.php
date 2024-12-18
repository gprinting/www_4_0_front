<?php
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/product/ProductAoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ProductAoDAO();
$fb = new FormBean();

$fb = $fb->getForm();

$cate_sortcode = $fb["cate_sortcode"];
$after_name    = $fb["after_name"];
$depth1        = $fb["depth1"];
$depth2        = $fb["depth2"];

$flag          = $fb["flag"];

if ($flag === 'Y') {
    $flag = true;
} else {
    $flag = false;
}

$param = [];
$param["cate_sortcode"] = $cate_sortcode;
$param["after_name"]    = $after_name;
$param["depth1"]        = $depth1;
$param["depth2"]        = $depth2;

// 카테고리와 후공정명으로 후공정 하위 depth 검색
$rs = $dao->selectCateAoAfterLower($conn, $param, $flag);

$ret = '';
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $ret .= option($fields["lower_depth"], $fields["mpcode"]);

    $rs->MoveNext();
}

echo $ret;
$conn->Close();

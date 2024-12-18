<?
/*
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/10/13 엄준현 생성
 * 2016/12/01 엄준현 수정(쿼리 order by 추가, 배열값 이상한부분 수정)
 *============================================================================
 *
 */
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/SheetDAO.inc");
include_once($_SERVER["INC"] . "/common_define/common_config.inc");
include_once(INC_PATH . '/common_lib/CommonUtil.inc');

if ($is_login === false) {
    echo "<script>alert('로그인이 필요합니다.'); return false;</script>";
    exit();
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SheetDAO();
$util = new CommonUtil();

$session = $fb->getSession();
$fb = $fb->getForm();

$param = array();
$param["member_seqno"]     = $session["org_member_seqno"];
$param["order_common_seqno"] = $fb["order_seqno"];
$param["order_file_seqno"]   = $fb["file_seqno"];

$rs = $dao->selectOrderFile($conn, $param);

$base_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE;

$save_path = $rs["file_path"];
$save_file_name = $rs["save_file_name"];
$down_file_name = $rs["origin_file_name"];

$full_path = $base_path . $save_path . $save_file_name;

$file_size = filesize($full_path);

if ($util->isIe()) {
    $down_file_name = $util->utf2euc($down_file_name);
}

header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$down_file_name\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $file_size");

ob_flush();
flush();
readfile($full_path);
?>

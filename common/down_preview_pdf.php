<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");
include_once(INC_PATH . "/common_define/common_config.inc");

if ($is_login === false) {
    header("Location: /main/main.html");
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new OrderInfoDAO();
$prdtDAO = new ProductCommonDAO();
$zip = new ZipArchive;

$order_common_seqno = $fb->form("seqno");

$cate_sortcode = $dao->selectOrderCateSortcode($conn, $order_common_seqno);
$flattyp_yn = $prdtDAO->selectCateInfo($conn, $cate_sortcode)["flattyp_yn"];

$preview_rs = false;
if ($flattyp_yn === 'Y') {
    $preview_rs = $dao->selectSheetPreviewPath($conn, $order_common_seqno);
} else {
    $preview_rs = $dao->selectBrochurePreviewPath($conn, $order_common_seqno);
}

$base_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE;

$pdf_info_arr = [];
$pdf_info_arr[] = [
    "path" => "/home/sitemgr/test/",
    "name" => "test1.pdf"
];
$pdf_info_arr[] = [
    "path" => "/home/sitemgr/test/",
    "name" => "test2.pdf"
];
/*
*/
while ($preview_rs && !$preview_rs->EOF) {
    $fields = $preview_rs->fields;

    $pdf_info_arr[] = [
        "path" => $base_path . $fields["file_path"] . '/',
        "name" => $fields["file_name"]
    ];
    $preview_rs->MoveNext();
}

if (count($pdf_info_arr) > 1) {
    // pdf파일 여러개면 zip으로 묶음
    $zip_path = $_SERVER["DOCUMENT_ROOT"] . "/tmp/" . $order_common_seqno . ".zip";

    if ($zip->open($zip_path, ZipArchive::CREATE) !== true) {
        echo "<script>alert('파일생성에 실패했습니다.');</script>";
        exit;
    }

    foreach ($pdf_info_arr as $pdf_path) {
        $zip->addFile($pdf_path["path"] . $pdf_path["name"], $pdf_path["name"]);
    }

    $zip->close();

    $file_path = $zip_path;
    $file_size = filesize($zip_path);
    $down_file_name = "preview.zip";
} else {
    $file_path = $pdf_info_arr[0]["path"] . $pdf_info_arr[0]["name"];
    $file_size = filesize($file_path);
    $down_file_name = $pdf_info_arr[0]["name"];
}

header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$down_file_name\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $file_size");

ob_clean();
flush();
readfile($file_path);

unset($file_path);

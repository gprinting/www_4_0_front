<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/front/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ProductCommonDAO();
$fb = new FormBean();

$cate_sortcode = $fb->form("cs");
// 사진 위치
$seq = $fb->form("seq");
// b -> 원본, t -> 섬네일
$dvs = $fb->form("dvs");

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["seq"] = $seq;

// 상품 사진 정보 생성
$rs = $dao->selectCatePhoto($conn, $param);

$file_path = NIMDA_PATH . $rs->fields["file_path"];
$file_name = $rs->fields["save_file_name"];

$temp = explode('.', $file_name);
$ext = strtolower($temp[1]);

if ($dvs === "t") {
    $file_name = $temp[0] . "_75_75." . $ext;
}

$full_path = $file_path . $file_name;

if (is_file($full_path) === false) {
    $ext = "jpg";
    $full_path = NO_IMAGE;
}

$mime = "";
if ($ext === "jpg" || $ext === "jpeg") {
    $mime = "image/jpeg";
} else if ($ext === "png") {
    $mime = "image/png";
}

header("Content-Type:" . $type);
header("Content-Length:" . filesize($full_path));
readfile($full_path);
?>

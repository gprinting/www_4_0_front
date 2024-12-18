<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/define/front/common_config.inc");
include_once(INC_PATH . "/define/front/message.inc");
include_once(INC_PATH . "/com/nexmotion/html/front/product/QuickEstimate.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/product/ProductNcDAO.inc");
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");

//include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");
//$dao = new ProductCommonDAO();

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ProductNcDAO();
$frontUtil = new FrontCommonUtil();
$fb = new FormBean();

$sortcode_b = $fb->form("cate_sortcode");

// 상품 사진 정보 생성
$picture_arr = array();
$param["cate_sortcode"] = $sortcode_b;
$picture_rs = $dao->selectCatePhoto($conn, $param);

if ($picture_rs->EOF) {
    $picture_arr[0]["org"]   = NO_IMAGE;
    $picture_arr[0]["thumb"] = NO_IMAGE_THUMB;
    $picture_arr[1]["org"]   = NO_IMAGE;
    $picture_arr[1]["thumb"] = NO_IMAGE_THUMB;
    $picture_arr[2]["org"]   = NO_IMAGE;
    $picture_arr[2]["thumb"] = NO_IMAGE_THUMB;
    $picture_arr[3]["org"]   = NO_IMAGE;
    $picture_arr[3]["thumb"] = NO_IMAGE_THUMB;
}

$root = $_SERVER["DOCUMENT_ROOT"];
$i = 0;
while ($picture_rs && !$picture_rs->EOF) {
    $file_path = $picture_rs->fields["file_path"];
    $file_name = $picture_rs->fields["save_file_name"];

    $full_path = $file_path . $file_name;

    $chk_path = $root . $full_path;

    if (is_file($chk_path) === false) {
        $full_path = NO_IMAGE;
    }

    $temp = explode('.', $full_path);
    $ext = strtolower($temp[1]);

    $thumb_full_path = $temp[0] . "_75_75." . $ext;

    $picture_arr[$i]["org"]     = $full_path;
    $picture_arr[$i++]["thumb"] = $thumb_full_path;

    $picture_rs->MoveNext();
}

$ret = "{\"org1\" : \"%s\",\"org2\" : \"%s\",\"org3\" : \"%s\",\"org4\" : \"%s\", \"thumb1\" : \"%s\",\"thumb2\" : \"%s\",\"thumb3\" : \"%s\",\"thumb4\" : \"%s\"}";

echo sprintf($ret, $picture_arr[0]["org"]
                , $picture_arr[1]["org"]
                , $picture_arr[2]["org"]
                , $picture_arr[3]["org"]
                , $picture_arr[0]["thumb"]
                , $picture_arr[1]["thumb"]
                , $picture_arr[2]["thumb"]
                , $picture_arr[3]["thumb"]);
$conn->Close();
?>

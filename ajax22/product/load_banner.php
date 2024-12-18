<?
define(INC_PATH, $_SERVER["INC"]);
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

// 상품 배너 정보 생성
$banner_rs = $dao->selectCateBanner($conn, $sortcode_b);

$url_addr  = $banner_rs->fields["url_addr"];
$target_yn = $banner_rs->fields["target_yn"];

$file_path = NIMDA_PATH . $banner_rs->fields["file_path"];
$file_name = $banner_rs->fields["save_file_name"];
$full_path = $file_path . $file_name;

if (is_file($full_path) === false) {

    $ret  = "{\"banner_display\" : \"none\"";
    $ret .= ",\"banner_url\" : \"#none\"";
    $ret .= ",\"banner_target\" : \"_self\"";
    $ret .= ",\"banner_src\" : \"#none\"}";

} else {

    $target_yn = ($target_yn === "Y") ? "_self" : "_blank";
    $banner_src = $full_path;

    $ret  = "{\"banner_display\" : \"block\"";
    $ret .= ",\"banner_url\" : \"%s\"";
    $ret .= ",\"banner_target\" : \"%s\"";
    $ret .= ",\"banner_src\" : \"%s\"}";

    $ret = sprintf($ret, ""
                       , $url_addr
                       , $target_yn
                       , $banner_src);
}

echo $ret;
$conn->Close();
?>

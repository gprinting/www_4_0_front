<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/front/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/define/front/product_info_class.inc");

$frontUtil = new FrontCommonUtil();

// 상품이동으로 넘어온 제목 있으면 처리
$title = urldecode($fb->form('t'));
$template->reg("title", $title); 

// 쿼리 검색조건용 배열
$param = [];

$cate_sortcode = $fb->form("cs");

// 카테고리분류코드( top / mid / bottom )
$sortcode_arr = $frontUtil->getTMBCateSortcode($conn, $dao, $cate_sortcode);

$sortcode_t = $sortcode_arr["sortcode_t"];
$sortcode_m = $sortcode_arr["sortcode_m"];
$sortcode_b = $sortcode_arr["sortcode_b"];

$template->reg("cs", $sortcode_b); 

// 상품 사진 정보 생성
$picture_arr = [];
$param["cate_sortcode"] = $sortcode_b;
$picture_rs = $dao->selectCatePhoto($conn, $param);
if ($picture_rs->EOF) {
    $picture_arr[0]["org"]   = NO_IMAGE;
    $picture_arr[0]["thumb"] = NO_IMAGE_THUMB;
    $picture_arr[0]["view"] = NO_IMAGE;
    $picture_arr[1]["org"]   = NO_IMAGE;
    $picture_arr[1]["thumb"] = NO_IMAGE_THUMB;
    $picture_arr[1]["view"] = NO_IMAGE;
    $picture_arr[2]["org"]   = NO_IMAGE;
    $picture_arr[2]["thumb"] = NO_IMAGE_THUMB;
    $picture_arr[2]["view"] = NO_IMAGE;
    $picture_arr[3]["org"]   = NO_IMAGE;
    $picture_arr[3]["thumb"] = NO_IMAGE_THUMB;
    $picture_arr[3]["view"] = NO_IMAGE;
}

$root = INC_PATH;
$i = 0;
while ($picture_rs && !$picture_rs->EOF) {
    $file_path = $frontUtil->getAliasAttachPath($picture_rs->fields["file_path"]);
    $file_name = $picture_rs->fields["save_file_name"];

    $full_path = $file_path . $file_name;

    $chk_path = $root . $full_path;
    /*
    if (is_file($chk_path) === false) {
        $full_path = NO_IMAGE;
    }
    */

    $temp = explode('.', $full_path);
    $ext = strtolower($temp[1]);

    $thumb_full_path = $temp[0] . "_75_75." . $ext;

    $view_full_path = $temp[0] . "_315_315." . $ext;

    $picture_arr[$i]["org"]    = $full_path;
    $picture_arr[$i]["thumb"]  = $full_path;
    $picture_arr[$i++]["view"] = $full_path;

    $picture_rs->MoveNext();
}

unset($picture_rs);

$template->reg("pic_org_1"  , $picture_arr[0]["org"]); 
$template->reg("pic_thumb_1", $picture_arr[0]["thumb"]); 
$template->reg("pic_view_1" , $picture_arr[0]["view"]); 

$template->reg("pic_org_2"  , $picture_arr[1]["org"]); 
$template->reg("pic_thumb_2", $picture_arr[1]["thumb"]); 
$template->reg("pic_view_2" , $picture_arr[1]["view"]); 

$template->reg("pic_org_3"  , $picture_arr[2]["org"]); 
$template->reg("pic_thumb_3", $picture_arr[2]["thumb"]); 
$template->reg("pic_view_3" , $picture_arr[2]["view"]); 

$template->reg("pic_org_4"  , $picture_arr[3]["org"]); 
$template->reg("pic_thumb_4", $picture_arr[3]["thumb"]); 
$template->reg("pic_view_4" , $picture_arr[3]["view"]); 

unset($param);

// 상품 배너 정보 생성
$banner_rs = $dao->selectCateBanner($conn, $sortcode_b);

$url_addr  = $banner_rs->fields["url_addr"];
$target_yn = $banner_rs->fields["target_yn"];

$file_path = $banner_rs->fields["file_path"];
$file_name = $banner_rs->fields["save_file_name"];
$full_path = $file_path . $file_name;

unset($banner_rs);

if (empty($url_addr) === true) {
    $url_addr = "#none";
}

if (empty($target_yn) === true) {
    $target_yn = "N";
}

if (is_file(INC_PATH . $full_path) === false) {
    $template->reg("banner_attr"  , "style=\"display:none;\""); 
    $template->reg("banner_url"   , "#none"); 
    $template->reg("banner_target", "_self"); 
    $template->reg("banner_src"   , '#none'); 
} else {
    $target_yn = ($target_yn === "Y") ? "_self" : "_blank";
    $banner_src = $full_path;

    $template->reg("banner_attr"  , ''); 
    $template->reg("banner_url"   , "http://" . $url_addr); 
    $template->reg("banner_target", $target_yn); 
    $template->reg("banner_src"   , $banner_src);
}

// 카테고리 셀렉트박스 생성
$cate_mid = $dao->selectCateHtml($conn, $dvs,
                                 $sortcode_m, $sortcode_t, $is_mobile);
$cate_bot = $dao->selectCateHtml($conn, $dvs,
                                 $sortcode_b, $sortcode_m, $is_mobile); 
//$template->reg("cate_top", $cate_top); 
$template->reg("cate_mid", $cate_mid); 
$template->reg("cate_bot", $cate_bot);

$cate_name = $dao->selectCateName($conn, $sortcode_m);
$template->reg("cate_sortcode", $sortcode_m); 
$template->reg("cate_name"    , $cate_name); 

// 서브배너 이미지값 처리
if (!empty(SUB_BANNER_IMAGE_ARR[$sortcode_b])) {
    $sub_banner_img = SUB_BANNER_IMAGE_ARR[$sortcode_b];
} else {
    $sub_banner_img = SUB_BANNER_IMAGE_ARR[$sortcode_m];
}
$template->reg("sub_banner", $sub_banner_img); 

// 모바일, 로그인여부 등록
$template->reg("is_login", intval($is_login));
$template->reg("is_mobile", intval($is_mobile));

// 기본값 배열 받아서 decode
$def_arr = json_decode($fb->form("def_json"), true);

// 파일업로드 스크립트용 파라미터 처리
$template->reg("file_list_id", "order_file_list"); 
$template->reg("file_upload_url", "/proc/order/upload_file.php"); 
$template->reg("file_button_id", "order_file"); 
$template->reg("file_max_size", "2GB"); 

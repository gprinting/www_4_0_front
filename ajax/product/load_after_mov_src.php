<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");

$fb = new FormBean();

$base_path = "/design_template/movie/after/foldline/";
$file_ext = ".mp4";

$depth1 = intval($fb->form("depth1"));
$depth2 = $fb->form("depth2");

$file_name = $depth1 . '_';

switch ($depth2) {
case "비중앙" :
case "중앙" :
    $file_name .= "center";
    break;
case "정접지" :
    $file_name .= "right";
    break;
case "반접지후정접지" :
    $file_name .= "half_right";
    break;
case "N접지후반접지" :
    $file_name .= "n_fold_half";
    break;
case "N접지" :
    $file_name .= "n_fold";
    break;
case "정접지후반접지" :
    $file_name .= "right_half";
    break;
case "반접지후N접지" :
    $file_name .= "half_n_fold";
    break;
case "병풍접지후반접지" :
    $file_name .= "screen_half";
    break;
case "병풍접지" :
    $file_name .= "screen";
    break;
case "DM 8p접지" :
    $file_name .= "dm8p";
    break;
case "두루마리접지" :
    $file_name .= "scroll";
    break;
case "십자접지" :
    $file_name .= "cross";
    break;
case "대문접지" :
    $file_name .= "door";
    break;
}

$file_name .= $file_ext;

echo $base_path . $file_name;
?>

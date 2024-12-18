<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/front/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");

// 모바일, 로그인여부 등록
$template->reg("is_login", intval($is_login)); 
$template->reg("is_mobile", intval($is_mobile)); 
?>

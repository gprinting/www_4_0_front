<?
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/Template.inc");

$template = new Template();
$template->reg("test", "테스트12312");
$template->htmlPrint($_SERVER["PHP_SELF"]);
?>

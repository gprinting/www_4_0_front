<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
$fb->removeAllSession();

if($_SERVER["SELL_SITE"] == "GP" || $_SERVER["SELL_SITE"] == "DP")
    header("Location: /main22/main.html");
else
    header("Location: /main/main.html");
?>

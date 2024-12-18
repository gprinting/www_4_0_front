<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/html/front/product/EstimatePop.inc");

// 실제 html 생성부분
include_once($_SERVER["DOCUMENT_ROOT"] . "/product/common/esti_pop_common.php");

$to = null;
if ($fb["email_dvs"] === 'm') {
    $to = sprintf("%s@%s", $fb["m_acc"], $fb["m_dom"]);
} else {
    $to = sprintf("%s@%s", $fb["d_acc"], $fb["d_dom"]);
}

$subject = sprintf("[%s] %s-%s-%s 견적서", $param["sell_site"]
                                         , $param["year"]
                                         , $param["month"]
                                         , $param["day"]);
$subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";

$html_base  = "\n <html lang=\"ko\">";
$html_base .= "\n     <head>";
$html_base .= "\n         <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">";
$html_base .= "\n         <meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\">";
$html_base .= "\n         <link rel=\"stylesheet\" href=\"$_SERVER['HTTP_HOST']/design_template/css/reset.css\" />";
$html_base .= "\n         <link rel=\"stylesheet\" href=\"$_SERVER['HTTP_HOST']/design_template/css/common.css\" />";
$html_base .= "\n         <link rel=\"stylesheet\" href=\"$_SERVER['HTTP_HOST']/design_template/css/product.css\" />";
$html_base .= "\n     </head>";
$html_base .= "\n     <body>";
$html_base .= $html_top . $html_mid . $html_bot;
$html_base .= "\n     </body>";
$html_base .= "\n </html>";

$header = "From: =?UTF-8?B?" . base64_encode($param["sell_site"]) . "?= <master@dprinting.biz>\r\n" .
          "Reply-To: master@dprinting.biz\r\n" .
          "Mime-Version: 1.0\r\n" .
          "Content-Type: text/html; cmsgharset=utf-8\r\n";

echo $html_base . "\n";

$ret = mail($to, $subject, $html_base, $header) ? 'T' : 'F';

echo $ret;

$conn->Close();
exit;
?>

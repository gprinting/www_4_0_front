<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/define/front/product_info_class.inc');
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ProductCommonDAO();

$frontUtil = new FrontCommonUtil();

$fb = new FormBean();

$page_arr = ProductInfoClass::PAGE_ARR;

$title         = urlencode($fb->form('t'));
$cate_sortcode = $fb->form("cs");
$def_json = $fb->form("def_json");
$def_json = str_replace('"', "&quot;", $def_json);

$sortcode_arr  = $frontUtil->getTMBCateSortcode($conn, $dao, $cate_sortcode);

$sortcode_t = $sortcode_arr["sortcode_t"];
$sortcode_m = $sortcode_arr["sortcode_m"];
$sortcode_b = $sortcode_arr["sortcode_b"];

if (is_array($page_arr[$sortcode_t])) {
    $page_name = $page_arr[$sortcode_t][$sortcode_m];

    if (!empty($page_arr[$sortcode_t][$sortcode_b])) {
        $page_name = $page_arr[$sortcode_t][$sortcode_b];
    }

    if (empty($page_name)) {
        $page_name = $page_arr[$sortcode_t]["ELSE"];
    }
} else {
    $page_name = $page_arr[$sortcode_t];
}

$html = <<<html
    <html>
        <head>
        </head>
        <body>
            <form id="frm" method="post" action="/product/{$page_name}">
                <input type="hidden" name="cs" value="{$sortcode_b}" />
                <input type="hidden" name="t" value="{$title}" />
                <input type="hidden" name="def_json" value="{$def_json}" />
            </form>

            <script>
                (function() {
                    document.getElementById("frm").submit();
                })();
            </script>
        </body>
    </html>
html;

echo $html;

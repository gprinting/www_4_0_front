<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/NonStandardUtil.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ProductCommonDAO();
$fb = new FormBean();

$param = [];
$param["conn"] = $conn;
$param["dao"] = $dao;
$util = new NonStandardUtil($param);

$cate_sortcode = $fb->form("cate_sortcode");
$paper_mpcode  = $fb->form("paper_mpcode");
$cut_wid  = intval($fb->form('w'));
$cut_vert = intval($fb->form('v'));

// 사이즈, 해당 종이에 물려있는 규격 맵핑코드 검색
unset($param);
$param["cate_sortcode"] = $cate_sortcode;
$param["paper_mpcode"]  = $paper_mpcode;

$stan_mpcode_rs = $dao->selectCateStanMpcodeByPrice($conn, $param);

$mpcode_arr = [];
while ($stan_mpcode_rs && !$stan_mpcode_rs->EOF) {
    $mpcode_arr[] = $stan_mpcode_rs->fields["cate_stan_mpcode"];

    $stan_mpcode_rs->MoveNext();
}

// 독판류
if (empty($mpcode_arr)) {
    $stan_mpcode_rs = $dao->selectCateSize($conn, $param);

    while ($stan_mpcode_rs && !$stan_mpcode_rs->EOF) {
        $mpcode_arr[] = $stan_mpcode_rs->fields["mpcode"];

        $stan_mpcode_rs->MoveNext();
    }
}

// 규격 맵핑코드 중에 제일 큰 사이즈 검색
unset($param);
$param["cate_sortcode"] = $cate_sortcode;
$param["cut_wid"]       = $cut_wid;
$param["cut_vert"]      = $cut_vert;
$param["mpcode"]        = $dao->arr2paramStr($conn, $mpcode_arr);

$similar_info_arr = $util->getSimilarSizeInfo($param);

$name   = $similar_info_arr["name"];
$affil  = $similar_info_arr["affil"];
$mpcode = $similar_info_arr["mpcode"];
$org_cut_wid  = $similar_info_arr["org_cut_wid"];
$org_cut_vert = $similar_info_arr["org_cut_vert"];
$max_wid_size  = $similar_info_arr["max_wid_size"];
$max_vert_size = $similar_info_arr["max_vert_size"];

$wid_divide1  = floor($org_cut_wid / $cut_wid);
$vert_divide1 = floor($org_cut_vert / $cut_vert);
$divide1 = $wid_divide1 * $vert_divide1;

$wid_divide2  = floor($org_cut_wid / $cut_vert);
$vert_divide2 = floor($org_cut_vert / $cut_wid);
$divide2 = $wid_divide2 * $vert_divide2;

$divide = ($divide1 > $divide2) ? $divide1 : $divide2;

$json  = "{\"name\"     : \"%s\",";
$json .= " \"affil\"    : \"%s\",";
$json .= " \"divide\"   : \"%s\",";
$json .= " \"mpcode\"   : \"%s\",";
$json .= " \"max_wid\"  : \"%s\",";
$json .= " \"max_vert\" : \"%s\"}";

echo sprintf($json, $name
                  , $affil
                  , ($divide < 1) ? 1 : $divide
                  , $mpcode
                  , $max_wid_size
                  , $max_vert_size);
?>

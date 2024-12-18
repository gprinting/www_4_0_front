<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/cpn_info_define.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ProductCommonDAO();

$session = $fb->getSession();
$fb = $fb->getForm();

//$json_text = $_GET["json"];
$json_text = $_POST["json"];

// 견적서 팝업의 경우 param이 json형태이므로 처리
$json = json_decode($json_text, true);

$rs = $dao->selectChannelInfo(
    $conn, ["sell_site" => $_SERVER["SELL_SITE"]]
);

/*
echo $json_text;
echo "!!!\n";
var_dump($json);
exit;
*/

if (!empty($json)) {
    $fb = $json;
}

//print_r($fb);

$cpn_rs  = BASIC_COM_INFO;
$aft_arr = ProductInfoClass::AFTER_ARR;

$booklet = $fb["booklet"];

$param = array();
$param["year"]  = date('Y');
$param["month"] = date('m');
$param["day"]   = date('d');

$param["sell_site"]   = $rs["company_name"];
$param["repre_name"]  = $rs["repre_name"];
$param["repre_num"]   = $rs["repre_num"];
$param["addr"]        = $rs["addr"];
$param["fax"]        = $rs["fax"];
//$param["addr_detail"] = $cpn_rs["addr_detail"];

$param["common_cate_name"] = $fb["common_cate_name"];
$param["cate_name_arr"] = $fb["cate_name"];
$param["paper_arr"]     = $fb["paper"];
$param["size_arr"]      = $fb["size"];
$param["tmpt_arr"]      = $fb["tmpt"];
$param["amt_arr"]       = $fb["amt"];
$param["amt_unit_arr"]  = $fb["amt_unit"];
$param["count_arr"]     = $fb["count"];
$param["page_arr"]      = $fb["page"];
$param["after_arr"]     = $fb["after"];
$param["after_det_arr"] = $fb["after_det"]; // 180226 추가, 후공정 세부내역

$param["paper_price"]  = $fb["paper_price"];
$param["print_price"]  = $fb["print_price"];
$param["output_price"] = $fb["output_price"];
$param["opt_price"]    = $fb["opt_price"];
$param["supply_price"] = $fb["supply_price"];
$param["tax"]          = $fb["tax"];
$param["sell_price"]   = $fb["sell_price"];
$param["sale_price"]   = $fb["sale_price"];
$param["pay_price"]    = $fb["pay_price"];

foreach ($aft_arr as $aft_ko => $aft_en) {
    $param[$aft_en . "_price"]  = $fb[$aft_en . "_price"];
} 

if ($is_login === false) {
    $param["member_mail"]    = '-';
    $param["member_name"]    = "손님";
    $param["member_tel"]     = '-';
    $param["member_mng"]     = '-';
    $param["member_mng_tel"] = '-';
} else {
    $member_seqno = $session["member_seqno"];
    $member_rs = $dao->selectMemberInfo($conn, $member_seqno);

    $param["member_mail"]    = $member_rs["mail"];
    $param["member_name"]    = $session["name"];
    $param["member_tel"]     = $member_rs["tel_num"];
    $param["member_mng"]     = $session["member_mng_name"];
    $param["member_mng_tel"] = $session["member_mng_tel"];
}

unset($fb);
unset($member_rs);
unset($cpn_rs);
?>

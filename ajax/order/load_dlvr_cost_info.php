<?
/***********************************************************************************
 *** 프로 젝트 : 3.0
 *** 개발 영역 : 택배운임료 측정
 *** 개  발  자 : 조현식
 *** 개발 날짜 : 2016.06.29
 ***********************************************************************************/

define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/SheetDAO.inc");
include_once(INC_PATH . "/com/nexmotion/doc/front/order/SheetPopup.inc");
include_once(INC_PATH . "/classes/dprinting/PriceCalculator/Common/DPrintingFactory.php");

/***********************************************************************************
**** 기초 데이터 사용을 위한 요소들 정의
 ***********************************************************************************/

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();
$session = $fb->getSession();
$fb = $fb->getForm();
$dao = new SheetDAO();
$util = new FrontCommonUtil();
$state_arr = $session["state_arr"];

//그루핑이 된 목록들은 sortcode가 같고 합쳐서 20kg가 안넘어야한다
$seqAll = $fb["seqno"];
$dlvr_way = $fb["dlvr_way"];
$zipcode = $fb["zipcode"];

if (empty($seqAll) === false) {
    $seqAll = explode('|', $seqAll);
    $seqAll = $dao->arr2paramStr($conn, $seqAll);
}

/***********************************************************************************
******* 데이터 불러오기
 ***********************************************************************************/

$param = array();
$param["member_seqno"] = $session["org_member_seqno"];
$param["order_state"]  = $state_arr["주문대기"];
$param["order_common_seqno"] = $seqAll;
$param['zipcode'] = $zipcode;
$sheet_list = $dao->selectCartOrderList($conn, $param);

$i = 0;
$dlvr_arr = array();
while($sheet_list && !$sheet_list->EOF) {
    $fields = $sheet_list->fields;
    $expec_release_date = explode(' ', $fields['expec_release_date'])[0];

    $dlvr_arr[$expec_release_date][] = [
         'order_detail'       => $fields['order_detail']
        ,'order_common_seqno' => $fields['order_common_seqno']
        ,'amt'                => $fields['amt']
        ,'count'              => $fields['count']
        ,'order_detail'       => $fields['order_detail']
        ,'cate_sortcode'      => $fields['cate_sortcode']
        ,'expec_weight'       => $fields['expec_weight']
    ];

	$i++;
	$sheet_list->moveNext();
}

/***********************************************************************************
**** 가져온 데이터들을 통해 택배 운임요금 계산
 ***********************************************************************************/

$factory = new DPrintingFactory();
$dlvr_cost_nc = '';
$dlvr_cost_bl = '';
$weight_leaflet = '';
$weight_namecard = '';
$seq_leaflet = '';
$seq_namecard = '';
$ncBoxCount = '';
$blBoxCount = '';
$island_cost = '';

foreach ($dlvr_arr as $expec_release_date => $dlvr_param) {
    if($dlvr_way == "01") {
        $rs = $dao->selectIslandParcelCost($conn, $param);
        while ($rs && !$rs->EOF) {
            $tmp_island_cost = $rs->fields["price"];
            $rs->MoveNext();
        }
    }

    $tmp_weight_namecard = 0;
    $tmp_seq_namecard = '';
    $tmp_dlvr_cost_nc = 0;

    $count_dlvr_param = count($dlvr_param);
    for($i = 0; $i < $count_dlvr_param; $i++) {
        $cate_sortcode = $dlvr_param[$i]['cate_sortcode'];
        $product = $factory->create($cate_sortcode);

        $sort = $product->getSort();

        // 명함은 주문건의 모든 상품을 합쳐서 배송비를 받아야함
        if ($sort == "namecard") {
            $tmp_weight_namecard += $dlvr_param[$i]['expec_weight'];
            $tmp_seq_namecard .= $dlvr_param[$i]['order_common_seqno'] . "|";
        }
        // 전단은 건당으로 배송비를 받아야함
        else if ($sort == "leaflet") {
            $param['sort'] = $sort;
            $param['expec_weight'] = $dlvr_param[$i]['expec_weight'];
            $result = $product->getDlvrCost($param, $dlvr_way);
            $tmp_dlvr_cost_bl += $result["price"];
            $seq_leaflet .= $dlvr_param[$i]['order_common_seqno'] . "|";
            $tmp_blBoxCount = $result["box_count"];
            $tmp_boxCount += $tmp_blBoxCount;
            //$tmp_dlvr_cost_bl += $tmp_blBoxCount * $tmp_island_cost;
            $tmp_weight_leaflet += $dlvr_param[$i]['expec_weight'];
        }
    }

    if($tmp_seq_namecard != "") {
        $tmp_seq_namecard = substr($tmp_seq_namecard , 0, -1);
    }

    if($tmp_weight_namecard != 0) {
        $product = $factory->create("003001001");
        $param = array();
        $param['zipcode'] = $zipcode;
        $param['expec_weight'] = $tmp_weight_namecard;
        $result = $product->getDlvrCost($param, $dlvr_way);

        $tmp_dlvr_cost_nc += $result["price"];
    }

    /*
    if($weight_leaflet != 0) {
        $product = $factory->create("005001001");
        $param = array();
        $param['zipcode'] = $zipcode;
        $param['expec_weight'] = $weight_leaflet;
        $dlvr_cost_bl += $product->getDlvrCost($param, $dlvr_way);
    }
    */

    $dlvr_cost_nc    .= empty($tmp_dlvr_cost_nc)    ? '' : $tmp_dlvr_cost_nc    . '|';
    $dlvr_cost_bl    .= empty($tmp_dlvr_cost_bl)    ? '' : $tmp_dlvr_cost_bl    . '|';
    $seq_namecard    .= empty($tmp_seq_namecard)    ? '' : $tmp_seq_namecard    . '|';
    $island_cost     .= empty($tmp_island_cost)     ? '' : $tmp_island_cost     . '|';
    $ncBoxCount      .= empty($tmp_ncBoxCount)      ? '' : $tmp_ncBoxCount      . '|';
    $blBoxCount      .= empty($tmp_blBoxCount)      ? '' : $tmp_blBoxCount      . '|';
    $weight_namecard .= empty($tmp_weight_namecard) ? '' : $tmp_weight_namecard . '|';
    $weight_leaflet  .= empty($tmp_weight_leaflet)  ? '' : $tmp_weight_leaflet  . '|';
}

$ret = "{\"cover\" : {
         \"price_nc\" : \"%s\"
        ,\"price_bl\" : \"%s\"
        ,\"bl\" : \"%s\"
        ,\"nc\" : \"%s\"
        ,\"island_cost\" : \"%s\"
        ,\"boxcount_nc\" : \"%s\"
        ,\"boxcount_bl\" : \"%s\"
        ,\"expec_weight_nc\" : \"%s\"
        ,\"expec_weight_bl\" : \"%s\"}}";

echo sprintf($ret,
		substr($dlvr_cost_nc, 0, -1),
		substr($dlvr_cost_bl, 0, -1),
		substr($seq_leaflet, 0, -1),
		substr($seq_namecard, 0, -1),
		substr($island_cost, 0, -1),
		substr($ncBoxCount, 0, -1),
		substr($blBoxCount, 0, -1),
		substr($weight_namecard, 0, -1),
		substr($weight_leaflet, 0, -1)
);

$conn->Close();
exit;
?>

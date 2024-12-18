<?php
define(INC_PATH, $_SERVER["INC"]);

include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/SheetDAO.inc");
include_once(INC_PATH . "/classes/dprinting/PriceCalculator/Common/DPrintingFactory.php");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$sheetDAO = new SheetDAO();

$cate_sortcode = $fb->form("cate_sortcode");
$expec_weight  = $fb->form("expec_weight");
$zipcode       = $fb->form("zipcode");
$dlvr_way      = $fb->form("dlvr_way");
$dlvr_sum_way  = $fb->form("dlvr_sum_way");
$dlvr_price    = $fb->form("dlvr_price");

$param = array();
$param["zipcode"]      = $zipcode;

$factory         = new DPrintingFactory();
$dlvr_cost_nc    = 0;
$dlvr_cost_bl    = 0;
$weight_leaflet  = 0;
$weight_namecard = 0;
$seq_leaflet     = "";
$seq_namecard    = "";
$boxCount        = 0;
$island_cost     = 0;

// 택배의 경우 도서지방 배송료 계산 필요
// 화물의 경우도 계산 필요한지 확인 필요($dlvr_way == "03")
if ($dlvr_way == "01") {
	$rs_dlvr = $sheetDAO->selectIslandParcelCost($conn, $param);
	while ($rs_dlvr && !$rs_dlvr->EOF) {
		$island_cost = $rs_dlvr->fields["price"];
		$rs_dlvr->MoveNext();
	}
}

$product = $factory->create($cate_sortcode);
$sort    = $product->getSort();

// 명함은 주문건의 모든 상품을 합쳐서 배송비를 받아야함
if ($sort == "namecard") {
	$weight_namecard += $expec_weight;
	$seq_namecard = $order_seqno;

// 전단은 건당으로 배송비를 받아야함
} else if ($sort == "leaflet") {
	//$weight_leaflet += $dlvr_param[$i]['expec_weight'];
	$param['sort'] = $sort;
	$param['expec_weight'] = $expec_weight;
	$dlvr_cost_bl += $product->getDlvrCost($param, $dlvr_way);
	$seq_leaflet = $order_seqno;
	$blBoxCount = getLeafletBoxcount($expec_weight);
	$boxCount += $blBoxCount;
	$dlvr_cost_bl += $blBoxCount * $island_cost;
	$weight_leaflet += $expec_weight;

// 모든 상품들이 전단 / 명함으로 구분지어지면 삭제해야한다.
} else { 
	$weight_leaflet += $expec_weight;
	$seq_leaflet = $order_seqno;
}

if ($weight_namecard != 0) {
	$ncBoxCount = (int)($weight_namecard / 12) + 1;
	$boxCount += $ncBoxCount;
	$dlvr_cost_nc += $ncBoxCount * $island_cost;
}

if ($seq_leaflet != "") {
	$seq_leaflet = substr($seq_leaflet , 0, -1);
}

if ($seq_namecard != "") {
	$seq_namecard = substr($seq_namecard , 0, -1);
}

if ($weight_namecard != 0) {
	$product = $factory->create("003001001");
	$param_namecard = array();
	$param_namecard['zipcode'] = $zipcode;
	$param_namecard['expec_weight'] = $weight_namecard;
	$dlvr_cost_nc += $product->getDlvrCost($param_namecard, $dlvr_way);
}

if ($weight_leaflet != 0) {
	$product = $factory->create("005001001");
	$param_leaflet = array();
	$param_leaflet['zipcode'] = $zipcode;
	$param_leaflet['expec_weight'] = $weight_leaflet;
	//$dlvr_cost_bl += $product->getDlvrCost($param, $dlvr_way);
}

/****** 전단 박스갯수 세는 함수 START ******/
function getLeafletBoxcount($expec_weight) {
	$count = 1;

	if ($expec_weight > 32) {
	    $count = (int)($expec_weight / 25) + 1;
	}

	return $count;
}
/****** 전단 박스갯수 세는 함수 END ********/
/***************** 배송비 재계산 END *****************/

$dlvr_cost = !empty($dlvr_cost_nc) ? $dlvr_cost_nc : $dlvr_cost_bl;

// 퀵인데 배송비 0 뜰 경우 퀵을 이용할 수 없는 지역
if ($dlvr_way == "04" && $dlvr_cost == 0) {
    echo "cnuq";
} else {
    echo $dlvr_cost;

    /*
    if ($dlvr_cost == $dlvr_price) {
        echo $dlvr_price; // 가격차이가 없을경우 이전 배송가
    } else {
        // 최종 배송금액 = 새로운 배송금액 - 이전 배송금액
        // +가 될수도 -가 될수도 있음
        $tot_dlvr = $dlvr_cost - $dlvr_price;
        echo $tot_dlvr;
    }
    */

}
?>

<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/DeliveryGroupDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/SheetDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/CompleteDAO.inc");
include_once(INC_PATH . "/classes/dprinting/PriceCalculator/Common/DPrintingFactory.php");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$check = 1;

$fb = new FormBean();
$dao = new OrderInfoDAO();
$util = new FrontCommonUtil();
$dgDAO = new DeliveryGroupDAO();
$sheetDAO = new SheetDAO();
$completeDAO = new CompleteDAO();

$order_seqno     = $fb->form("seqno");
$cate_sortcode   = $fb->form("cate_sortcode");
$expec_weight    = $fb->form("expec_weight");
$zipcode         = $fb->form("zipcode");
$dlvr_way        = $fb->form("dlvr_way");
$dlvr_sum_way    = $fb->form("dlvr_sum_way");
$dlvr_new_price  = $fb->form("dlvr_new_price");
$name            = $fb->form("name");
$recei           = $fb->form("recei");
$addr            = $fb->form("addr");
$addr_detail     = $fb->form("addr_detail");
$dlvr_prev_price = $fb->form("dlvr_prev_price");
$tel_num         = $fb->form("tel_num");
$cell_num        = $fb->form("cell_num");

//$conn->debug = 1;

$session         = $fb->getSession();
$member_seqno    = $session["member_seqno"];

$dlvr_new_price  = substr($dlvr_new_price, 0, -1);
$dlvr_new_price  = $util->rmComma($dlvr_new_price);

$conn->StartTrans();

//1. 배송지변경이 가능한 order_state인지 확인한다.
$param = array();
$param["order_seqno"] = $order_seqno;
$rs = $dao->selectOrderInfoForDlvr($conn, $param);

$fields         = $rs->fields;
$order_state    = $fields["order_state"];
$prev_pay_price = $fields["pay_price"];
$bun_yn         = $fields["bun_yn"];
$order_num      = $fields["order_num"];
$invo_num      = $fields["invo_num"];
$pay_way      = $fields["pay_way"];
$old_addr = $fields["addr"] . " " . $fields["addr_detail"];
$old_dlvr_way = $fields["dlvr_way"];

if ($old_dlvr_way == "01") {
	$old_dlvr_way = "택배";
} else if ($old_dlvr_way == "02") {
	$old_dlvr_way = "직배";
} else if ($old_dlvr_way == "03") {
	$old_dlvr_way = "대신화물";
} else if ($old_dlvr_way == "04") {
	$old_dlvr_way = "퀵";
} else if ($old_dlvr_way == "05") {
	$old_dlvr_way = "지하철";
} else if ($old_dlvr_way == "06") {
	$old_dlvr_way = "인현동방문";
} else if ($old_dlvr_way == "07") {
	$old_dlvr_way = "성수동방문";
}



if ($order_state > 2120 || $invo_num != "") {
    $check = 0; // 주문상태로인한 변경불가
    goto ERR;
}

$param["zipcode"]     = $zipcode;
//1-1. 배송금액을 다시 계산한다.(파라미터 변조 방지)
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

/***************** 배송비 재계산 END *****************/

$dlvr_cost = !empty($dlvr_cost_nc) ? $dlvr_cost_nc : $dlvr_cost_bl;

// 퀵인데 배송비 0 뜰 경우 퀵을 이용할 수 없는 지역
if ($dlvr_way == "04" && $dlvr_cost == 0) {
    $check = 3; // 퀵 이용 불가
    goto ERR;
} else {
    $tot_dlvr_price = $dlvr_cost;
    /*
    if ($dlvr_cost == $dlvr_prev_price) {
        if ($dlvr_sum_way == "01") {
            $tot_dlvr_price = 0;
        } else if ($dlvr_sum_way == "02") {
            $tot_dlvr_price = -$dlvr_prev_price;
        }
    } else {
        // 최종 배송금액 = 새로운 배송금액 - 이전 배송금액
        // +가 될수도 -가 될수도 있음
        $tot_dlvr_price = $dlvr_cost - $dlvr_prev_price;
    }
    */

}
// 착불의 경우 계산결과와 관계없이 0으로 고정
if($dlvr_sum_way == "02") {
    $tot_dlvr_price = 0;
}
/*
// 넘어온 배송비와 계산한 배송비(착불은 0)가 일치하는지 확인
if ($dlvr_new_price != $tot_dlvr_price) {
    $check = 4; // 배송비 불일치
    // 이곳에 배송비 불일치 관련 로직 추가하면 됨
    // 파라미터 변조 가능성이 있으니 로그 남기는 로직 추가 필요
    goto ERR;
}
*/

//1-2. 선입금이 배송금액만큼 남아있는지 확인한다.
$param["member_seqno"] = $member_seqno;
$id = $_SESSION["id"];
$rs     = $dao->selectPrepayPrice($conn, $id);
$fields = $rs->fields;
$prepay_money  = intval($fields[0]);
$prepay_price = $prepay_money;

// 회원_결제_내역 입력용 선입금액 임시변수
$temp_prepay = $prepay_price;

if ($prepay_price < $tot_dlvr_price) {
    $check = 5; // 선입금 부족
    goto ERR;
}

//2. 배송지변경을 실행한다.
$param["dlvr_way"]     = $dlvr_way;
$param["dlvr_sum_way"] = $dlvr_sum_way;
$param["name"]         = $name;
$param["recei"]        = $recei;
$param["addr"]         = $addr;
$param["addr_detail"]  = $addr_detail;
$param["tel_num"]      = $tel_num;
$param["cell_num"]     = $cell_num;
$param["dlvr_price"]   = $tot_dlvr_price;
if ($dlvr_way == "01") {
    $param["invo_cpn"] = "롯데택배";
} else if ($dlvr_way == "02") {
    $param["invo_cpn"] = "직배";
} else if ($dlvr_way == "03") {
    $param["invo_cpn"] = "대신화물";
} else if ($dlvr_way == "04") {
    if ($expec_weight < 63) {
        $param["invo_cpn"] = "오토바이";
    } else if ($expec_weight < 315) {
        $param["invo_cpn"] = "다마스";
    } else if ($expec_weight < 900) {
        $param["invo_cpn"] = "라보";
    }
} else if ($dlvr_way == "05") {
    $param["invo_cpn"] = "지하철";
} else if ($dlvr_way == "06") {
    $param["invo_cpn"] = "인현동방문";
} else if ($dlvr_way == "07") {
    $param["invo_cpn"] = "성수동방문";
}

$new_dlvr_way = $param["invo_cpn"];
$new_addr = $param["addr"] . " " . $param["addr_detail"];

$rs_dlvr = $dao->updateMemberDlvrOnProgress($conn, $param);

if (empty($rs_dlvr)) {
    $check = 6; // 배송지변경 실패
    goto ERR;
}

//3. 배송금액에 따라 환불한다.
// 엔진으로 따로 돌릴 예정이었으나 기획변경
// order_dlvr_price_refund(환불테이블) 을 거치지 않고 즉시 환불/차감한다.

$param["exist_prepay"] = $temp_prepay;
$adjust_val = 0;
if ($tot_dlvr_price == $dlvr_prev_price) {
    $adjust_val = 0; 
} else if ($tot_dlvr_price > $dlvr_prev_price) {
    $adjust_val = -($tot_dlvr_price - $dlvr_prev_price);
} else if ($tot_dlvr_price < $dlvr_prev_price) {
    $adjust_val = $dlvr_prev_price - $tot_dlvr_price;
}

//3-1. 선입금 히스토리 입력
if ($adjust_val == 0) {
    // 가격차이 없을 경우엔 선입금 변동없음
    goto END;
// 환불의 경우
} else {
	if($pay_way == "카드") {
		$check = 7; // 카드결제, 배송비 달라짐
		goto ERR;
	}
	$change_param = array();
	$change_param["order_common_seqno"] = $param["order_seqno"];
	$change_param["sell_price"]   = 0;
	$change_param["pay_price"]   = 0;
	$change_param["dlvr_price"]   = 0;
	$change_param["is_init"] = true;
	$dao->updateMemberPayDlvr($conn, $change_param);


	$change_param["sell_price"]   = $tot_dlvr_price;
	$change_param["pay_price"]   = $tot_dlvr_price;
	$change_param["dlvr_price"]   = $tot_dlvr_price;
	$change_param["is_init"] = false;
	$dao->updateMemberPayDlvr($conn, $change_param);

	// 묶음전체금액
    // 묶음 한가지 금액만 변경

}

$record_param = array();
$record_param["state"] = "";
$record_param["empl_id"] = "고객";
$record_param["kind"] = "배송지변경";
$record_param["before"] = "(" . $old_dlvr_way . ")" . $old_addr;
$record_param["after"] = "(" . $new_addr . ")" . $new_addr;
$record_param["order_common_seqno"] = $order_seqno;
$dao->insertOrderInfoHistory($conn, $record_param);

goto END;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();
END:
    $conn->CompleteTrans();
    $conn->close();
    echo $check;
    exit;

/********************** 함수 영역*********************/
/****** 전단 박스갯수 세는 함수 START ******/
function getLeafletBoxcount($expec_weight) {
	$count = 1;

	if ($expec_weight > 32) {
	    $count = (int)($expec_weight / 25) + 1;
	}

	return $count;
}
/****** 전단 박스갯수 세는 함수 END ********/

/**
 * @brief 일매출 검색
 *
 * @return 일매출 결과값
 */
function searchMemberDaySales($conn, $dao, $param) {
    
    $rs = $dao->selectMemberDaySales($conn, $param);

    return $rs;
}

?>

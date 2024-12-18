<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$orderDAO = new OrderInfoDAO();
$conn->StartTrans();

$check = 1;

$seqno_set = explode(",", $fb->form("select_prdt"));

$seqno_count = count($seqno_set);

//관심상품 for문 돌며 삭제
for ($i = 0; $i < $seqno_count; $i++) {
    $seqno = $seqno_set[$i];

    // 관심상품 상세에서 후공정 내역 삭제용 데이터 검색
    $param = array();
    $param["col"] = "interest_prdt_detail_dvs_num";
    $param["table"] = "interest_prdt_detail";
    $param["where"]["interest_prdt_seqno"] = $seqno;
    $rs = $orderDAO->selectData($conn, $param);

    //관심 상품 후공정 내역 삭제
    $param = array();
    $param["table"] = "interest_prdt_after_history";
    $param["prk"] = "interest_prdt_detail_dvs_num";

    while ($rs && !$rs->EOF) {
        $param["prkVal"] = $rs->fields["interest_prdt_detail_dvs_num"];
        $result = $orderDAO->deleteData($conn, $param);
        if (!$result) $check = 0;

        $rs->MoveNext();
    }

    //관심 상품 상세 삭제
    $param = array();
    $param["prk"] = "interest_prdt_seqno";
    $param["table"] = "interest_prdt_detail";
    $param["prkVal"] = $seqno_set[$i];
    $result = $orderDAO->deleteData($conn, $param);
    if (!$result) $check = 0;

    // 관심상품 상세 책자에서 후공정 내역 삭제용 데이터 검색
    $param = array();
    $param["col"] = "interest_prdt_detail_dvs_num";
    $param["table"] = "interest_prdt_detail_brochure";
    $param["where"]["interest_prdt_seqno"] = $seqno;
    $rs = $orderDAO->selectData($conn, $param);

    //관심 상품 상세 책자 삭제
    $param = array();
    $param["table"] = "interest_prdt_detail_brochure";
    $param["prk"] = "interest_prdt_seqno";
    $param["prkVal"] = $seqno_set[$i];
    $result = $orderDAO->deleteData($conn, $param);
    if (!$result) $check = 0;
   
    //관심 상품 후공정 내역 삭제
    $param = array();
    $param["table"] = "interest_prdt_after_history";
    $param["prk"] = "interest_prdt_detail_dvs_num";

    while ($rs && !$rs->EOF) {
        $param["prkVal"] = $rs->fields["interest_prdt_detail_dvs_num"];
        $result = $orderDAO->deleteData($conn, $param);
        if (!$result) $check = 0;

        $rs->MoveNext();
    }
   
    //관심 상품 옵션 내역 삭제
    $param = array();
    $param["table"] = "interest_prdt_opt_history";
    $param["prk"] = "interest_prdt_seqno";
    $param["prkVal"] = $seqno_set[$i];
    $result = $orderDAO->deleteData($conn, $param);
    if (!$result) $check = 0;
   
    //관심 상품 삭제
    $param = array();
    $param["table"] = "interest_prdt";
    $param["prk"] = "interest_prdt_seqno";
    $param["prkVal"] = $seqno_set[$i];
    $result = $orderDAO->deleteData($conn, $param);
    if (!$result) $check = 0;

}

if ($check == 1) {
    
    echo "1";

} else {

    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>

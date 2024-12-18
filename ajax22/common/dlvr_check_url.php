<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/member/MemberJoinDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");


$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new OrderInfoDAO();

 

//$conn->debug= 1;

$order_seqno = $fb->form("order_seqno"); // 주문 seqno


$param = array();
$param["order_common_seqno"] = $order_seqno;
$url = "";
$delnum = "";
$exnum = "";

$fields = $dao->selectDlvrInfoDup($conn, $param, '수신');

if (strpos($fields['invo_num'],',') == false) { 
    $delnum = $fields['invo_num'];
}else{

    $exnum = explode(",",$fields['invo_num']);
    $delnum = $exnum[0];

}
    
if($fields['invo_cpn'] == "롯데택배"){
    $url = "https://www.lotteglogis.com/personalService/tracking/06/tracking_goods_result.jsp?InvNo=".$delnum;
}else if($fields['invo_cpn'] == "CJ택배"){
    $url = "https://www.doortodoor.co.kr/parcel/doortodoor.do?fsp_action=PARC_ACT_002&fsp_cmd=retrieveInvNoACT&invc_no=".$delnum;
}else if($fields['invo_cpn'] == "로젠택배"){
    $url = "https://www.ilogen.com/web/personal/trace/".$delnum;
}

$json = "{\"success\" : \"%s\", \"url\" : \"%s\", \"query\" : \"%s\" }";
//$param["dvs"] = "SEQ";

//$tal = "NCR 4매 1조 / 상 : 녹색 56g, 중 : 적색 53g, 중 : 황색 53g, 하 : 백색 57g / 16절(B5) / 단면1도 - [전면 : 먹, 후면 :] / 내용 다름 / 가로좌철";
//$tal2 = explode("+", $tal);
//$tal2  = explode(' ',explode('/', $tal)[1])[1];
echo sprintf($json, '1', $url, print_r($tal2));

$conn->Close();
?>

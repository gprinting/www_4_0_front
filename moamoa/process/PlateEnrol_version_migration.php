<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/MoamoaDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MoamoaDAO();
$fb = new FormBean();

/*
PEN=판번호&								판번호 12자리 (PNC160303001)
PENO=호수판번호&						호수판번호 15자리(판번호12 + 호수3) (PNC160303001001)
PD=시간&								YYYY-MM-DD-hh:mm(2016-03-03-20:30)
PW=작업자아이디&
PT=판형::이름&							NN::일반명함
PS=판사이즈이름&						국전
PSW=판사이즈 가로&						904
PSH=판사이즈 세로&						604
PL=대첩방식&							1=홍각게수평대첩, 2=홍각게수직대첩, 3=하리돈땡, 4=구와이돈땡
PP=재질&								[100]아트
PQ=판매수&								1000
PN=$n&									판 면수(1: 단면, 2: 양면… n)
PC1=도수&								1면 도수
PC2=도수&								2면 도수
PC$n=도수&								n면 도수
AWN=$n&									판 후처리 수
AW1=후처리&								판 후처리1 (출력)
AW2=후처리&								판 후처리2 (인쇄)
AW$n=후처리&							판 후처리n (후가공)
ONS=$n&									총 주문개수 (현재 판에 포함된 주문의 총 개수)
ON$n=주문번호&							ON1=NNC1603030001
OC$n=건수인덱스&						OC1=1
OP$n=자리수&							OP1=3 (3자리)
PM=판메모&								작업자가 작성한 판 메모
FILES=파일 폴더 위치					결과물 (PDF)파일들이 저장된 위치 (2016/03/03/NC160303001)
PRC=프리셋카테고리&
PRN=프리셋이름
*/
$str_json = $fb->form("json");
$json  = json_decode($str_json);

$param = [];
$param['typset_num'] = $json->PEN;
$param['state'] = "2220";
$param['honggak_yn'] = $json->PL == '3' ? "N" : "Y";
$param['paper_name'] = $json->PP;
$param['print_amt'] = $json->PQ;
$param['prdt_page'] = $json->PN;
$param['print_amt_dvs'] = "장";
$param['beforeside_tmpt'] = $json->PC1;
$param['aftside_tmpt'] = $json->PC2;
$param['dvs'] = $json->PRC;
$param['print_etprs'] = $json->DEPTH2;
$param['memo'] = $json->PM;
$param['print_title'] = $json->PT;
$param['size'] = $json->PSW . "x" . $json->PSH;
$param['typset_way'] = "MOAMOA";

$param['typset_num_file'] = str_replace("_", "-", $param['typset_num']);

$dao->deleteTypesetFile($conn,$param);
$dao->deleteTypesetLabel($conn,$param);
$dao->deleteTypesetInfo($conn,$param);
$dao->deleteOrderTypeset($conn,$param);
$rs = $dao->insertTypesetInfo3($conn,$param);
$param['sheet_typset_seqno'] = $conn->Insert_ID("sheet_typset");

$dao->insertTypesetFile($conn,$param);
$dao->insertTypesetLabel($conn,$param);
$dao->insertTypsetStateHistory($conn,$param);

$orders = $json->ONP;
foreach($orders as $order) {
    $jumunno = $order->JumunNo;
    if($order->IsJunDan == 'Y')
        $jumunno .= "B";

    $param["ordernum"] = $dao->selectOrderNumBy2JumunNo($conn, $jumunno);
    $param["nestamt"] = $order->NestAmt;
    $param['seq'] = $order->Seq;
    $param['empl_id'] = "dpuser1";
    $dao->updateProductStatecode($conn, $param);
    $dao->insertOrderTypeset($conn, $param);
    $dao->insertStateHistory($conn, $param);
}


$result = array();
$result["code"] = "200";
$result["message"] = "ok";

$data = array();
$ret["result"] = $result;

echo json_encode($ret);

$conn->Close();
?>
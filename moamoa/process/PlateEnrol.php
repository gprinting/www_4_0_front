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

$tmp_param = array();
$tmp_param["table"] = "empl";
$tmp_param["col"] = "empl_seqno";
$tmp_param["where"]["empl_id"] = $json->USER;
$rs = $dao->selectData($conn, $tmp_param);
$empl_seqno = $rs->fields["empl_seqno"];

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
$param['dlvrboard'] = $json->DEPTH1;
$param['print_etprs'] = $json->DEPTH2;
$param['memo'] = $json->PM;
$param['print_title'] = $json->PT;
$param['size'] = $json->PSW . "x" . $json->PSH;
$param['typset_way'] = "MOAMOA";
$param['width'] = $json->PSW;
$param['height'] = $json->PSH;
$param['empl_seqno'] = $empl_seqno;

$print_title = $param["print_title"];
$paper_name = $param['paper_name'];

if($param['print_etprs'] == "디지털") {
    $param['dvs'] = "디지털";
}

$tmp_paper = $paper_name;
if($paper_name == "#91 백색 레쟈크")
    $tmp_paper = "레자크 #91(체크)";
if($paper_name == "#92 백색 레쟈크")
    $tmp_paper = "레자크 #92(줄)";
$key = $print_title . "|" . $tmp_paper;

$size = getEnvelopeValue($key);
if($size != "") {
    $param['size'] = $size;
}

$param['typset_num_file'] = $param['typset_num'];
$dao->deleteTypesetFile($conn,$param);
$dao->deleteTypesetLabel($conn,$param);
$dao->deleteTypesetInfo($conn,$param);
$dao->deleteOrderTypeset($conn,$param);
$rs = $dao->insertTypesetInfo($conn,$param);
$param['sheet_typset_seqno'] = $conn->Insert_ID("sheet_typset");

$dao->insertTypesetFile($conn,$param);
$dao->insertTypesetLabel($conn,$param);
$dao->insertTypsetStateHistory($conn,$param);

$orders = $json->ONP;
foreach($orders as $order) {
    $param["ordernum"] = $order->JumunNo;
    $param["nestamt"] = $order->NestAmt;
    $param['seq'] = $order->Seq;
    $param['empl_id'] = $json->USER;
    $dao->updateProductStatecode($conn, $param);
    $dao->insertOrderTypeset($conn, $param);
    $dao->insertStateHistory($conn, $param);
}

/*
$typset_type = explode("-", $param['typset_num'])[1];
if(startsWith($typset_type,"15")) {
    $param['state'] = "2220";
    $dao->insertTypsetStateHistory($conn,$param);
    foreach($orders as $order) {
        $param["ordernum"] = $order->JumunNo;
        $param["nestamt"] = $order->NestAmt;
        $param['seq'] = $order->Seq;
        $param['empl_id'] = "dpuser22";
        $dao->updateProductStatecode($conn, $param);
        $dao->insertOrderTypeset($conn, $param);
        $dao->insertStateHistory($conn, $param);
    }
}
*/

$result = array();
$result["code"] = "200";
$result["message"] = "ok";

$data = array();
$ret["result"] = $result;

echo json_encode($ret);

$conn->Close();

function startsWith($haystack, $needle){
    return strncmp($haystack, $needle, strlen($needle)) === 0;
}

function getEnvelopeValue($key)
{
    $size = "";
    switch ($key) {
        case "국전-대봉2개-소봉4개|모조지":
        case "국전-대봉2개-소봉4개|100 모조":
        case "국전-대봉2개-소봉4개|120 모조":
        case "국전-대봉2개-소봉4개|150 모조":
        case "8색기-국전-대봉2개-소봉4개|모조지":
        case "8색기-국전-대봉2개-소봉4개|100 모조":
        case "8색기-국전-대봉2개-소봉4개|120 모조":
        case "8색기-국전-대봉2개-소봉4개|150 모조":
            $size = "1020 x 670";
            break;
        case "국전-대봉2개-소봉4개|레자크 #91(체크)":
        case "국전-대봉2개-소봉4개|#91 백색 레쟈크":
        case "8색기-국전-대봉2개-소봉4개|레자크 #91(체크)":
        case "8색기-국전-대봉2개-소봉4개|#91 백색 레쟈크":
            $size = "1020 x 670";
            break;
        case "국전-대봉2개-소봉4개|레자크 #92(줄)":
        case "국전-대봉2개-소봉4개|#92 백색 레쟈크":
        case "8색기-국전-대봉2개-소봉4개|레자크 #92(줄)":
        case "8색기-국전-대봉2개-소봉4개|#92 백색 레쟈크":
            $size = "1020 x 670";
            break;

        case "2절-대봉2개|모조지":
        case "2절-대봉2개|100 모조":
        case "2절-대봉2개|120 모조":
        case "2절-대봉2개|150 모조":
        case "우성-2절-대봉2개|모조지":
        case "우성-2절-대봉2개|100 모조":
        case "우성-2절-대봉2개|120 모조":
        case "우성-2절-대봉2개|150 모조":
        case "8색기-2절-대봉2개|모조지":
        case "8색기-2절-대봉2개|100 모조":
        case "8색기-2절-대봉2개|120 모조":
        case "8색기-2절-대봉2개|150 모조":
            $size = "545 x 788";break;
        case "2절-대봉2개|레자크 #91(체크)":
        case "2절-대봉2개|#91 백색 레쟈크":
        case "우성-2절-대봉2개|레자크 #91(체크)":
        case "우성-2절-대봉2개|#91 백색 레쟈크":
        case "8색기-2절-대봉2개|레자크 #91(체크)":
        case "8색기-2절-대봉2개|#91 백색 레쟈크":
            $size = "506 x 788";break;
        case "2절-대봉2개|레자크 #92(줄)":
        case "2절-대봉2개|#92 백색 레쟈크":
        case "우성-2절-대봉2개|레자크 #92(줄)":
        case "우성-2절-대봉2개|#92 백색 레쟈크":
        case "8색기-2절-대봉2개|레자크 #92(줄)":
        case "8색기-2절-대봉2개|#92 백색 레쟈크":
            $size = "506 x 788";break;


        case "국전-소봉투(12개)|모조지":
        case "국전-소봉투(12개)|100 모조":
        case "국전-소봉투(12개)|120 모조":
        case "국전-소봉투(12개)|150 모조":
        case "8색기-국전-소봉투(12개)|모조지":
        case "8색기-국전-소봉투(12개)|100 모조":
        case "8색기-국전-소봉투(12개)|120 모조":
        case "8색기-국전-소봉투(12개)|150 모조":
            $size = "1060 x 740";break;
        case "국전-소봉투(12개)|레자크 #91(체크)":
        case "국전-소봉투(12개)|#91 백색 레쟈크":
        case "8색기-국전-소봉투(12개)|레자크 #91(체크)":
        case "8색기-국전-소봉투(12개)|#91 백색 레쟈크":
            $size = "1060 x 740";break;
        case "국전-소봉투(12개)|레자크 #92(줄)":
        case "국전-소봉투(12개)|#92 백색 레쟈크":
        case "8색기-국전-소봉투(12개)|레자크 #92(줄)":
        case "8색기-국전-소봉투(12개)|#92 백색 레쟈크":
            $size = "1060 x 740";break;


        case "2절-대봉1개-소봉2개|모조지":
        case "2절-대봉1개-소봉2개|100 모조":
        case "2절-대봉1개-소봉2개|120 모조":
        case "2절-대봉1개-소봉2개|150 모조":
        case "우성-2절-대봉1개-소봉2개|모조지":
        case "우성-2절-대봉1개-소봉2개|100 모조":
        case "우성-2절-대봉1개-소봉2개|120 모조":
        case "우성-2절-대봉1개-소봉2개|150 모조":
        case "8색기-2절-대봉1개-소봉2개|모조지":
        case "8색기-2절-대봉1개-소봉2개|100 모조":
        case "8색기-2절-대봉1개-소봉2개|120 모조":
        case "8색기-2절-대봉1개-소봉2개|150 모조":
            $size = "545 x 788";break;
        case "2절-대봉1개-소봉2개|레자크 #91(체크)":
        case "2절-대봉1개-소봉2개|#91 백색 레쟈크":
        case "우성-2절-대봉1개-소봉2개|레자크 #91(체크)":
        case "우성-2절-대봉1개-소봉2개|#91 백색 레쟈크":
        case "8색기-2절-대봉1개-소봉2개|레자크 #91(체크)":
        case "8색기-2절-대봉1개-소봉2개|#91 백색 레쟈크":
            $size = "506 x 788";break;
        case "2절-대봉1개-소봉2개|레자크 #92(줄)":
        case "2절-대봉1개-소봉2개|#92 백색 레쟈크":
        case "우성-2절-대봉1개-소봉2개|레자크 #92(줄)":
        case "우성-2절-대봉1개-소봉2개|#92 백색 레쟈크":
        case "8색기-2절-대봉1개-소봉2개|레자크 #92(줄)":
        case "8색기-2절-대봉1개-소봉2개|#92 백색 레쟈크":
            $size = "788 x 506";break;


        case "2절-대봉1개-소봉1개-이삿짐봉투1개|모조지":
        case "2절-대봉1개-소봉1개-이삿짐봉투1개|100 모조":
        case "2절-대봉1개-소봉1개-이삿짐봉투1개|120 모조":
        case "2절-대봉1개-소봉1개-이삿짐봉투1개|150 모조":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|모조지":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|100 모조":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|120 모조":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|150 모조":
            $size = "545 x 788";break;
        case "2절-대봉1개-소봉1개-이삿짐봉투1개|레자크 #91(체크)":
        case "2절-대봉1개-소봉1개-이삿짐봉투1개|#91 백색 레쟈크":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|레자크 #91(체크)":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|#91 백색 레쟈크":
            $size = "788 x 506";break;
        case "2절-대봉1개-소봉1개-이삿짐봉투1개|레자크 #92(줄)":
        case "2절-대봉1개-소봉1개-이삿짐봉투1개|#92 백색 레쟈크":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|레자크 #92(줄)":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|#92 백색 레쟈크":
            $size = "788 x 506";break;


        case "2절-소봉6개|모조지":
        case "2절-소봉6개|100 모조":
        case "2절-소봉6개|120 모조":
        case "2절-소봉6개|150 모조":
        case "8색기-2절-소봉6개|모조지":
        case "8색기-2절-소봉6개|100 모조":
        case "8색기-2절-소봉6개|120 모조":
        case "8색기-2절-소봉6개|150 모조":
            $size = "788 x 545";break;
        case "2절-소봉6개|레자크 #91(체크)":
        case "2절-소봉6개|#91 백색 레쟈크":
        case "8색기-2절-소봉6개|레자크 #91(체크)":
        case "8색기-2절-소봉6개|#91 백색 레쟈크":
            $size = "788 x 545";break;
        case "2절-소봉6개|레자크 #92(줄)":
        case "2절-소봉6개|#92 백색 레쟈크":
        case "8색기-2절-소봉6개|레자크 #92(줄)":
        case "8색기-2절-소봉6개|#92 백색 레쟈크":
            $size = "788 x 545";break;
        case "8색-2절-788x545":
        case "우성-2절-788x545":
        case "8색-788x545-문고리":
        case "V3000-788x545-문고리":
        case "우성-788x545-문고리":
            $size = "2절 788X545";break;
        case "8색-2절-545x788":
        case "우성-2절-545x788":
            $size = "2절 545x788";break;
        case "8색-소국전-880x625":
        case "우성-소국전-880x625":
            $size = "국전 880X625";break;
        case "8색-국전-939x636":
        case "우성-국전-939x636":
        case "우성-939x636-문고리":
        case "V3000-939x636-문고리":
        case "8색-939x636-문고리":
            $size = "국전 939x636";break;
        case "8색-국전-636x939":
        case "우성-국전-636x939":
            $size = "국전 636x939";break;
        case  "V3000-국2절-635x523" :
            $size = "국2절 636x469";break;
        default:
            $size = "";
    }

    if($size == "") {
        $format = explode('|', $key)[0];
        switch ($format) {
            case "8색-2절-788x545":
            case "우성-2절-788x545":
            case "V3000-2절-788x545":
            case "8색-788x545-문고리":
            case "V3000-788x545-문고리":
            case "우성-788x545-문고리":
                $size = "2절 788X545";break;
            case "8색-2절-545x788":
            case "우성-2절-545x788":
            case "V3000-2절-545x788":
                $size = "2절 545x788";break;
            case "8색-소국전-880x625":
            case "우성-소국전-880x625":
            case "V3000-소국전-880x625":
                $size = "국전 880X625";break;
            case "8색-국전-939x636":
            case "우성-국전-939x636":
            case "V3000-국전-939x636":
            case "우성-939x636-문고리":
            case "V3000-939x636-문고리":
            case "8색-939x636-문고리":
                $size = "국전 939x636";break;
            case "8색-국전-636x939":
            case "우성-국전-636x939":
            case "V3000-국전-636x939":
                $size = "국전 636x939";break;
            case "8색-2절-1060x750":
                $size = "2절-1060x750";break;
            case "국전 880X625(8개)#(45D)":
                $size = "국전 880X625";break;
            case "국전 910X650(9개)#(45)":
                $size = "국전 910X650";break;
            case "2절 788X650(10개)#(45)":
                $size = "2절 788X650";break;
            case "국전 880X625(8개)#(37D)":
                $size = "국전 880X625";break;
            case "국전 1060X650(10개)#(37D)":
                $size = "국전 1060X650";break;
            case "2절 1060X750(16개)#(37)":
                $size = "2절 1060X750";break;
            case "국전 910X650(9개)#(37)":
                $size = "국전 910X650";break;
            case  "V3000-국2절-635x523" :
                $size = "국2절 636x469";break;
            default:
                $size = "";
        }
    }

    return $size;
}

?>
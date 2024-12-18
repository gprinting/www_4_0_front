<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/order/SheetDAO.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/doc/front/order/SheetPopup.inc");

$frontUtil = new FrontCommonUtil();

if ($is_login === false) {
    $frontUtil->errorGoBack("로그인 상태가 아닙니다.");
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

//$conn->debug = 1;

$fb = new FormBean();
$dao = new SheetDAO();

$session = $fb->getSession();
$fb = $fb->getForm();

$member_seqno = $session["org_member_seqno"];   // 회원일련번호
$dvs          = $fb["dvs"];                     // 구분값

$dlvr_name    = $fb["dlvr_name"];               // 배송지이름
$recei        = $fb["recei"];                   // 수신자
$tel_num      = $fb["tel_num"];                 // 전화번호
$cell_num     = $fb["cell_num"];                // 핸드폰번호
$zipcode      = $fb["zipcode"];                 // 우편번호
$addr         = $fb["addr"];                    // 주소
$addr_detail  = $fb["addr_detail"];             // 상세주소

// 수동등록 시
if ($dvs == "rm") {

    $param = array();
    $param["member_seqno"] = $member_seqno;
    $param["dlvr_name"]    = $dlvr_name;
    $param["recei"]        = $recei;
    $param["tel_num"]      = $tel_num;
    $param["cell_num"]     = $cell_num;
    $param["zipcode"]      = $zipcode;
    $param["addr"]         = $addr;
    $param["addr_detail"]  = $addr_detail;
    $param["basic_yn"]     = 'N';
    // dlvr_dvs, doro_yn 확인 필요
    
    // 주소 등록
    $insert_ret = $dao->insertMemberDlvr($conn, $param);
    
    // 주소 등록 실패시 튕김
    if ($insert_ret === false) {
        echo 'F';
        exit;
    }
    
    echo 'T';
    
    exit;

// 자동등록 시
} else if ($dvs == "ra") {
    
    $param = array();
    $param["member_seqno"] = $member_seqno;
    $param["dlvr_name"]    = $dlvr_name;
    $param["recei"]        = $recei;
    $param["tel_num"]      = $tel_num;
    $param["cell_num"]     = $cell_num;
    $param["zipcode"]      = $zipcode;
    $param["addr"]         = $addr;
    $param["addr_detail"]  = $addr_detail;
    $param["basic_yn"]     = 'N';

    // dlvr_dvs, doro_yn 확인 필요

    if (empty($dlvr_name) && empty($recei)) {
        echo 'EP'; // 배송지명과 수신자가 둘다 비었을 경우 튕김
        exit;
    }

    $tel_exp  = explode('-' ,$tel_num);
    $cell_exp = explode('-', $cell_num);
    if (empty($tel_exp) && $cell_exp) {
        echo 'NN'; // 전화번호 또는 핸드폰번호는 둘중 하나라도 입력되어야됨
    }
    
    $rs = $dao->selectMemberDlvrExist($conn, $param);
   
    $fields = $rs->fields;  
    if (!empty($fields["addr"])) {
        echo 'BF'; // 기 등록된 주소, 그냥 넘어감
        exit;
    }

    // 주소 등록
    $insert_ret = $dao->insertMemberDlvr($conn, $param);
    
    // 주소 등록 실패시에도 넘어감
    if ($insert_ret === false) {
        echo 'F';
        exit;
    }
    
    echo 'T';
    
    exit;

}

?>

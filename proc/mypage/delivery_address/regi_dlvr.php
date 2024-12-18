<?
include_once($_SERVER["INC"] . "/define/front/common_config.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/MemberDlvrDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$check = "등록에 성공했습니다.";

$fb = new FormBean();
$dao = new MemberDlvrDAO();
$conn->StartTrans();

$dvs = $fb->form("dvs");

$param = array();
$param["dlvr_name"] = $fb->form("dlvr_name");
$param["recei"] = $fb->form("recei");
$param["tel_num"] = $fb->form("tel_num");
$param["cell_num"] = $fb->form("cell_num");
$param["zipcode"] = $fb->form("zipcode");
$param["addr"] = $fb->form("addr");
$param["addr_detail"] = $fb->form("addr_detail");
//$param["member_seqno"] = $fb->session("org_member_seqno");
$param["member_seqno"] = $fb->session("member_seqno");

if ($dvs) {
    $param["basic_yn"] = "Y";
} else {
    $param["basic_yn"] = "N";
}

$result = $dao->insertDlvr($conn, $param);

if (!$result) {
    $check = "등록에 실패했습니다.";
    $conn->FailTrans();
    $conn->RollbackTrans();
}

if ($dvs) {
    setCookie("addr", $param["addr"], 0, "/");
    setCookie("addr_det", $param["addr_detail"], 0, "/");
}

echo $check;
$conn->CompleteTrans();
$conn->close();
?>

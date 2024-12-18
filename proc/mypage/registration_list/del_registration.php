<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/define/front/common_config.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/BusinessRegistrationDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$check = "삭제에 성공했습니다.";

$fb = new FormBean();
$dao = new BusinessRegistrationDAO();
$conn->StartTrans();

$param = array();
$param["admin_licenseeregi_seqno"] = $fb->form("seq");

$result = $dao->deleteRegistration($conn, $param);
if (!$result) 
    $check = "삭제에 실패했습니다.";

echo $check;
$conn->CompleteTrans();
$conn->close();
?>

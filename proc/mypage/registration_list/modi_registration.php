<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/front/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/BusinessRegistrationDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$check = "수정에 성공했습니다.";

$fb = new FormBean();
$dao = new BusinessRegistrationDAO();
$conn->StartTrans();

$param = array();
$param["admin_licenseeregi_seqno"] = $fb->form("seq");
$param["crn"] = $fb->form("crn");
$param["corp_name"] = $fb->form("corp_name");
$param["repre_name"] = $fb->form("repre_name");
$param["bc"] = $fb->form("bc");
$param["tob"] = $fb->form("tob");
$param["tel_num"] = $fb->form("tel_num");
$param["zipcode"] = $fb->form("zipcode");
$param["addr"] = $fb->form("addr");
$param["addr_detail"] = $fb->form("addr_detail");
$param["mng_name"] = $fb->form("mng_name");
$param["posi"] = $fb->form("posi");
$param["mail"] = $fb->form("mail");

$result = $dao->updateRegistration($conn, $param);
if (!$result) 
    $check = "수정에 실패했습니다.";

echo $check;
$conn->CompleteTrans();
$conn->close();
?>

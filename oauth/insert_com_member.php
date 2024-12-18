<?php
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . '/test-devel/testDAO.php');
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new testDAO();

$fb = $fb->getForm();

$email       = $fb["jem"];
$pw          = $fb["jpw"];
$pw          = md5($pw);
$withdraw_yn = "N";
$grade       = "20";

//$conn->debug=1;

$param = array();
$param["mail"]        = $email;
$param["passwd"]      = $pw;
$param["withdraw_yn"] = $withdraw_yn;
$param["grade"]       = $grade;

$conn->StartTrans();
$ins = $dao->insertComMember($conn, $param);

if (!$ins) {
    $ins = "입력에 실패했습니다.";
    goto ERR;
}
$ins = "등록 성공";

goto END;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();

END:
    echo $ins;
    $conn->CompleteTrans();
    $conn->Close();
?>

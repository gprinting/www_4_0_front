<?
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/doc/front/mypage/MemberInfoDOC.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");

$frontUtil = new FrontCommonUtil();

if ($is_login === false) {
    echo noLoginPop();
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new FrontCommonDAO();

$fb = new FormBean();

$session = $fb->getSession();
unset($fb);

$url = "https://" . $_SERVER['HTTP_HOST'];

$info = array(
     "group_name"   => $session["group_name"]
    ,"member_name"  => $session["name"]
    ,"email"        => $session["email"]
    ,"url"          => $url
);

echo prepaymentPop($info);
exit;
?>

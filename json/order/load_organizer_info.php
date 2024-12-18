<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.inc");
include_once(INC_PATH . "/define/front/message.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");

if ($is_login === false) {
    echo "{\"err\" : \"" . NO_LOGIN . "\"}";
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new FrontCommonUtil();
$dao = new FrontCommonDAO();

$session = $fb->getSession();

$param = array();
$param["table"] = "admin_licenseeregi";
$param["col"] = "corp_name, repre_name, crn, bc, tob, addr, addr_detail";
$param["where"]["admin_licenseeregi_seqno"] = $fb->form("seqno");

$rs = $dao->selectData($conn, $param);

$ret  = '{';
$ret .= " \"member_name\" : \"%s\",";
$ret .= " \"supply_corp\" : \"%s\",";
$ret .= " \"repre_name\" : \"%s\",";
$ret .= " \"crn\" : \"%s\",";
$ret .= " \"bc\" : \"%s\",";
$ret .= " \"tob\" : \"%s\",";
$ret .= " \"addr\" : \"%s %s\"";
$ret .= '}';

echo sprintf($ret, $session["member_name"]
                 , $rs->fields["corp_name"]
                 , $rs->fields["repre_name"]
                 , $rs->fields["crn"]
                 , $rs->fields["bc"]
                 , $rs->fields["tob"]
                 , $rs->fields["addr"]
                 , $rs->fields["addr_detail"]);
?>

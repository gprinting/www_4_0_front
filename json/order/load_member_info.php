<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
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
$param["table"] = "licensee_info";
$param["col"] = "corp_name, repre_name, crn, bc, tob, zipcode, addr, addr_detail";
$param["where"]["member_seqno"] = $session["member_seqno"];

$rs = $dao->selectData($conn, $param);

$ret  = '{';
$ret .= " \"member_name\" : \"%s\",";
$ret .= " \"supply_corp\" : \"%s\",";
$ret .= " \"repre_name\" : \"%s\",";
$ret .= " \"crn\" : \"%s\",";
$ret .= " \"bc\" : \"%s\",";
$ret .= " \"tob\" : \"%s\",";
$ret .= " \"zipcode\" : \"%s\",";
$ret .= " \"addr\" : \"%s %s\"";
$ret .= '}';

echo sprintf($ret, $session['name']
                 , $rs->fields["corp_name"]
                 , $rs->fields["repre_name"]
                 , $rs->fields["crn"]
                 , $rs->fields["bc"]
                 , $rs->fields["tob"]
                 , $rs->fields["zipcode"]
                 , $rs->fields["addr"]
                 , $rs->fields["addr_detail"]);
?>

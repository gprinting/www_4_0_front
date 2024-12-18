<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberInfoDAO();

$param = array();
$param["table"] = "member_grade";
$param["col"] = "m1 ,m2 ,m3 ,m4 ,m5 ,m6 ,m7 ,m8 ,m9 ,m10 ,m11 ,m12";
$param["where"]["year"] = $fb->form("year");

$session = $fb->getSession();
$seqno = $session["org_member_seqno"];

$param["where"]["member_seqno"] = $session["org_member_seqno"];

$rs = $dao->selectData($conn, $param);

$ret  = '{';
$ret .= "\n\"data\" : [";
$ret .= "%s";
$ret .= '],';
$ret .= "\n\"cate\" : [";
$ret .= "%s";
$ret .= ']';
$ret .= '}';

$m = "";
$c = "";
$j = 1;

for ($i=1; $i<=12; $i++) {

    if ($rs->fields["m" . $i]) {
        if ($j == 1) {
            $m .= number_format($rs->fields["m" . $i]);
            $c .= "\"" . $i . "월\"";
        } else {
            $m .= ", " . number_format($rs->fields["m" . $i]);
            $c .= ", " . "\"" . $i . "월\"";
        }
        $j++;
    }
}

echo sprintf($ret ,$m ,$c);
$conn->close();
?>

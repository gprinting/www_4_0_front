<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/cscenter/FaqDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new FaqDAO();

$seqno = $fb->form("seqno");

$param = array();
$param["table"] = "faq";
$param["col"] = "hits";
$param["where"]["faq_seqno"] = $seqno;

$rs = $dao->selectData($conn, $param);

$hits = $rs->fields["hits"];

$check = 1;
$conn->StartTrans();

$param = array();
$param["table"] = "faq";
$param["col"]["hits"] = $hits + 1;
$param["prk"] = "faq_seqno";
$param["prkVal"] = $seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->close();
echo $check;
?>

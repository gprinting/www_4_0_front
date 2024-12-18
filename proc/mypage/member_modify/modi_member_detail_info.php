<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MemberInfoDAO();

$member_seqno  = $fb->form("member_seqno");
$member_dvs    = $fb->form("member_dvs");
$basic_addr_yn = $fb->form("basic_addr_yn");

$check = 1;
//$conn->debug = 1;
$conn->StartTrans();

$param = array();
$param["mail"] = $fb->form("mail");
$param["tel_num"] = $fb->form("tel_num");
$param["cell_num"] = $fb->form("cell_num");
$param["zipcode"] = $fb->form("zipcode");
$param["addr"] = $fb->form("addr");
$param["addr_detail"] = $fb->form("addr_detail");
$param["mailing_yn"] = $fb->form("mailing_yn");
$param["sms_yn"] = $fb->form("sms_yn");
$param["member_seqno"] = $member_seqno;
$rs = $dao->updateMemberJoinInfo($conn, $param);

if ($basic_addr_yn === 'Y') {
    $dlvr_seqno = $dao->selectMemberBasicDlvrSeqno($conn, $member_seqno);

    unset($param);
    $param["dlvr_name"] =
        empty($fb->session("group_name")) ? '' : $fb->session("group_name");
    $param["recei"]             = $fb->session("name");
    $param["tel_num"]           = $fb->form("tel_num");
    $param["cell_num"]          = $fb->form("cell_num");
    $param["zipcode"]           = $fb->form("zipcode");
    $param["addr"]              = $fb->form("addr");
    $param["addr_detail"]       = $fb->form("addr_detail");
    $param["member_seqno"]      = $member_seqno;
    $param["member_dlvr_seqno"] = $dlvr_seqno;

    $rs = $dao->updateMemberBasicDlvr($conn, $param);
}

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "licensee_info";
$param["col"]["repre_name"] = $fb->form("repre_name");
$param["col"]["corp_name"] = $fb->form("corp_name");
$param["col"]["crn"] = $fb->form("crn");
$param["col"]["bc"] = $fb->form("bc");
$param["col"]["tob"] = $fb->form("tob");
//$param["col"]["tel_num"] = $fb->form("co_tel_num");
$param["col"]["zipcode"] = $fb->form("zipcode2");
$param["col"]["addr"] = $fb->form("addr2");
$param["col"]["addr_detail"] = $fb->form("addr_detail2");
$param["col"]["email"] = $fb->form("email2");

$param["col"]["member_seqno"] = $member_seqno;

$del_param = array();
$del_param["table"] = "licensee_info";
$del_param["prk"] = "member_seqno";
$del_param["prkVal"] = $member_seqno;
$rs = $dao->deleteData($conn, $del_param);

if (!$rs) {
    $check = 0;
}

$rs = $dao->insertData($conn, $param);


if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "member_detail_info";
$param["col"] = "member_seqno";
$param["where"]["member_seqno"] = $member_seqno;

$sel_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "member_detail_info";
$param["col"]["occu1"] = $fb->form("occu1");
$param["col"]["occu2"] = $fb->form("occu2");
$param["col"]["occu_detail"] = $fb->form("occu_detail");
$param["col"]["interest_field1"] = $fb->form("interest_field1");
$param["col"]["interest_field2"] = $fb->form("interest_field2");
$param["col"]["interest_field_detail"] = $fb->form("interest_field_detail");
$param["col"]["design_outsource_yn"] = $fb->form("design_outsource_yn");
$param["col"]["produce_outsource_yn"] = $fb->form("produce_outsource_yn");
$param["col"]["add_interest_items"] = $fb->form("add_interest_items");
$param["col"]["interest_prior"] = $fb->form("interest_prior");
$param["col"]["plural_deal_yn"] = $fb->form("plural_deal_yn");
$param["col"]["plural_deal_site_name1"] = $fb->form("plural_deal_site_name1");
$param["col"]["plural_deal_site_name2"] = $fb->form("plural_deal_site_name2");
$param["col"]["plural_deal_site_detail1"] = $fb->form("plural_deal_site_detail1");
$param["col"]["plural_deal_site_detail2"] = $fb->form("plural_deal_site_detail2");
$param["col"]["memo"] = $fb->form("memo");

if ($sel_rs->EOF == 1) {
    $param["col"]["regi_date"] = date("Y-m-d H:i:s");
    $param["col"]["member_seqno"] = $member_seqno;
    $rs = $dao->insertData($conn, $param);

} else {
    $param["prk"] = "member_seqno";
    $param["prkVal"] = $member_seqno;
    $rs = $dao->updateData($conn, $param);
}

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "member_interest_prdt";
$param["col"] = "member_seqno";
$param["where"]["member_seqno"] = $member_seqno;

$sel_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "member_interest_prdt";
$param["col"]["interest_1"] = $fb->form("inter_prdt1");
$param["col"]["interest_2"] = $fb->form("inter_prdt2");
$param["col"]["interest_3"] = $fb->form("inter_prdt3");
$param["col"]["interest_4"] = $fb->form("inter_prdt4");
$param["col"]["interest_5"] = $fb->form("inter_prdt5");
$param["col"]["interest_6"] = $fb->form("inter_prdt6");
$param["col"]["interest_7"] = $fb->form("inter_prdt7");
$param["col"]["interest_8"] = $fb->form("inter_prdt8");
$param["col"]["interest_9"] = $fb->form("inter_prdt9");
$param["col"]["interest_10"] = $fb->form("inter_prdt10");
$param["col"]["interest_11"] = $fb->form("inter_prdt11");
$param["col"]["interest_12"] = $fb->form("inter_prdt12");

if ($sel_rs->EOF == 1) {
    $param["col"]["member_seqno"] = $member_seqno;
    $rs = $dao->insertData($conn, $param);

} else {
    $param["prk"] = "member_seqno";
    $param["prkVal"] = $member_seqno;
    $rs = $dao->updateData($conn, $param);
}

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "member_interest_event";
$param["col"] = "member_seqno";
$param["where"]["member_seqno"] = $member_seqno;

$sel_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "member_interest_event";
$param["col"]["interest_1"] = $fb->form("inter_event1");
$param["col"]["interest_2"] = $fb->form("inter_event2");
$param["col"]["interest_3"] = $fb->form("inter_event3");
$param["col"]["interest_4"] = $fb->form("inter_event4");
$param["col"]["interest_5"] = $fb->form("inter_event5");

if ($sel_rs->EOF == 1) {
    $param["col"]["member_seqno"] = $member_seqno;
    $rs = $dao->insertData($conn, $param);

} else {
    $param["prk"] = "member_seqno";
    $param["prkVal"] = $member_seqno;
    $rs = $dao->updateData($conn, $param);
}

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "member_interest_design";
$param["col"] = "member_seqno";
$param["where"]["member_seqno"] = $member_seqno;

$sel_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "member_interest_design";
$param["col"]["interest_1"] = $fb->form("inter_design1");
$param["col"]["interest_2"] = $fb->form("inter_design2");
$param["col"]["interest_3"] = $fb->form("inter_design3");
$param["col"]["interest_4"] = $fb->form("inter_design4");
$param["col"]["interest_5"] = $fb->form("inter_design5");
$param["col"]["interest_6"] = $fb->form("inter_design6");

if ($sel_rs->EOF == 1) {
    $param["col"]["member_seqno"] = $member_seqno;
    $rs = $dao->insertData($conn, $param);

} else {
    $param["prk"] = "member_seqno";
    $param["prkVal"] = $member_seqno;
    $rs = $dao->updateData($conn, $param);
}

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "member_interest_needs";
$param["col"] = "member_seqno";
$param["where"]["member_seqno"] = $member_seqno;

$sel_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "member_interest_needs";
$param["col"]["interest_1"] = $fb->form("inter_needs1");
$param["col"]["interest_2"] = $fb->form("inter_needs2");
$param["col"]["interest_3"] = $fb->form("inter_needs3");
$param["col"]["interest_4"] = $fb->form("inter_needs4");
$param["col"]["interest_5"] = $fb->form("inter_needs5");
$param["col"]["interest_6"] = $fb->form("inter_needs6");
$param["col"]["interest_7"] = $fb->form("inter_needs7");
$param["col"]["interest_8"] = $fb->form("inter_needs8");
$param["col"]["interest_9"] = $fb->form("inter_needs9");
$param["col"]["interest_10"] = $fb->form("inter_needs10");

if ($sel_rs->EOF == 1) {
    $param["col"]["member_seqno"] = $member_seqno;
    $rs = $dao->insertData($conn, $param);

} else {
    $param["prk"] = "member_seqno";
    $param["prkVal"] = $member_seqno;
    $rs = $dao->updateData($conn, $param);
}

if (!$rs) {
    $check = 0;
}

$_SESSION["sync_flag"] = "";

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>

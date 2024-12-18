<?php
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberInfoDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberInfoDAO();

$session = $fb->getSession();
$member_seqno = $session["member_seqno"];

//$conn->debug = 1;

$param = array();
$param["member_seqno"] = $member_seqno;

$ba_info = $dao->selectMemberVirtBaInfo($conn, $param);
$fields  = $ba_info->fields;

$data_tr = "";
// 등록된 가상계좌가 없을 시
if (!$ba_info || $ba_info->EOF) {
    $data_tr .= "<tr>";
    $data_tr .= "   <td colspan=\"5\">등록된 가상계좌가 없습니다. <button class=\"function\" onclick=\"regiVirtAcc();\">등록하기</button></td>";
    $data_tr .= "</tr>";

// 등록된 가상계좌가 있을 시
} else {
    $data_tr .= "<tr>";
    $data_tr .= "   <td class=\"left\">%s</td>";
    $data_tr .= "   <td class=\"left\">%s</td>";
    $data_tr .= "   <td class=\"left\">%s</td>";
    $data_tr .= "   <td><span class=\"status C\">정상발급</span></td>";
    $data_tr .= "   <td class=\"btn\">";
    $data_tr .= "   <button class=\"modify\" onclick=\"layerPopup('l_virt_ba_modify', 'popup/l_virt_account_modify.html');\" title=\"수정하기\">수정하기</button>
";
    $data_tr .= "   </td>";
    $data_tr .= "</tr>";

    $data_tr  = sprintf($data_tr, $fields["depo_name"]
                                , $fields["bank_name"]
                                , $fields["ba_num"]);
}

$param["maxfive"] = "Y";
$ba_change_info = $dao->selectPrevChangeList($conn, $param);

// 변경내역이 없을 경우
if (!$ba_change_info || $ba_change_info->EOF) {
    $data_tr_s .= "<tr>";
    $data_tr_s .= "   <td colspan=\"7\">변경신청 내역이 없습니다.</td>";
    $data_tr_s .= "</tr>";

// 변경내역이 있을 경우
} else {

    while ($ba_change_info && !$ba_change_info->EOF) {
        $change_fields  = $ba_change_info->fields;

        $data_tr_s .= "<tr>";
        $data_tr_s .= "   <td>%s</td>";
        $data_tr_s .= "   <td class=\"left\">%s</td>";
        $data_tr_s .= "   <td class=\"left\">%s</td>";
        $data_tr_s .= "   <td class=\"left\">%s</td>";
        $data_tr_s .= "   <td><span class=\"status %s\">%s</span></td>";
        $data_tr_s .= "   <td>%s</td>";
        $data_tr_s .= "   <td class=\"btn\"><button onclick=\"cancelChangeVirtBa('%s');\" class=\"cancel\" titile=\"취소하기\">취소하기</button></td>";
        $data_tr_s .= "</tr>";
    
        $data_tr_s  = sprintf($data_tr_s, $change_fields["change_date"]
                                        , $change_fields["depo_name"]
                                        , $change_fields["bank_before"]
                                        , $change_fields["bank_aft"]
                                        , $change_fields["prog_state"]
                                        , $change_fields["prog_state"]
                                        , $change_fields["progday"]
                                        , $change_fields["virt_ba_change_history_seqno"]);

        $ba_change_info->MoveNext();
    
    }
}

echo $data_tr . '@'. $data_tr_s;

?>

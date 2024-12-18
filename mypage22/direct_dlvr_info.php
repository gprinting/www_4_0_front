<?php
$direct_dlvr_desc = "현재 월배송 서비스를 이용하고 계시지 않습니다.";
$leave_period = '-';
$end_date = '';
$rs = $dao->selectDirectDlvrReqPeriod($conn, $fb->session("member_seqno"));
if (!empty($rs)) {
    $cur_stamp = time();
    $end_stamp = explode('-', $rs["end_period"]);
    $end_stamp = mktime(0, 0, 0,
                        $end_stamp[1],
                        $end_stamp[2],
                        $end_stamp[0]);

    if ($end_stamp > $cur_stamp) {
        $direct_dlvr_desc = "고객님께서는 월배송 서비스를 이용하고 계십니다.";
        $leave_period = date('j', $end_stamp - $cur_stamp);
        $end_date = '(' . $rs["end_period"] . " 까지)";
    }
}
$template->reg("direct_dlvr_desc", $direct_dlvr_desc);
$template->reg("leave_period", $leave_period);
$template->reg("end_date", $end_date);

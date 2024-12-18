<?php
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/DeliveryGroupDAO.inc");

if (!$is_login) {
    echo "{}";
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$dao = new DeliveryGroupDAO();

$sess = $fb->getSession();

//$conn->debug = 1;
$rs = $dao->selectOrderDlvrGroup($conn, $sess["org_member_seqno"]);

$param = [];
$param["basic_yn"] = 'N';
$sort_arr = [];
while ($rs && !$rs->EOF) {
    $flds = $rs->fields;

    $dlvr_sum_way = $flds["dlvr_sum_way"] === "01" ? "선불택배" : "후불택배";
    $order_detail = $flds["order_detail"];
    $cate_name    = trim(explode(',', (explode('/', $order_detail)[0]))[0]);
    $order_detail  = explode('/', $order_detail, 2)[1];

    $param["order_seqno"] = $flds["order_common_seqno"];
    $after_rs = $dao->selectOrderAfter($conn, $param);
    $after = '';
    while ($after_rs && !$after_rs->EOF) {
        $aft = $after_rs->fields;

        $depth = '';
        if ($aft["depth1"] !== '-') {
            $depth .= '(' . $aft["depth1"];
        }
        if ($aft["depth2"] !== '-') {
            $depth .= ' ' . $aft["depth2"];
        }
        if ($aft["depth3"] !== '-') {
            $depth .= ' ' . $aft["depth1"];
        }
        if (!empty($depth)) {
            $depth .= ')';
        }

        $after .= $aft["after_name"] . $depth . " / ";

        $after_rs->MoveNext();
    }

    if (empty($sort_arr[$flds["bun_dlvr_order_num"]])) {
        $sort_arr[$flds["bun_dlvr_order_num"]] = [
            "order_arr" => [
                 0 => [
                     "order_seqno" => $flds["order_common_seqno"]
                    ,"cate_name"   => $cate_name
                    ,"title"       => $flds["title"]
                    ,"detail"      => $order_detail
                    ,"after"       => substr($after, 0, -3)
                    ,"expec_date"  => explode(' ', $flds["expec_release_date"])[0]
                    ,"amt"         => number_format($flds["amt"]) . $flds["amt_unit_dvs"]
                    ,"count"       => $flds["count"]
                    ,"weight"      => doubleval($flds["expec_weight"])
                    ,"lump"        => $flds["lump_count"]
                ]
            ]
            ,"sum_order"    => 1
            ,"name"         => $flds["name"]
            ,"recei"        => $flds["recei"]
            ,"zipcode"      => $flds["zipcode"]
            ,"addr"         => $flds["addr"]
            ,"addr_detail"  => $flds["addr_detail"]
            ,"dlvr_sum_way" => $dlvr_sum_way
            ,"invo_cpn"     => $flds["invo_cpn"]
            ,"sum_weight"   => doubleval($flds["expec_weight"])
            ,"sum_lump"     => intval($flds["lump_count"])
            ,"bun_yn"       => $flds["bun_yn"]
        ];
    } else {
        $order_arr = [
             "order_seqno" => $flds["order_common_seqno"]
            ,"cate_name"   => $cate_name
            ,"title"       => $flds["title"]
            ,"detail"      => $order_detail
            ,"after"       => substr($after, 0, -3)
            ,"expec_date"  => explode(' ', $flds["expec_release_date"])[0]
            ,"amt"         => number_format($flds["amt"]) . $flds["amt_unit_dvs"]
            ,"count"       => $flds["count"]
            ,"weight"      => doubleval($flds["expec_weight"])
            ,"lump"        => $flds["lump_count"]
        ];

        $sort_arr[$flds["bun_dlvr_order_num"]]["order_arr"][] = $order_arr;
        $sort_arr[$flds["bun_dlvr_order_num"]]["sum_order"]++;
        $sort_arr[$flds["bun_dlvr_order_num"]]["sum_weight"] +=
            doubleval($flds["expec_weight"]);
        $sort_arr[$flds["bun_dlvr_order_num"]]["sum_lump"] +=
            intval($flds["lump_count"]);
    }

    $rs->MoveNext();
}
unset($rs);
unset($cate_name_arr);

$group_form      = getGroupHtmlForm();
$group_unit_form = getGroupUnitHtmlForm();
$unit_form = getUnitHtmlForm();

$group_count       = 0;
$group_order_count = 0;
$unit_order_count = 0;

$group_html = '';
$unit_html = '';
foreach ($sort_arr as $dlvr_num => $sort) {
    $disabled = false;
    if (ceil($sort["sum_weight"]) >= 12) {
        $disabled = true;
    }

    if ($sort["bun_yn"] === 'Y') {
        //--- 그룹
        $group_count++;
        $group_order_count += $sort["sum_order"];

        // 묶음정보 tr
        $group_html .= sprintf($group_form, $sort["name"] //#1
                                          , $sort["recei"] //#2
                                          , $sort["addr"] //#3
                                          , $sort["addr_detail"] //#3
                                          , $sort["dlvr_sum_way"] //#4
                                          , $sort["sum_weight"] //#5
                                          );

        $order_arr = $sort["order_arr"];
        foreach ($order_arr as $order) {
            // 묶음상품 tr
            $group_html .= sprintf($group_unit_form, $order["cate_name"] //#1
                                                   , $order["title"] //#1
                                                   , $order["detail"] //#2
                                                   , $order["after"] //#3
                                                   , $order["expec_date"] //#4
                                                   , $order["amt"] //#5
                                                   , $order["count"] //#5
                                                   , $order["weight"] //#6
                                                   , $dlvr_num //#7
                                                   , $order["order_seqno"] //#7
                                                   );
        }
    } else {
        //--- 낱건
        $unit_order_count++;

        // 낱건상품 tr
        $order = $sort["order_arr"][0];
        $unit_html .= sprintf($unit_form, $order["order_seqno"] //#0
                                        , $order["cate_name"] //#1
                                        , $order["title"] //#1
                                        , $order["detail"] //#2
                                        , $order["after"] //#3
                                        , $order["expec_date"] //#4
                                        , $order["amt"] //#5
                                        , $order["count"] //#5
                                        , $order["weight"] //#6
                                        , $dlvr_num //#7
                                        , $order["order_seqno"] //#7
                                        );
    }
}

if($group_html == '') {
    $group_html = getEmptyGroupHtmlForm();
}

if($unit_html == '') {
    $unit_html = getEmptyGroupUnitHtmlForm();
}

$ret = [];
$ret["group"] = $group_html;
$ret["group_count"] = $group_count;
$ret["group_order_count"] = $group_order_count;
$ret["unit_order_count"] = $unit_order_count;
$ret["unit"] = $unit_html;

echo json_encode($ret);

$conn->Close();
exit;

/******************************************************************************
 ************** 함수영역
 ******************************************************************************/

function getGroupHtmlForm() {
    $ret  = "<tr class=\"delivery_group_top_tr\">";
    $ret .=     "<td class=\"left\">%s</td>"; //#1 name
    $ret .=     "<td>%s</td>"; //#2 recei
    $ret .=     "<td colspan=\"3\" class=\"address\">";
    $ret .=         "%s %s"; //#4 addr, addr_detail
    $ret .=     "</td>";
    $ret .=     "<td>%s</td>"; //#5 dlvr_sum_way
    $ret .=     "<td>%skg</td>"; //#6 sum_weight
    $ret .=     "<td class=\"order_list_set_td\">";
    $ret .=     "</td>";
    $ret .= "</tr>";

    return $ret;
}

function getEmptyGroupHtmlForm() {
    $ret  = "<tr class=\"delivery_group_top_tr\">";
    $ret .=     "<td colspan=\"8\" class=\"address\">";
    $ret .=     "묶음 상품이 없습니다.";
    $ret .=     "</td>";
    $ret .= "</tr>";

    return $ret;
}

function getEmptyGroupUnitHtmlForm() {
    $ret  = "<tr class=\"delivery_group_top_tr\">";
    $ret .=     "<td colspan=\"8\" class=\"address\">";
    $ret .=     "묶음 가능한 상품이 없습니다.";
    $ret .=     "</td>";
    $ret .= "</tr>";

    return $ret;
}

function getGroupUnitHtmlForm() {
    $ret  = "<tr>";
    $ret .=     "<td colspan=\"3\" class=\"left\">";
    $ret .=         "<dl class=\"orderDetail\">";
    $ret .=             "<dt><strong>[%s]</strong> %s"; //#1 cate_name, title
    $ret .=             "<dd>%s</dd>"; //#2 detail
    $ret .=         "</dl>";
    $ret .=     "</td>";
    $ret .=     "<td class=\"left\">";
    $ret .=         "<ul class=\"post\">%s</ul>"; //#3 after
    $ret .=     "</td>";
    $ret .=     "<td>%s</td>"; //#4 expec_date
    $ret .=     "<td class=\"amount\">%s(%s건)</td>"; //#5 amt, count
    $ret .=     "<td>%skg</td>"; //#6 expec_weight
    $ret .=     "<td class=\"btn\">";
    $ret .=         "<button class=\"ungroup\" title=\"묶음해제\" onclick=\"modiGroup('%s', '%s', 'e');\">"; //#7 bun_dlvr_order_num, order_seqno
    $ret .=         "묶음해제";
    $ret .=         "</button>";
    $ret .=     "</td>";
    $ret .= "</tr>";

    return $ret;
}

function getUnitHtmlForm() {
    $ret  = "<tr>";
    $ret .=     "<td colspan=\"3\" class=\"left\">";
    $ret .=         "<div class=\"subject\" onclick=\"goOrderAll('%s');\" style=\"cursor:pointer;\">"; //#0 order_seqno
    $ret .=             "<span class=\"category_text\">[%s]</span>&nbsp;<span class=\"order_list_title_text\">%s</span><br>"; //#1 cate_name, title
    $ret .=             "<ul class=\"information\">%s</ul>"; //#2 detail
    $ret .=         "</div>";
    $ret .=     "</td>";
    $ret .=     "<td class=\"left\">";
    $ret .=         "<div class=\"subject\">";
    $ret .=             "<ul class=\"information\">%s</ul>"; //#3 after
    $ret .=         "</div>";
    $ret .=     "</td>";
    $ret .=     "<td>%s</td>"; //#4 expec_date
    $ret .=     "<td class=\"amount\">%s(%s건)</td>"; //#5 amt, count
    $ret .=     "<td>%skg</td>"; //#6 expec_weight
    $ret .=     "<td class=\"btn\">";
    $ret .=         "<button class=\"group\" title=\"묶음추가\" onclick=\"modiGroup('%s', '%s', 'a');\">"; //#7 bun_dlvr_order_num, order_seqno
    $ret .=         "묶음추가";
    $ret .=         "</button>";
    $ret .=     "</td>";
    $ret .= "</tr>";

    return $ret;
}

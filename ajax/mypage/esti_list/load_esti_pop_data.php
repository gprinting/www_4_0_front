<?php
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/EstiInfoDAO.inc"); 
include_once(INC_PATH . '/com/nexmotion/common/util/front/pageLib.inc');
include_once(INC_PATH . '/define/front/product_info_class.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EstiInfoDAO();

$esti_seqno   = $fb->form("esti_seqno");
$member_seqno = $fb->session("org_member_seqno");

$param = [
    "esti_seqno" => $esti_seqno
    ,"member_seqno" => $member_seqno
];

$base = $dao->selectEstiBaseInfo($conn, $param);
if (empty($base)) {
    echo "{}";
    exit;
}

$after_en_arr = ProductInfoClass::AFTER_ARR;

$cate_info = $dao->selectCateInfo($conn, $base["cate_sortcode"]);

// $ret에 들어갈 데이터
$amt = $base["amt"];
if (!empty($base["amt_note"])) {
    $amt .= '[' . $base["amt_note"] . ']';
}

$paper_arr = [];
$size_arr  = [];
$tmpt_arr  = [];
$after_arr       = []; // 이름만
$after_det_arr   = []; // 이름[depth]
$after_price_arr = []; // 후가공 가격

$paper_price  = 0;
$print_price  = 0;
$output_price = 0;

$detail = null;
if ($cate_info["flattyp_yn"] === 'Y') {
    $detail = $dao->selectEstiInfoDetailSheet($conn, $param);
} else {
    $detail = $dao->selectEstiInfoDetailBrochure($conn, $param);
}

getDetailData($conn, $dao, $detail,
              $paper_price, $output_price, $print_price,
              $paper_arr, $size_arr, $tmpt_arr,
              $after_arr, $after_det_arr, $after_price_arr);
$ret = [
    "cate_name" => [
        $cate_info["cate_name"]
    ]
    ,"paper" => $paper_arr
    ,"size"  => $size_arr
    ,"tmpt"  => $tmpt_arr
    ,"amt" => [
        $amt
    ]
    ,"amt_unit" => [
        $base["amt_unit_dvs"]
    ]
    ,"count" => [1]
    ,"after"     => $after_arr
    ,"after_det" => $after_det_arr

    ,"paper_price"  => number_format($paper_price)
    ,"print_price"  => number_format($print_price)
    ,"output_price" => number_format($output_price)

    ,"opt_price"    => 0
    ,"supply_price" => number_format(doubleval($base["origin_price"]))
    ,"tax"          => number_format(doubleval($base["vat"]))
    ,"sell_price"   => number_format(doubleval($base["esti_price"]))
    ,"sale_price"   => number_format(doubleval($base["sale_price"]))
    ,"pay_price"    => number_format(doubleval($base["order_price"]))
];

// ret에 후공정별 가격 넣기
foreach ($after_arr as $after) {
    $ret[$after_en_arr[$after] . "_price"] =
        number_format(doubleval($after_price_arr[$after]));
}

echo json_encode($ret);

$conn->Close();
exit;

/**
 * @brief 견적서 출력용 상세데이터 생성
 */
function getDetailData($conn, $dao, $detail_rs,
        &$paper_price, &$output_price , &$print_price,
        &$paper_arr  , &$size_arr     , &$tmpt_arr,
        &$after_arr  , &$after_det_arr, &$after_price_arr) {

    while ($detail_rs && !$detail_rs->EOF) {
        $detail = $detail_rs->fields;

        $paper_price  += intval($detail["paper_price"]);
        $print_price  += intval($detail["output_price"]);
        $output_price += intval($detail["print_price"]);

        $paper_info = $detail["paper_info"];
        if (!empty($detail["paper_info_note"])) {
            $paper_info .= '[' . $detail["paper_info_note"] . ']';
        }

        $size_info = $detail["size_info"];
        if (!empty($detail["size_info_note"])) {
            $size_info .= '[' . $detail["size_info_note"] . ']';
        }

        $bef_tmpt_info = $detail["bef_tmpt_info"];
        if (!empty($detail["bef_tmpt_info_note"])) {
            $bef_tmpt_info .= '[' . $detail["bef_tmpt_info_note"] . ']';
        }
        $aft_tmpt_info = $detail["aft_tmpt_info"];
        if (!empty($detail["aft_tmpt_info_note"])) {
            $aft_tmpt_info .= '[' . $detail["aft_tmpt_info_note"] . ']';
        }
        $tmpt_info = $bef_tmpt_info . " / " . $aft_tmpt_info;

        $paper_arr[] = $paper_info;
        $size_arr[]  = $size_info;
        $tmpt_arr[]  = $tmpt_info;

        $param["esti_detail_dvs_num"] = $detail["esti_detail_dvs_num"];
        $after_rs = $dao->selectEstiInfoAfter($conn, $param);

        $after_det = '';
        while ($after_rs && !$after_rs->EOF) {
            $after = $after_rs->fields;

            $name = $after["after_name"];
            $d1   = $after["depth1"];
            $d2   = $after["depth2"];
            $d3   = $after["depth3"];
            $price = $after["price"];

            $after_det .= $name;
            $det_temp = null;
            if ($d1 !== '-') {
                $det_temp .= '[' . $d1;
            }
            if ($d2 !== '-') {
                $det_temp .= empty($det_temp) ? '[' : '/';
                $det_temp .= $d2;
            }
            if ($d3 !== '-') {
                $det_temp .= empty($det_temp) ? '[' : '/';
                $det_temp .= $d3;
            }
            $det_temp .= empty($det_temp) ? '' : ']';

            $after_det .= ' ' . $det_temp . ', ';

            $after_arr[] = $name;

            if (empty($after_price_arr[$name])) {
                $after_price_arr[$name] = $price;
            } else {
                $after_price_arr[$name] += $price;
            }

            $after_rs->MoveNext();
        }

        $after_det_arr[] = substr($after_det, 0, -2);

        $detail_rs->MoveNext();
    }
}

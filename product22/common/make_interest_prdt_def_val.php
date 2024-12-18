<?php define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/front/FrontCommonUtil.inc');

if (!$is_login) {
    goto ERR;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new OrderInfoDAO();

$interest_prdt_seqno = $fb->form("seqno");
$member_seqno = $fb->session("org_member_seqno");
unset($fb);

$param = [];
$param["interest_prdt_seqno"] = $interest_prdt_seqno;
$param["member_seqno"] = $member_seqno;

//$conn->debug = 1;

//$cate_sortcode = $dao->selectInterestPrdtCateSortcode($conn, $param);
$res = $dao->selectInterestPrdtCateSortcode($conn, $param);
if (empty($res->fields["cate_sortcode"])) {
    goto ERR;
}

//$cate_info = $dao->selectCateInfo($conn, $cate_sortcode);
$cate_info = $dao->selectCateInfo($conn, $res->fields["cate_sortcode"]);
$flattyp_yn = $cate_info["flattyp_yn"];
unset($cate_info);

if ($flattyp_yn === 'Y') {
    $prdt_rs = $dao->selectInterestPrdtDetail($conn, $param);
} else {
    $prdt_rs = $dao->selectInterestPrdtDetailBrochure($conn, $param);
}

$aft_rs = $dao->selectPrdtAfter($conn, $param);
$opt_rs = $dao->selectPrdtOpt($conn, $param);

//$arr = makeDefaultValArr($conn, $dao,
//                         $cate_sortcode, $prdt_rs, $aft_rs, $opt_rs);
$arr = makeDefaultValArr($conn, $dao,
                         $res, $prdt_rs, $aft_rs, $opt_rs);
$arr["title"] = $res->fields["title"];

$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
$json = str_replace('"', "&quot;", $json);

$html = <<<html
    <html>
        <head>
        </head>
        <body>
            <form id="frm" method="post" action="/product22/common/move_product.php">
                <input type="hidden" name="t" value="{$res->fields["title"]}" />
                <input type="hidden" name="cs" value="{$res->fields["cate_sortcode"]}" />
                <input type="hidden" name="def_json" value="{$json}" />
                <button type="submit">submit</button>
            </form>

            <script>
                (function() {
                   document.getElementById("frm").submit();
                })();
            </script>
        </body>
    </html>
html;

echo $html;

exit;

ERR:
    $conn->Close();
    header("Location: /main/main.html");
    exit;

/******************************************************************************
 *************** 함수영역
 ******************************************************************************/

/**
 * @brief 카테고리별 default 배열 카테고리별 유형에 따라서 생성
 *
 * @param $cate_sortcode = 카테고리 분류코드
 * @param $prdt_rs = 관심상품 상세 검색결과
 * @param $aft_rs  = 관심상품 후공정 검색결과
 * @param $opt_rs  = 관심상품 옵션 검색결과
 *
 * @return 배열값
 *
function makeDefaultValArr($conn, $dao,
                           $cate_sortcode, $prdt_rs, $aft_rs, $opt_rs) {
    switch($cate_sortcode) {
        // 카다로그, 브로셔 = type1
        case "001001001" :
            $ret = makeType1Arr($conn, $dao, $prdt_rs, $aft_rs, $opt_rs);
            break;
        // 현수막 = type2
        case "002005003" :
            $ret = makeType2Arr($conn, $dao, $prdt_rs, $aft_rs, $opt_rs);
            break;
        // 독판전단 = type3
        case "005002001" :
            $ret = makeType3Arr($conn, $dao, $prdt_rs, $aft_rs, $opt_rs);
            break;
        // NCR = type4
        case "007001001" :
        case "007001002" :
        case "007001003" :
            $ret = makeType4Arr($conn, $dao, $prdt_rs, $aft_rs, $opt_rs);
            break;
        // 리플렛, 팜플렛 / 합판전단 = type5
        case "001002001" :
        case "005003001" :
            $ret = makeType5Arr($conn,
                                $dao,
                                $prdt_rs,
                                $aft_rs,
                                $opt_rs,
                                [ "paper_name" => true
                                 ,"size_typ"   => false]);
            break;
        // 강접스티커 / 특수지스티커 / 문어발 = type5
        case "004001001" :
        case "004002001" :
        case "008001003" :
        case "008001004" :
            $ret = makeType5Arr($conn,
                                $dao,
                                $prdt_rs,
                                $aft_rs,
                                $opt_rs,
                                [ "paper_name" => false
                                 ,"size_typ"   => true]);
            break;
        // 그 외 = type5
        default :
            $ret = makeType5Arr($conn,
                                $dao,
                                $prdt_rs,
                                $aft_rs,
                                $opt_rs,
                                [ "paper_name" => false
                                 ,"size_typ"   => false]);
            break;
    }

    return $ret;
}
*/

/**
 * @brief 카테고리별 default 배열 카테고리별 유형에 따라서 생성
 *
 * @param $cate_sortcode = 카테고리 분류코드
 * @param $prdt_rs = 관심상품 상세 검색결과
 * @param $aft_rs  = 관심상품 후공정 검색결과
 * @param $opt_rs  = 관심상품 옵션 검색결과
 *
 * @return 배열값
 */
function makeDefaultValArr($conn, $dao,
                           $res, $prdt_rs, $aft_rs, $opt_rs) {
    switch($res->fields["cate_sortcode"]) {
        // 카다로그, 브로셔 = type1
        case "001001001" :
            $ret = makeType1Arr($conn, $dao, $prdt_rs, $aft_rs, $opt_rs);
            break;
        // 현수막 = type2
        case "002005003" :
            $ret = makeType2Arr($conn, $dao, $prdt_rs, $aft_rs, $opt_rs);
            break;
        // 독판전단 = type3
        case "005002001" :
            $ret = makeType3Arr($conn, $dao, $prdt_rs, $aft_rs, $opt_rs);
            break;
        // NCR = type4
        case "007001001" :
        case "007001002" :
        case "007001003" :
            $ret = makeType4Arr($conn, $dao, $prdt_rs, $aft_rs, $opt_rs);
            break;
        // 리플렛, 팜플렛 / 합판전단 = type5
        case "001002001" :
        case "005003001" :
            $ret = makeType5Arr($conn,
                                $dao,
                                $prdt_rs,
                                $aft_rs,
                                $opt_rs,
                                [ "paper_name" => true
                                 ,"size_typ"   => false]);
            break;
        // 강접스티커 / 특수지스티커 / 문어발 = type5
        case "004001001" :
        case "004002001" :
        case "008001003" :
        case "008001004" :
            $ret = makeType5Arr($conn,
                                $dao,
                                $prdt_rs,
                                $aft_rs,
                                $opt_rs,
                                [ "paper_name" => false
                                 ,"size_typ"   => true]);
            break;
        // 그 외 = type5
        default :
            $ret = makeType5Arr($conn,
                                $dao,
                                $prdt_rs,
                                $aft_rs,
                                $opt_rs,
                                [ "paper_name" => false
                                 ,"size_typ"   => false]);
            break;
    }

    return $ret;
}

/**
 * @brief 카다로그, 브로셔 기본값 배열 생성
 */
function makeType1Arr($conn, $dao, $prdt_rs, $aft_rs, $opt_rs) {
    /*
    [
        "cover" => [
            "paper_sort" => "일반용지"
            ,"paper_name" => "아트지"
            ,"paper"      => "백색 150g"
            ,"print"      => [
                "bef_print" => "칼라4도", "aft_print" => "칼라4도"
            ]
            ,"print_purp" => "일반옵셋"
            ,"page"       => 4
        ]
        ,"inner1" => [
            "paper_sort" => "일반용지"
            ,"paper_name" => "아트지"
            ,"paper"      => "백색 150g"
            ,"print"      => [
                "bef_print" => "칼라4도", "aft_print" => "칼라4도"
            ]
            ,"print_purp" => "일반옵셋"
            ,"page"       => 4
        ]
        ,"inner2" => [
            "paper_sort" => "일반용지"
            ,"paper_name" => "아트지"
            ,"paper"      => "백색 150g"
            ,"print"      => [
                "bef_print" => "칼라4도", "aft_print" => "칼라4도"
            ]
            ,"print_purp" => "일반옵셋"
            ,"page"       => 4
        ]
        ,"inner3" => [
            "paper_sort" => "일반용지"
            ,"paper_name" => "아트지"
            ,"paper"      => "백색 150g"
            ,"print"      => [
                "bef_print" => "칼라4도", "aft_print" => "칼라4도"
            ]
            ,"print_purp" => "일반옵셋"
            ,"page"       => 4
        ]
        ,"binding_d1" => "중철제본"
        ,"binding_d2" => "세로좌철"
        ,"size"       => "A4"
        ,"amt"        => 500
    ]
     */

    $util = new FrontCommonUtil();

    // interest_prdt_detail_dvs_num 기준으로 배열 생성
    $binding_info = null;

    $aft_sort_arr = [];
    while ($aft_rs && !$aft_rs->EOF) {
        $fields = $aft_rs->fields;

        if ($fields["after_name"] === "제본") {
            $binding_info = $fields;
            $aft_rs->MoveNext();
        }

        $name = $fields["name"];
        $depth1 = $fields["depth1"];
        $depth2 = $fields["depth2"];
        $depth3 = $fields["depth3"];
        $mpcode = $fields["mpcode"];
        $info = $fields["info"];

        $aft_sort_arr[$fields["interest_prdt_detail_dvs_num"]][$name] = [
             "depth1" => $depth1
            ,"depth2" => $depth2
            ,"depth3" => $depth3
            ,"info"   => $info
            ,"mpcode" => $mpcode
        ];

        $aft_rs->MoveNext();
    }
    unset($aft_rs);

    $opt_sort_arr = makeSortArr($opt_rs);
    unset($opt_rs);

    $typ_arr = [
         "표지"  => "cover"
        ,"내지1" => "inner1"
        ,"내지2" => "inner2"
        ,"내지3" => "inner3"
    ];

    $tmp = $prdt_rs->fields;

    $ret = [
         "binding_d1" => $binding_info["depth1"]
        ,"binding_d2" => $binding_info["depth2"]
        ,"size"       => $tmp["stan_name"]
        ,"amt"        => $tmp["amt"]
        ,"opt"        => $opt_sort_arr
    ];
    unset($tmp);

    $param = [];

    while ($prdt_rs && !$prdt_rs->EOF) {
        $fields = $prdt_rs->fields;

        $dvs_num = $fields["interest_prdt_detail_dvs_num"];

        $paper_mpcode     = $fields["cate_paper_tot_mpcode"];
        $bef_print_mpcode = $fields["cate_beforeside_print_mpcode"];
        $aft_print_mpcode = $fields["cate_aftside_print_mpcode"];

        $paper_info = $dao->selectCatePaperInfo($conn, $paper_mpcode);
        $paper = $util->makePaperInfoStr($paper_info, [ "name"        => false
                                                       ,"dvs"         => true
                                                       ,"color"       => true
                                                       ,"basisweight" => true]);

        $param["cate_sortcode"] = $fields["cate_sortcode"];
        $param["mpcode"] = $bef_print_mpcode;
        $bef_print_info = $dao->selectCatePrintTmpt($conn, $param);
        $param["mpcode"] = $aft_print_mpcode;
        $aft_print_info = $dao->selectCatePrintTmpt($conn, $param);

        $ret[$typ_arr[$fields["typ"]]] = [
             "paper_sort" => $paper_info["sort"]
            ,"paper_name" => $paper_info["name"]
            ,"paper"      => $paper
            ,"print"      => [
                 "bef_print" => $bef_print_info->fields["name"]
                ,"aft_print" => $aft_print_info->fields["name"]
            ]
            ,"print_purp" => $fields["print_purp_dvs"]
            ,"page"       => $fields["page_amt"]
            ,"after"      => $aft_sort_arr[$dvs_num]
        ];

        $prdt_rs->MoveNext();
    }

    return $ret;
}

/**
 * @brief 현수막 기본값 배열 생성
 */
function makeType2Arr($conn, $dao, $prdt_rs, $aft_rs, $opt_rs) {
    /*
    [
        "paper"      => "현수막천"
        ,"size_type"  => "가로분할"
        ,"size"       => [
            "wid" => 900, "vert" => 1800
        ]
        ,"print"      => "수성4색"
        ,"amt"        => 1
    ]
     */
    $util = new FrontCommonUtil();

    $aft_sort_arr = makeSortArr($aft_rs);
    unset($aft_rs);

    $opt_sort_arr = makeSortArr($opt_rs);
    unset($opt_rs);

    $fields = $prdt_rs->fields;

    $order_detail = $fields["order_detail"];

    $paper_mpcode     = $fields["cate_paper_tot_mpcode"];
    $bef_print_mpcode = $fields["cate_beforeside_print_mpcode"];

    $paper_info = $dao->selectCatePaperInfo($conn, $paper_mpcode);
    $paper = $util->makePaperInfoStr($paper_info, null);

    $param["cate_sortcode"] = $fields["cate_sortcode"];
    $param["mpcode"] = $bef_print_mpcode;
    $print_info = $dao->selectCatePrintTmpt($conn, $param);

    $ret = [
         "paper" => $paper
        ,"size"  => $fields["stan_name"]
        ,"print" => $print_info["name"]
        ,"amt"   => $fields["amt"]
        ,"after" => $aft_sort_arr
        ,"opt"   => $opt_sort_arr
    ];

    if (strpos($order_detail, "가로분할") !== false) {
        $ret["site_type"] = "가로형";
    } else {
        $ret["site_type"] = "세로형";
    }

    return $ret;
}

function makeType3Arr($conn, $dao, $prdt_rs, $aft_rs, $opt_rs) {
    /*
    [
        "paper_sort" => "일반용지"
        ,"paper_name" => "아트지"
        ,"paper"      => "백색 150g"
        ,"size"       => "A4"
        ,"print"      => [
            "bef_print" => "칼라4도", "aft_print" => "칼라4도"
        ]
        ,"print_purp" => "일반옵셋"
        ,"amt"        => 500
    ]
     */
    $util = new FrontCommonUtil();

    $aft_sort_arr = makeSortArr($aft_rs);
    unset($aft_rs);

    $opt_sort_arr = makeSortArr($opt_rs);
    unset($opt_rs);

    $fields = $prdt_rs->fields;

    $paper_mpcode     = $fields["cate_paper_tot_mpcode"];
    $bef_print_mpcode = $fields["cate_beforeside_print_mpcode"];
    $aft_print_mpcode = $fields["cate_aftside_print_mpcode"];

    $paper_info = $dao->selectCatePaperInfo($conn, $paper_mpcode);
    $paper = $util->makePaperInfoStr($paper_info, [ "name"        => false
                                                   ,"dvs"         => true
                                                   ,"color"       => true
                                                   ,"basisweight" => true]);

    $param["cate_sortcode"] = $fields["cate_sortcode"];
    $param["mpcode"] = $bef_print_mpcode;
    $bef_print_info = $dao->selectCatePrintTmpt($conn, $param);
    $param["mpcode"] = $aft_print_mpcode;
    $aft_print_info = $dao->selectCatePrintTmpt($conn, $param);

    $ret = [
         "paper_sort" => $paper_info["sort"]
        ,"paper_name" => $paper_info["name"]
        ,"paper" => $paper
        ,"size"  => $fields["stan_name"]
        ,"print"      => [
             "bef_print" => $bef_print_info->fields["name"]
            ,"aft_print" => $aft_print_info->fields["name"]
        ]
        ,"print_purp" => $fields["print_purp_dvs"]
        ,"amt"   => $fields["amt"]
        ,"after" => $aft_sort_arr
        ,"opt"   => $opt_sort_arr
    ];

    return $ret;
}

function makeType4Arr($conn, $dao, $prdt_rs, $aft_rs, $opt_rs) {
    /*
    [
        "paper_top"  => "NCR 상지 백색 56g"
        ,"paper_mid"  => "NCR 중지 청색 53g"
        ,"paper_bot"  => "NCR 하지 황색 57g"
        ,"size"       => "48절"
        ,"print"      => "단면1도"
        ,"print_purp" => "마스터인쇄"
        ,"amt"        => 500
    ]
     */
    $util = new FrontCommonUtil();

    $aft_sort_arr = makeSortArr($aft_rs);
    unset($aft_rs);

    $opt_sort_arr = makeSortArr($opt_rs);
    unset($opt_rs);

    $fields = $prdt_rs->fields;

    $paper_mpcode_arr = explode('|', $fields["cate_paper_tot_mpcode"]);
    $bef_print_mpcode = $fields["cate_beforeside_print_mpcode"];
    $aft_print_mpcode = $fields["cate_aftside_print_mpcode"];

    $paper_mpcode_count = count($paper_mpcode_count);

    // 상지
    $paper_top_info = $dao->selectCatePaperInfo($conn,
                                                $paper_mpcode_arr[0]);
    $paper_top = $util->makePaperInfoStr($paper_top_info, null);
    // 중지1
    if (empty($paper_mpcode_arr[1])) {
        $paper_mid1_info = $dao->selectCatePaperInfo($conn,
                                                     $paper_mpcode_arr[1]);
        $paper_mid1 = $util->makePaperInfoStr($paper_mid1_info, null);
    }
    // 중지2
    if (empty($paper_mpcode_arr[2])) {
        $paper_mid2_info = $dao->selectCatePaperInfo($conn,
                                                     $paper_mpcode_arr[2]);
        $paper_mid2 = $util->makePaperInfoStr($paper_mid2_info, null);
    }
    // 하지
    $paper_bot_info =
        $dao->selectCatePaperInfo($conn,
                                  $paper_mpcode_arr[$paper_mpcode_count - 1]);
    $paper_bot = $util->makePaperInfoStr($paper_bot_info, null);


    $param["cate_sortcode"] = $fields["cate_sortcode"];
    $param["mpcode"] = $bef_print_mpcode;
    $bef_print_info = $dao->selectCatePrintTmpt($conn, $param);
    $param["mpcode"] = $aft_print_mpcode;
    $aft_print_info = $dao->selectCatePrintTmpt($conn, $param);

    $ret = [
         "paper_top"  => $paper_top
        ,"paper_mid1" => $paper_mid1
        ,"paper_mid2" => $paper_mid2
        ,"paper_bot"  => $paper_bot
        ,"size"       => $fields["stan_name"]
        ,"print"      => $bef_print_info->fields["name"]
        ,"print_purp" => $fields["print_purp_dvs"]
        ,"amt"   => $fields["amt"]
        ,"after" => $aft_sort_arr
        ,"opt"   => $opt_sort_arr
    ];

    return $ret;
}

function makeType5Arr($conn, $dao, $prdt_rs, $aft_rs, $opt_rs, $flag_arr) {
    /*
    [
        "paper_name" => "아트지"
        ,"paper"      => "백색 150g"
        ,"size_typ"   => "2단 리플렛"
        ,"size"       => "2단 리플렛"
        ,"print"      => "양면칼라8도"
        ,"print_purp" => "일반옵셋"
        ,"amt"        => 500
    ]
     */
    $util = new FrontCommonUtil();

    $aft_sort_arr = makeSortArr($aft_rs);
    unset($aft_rs);

    $opt_sort_arr = makeSortArr($opt_rs);
    unset($opt_rs);

    $fields = $prdt_rs->fields;

    $paper_mpcode     = $fields["cate_paper_tot_mpcode"];
    $bef_print_mpcode = $fields["cate_beforeside_print_mpcode"];


    $param["cate_sortcode"] = $fields["cate_sortcode"];
    $param["mpcode"] = $bef_print_mpcode;
    $print_info = $dao->selectCatePrintTmpt($conn, $param);

    $ret = [
         "size"  => $fields["stan_name"]
        ,"print" => $print_info->fields["name"]
        ,"print_purp" => $fields["print_purp_dvs"]
        ,"amt"   => $fields["amt"]
        ,"after" => $aft_sort_arr
        ,"opt"   => $opt_sort_arr
    ];

    $paper_info = $dao->selectCatePaperInfo($conn, $paper_mpcode);
    if ($flag_arr["paper_name"]) {
        $paper = $util->makePaperInfoStr($paper_info, [ "name"        => false
                                                       ,"dvs"         => true
                                                       ,"color"       => true
                                                       ,"basisweight" => true]);
        $ret["paper_name"] = $paper_info["name"];
    } else {
        $paper = $util->makePaperInfoStr($paper_info, null);
    }

    $ret["paper"] = $paper;

    if ($flag_arr["size_typ"]) {
        $param["cate_mpcode"] = $fields["cate_output_mpcode"];
        $stan_info = $dao->selectCateSize($conn, $param);

        $ret["size_typ"] = $stan_info->fields["typ"];
    }

    return $ret;
}

function makeSortArr($rs) {
    $ret = [];

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $name = $fields["name"];
        $depth1 = $fields["depth1"];
        $depth2 = $fields["depth2"];
        $depth3 = $fields["depth3"];
        $mpcode = $fields["mpcode"];
        $info = $fields["info"];

        $temp = [
             "depth1" => $depth1
            ,"depth2" => $depth2
            ,"depth3" => $depth3
            ,"info"   => $info
            ,"mpcode" => $mpcode
        ];

        $ret[$name] = $temp;

        $rs->MoveNext();
    }

    return $ret;
}

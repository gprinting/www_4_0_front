<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/product/ProductEstiDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/CartDAO.inc");
include_once(INC_PATH . "/common_define/prdt_default_info.inc");
include_once(INC_PATH . "/common_define/common_config.inc");

if ($is_login === false) {
    echo "로그인이 필요합니다.";
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new FrontCommonUtil();
$dao = new ProductEstiDAO();
$cartDAO = new CartDAO();
$fb = new FormBean();

$session = $fb->getSession();
$fb = $fb->getForm();

/*
echo "<pre>";
$conn->debug = 1;
print_r($fb);
*/

// insert 실패시 에러메세지
$err_msg = '';

//@ 제목, 빈값이면 뒤로가기
$title = $fb["title"];
if (empty($title)) {
    $err_msg = "인쇄물 제목을 입력해주세요.";
    exit;
}

$state_arr = $session["state_arr"];
$after_en_arr = ProductInfoClass::AFTER_ARR;

$common_param = [];
$common_param["member_seqno"] = $session["org_member_seqno"];
$common_param["group_seqno"]  = null;
$common_param["title"]        = $title;
if($fb["is_admin"] == 1) {
    $common_param["state"] = $state_arr["견적완료"];
} else {
    $common_param["state"] = $state_arr["견적대기"];
}
$common_param["expec_weight"] = 0;
$common_param["memo"]         = $fb["cust_memo"];

if ($session["member_dvs"] === "기업" ||
        $session["member_dvs"] === "기업개인") {
    $common_param["group_seqno"] = $session["member_seqno"];
}
$common_param["opt_use_yn"] = 'Y';
if (empty($fb["opt_add"])) {
    $common_param["opt_use_yn"] = 'N';
}


// 제품 공통구분값, 책자형 제품인지 구분
$common_prdt_dvs = $fb["common_prdt_dvs"];
// 책자형인지 판단
$is_booklet = false;
if (!empty($common_prdt_dvs)) {
    $prefix = $common_prdt_dvs . '_';
    $common_cate_sortcode = $fb[$prefix . "cate_sortcode"];
    $common_amt           = $fb[$prefix . "amt"];

    $is_booklet = true;

    $cate_info = $dao->selectCateInfo($conn, $common_cate_sortcode);
    $flattyp_yn = $cate_info["flattyp_yn"];
    unset($cate_info);

    $common_param["cate_sortcode"]  = $common_cate_sortcode;
    $common_param["esti_detail"]    = $fb["esti_detail"];

    $common_param["amt"]            = $common_amt;
    $common_param["amt_note"]       = $fb[$prefix . "ext_amt"];
    $common_param["amt_unit_dvs"]   = $fb[$prefix . "amt_unit"];
    $common_param["count"]          = '1';

    $common_param["page_cnt"]      = intval($fb[$prefix . "sheet_count"]);

    // 견적번호
    $esti_num = makeEstiNum($conn, $cartDAO, $dao, $common_cate_sortcode);
    $common_param["esti_num"] = $esti_num;

    // 공통 후공정 가격(제본비 등)
    $common_after_price = $fb[$prefix . "after_price"];

    // 책자 제본 정보
    $binding_mpcode = $fb[$prefix . "binding_val"];
}

$dvs_arr = explode('|', $fb["prdt_dvs"]);
$dvs_arr_count = count($dvs_arr);

$detail_param_arr = [];
$param = [];
for ($i = 0; $i < $dvs_arr_count; $i++) {
    $dvs = $dvs_arr[$i];
    $prefix = $dvs . '_';
    $typ = $fb[$prefix . "typ"];

    $cate_sortcode = $fb[$prefix . "cate_sortcode"];

    //@ 카테고리 분류코드 없는경우 비정상 접근
    if (empty($cate_sortcode) === true) {
        $location .= "/main22/main.html";
        $err_line = __LINE__;
        goto SUCCESS;
    }

    $cate_info = $dao->selectCateInfo($conn, $cate_sortcode);
    $flattyp_yn = $cate_info["flattyp_yn"];
    $tmpt_dvs   = $cate_info["tmpt_dvs"];

    unset($param);
    // 수량_단위_구분
    $amt_unit_dvs = $fb[$prefix . "amt_unit"];

    //@ 수량_단위_구분 없는경우 비정상 접근
    if (empty($amt_unit_dvs) === true) {
        $location .= "/main22/main.html";
        $err_line = __LINE__;
        goto SUCCESS;
    }
    // 수량
    $amt = $fb[$prefix . "amt"];
    if (empty($amt)) {
        $amt = $common_amt;
    }
    // 건수
    $count = 1;
    if (empty($fb[$prefix . "count"]) === false) {
        $count = intval($fb[$prefix . "count"]);
    }

    $param["conn"]      = $conn;
    $param["dao"]       = $cartDAO;
    $param["util"]      = $util;
    $param["fb"]        = $fb;
    $param["prefix"]    = $prefix;
    $param["state_arr"] = $state_arr;
    $param["tmpt_dvs"]  = $tmpt_dvs;
    $detail_param_arr[$i] = makeDetailParam($param);
    $detail_param_arr[$i]["flattyp_yn"] = $flattyp_yn;
    $detail_param_arr[$i]["prefix"] = $prefix;

    $expec_weight = $detail_param_arr[$i]["expec_weight"];
    $page_amt     = $detail_param_arr[$i]["page_amt"];
    $common_param["expec_weight"] += $expec_weight;
    $common_param["page_amt"]     += $page_amt;

    if (!$is_booklet) {
        $common_cate_sortcode = $cate_sortcode;
        $temp = $detail_param_arr[$i];

        $common_param["cate_sortcode"] = $cate_sortcode;
        $common_param["esti_detail"]   = $fb["esti_detail"];

        $common_param["amt"]          = $amt;
        $common_param["amt_note"]     = $fb[$prefix . "ext_amt"];
        $common_param["amt_unit_dvs"] = $amt_unit_dvs;
        $common_param["count"]        = $count;

        if (empty($fb[$prefix . "sheet_count"]) === true) {
            $amt = doubleval($fb[$prefix . "amt"]);
        } else {
            $amt = doubleval($fb[$prefix . "sheet_count"]);
        }

        $common_param["page_cnt"] = $amt;

        // 견적번호
        $esti_num = makeEstiNum($conn, $cartDAO, $dao, $cate_sortcode);
        $common_param["esti_num"] = $esti_num;

        unset($temp);
    }
}

$insert_ret = $dao->insertEsti($conn, $common_param);

if ($insert_ret === false) {
    $err_line = __LINE__;
    $err_msg = "공통 데이터 입력에 실패했습니다.";
    $conn->FailTrans();
    $conn->RollbackTrans();
    goto ERR;
}

$esti_seqno = $conn->Insert_ID("esti");

// 견적 파일 견적일련번호 null값 수정
unset($param);
if (!empty($fb["file_seqno"])) {
    $param["esti_file_seqno"]   = $fb["file_seqno"];
    $file_info = $dao->selectEstiFile($conn, $param);
    $ext = array_pop(explode('.', $file_info["origin_file_name"]));

    $param["esti_seqno"]      = $esti_seqno;
    $param["esti_file_seqno"] = $fb["file_seqno"];
    $param["save_file_name"] = $fb["file_seqno"] . "." . $ext;
    $dao->updateEstiFile($conn, $param);

    if ($conn->HasFailedTrans() === true) {
        $err_line = __LINE__;
        $err_msg = "견적 파일 정보 수정에 실패했습니다.";
        $conn->FailTrans();
        $conn->RollbackTrans();
        goto ERR;
    }
}

// 에러났을 경우 데이터 삭제할 때 사용
$detail_dvs_num_arr = [];
$detail_seqno_arr = [];

$after_add_param_arr = [];

$conn->StartTrans();
$detail_param_arr_count = count($detail_param_arr);
for ($i = 0; $i < $detail_param_arr_count; $i++) {
    $detail_param = $detail_param_arr[$i];

    $prefix = $detail_param["prefix"];
    $flattyp_yn = $detail_param["flattyp_yn"];
    $cate_sortcode = $detail_param["cate_sortcode"];

    $detail_param["esti_seqno"] = $esti_seqno;

    $detail_num = str_pad(strval($i + 1), 2, '0', STR_PAD_LEFT);
    // 주문_상세_번호
    $detail_num = $esti_num . $detail_num;

    //! 낱장여부에 따라 주문_상세 or 주문_상세_책장 입력
    $detail_dvs_num = null;
    if ($flattyp_yn === 'Y') {
        $detail_dvs_num = 'S' . $detail_num;
        $detail_param["esti_detail_dvs_num"] = $detail_dvs_num;
        $dao->insertEstiDetail($conn, $detail_param);

        $detail_seqno = $conn->Insert_ID("esti_detail");

    } else {
        $detail_dvs_num = 'B' . $detail_num;
        $detail_param["esti_detail_dvs_num"] = $detail_dvs_num;
        $dao->insertEstiDetailBrochure($conn, $detail_param);

        $detail_seqno = $conn->Insert_ID("esti_detail_brochure");
    }
    $detail_seqno_arr[] = $detail_seqno;
    $detail_dvs_num_arr[] = $detail_dvs_num;

    if ($conn->HasFailedTrans() === true) {
        $err_line = __LINE__;
        $err_msg = "견적 상세 데이터 입력에 실패했습니다.";
        $conn->FailTrans();
        $conn->RollbackTrans();
        goto ESTI_DETAIL_ERR;
    }

    //! 견적_후공정_내역

    // 견적 후공정 검색
    $after_ko_arr = $fb[$prefix . "chk_after"];
    $count_after_ko_arr = count($after_ko_arr);

    $after_add_mpcode_arr = [];
    $after_add_info_arr = [];

    for ($j = 0; $j < $count_after_ko_arr; $j++) {
        $after_ko = $after_ko_arr[$j];
        $after_en = $after_en_arr[$after_ko];

        $mpcode = $fb[$prefix . $after_en . "_val"];
        $detail = $fb[$prefix . $after_en . "_info"];
        $info   = $fb[$prefix . $after_en . "_data"];

        if (empty($mpcode)) {
            $err_line = __LINE__;
            $err_msg = "견적 후공정 내역 데이터 입력에 실패했습니다.";
            $conn->FailTrans();
            $conn->RollbackTrans();
            goto AFT_ERR;
        }

        $tmp = explode('|', $mpcode)[0];

        $after_add_mpcode_arr[] = $tmp;

        $after_add_info_arr[$tmp]["mpcode"] = $mpcode;
        $after_add_info_arr[$tmp]["detail"] = $detail;
        $after_add_info_arr[$tmp]["info"]   = $info;
        $after_add_info_arr[$tmp]["price"]  = $price;
    }

    if (!empty($after_add_mpcode_arr)) {
        $param["mpcode"]   = $dao->arr2paramStr($conn, $after_add_mpcode_arr);
        $param["basic_yn"] = 'N';

        $add_after_rs = $dao->selectCateAfterInfo($conn, $param);

        $after_add_param_arr[] = makeAfterAddParam($add_after_rs,
                                                   $after_add_info_arr,
                                                   $detail_dvs_num);
    }
}

$conn->CompleteTrans();

// 기본후공정(책자 -> 제본) 검색
if (!empty($binding_mpcode)) {
    $param["mpcode"]   = $dao->parameterEscape($conn, $binding_mpcode);
    $param["basic_yn"] = 'Y';
    $binding = $dao->selectCateAfterInfo($conn, $param)->fields;


    $temp = [];
    $temp["esti_detail_dvs_num"] = $detail_dvs_num_arr[0];
    $temp["mpcode"]   = $binding_mpcode;
    $temp["after_name"] = $binding["after_name"];
    $temp["depth1"]     = $binding["depth1"];
    $temp["depth2"]     = $binding["depth2"];
    $temp["depth3"]     = $binding["depth3"];
    $temp["detail"] = '';
    $temp["info"]   = '';

    $after_add_param_arr[][] = $temp;
}


print_r($after_add_param_arr);
$conn->StartTrans();
$after_add_param_arr_count = count($after_add_param_arr);
for ($i = 0; $i < $after_add_param_arr_count; $i++) {
    $after_add_param = $after_add_param_arr[$i];
    $after_add_param_count = count($after_add_param);

    for ($j = 0; $j < $after_add_param_count; $j++) {
        $param = $after_add_param[$j];
        $param["esti_seqno"] = $esti_seqno;

        $dao->insertEstiAfterHistory($conn, $param);

        if ($conn->HasFailedTrans() === true) {
            $err_line = __LINE__;
            $err_msg = "견적 후공정 내역 데이터 입력에 실패했습니다.";
            $conn->FailTrans();
            $conn->RollbackTrans();
            goto AFT_ERR;
        }
    }
}
$conn->CompleteTrans();

// 추가 옵션 정보 생성
$opt_add_param = [];

if (empty($fb["opt_add"]) === false) {
    $opt_add_mpcode_arr = explode('|', $fb["opt_add"]);
    $opt_add_detail_arr = explode('|', $fb["opt_add_info"]);
    $opt_add_data_arr   = explode('|', $fb["opt_add_data"]);

    $count_opt_add_arr = count($opt_add_mpcode_arr);

    $param["mpcode"] = $util->arr2delimStr($opt_add_mpcode_arr);

    if ($sortcode_t === "002") {
        $add_opt_rs = $dao->selectCateAoOptInfo($conn, $param);
    } else {
        $add_opt_rs = $dao->selectCateOptInfo($conn, $param);
    }

    $opt_add_info_arr = makeOptAddInfoArr($add_opt_rs);

    unset($add_opt_rs);

    $i = 0;

    for (; $i < $count_opt_add_arr; $i++) {
        $mpcode = $opt_add_mpcode_arr[$i];
        $info_arr = $opt_add_info_arr[$mpcode];

        $name = $info_arr["name"];
        $depth1 = $info_arr["depth1"];
        $depth2 = $info_arr["depth2"];
        $depth3 = $info_arr["depth3"];

        $opt_add_param[$i]["esti_seqno"] = $esti_seqno;
        $opt_add_param[$i]["mpcode"]   = $mpcode;
        $opt_add_param[$i]["opt_name"] = $name;
        $opt_add_param[$i]["depth1"]   = $depth1;
        $opt_add_param[$i]["depth2"]   = $depth2;
        $opt_add_param[$i]["depth3"]   = $depth3;
        $opt_add_param[$i]["detail"] = $opt_add_detail_arr[$i];
        $opt_add_param[$i]["info"]   = $opt_add_data_arr[$i];

        // 기본옵션하고 추가옵션하고 겹치는 이름 있을 경우
        // 추가옵션이 기본옵션을 덮어씌움
        if ($order_opt_basic_param[$name] !== null) {
            unset($order_opt_basic_param[$name]);
        }
    }
}

$count_order_opt_add_param = count($opt_add_param);

$conn->StartTrans();
for ($i = 0; $i < $count_order_opt_add_param; $i++) {
    $dao->insertEstiOptHistory($conn, $opt_add_param[$i]);

    if ($conn->HasFailedTrans() === true) {
        $err_line = __LINE__;
        $err_msg = "옵션 내역 데이터 입력에 실패했습니다.";
        $conn->FailTrans();
        $conn->RollbackTrans();
        goto OPT_ERR;
    }
}
$conn->CompleteTrans();

/*
echo "</pre>";
goto OPT_ERR;
exit;
*/

removeEmptyEstiFile($conn, $dao, $session["org_member_seqno"]);

goto SUCCESS;

exit;

OPT_ERR:
    $param["table"] = "esti_opt_history";
    $param["esti_seqno"] = $esti_seqno;
    $ret = $dao->deleteEstiData($conn, $param);
AFT_ERR:
    unset($param);
    $param["esti_detail_dvs_num"] = $detail_dvs_num_arr;
    $ret = $dao->deleteEstiAfterHistory($conn, $param);
ESTI_DETAIL_ERR:
    $param["table"] = "esti_detail";
    $param["esti_seqno"] = $esti_seqno;
    $ret = $dao->deleteEstiData($conn, $param);
    $param["table"] = "esti_detail_brochure";
    $param["esti_seqno"] = $esti_seqno;
    $ret = $dao->deleteEstiData($conn, $param);
ERR:
    $param["table"] = "esti";
    $param["esti_seqno"] = $esti_seqno;
    $ret = $dao->deleteEstiData($conn, $param);
    $conn->CompleteTrans();
    $conn->Close();
    
    $err_msg = $err_line . ':' . $err_msg;
    exit;
SUCCESS:
    $conn->CompleteTrans();
    $conn->Close();

    if($fb["is_admin"] == 1) {
        header("Location: /order/esti_order.html?cart=N&seqno=" . $esti_seqno);
    } else {
        header("Location: /mypage22/estimate_list.html");
    }
    exit;

/******************************************************************************
 * 함수 영역
 *****************************************************************************/

function makeDetailParam($param) {
    $conn      = $param["conn"];
    $dao       = $param["dao"];
    $util      = $param["util"];
    $fb        = $param["fb"];
    $prefix    = $param["prefix"];
    $state_arr = $param["state_arr"];
    $tmpt_dvs  = $param["tmpt_dvs"];

    $mono_yn = ($fb[$prefix . "mono_yn"] === '0') ? 'N' : 'Y';

    $size_dvs = $fb[$prefix . "size_dvs"];
    if ($size_dvs === "manu") {
        $stan_name = "비규격";
    } else {
        $stan_name = $fb[$prefix . "size_name"];
    }

    $tot_tmpt = 0;
    $side_dvs = '-';

    $tmpt_name = makePrintTmptName($param, $tot_tmpt, $side_dvs);

    $expec_weight = $util->calcExpectWeight($conn, $dao, $param);

    $after_use_yn = 'Y';
    if (count($fb[$prefix . "chk_after"]) === 0) {
        $after_use_yn = 'N';
    }

    $ret = [];
    $ret["cate_sortcode"] = $fb[$prefix . "cate_sortcode"];

    $ret["paper_info"]      = $fb[$prefix . "paper_info"];

    if($fb[$prefix . "paper"] == "-1") {
        $ret["paper_info"]      = $fb[$prefix . "ext_paper"];
    }
    $ret["paper_info_note"] = $fb[$prefix . "ext_paper"];
    $ret["size_info"] = $fb[$prefix . "size_name"];
    $ret["beforeside_tmpt_info"]      = $fb[$prefix . "bef_tmpt_info"];
    $ret["beforeside_tmpt_info_note"] = $fb[$prefix . "ext_bef_tmpt"];
    $ret["aftside_tmpt_info"]         = $fb[$prefix . "aft_tmpt_info"];
    $ret["aftside_tmpt_info_note"]    = $fb[$prefix . "ext_aft_tmpt"];
    $ret["print_purp_info"]      = $fb[$prefix . "print_purp_info"];
    $ret["print_purp_info_note"] = $fb[$prefix . "ext_print_purp"];
    $ret["after_info"]      = $fb[$prefix . "after_info"];
    $ret["after_info_note"] = $fb[$prefix . "ext_after"];

    $ret["typ"] = $fb[$prefix . "typ"];

    $ret["work_size_wid"]    = $fb[$prefix . "work_wid_size"];
    $ret["work_size_vert"]   = $fb[$prefix . "work_vert_size"];
    $ret["cut_size_wid"]     = $fb[$prefix . "cut_wid_size"];
    $ret["cut_size_vert"]    = $fb[$prefix . "cut_vert_size"];
    $ret["tomson_size_wid"]  = $fb[$prefix . "tomson_wid_size"];
    $ret["tomson_size_vert"] = $fb[$prefix . "tomson_vert_size"];

    $ret["cut_front_wing_size_wid"]   = $fb[$prefix . "cut_front_wing_size_wid"];
    $ret["cut_front_wing_size_vert"]  = $fb[$prefix . "cut_front_wing_size_vert"];
    $ret["work_front_wing_size_wid"]  = $fb[$prefix . "work_front_wing_size_wid"];
    $ret["work_front_wing_size_vert"] = $fb[$prefix . "work_front_wing_size_vert"];
    $ret["cut_rear_wing_size_wid"]    = $fb[$prefix . "cut_rear_wing_size_wid"];
    $ret["cut_rear_wing_size_vert"]   = $fb[$prefix . "cut_rear_wing_size_vert"];
    $ret["work_rear_wing_size_wid"]   = $fb[$prefix . "work_rear_wing_size_wid"];
    $ret["work_rear_wing_size_vert"]  = $fb[$prefix . "work_rear_wing_size_vert"];
    $ret["seneca_size"]               = $fb[$prefix . "seneca_size"];

    $ret["paper_mpcode"]                = $fb[$prefix . "paper"];
    $ret["beforeside_print_mpcode"]     = $fb[$prefix . "bef_tmpt"];
    $ret["beforeside_add_print_mpcode"] = $fb[$prefix . "bef_add_tmpt"];
    $ret["aftside_print_mpcode"]        = $fb[$prefix . "aft_tmpt"];
    $ret["aftside_add_print_mpcode"]    = $fb[$prefix . "aft_add_tmpt"];
    $ret["output_mpcode"]               = $fb[$prefix . "size"];

    $ret["tot_tmpt"]     = $tot_tmpt;
    $ret["page_amt"]     = intval($fb[$prefix . "page"]);
    $ret["expec_weight"] = $expec_weight;
    $ret["after_use_yn"] = $after_use_yn;
    $ret["side_dvs"]     = $side_dvs;

    return $ret;
}

/**
 * @brief 추가 후공정 검색결과 테이블 입력 정보배열 생성
 *
 * @param $rs = 검색결과
 * @param $info_arr = 후공정 설명정보 배열
 * @param $esti_seqno = 주문공통_일련번호
 *
 * @return 생성된 배열
 */
function makeAfterAddParam($rs, $info_arr, $detail_dvs_num) {
    $ret = array();

    $i = 0;
    while ($rs && !$rs->EOF) {
        $name = $rs->fields["after_name"];
        $depth1 = $rs->fields["depth1"];
        $depth2 = $rs->fields["depth2"];
        $depth3 = $rs->fields["depth3"];
        $mpcode = $rs->fields["mpcode"];

        $info = $info_arr[$mpcode];

        $ret[$i]["esti_detail_dvs_num"] = $detail_dvs_num;
        $ret[$i]["mpcode"]     = $mpcode;
        $ret[$i]["after_name"] = $name;
        $ret[$i]["depth1"]     = $depth1;
        $ret[$i]["depth2"]     = $depth2;
        $ret[$i]["depth3"]     = $depth3;
        $ret[$i]["detail"]     = $info["detail"];
        $ret[$i++]["info"]     = $info["info"];

        $rs->MoveNext();
    }

    return $ret;
}

/**
 * @brief 인쇄_도수_이름 생성
 *
 * @detail 형식은 다음과 같다
 * ex) 표지:양면/8, 표지:전면/4/후면/4, 내지1:전면/4/후면/4
 *
 * @param $param     = db 커넥션, dao, fb
 * @param &$tot_tmpt = 총도수 반환용 변수
 * @param &$side_dvs = 단/양면 구분값 반환용 변수
 *
 * @return 인쇄_도수_이름
 */
function makePrintTmptName($param, &$tot_tmpt, &$side_dvs) {
    $conn   = $param["conn"];
    $dao    = $param["dao"];
    $fb     = $param["fb"];
    $prefix = $param["prefix"];

    $bef_mpcode = intval($fb[$prefix . "bef_tmpt"]);
    $aft_mpcode = intval($fb[$prefix . "aft_tmpt"]);
    $bef_add_mpcode = intval($fb[$prefix . "bef_add_tmpt"]);
    $aft_add_mpcode = intval($fb[$prefix . "aft_add_tmpt"]);

    $bef_form = "전면 %s + %s";
    $aft_form = "후면 %s + %s";
    $bef_add_form = "전면추가 %s + %s";
    $aft_add_form = "후면추가 %s + %s";

    $bef = '';
    $aft = '';
    $bef_add = '';
    $aft_add = '';

    $print_mpcode = $dao->arr2paramStr($conn, ["bef" => $bef_mpcode,
                                               "aft" => $aft_mpcode,
                                               "bef_add" => $bef_add_mpcode,
                                               "aft_add" => $aft_add_mpcode]);
    $rs = $dao->selectPrintTmptInfo($conn, $print_mpcode);

    $bef_chk = false;
    $aft_chk = false;

    while ($rs && !$rs->EOF) {
        $side_dvs = $rs->fields["side_dvs"];
        $add_tmpt = $rs->fields["add_tmpt"];
        if (empty($add_tmpt)) {
            $add_tmpt = '0';
        }

        $tot_tmpt += intval($rs->fields["tot_tmpt"]);

        if ($side_dvs === "전면") {
            $tmpt = $rs->fields["beforeside_tmpt"];

            if (intval($tmpt) > 0 || intval($add_tmpt) > 0) {
                $bef_chk = true;
            }

            $bef = sprintf($bef_form, $tmpt, $add_tmpt);
        } else if ($side_dvs === "후면") {
            $tmpt = $rs->fields["aftside_tmpt"];

            if (intval($tmpt) > 0 || intval($add_tmpt) > 0) {
                $aft_chk = true;
            }

            $aft = sprintf($aft_form, $tmpt, $add_tmpt);
        } else if ($side_dvs === "전면추가") {
            $tmpt = $rs->fields["beforeside_tmpt"];

            if (intval($tmpt) > 0 || intval($add_tmpt) > 0) {
                $bef_chk = true;
            }

            $bef_add = sprintf($bef_add_form, $tmpt, $add_tmpt);
        } else if ($side_dvs === "후면추가") {
            $tmpt = $rs->fields["aftside_tmpt"];

            if (intval($tmpt) > 0 || intval($add_tmpt) > 0) {
                $aft_chk = true;
            }

            $aft_add = sprintf($aft_add_form, $tmpt, $add_tmpt);
        }

        $rs->MoveNext();
    }

    if ($bef_mpcode < 0) {
        $bef_chk = true;

        $bef = sprintf($bef_form, 'X', 'X');
    }

    if ($aft_mpcode < 0) {
        $aft_chk = true;

        $aft = sprintf($aft_form, 'X', 'X');
    }

    if ($bef_chk || $aft_chk) {
        $side_dvs = "단면";
    }

    if ($bef_chk && $aft_chk) {
        $side_dvs = "양면";
    }

    $ret = $bef;
    if (!empty($bef_add)) {
        $ret .= " / " . $bef_add;
    }
    $ret .= " / " . $aft;
    if (!empty($aft_add)) {
        $ret .= " / " . $aft_add;
    }

    return $ret;
}

/**
 * @brief 추가 옵션 검색결과 맵핑코드에 맞춰서 배열생성
 *
 * @param $rs = 검색결과
 *
 * @return 생성된 배열
 */
function makeOptAddInfoArr($rs) {
    $ret = [];

    while ($rs && !$rs->EOF) {
        $name = $rs->fields["opt_name"];
        $depth1 = $rs->fields["depth1"];
        $depth2 = $rs->fields["depth2"];
        $depth3 = $rs->fields["depth3"];
        $mpcode = $rs->fields["mpcode"];

        $ret[$mpcode]["name"] = $name;
        $ret[$mpcode]["depth1"] = $depth1;
        $ret[$mpcode]["depth2"] = $depth2;
        $ret[$mpcode]["depth3"] = $depth3;

        $rs->MoveNext();
    }

    return $ret;
}

function removeEmptyEstiFile($conn, $dao, $member_seqno) {
    $base_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE;

    $rs = $dao->selectEstiFileEmpty($conn, ["member_seqno" => $member_seqno]);
    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        if (!empty($fields["file_path"])
                && !empty($fields["save_file_name"])) {
            $path = $base_path
                    . $fields["file_path"]
                    . $fields["save_file_name"];

            @unlink($path);
        }


        $dao->deleteEstiFile(
            $conn,
            ["esti_file_seqno" => $fields["esti_file_seqno"]]
        );

        $rs->MoveNext();
    }
}

function makeEstiNum($conn, $cartDao, $dao, $cate_sortcode) {
    $sell_site     = $_SESSION["sell_site"];
    $cate_sortcode = substr($cate_sortcode, 0, 3);
    $cate_nick     = $cartDao->selectCateNick($conn, $cate_sortcode);
    $last_num      = $dao->selectEstiLastNum($conn);
    $last_num      = str_pad($last_num, 5, '0', STR_PAD_LEFT);

    $form = "%s%s%s%s";
    $form = sprintf($form, $sell_site
                         , date("ymd")
                         , $cate_nick
                         , $last_num);
    return $form;
}
?>

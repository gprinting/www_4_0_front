<?php
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/DeliveryGroupDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");

if (!$is_login) {
    echo "{}";
    exit;
}

$session = $fb->getSession();
$fb      = $fb->getForm();

$member_seqno = $session["org_member_seqno"];

$dlvr_num    = $fb["dlvr_num"];
$order_seqno = $fb["order_seqno"];
$dvs         = $fb["dvs"];

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$dao  = new DeliveryGroupDAO();
$util = new FrontCommonUtil();

$success = '1';
$msg = '';

//$conn->debug = 1;
$conn->StartTrans();

$param = [];
if ($dvs === 'e') {
    // 묶음 해제
    // 주문이 한 개일 때 '-' 클릭시 order_common쪽 그룹만 해제
    $order_dlvr_cnt = $dao->selectOrderDlvrCount($conn, $dlvr_num);
    if ($order_dlvr_cnt === 1) {
        $param["order_common_seqno"] = $order_seqno;
        $param["bun_yn"] = 'N';
        $rs = $dao->updateOrderCommon($conn, $param);

        if ($conn->HasFailedTrans() || $rs === false) {
            $success = "-1";
            $msg = "묶음 해제에 실패했습니다.";
            goto ERR;
        }

        goto END;
    }

    /*
       --- 묶음 해제시 수정값
       * order_dlvr
         - bun_dlvr_order_num
         - dlvr_way -> '01'
         - dlvr_sum_way -> '02'
         - dlvr_price -> 착불택배니까 0원으로 처리
           = 업데이트 치려는 열에 배송비가 포함되어 있으면 별도처리(선불택배류)
             + 다른 남아있는 주문에 dlvr_price 업데이트
             + order_common -> pay_price, depo_price값 수정이전
         - 
     */
    //#1 배송비 있는지 검색
    unset($param);
    $param["bun_dlvr_order_num"] = $dlvr_num;
    $param["order_common_seqno"] = $order_seqno;
    $dlvr_price = $dao->selectOrderDlvrPrice($conn, $param);

    //#2 묶음 제외할 주문배송정보의 묶음배송주문번호 수정
    $param["bun_dlvr_order_num"]    = $util->makeBunDlvrOrderNum();
    $param["ex_bun_dlvr_order_num"] = $dlvr_num;
    $param["dlvr_way"]              = '01';
    $param["dlvr_sum_way"]          = '02';
    $param["dlvr_price"]            = '0';
    $param["order_common_seqno"]    = $order_seqno;
    $rs = $dao->updateOrderDlvr($conn, $param);

    if ($conn->HasFailedTrans() || $rs === false) {
        $success = "-1";
        $msg = "주문 배송 정보 수정에 실패했습니다.";
        goto ERR;
    }

    //#3 만약 제외한 주문이 이미 배송비를 지불한 상태라면 배송비 처리
    if ($dlvr_price > 0) {
        $param["bun_dlvr_order_num"] = $dlvr_num;
        $ex_info = $dao->selectExOrderDlvr($conn, $param);

        $ex_dlvr_seqno  = $ex_info["order_dlvr_seqno"];
        $ex_order_seqno = $ex_info["order_common_seqno"];

        if (!empty($ex_dlvr_seqno)) {
            unset($param);
            $param["dlvr_price"]       = $dlvr_price;
            $param["order_dlvr_seqno"] = $ex_dlvr_seqno;

            $rs = $dao->updateOrderDlvrPrice($conn, $param);

            if ($conn->HasFailedTrans() || $rs === false) {
                $success = "-1";
                $msg = "A : 배송비 수정에 실패했습니다.";
                goto ERR;
            }

            // 제외하는 주문에 입금/결제액에서 배송비 제외
            unset($param);
            $param["depo_price"] = $dlvr_price * -1;
            $param["pay_price"]  = $dlvr_price * -1;
            $param["order_common_seqno"] = $order_seqno;
            $param["bun_yn"] = 'N';
            $rs = $dao->updateOrderCommon($conn, $param);

            if ($conn->HasFailedTrans() || $rs === false) {
                $success = "-1";
                $msg = "B : 배송비 수정에 실패했습니다.";
                goto ERR;
            }

            // 다른 주문에 입금/결제액에서 배송비 추가
            unset($param["bun_yn"]);
            $param["depo_price"] *= -1;
            $param["pay_price"]  *= -1;
            $param["order_common_seqno"] = $ex_order_seqno;
            $rs = $dao->updateOrderCommon($conn, $param);

            if ($conn->HasFailedTrans() || $rs === false) {
                $success = "-1";
                $msg = "C : 배송비 수정에 실패했습니다.";
                goto ERR;
            }
        }
    } else {
        unset($param);
        $param["order_common_seqno"] = $order_seqno;
        $param["bun_yn"] = 'N';
        $rs = $dao->updateOrderCommon($conn, $param);

        if ($conn->HasFailedTrans() || $rs === false) {
            $success = "-1";
            $msg = "묶음 해제에 실패했습니다.";
            goto ERR;
        }
    }

} else {
    // 묶음 추가
    /*
       --- 묶음 추가시 수정값
       * order_dlvr
         - bun_dlvr_order_num
     */
    //#1 더할 주문의 배송정보 검색
    $param["order_common_seqno"] = $order_seqno;
    $param["member_seqno"]       = $member_seqno;
    $info = $dao->selectOrderDlvrInfo($conn, $param);

    //#2 배송그룹정보 검색
    $param["expec_release_date"] = explode(' ', $info["expec_release_date"])[0];
    $rs = $dao->selectOrderDlvrGroupInfo($conn, $param);

    //#3 #2에서 검색된 배송그룹 조건에 맞는지 확인, 안맞으면 종료
    $group_info = null;
    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        if ($fields["name"] === $info["name"]
                && $fields["recei"]        === $info["recei"]
                && $fields["zipcode"]      === $info["zipcode"]
                && $fields["addr"]         === $info["addr"]
                && $fields["addr_detail"]  === $info["addr_detail"]
                && $fields["dlvr_way"]     === $info["dlvr_way"]
                && $fields["dlvr_sum_way"] === $info["dlvr_sum_way"]
                && $fields["invo_cpn"]     === $info["invo_cpn"]) {

            $sum_weight = ceil(doubleval($info["expec_weight"]))
                          + ceil(doubleval($fields["sum_weight"]));
            if ($sum_weight <= 12) {
                $group_info = $fields;
                break;
            }
        }

        $rs->MoveNext();
    }

    if (empty($group_info)) {
        unset($param);
        $param["order_common_seqno"] = $order_seqno;
        $param["bun_yn"] = 'Y';
        $rs = $dao->updateOrderCommon($conn, $param);

        if ($conn->HasFailedTrans() || $rs === false) {
            $success = "-1";
            $msg = "묶음 설정에 실패했습니다.";
            goto ERR;
        }

        goto END;
    }

    //#4 추가할 수 있는 배송그룹이 있는 경우 처리
    unset($param);
    $dlvr_price = intval($info["dlvr_price"]);
    if ($dlvr_price > 0) {
        // 배송비가 있는경우 환불 테이블에 입력하고
        $param["member_seqno"] = $member_seqno;
        $param["order_num"]    = $info["order_num"];
        $param["dlvr_price"]   = $dlvr_price;
        $rs = $dao->insertOrderDlvrPriceRefund($conn, $param);

        if ($conn->HasFailedTrans() || $rs === false) {
            $success = "-1";
            $msg = "배송비 환불내역 입력에 실패했습니다.";
            goto ERR;
        }

        // 주문배송 테이블 배송비 0원처리
        unset($param);
        $param["dlvr_price"]       = '0';
        $param["order_dlvr_seqno"] = $info["order_dlvr_seqno"];
        $rs = $dao->updateOrderDlvrPrice($conn, $param);

        if ($conn->HasFailedTrans() || $rs === false) {
            $success = "-1";
            $msg = "D : 배송비 수정에 실패했습니다.";
            goto ERR;
        }

        // 주문공통 테이블에서 지불한 배송비 내역 차감
        unset($param);
        $param["depo_price"] = $dlvr_price * -1;
        $param["pay_price"]  = $dlvr_price * -1;
        $param["order_common_seqno"] = $order_seqno;
        //-- 이어짐 -->
    }
    // <-- 이어짐 --
    //#4 배송비가 없으면 그룹화
    // 배송비가 있으면 배송비 파라미터까지 처리, 없으면 그룹값만
    $param["bun_yn"] = 'Y';
    $rs = $dao->updateOrderCommon($conn, $param);

    if ($conn->HasFailedTrans() || $rs === false) {
        $success = "-1";
        $msg = "E : 배송비 수정에 실패했습니다.";
        goto ERR;
    }

    //#5 주문배송 테이블 정보 수정
    $param["bun_dlvr_order_num"]    = $group_info["bun_dlvr_order_num"];
    $param["ex_bun_dlvr_order_num"] = $info["bun_dlvr_order_num"];
    $param["dlvr_way"]              = $group_info["dlvr_way"];
    $param["dlvr_sum_way"]          = $group_info["dlvr_sum_way"];
    $param["dlvr_price"]            = '0';
    $param["order_common_seqno"]    = $order_seqno;
    $rs = $dao->updateOrderDlvr($conn, $param);

    if ($conn->HasFailedTrans() || $rs === false) {
        $success = "-1";
        $msg = "주문 배송 정보 수정에 실패했습니다.";
        goto ERR;
    }
}

goto END;
exit;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();
END:
    $conn->CompleteTrans();
    $conn->Close();

    echo sprintf("{\"success\" : %s, \"msg\" : \"%s\"}", $success, $msg);
    exit;

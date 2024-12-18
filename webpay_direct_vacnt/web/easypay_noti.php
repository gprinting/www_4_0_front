<?php
/* -------------------------------------------------------------------------- */
/* ::: 노티수신                                                               */
/* -------------------------------------------------------------------------- */
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new FrontCommonDAO();
$util = new CommonUtil();

//$fd = fopen("/home/dprinting/front/noti/noti_" . date("YmdHis"), "w");

$result_msg = "";

$r_res_cd         = $_POST[ "res_cd"         ];  // 응답코드
$r_res_msg        = $_POST[ "res_msg"        ];  // 응답 메시지
$r_cno            = $_POST[ "cno"            ];  // PG거래번호
$r_memb_id        = $_POST[ "memb_id"        ];  // 가맹점 ID
$r_amount         = $_POST[ "amount"         ];  // 총 결제금액
$r_order_no       = $_POST[ "order_no"       ];  // 주문번호
$r_noti_type      = $_POST[ "noti_type"      ];  // 노티구분
$r_auth_no        = $_POST[ "auth_no"        ];  // 승인번호
$r_tran_date      = $_POST[ "tran_date"      ];  // 승인일시
$r_card_no        = $_POST[ "card_no"        ];  // 카드번호
$r_issuer_cd      = $_POST[ "issuer_cd"      ];  // 발급사코드
$r_issuer_nm      = $_POST[ "issuer_nm"      ];  // 발급사명
$r_acquirer_cd    = $_POST[ "acquirer_cd"    ];  // 매입사코드
$r_acquirer_nm    = $_POST[ "acquirer_nm"    ];  // 매입사명
$r_install_period = $_POST[ "install_period" ];  // 할부개월
$r_noint          = $_POST[ "noint"          ];  // 무이자여부
$r_bank_cd        = $_POST[ "bank_cd"        ];  // 은행코드
$r_bank_nm        = $_POST[ "bank_nm"        ];  // 은행명
$r_account_no     = $_POST[ "account_no"     ];  // 계좌번호
$r_deposit_nm     = $_POST[ "deposit_nm"     ];  // 입금자명
$r_expire_date    = $_POST[ "expire_date"    ];  // 계좌사용만료일
$r_cash_res_cd    = $_POST[ "cash_res_cd"    ];  // 현금영수증 결과코드
$r_cash_res_msg   = $_POST[ "cash_res_msg"   ];  // 현금영수증 결과메시지
$r_cash_auth_no   = $_POST[ "cash_auth_no"   ];  // 현금영수증 승인번호
$r_cash_tran_date = $_POST[ "cash_tran_date" ];  // 현금영수증 승인일시
$r_cp_cd          = $_POST[ "cp_cd"          ];  // 포인트사
$r_used_pnt       = $_POST[ "used_pnt"       ];  // 사용포인트
$r_remain_pnt     = $_POST[ "remain_pnt"     ];  // 잔여한도
$r_pay_pnt        = $_POST[ "pay_pnt"        ];  // 할인/발생포인트 
$r_accrue_pnt     = $_POST[ "accrue_pnt"     ];  // 누적포인트
$r_escrow_yn      = $_POST[ "escrow_yn"      ];  // 에스크로 사용유무
$r_canc_date      = $_POST[ "canc_date"      ];  // 취소일시
$r_canc_acq_date  = $_POST[ "canc_acq_date"  ];  // 매입취소일시
$r_refund_date    = $_POST[ "refund_date"    ];  // 환불예정일시
$r_pay_type       = $_POST[ "pay_type"       ];  // 결제수단
$r_auth_cno       = $_POST[ "auth_cno"       ];  // 인증거래번호
$r_tlf_sno        = $_POST[ "tlf_sno"        ];  // 채번거래번호
$r_account_type   = $_POST[ "account_type"   ];  // 채번계좌 타입 US AN 1 (V-일반형, F-고정형)

if ( $r_res_cd == "0000" ) {
    /* ---------------------------------------------------------------------- */
    /* ::: 가맹점 DB 처리                                                     */
    /* ---------------------------------------------------------------------- */
    /* DB처리 성공 시 : res_cd=0000, 실패 시 : res_cd=5001                    */
    /* ---------------------------------------------------------------------- */
    //../inc/
    $param = [];

    $r_bank_nm = iconv("euc-kr", "utf-8", $r_bank_nm);
    $query =" SELECT member_seqno FROM virt_ba_admin
     WHERE bank_name='$r_bank_nm'
       AND ba_num='$r_account_no'";
    $rs = $conn->Execute($query);
    $member_seqno = $rs->fields["member_seqno"];

    $conn->StartTrans();
    $prepay_price_money = $dao->selectMemberPrepayLock($conn, $member_seqno);

    unset($param);
    $param["prepay_price_money"] = $r_amount;
    $param["prepay_price_card"] = 0;
    $param["member_seqno"] = $member_seqno;
    $ret = $dao->updateMemberPrepay($conn, $param);

    if ($conn->HasFailedTrans() === true) {
        $result_msg = "res_cd=0000" . chr(31) . "res_msg=FAIL";
        $conn->FailTrans();
        $conn->RollbackTrans();
        goto END;
    }

    unset($param);
    $param["member_seqno"] = $member_seqno;
    $param["order_num"]    = '';
    $param["exist_prepay"] = $prepay_price_money;

    $param["dvs"]           = "입금증가";
    $param["sell_price"]    = '0';
    $param["sale_price"]    = '0';
    $param["pay_price"]     = '0';
    $param["depo_price"]    = $r_amount;
    $param["input_typ"]     = $util->selectInputType("가상계좌");
    $param["prepay_bal"]    = intval($prepay_price_money) + intval($r_amount);
    $param["state"]         = '';
    $param["deal_num"]      = $r_cno;
    $param["order_cancel_yn"] = 'N';
    $param["pay_year"]      = date('Y');
    $param["pay_mon"]       = date('m');
    $param["cont"]          = "선입금 가상계좌충전";
    $param["prepay_use_yn"] = 'N';

    $ret = $dao->insertMemberPayHistory($conn, $param);
    $conn->CompleteTrans();

    if ($conn->HasFailedTrans() === true) {
        $result_msg = "res_cd=0000" . chr(31) . "res_msg=FAIL";
        $conn->FailTrans();
        $conn->RollbackTrans();
        goto END;
    }

    $result_msg = "res_cd=0000" . chr(31) . "res_msg=SUCCESS";
} else {
    $result_msg = "res_cd=0000" . chr(31) . "res_msg=FAIL";
}
/* -------------------------------------------------------------------------- */
/* ::: 노티 처리결과 처리                                                     */
/* -------------------------------------------------------------------------- */


END:
    echo $result_msg;
    $conn->Close();
    //fclose($fd);

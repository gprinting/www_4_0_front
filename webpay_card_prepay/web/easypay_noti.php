<?php
/* -------------------------------------------------------------------------- */
/* ::: 노티수신                                                               */
/* -------------------------------------------------------------------------- */
$result_msg = "";

$r_res_cd         = $_POST[ "res_cd"         ];  // 응답코드
$r_res_msg        = $_POST[ "res_msg"        ];  // 응답 메시지
$r_cno            = $_POST[ "cno"            ];  // PG거래번호
$r_memb_id        = $_POST[ "memb_id"        ];  // 가맹점 ID
$r_amount         = $_POST[ "amount"         ];  // 총 결제금액
$r_order_no       = $_POST[ "order_no"       ];  // 주문번호
$r_noti_type      = $_POST[ "noti_type"      ];  // 노티구분 변경(20), 입금(30), 에스크로 변경(40)
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

/* -------------------------------------------------------------------------- */
/* ::: 노티수신 - 에스크로 상태변경                                           */
/* -------------------------------------------------------------------------- */
$r_escrow_yn      = $_POST[ "escrow_yn "     ];  // 에스크로유무
$r_stat_cd        = $_POST[ "stat_cd "       ];  // 변경에스크로상태코드
$r_stat_msg       = $_POST[ "stat_msg"       ];  // 변경에스크로상태메세지    

if ( $r_res_cd == "0000" )    
{
    /* ---------------------------------------------------------------------- */
    /* ::: 가맹점 DB 처리                                                     */
    /* ---------------------------------------------------------------------- */
    /* DB처리 성공 시 : res_cd=0000, 실패 시 : res_cd=5001                    */
    /* ---------------------------------------------------------------------- */
    if // DB처리 성공 시
    {
        $result_msg = "res_cd=0000" . chr(31) . "res_msg=SUCCESS";
    }
    else // DB처리 실패 시
    {  
        $result_msg = "res_cd=5001" . chr(31) . "res_msg=FAIL";
    }
}
    
/* -------------------------------------------------------------------------- */
/* ::: 노티 처리결과 처리                                                     */
/* -------------------------------------------------------------------------- */
echo $result_msg;
?>
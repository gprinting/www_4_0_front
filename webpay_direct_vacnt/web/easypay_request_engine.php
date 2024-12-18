<?
    define(INC_PATH, $_SERVER["INC"]);
    include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
    include_once(INC_PATH . "/common_dao/CommonDAO.inc");

    define("HOME_DIR", $_SERVER["DOCUMENT_ROOT"] . "/webpay_direct_vacnt");
    include(HOME_DIR . "/web/easypay_client.php");
/* -------------------------------------------------------------------------- */
/* ::: 처리구분 설정                                                          */
/* -------------------------------------------------------------------------- */
$TRAN_CD_NOR_PAYMENT    = "00101000";   // 승인(일반, 에스크로)
$TRAN_CD_NOR_MGR        = "00201000";   // 변경(일반, 에스크로)

/* -------------------------------------------------------------------------- */
/* ::: 쇼핑몰 지불 정보 설정                                                  */
/* -------------------------------------------------------------------------- */
$GW_URL                 = "testgw.easypay.co.kr";  // Gateway URL ( test )
//$GW_URL               = "gw.easypay.co.kr";      // Gateway URL ( real )
$GW_PORT                = "80";                    // 포트번호(변경불가)

/* -------------------------------------------------------------------------- */
/* ::: 지불 데이터 셋업 (업체에 맞게 수정)                                    */
/* -------------------------------------------------------------------------- */
/* ※ 주의 ※                                                                 */
/* cert_file 변수 설정                                                        */
/* - pg_cert.pem 파일이 있는 디렉토리의  절대 경로 설정                       */
/* log_dir 변수 설정                                                          */
/* - log 디렉토리 설정                                                        */
/* log_level 변수 설정                                                        */
/* - log 레벨 설정                                                            */
/* -------------------------------------------------------------------------- */
$HOME_DIR   = HOME_DIR;
$CERT_FILE  = HOME_DIR . "/cert/pg_cert.pem";
$LOG_DIR    = HOME_DIR . "/log";
$LOG_LEVEL  = "1";

$g_mall_id        = $_POST["EP_mall_id"];          // [필수]가맹점ID
$tr_cd            = $_POST["EP_tr_cd"];            // [필수]요청구분

/* -------------------------------------------------------------------------- */
/* ::: 무통장입금 정보 설정                                                   */
/* -------------------------------------------------------------------------- */
$vacct_amt         = $_POST["EP_vacct_amt"];       // [필수]무통장입금 결제금액
$vacct_txtype      = $_POST["EP_vacct_txtype"];    // [필수]무통장입금 처리종류
$bank_cd           = $_POST["EP_bank_cd"];         // [필수]은행코드
$vacct_expr_date   = $_POST["EP_expire_date"];     // [필수]입금만료일
$vacct_expr_time   = $_POST["EP_expire_time"];     // [필수]입금만료시간
$vacct_account     = $_POST["EP_vacct_account"];   // [선택]고정형 등록시 필수 // 고정식 계좌번호

/* -------------------------------------------------------------------------- */
/* ::: 현금영수증 정보 설정                                                   */
/* -------------------------------------------------------------------------- */
$cash_yn           = $_POST["EP_cash_yn"];         // [필수]현금영수증 발행여부
$cash_issue_type   = $_POST["EP_cash_issue_type"]; // [선택]현금영수증발행용도
$cash_auth_type    = $_POST["EP_cash_auth_type"];  // [선택]인증구분
$cash_auth_value   = $_POST["EP_cash_auth_value"]; // [선택]인증번호

/* -------------------------------------------------------------------------- */
/* ::: 결제 주문 정보 설정                                                    */
/* -------------------------------------------------------------------------- */
$order_no         = $_POST["EP_order_no"];         // [필수]주문번호
$user_type        = $_POST["EP_user_type"];        // [선택]사용자구분구분[1:일반,2:회원]
$memb_user_no     = $_POST["EP_memb_user_no"];     // [선택]가맹점 고객일련번호
$user_id          = $_POST["EP_user_id"];          // [선택]고객 ID
$user_nm          = $_POST["EP_user_nm"];          // [필수]고객명
$user_mail        = $_POST["EP_user_mail"];        // [필수]고객 E-mail
$user_phone1      = $_POST["EP_user_phone1"];      // [선택]가맹점 고객 전화번호
$user_phone2      = $_POST["EP_user_phone2"];      // [선택]가맹점 고객 휴대폰
$user_addr        = $_POST["EP_user_addr"];        // [선택]가맹점 고객 주소
$product_type     = $_POST["EP_product_type"];     // [선택]상품정보구분[0:실물,1:컨텐츠]
$product_nm       = $_POST["EP_product_nm"];       // [필수]상품명
$product_amt      = $_POST["EP_product_amt"];      // [필수]상품금액
$user_define1     = $_POST["EP_user_define1"];     // 가맹점 필드1
$user_define2     = $_POST["EP_user_define2"];     // 가맹점 필드2
$user_define3     = $_POST["EP_user_define3"];     // 가맹점 필드3
$user_define4     = $_POST["EP_user_define4"];     // 가맹점 필드4
$user_define5     = $_POST["EP_user_define5"];     // 가맹점 필드5
$user_define6     = $_POST["EP_user_define6"];     // 가맹점 필드6

/* -------------------------------------------------------------------------- */
/* ::: 기타정보 설정                                                          */
/* -------------------------------------------------------------------------- */
$client_ip        = $_SERVER['REMOTE_ADDR'];       // [필수]결제고객 IP
$tot_amt          = $_POST["EP_tot_amt"];          // [필수]결제 총 금액
$curr_code        = $_POST["EP_currency"];         // [필수]통화코드
$escrow_yn        = $_POST["EP_escrow_yn"];        // [필수]에스크로여부
$complex_yn       = $_POST["EP_complex_yn"];       // [필수]복합결제여부

/* -------------------------------------------------------------------------- */
/* ::: 변경관리 정보 설정                                                     */
/* -------------------------------------------------------------------------- */
$mgr_txtype       = $_POST["mgr_txtype"];          // [필수]거래구분
$mgr_subtype      = $_POST["mgr_subtype"];         // [선택]변경세부구분
$org_cno          = $_POST["org_cno"];             // [필수]원거래고유번호
$mgr_amt          = $_POST["mgr_amt"];             // [선택]부분취소 금액
$mgr_rem_amt      = $_POST["mgr_rem_amt"];         // [선택]부분취소 잔액
$mgr_tax_flg      = $_POST["mgr_tax_flg"];         // [필수]과세구분 플래그(TG01:복합과세 변경거래)
$mgr_tax_amt      = $_POST["mgr_tax_amt"];         // [필수]과세부분취소 금액(복합과세 변경 시 필수)
$mgr_free_amt     = $_POST["mgr_free_amt"];        // [필수]비과세부분취소 금액(복합과세 변경 시 필수)
$mgr_vat_amt      = $_POST["mgr_vat_amt"];         // [필수]부가세 부분취소금액(복합과세 변경 시 필수)
$mgr_bank_cd      = $_POST["mgr_bank_cd"];         // [선택]환불계좌 은행코드
$mgr_account      = $_POST["mgr_account"];         // [선택]환불계좌 번호
$mgr_depositor    = $_POST["mgr_depositor"];       // [선택]환불계좌 예금주명
$mgr_socno        = $_POST["mgr_socno"];           // [선택]환불계좌 주민번호
$mgr_telno        = $_POST["mgr_telno"];           // [선택]환불고객 연락처
$deli_cd          = $_POST["deli_cd"];             // [선택]배송구분[자가:DE01,택배:DE02]
$deli_corp_cd     = $_POST["deli_corp_cd"];        // [선택]택배사코드
$deli_invoice     = $_POST["deli_invoice"];        // [선택]운송장 번호
$deli_rcv_nm      = $_POST["deli_rcv_nm"];         // [선택]수령인 이름
$deli_rcv_tel     = $_POST["deli_rcv_tel"];        // [선택]수령인 연락처
$req_id           = $_POST["req_id"];              // [선택]가맹점 관리자 로그인 아이디
$mgr_msg          = $_POST["mgr_msg"];             // [선택]변경 사유


/* -------------------------------------------------------------------------- */
/* ::: 결제 결과                                                              */
/* -------------------------------------------------------------------------- */
$res_cd           = "";
$res_msg          = "";
$r_order_no       = "";
$r_complex_yn     = "";
$r_msg_type       = "";     //거래구분
$r_noti_type        = "";     //노티구분
$r_cno            = "";     //PG거래번호
$r_amount         = "";     //총 결제금액
$r_auth_no        = "";     //승인번호
$r_tran_date      = "";     //거래일시
$r_pnt_auth_no    = "";     //포인트 승인 번호
$r_pnt_tran_date  = "";     //포인트 승인 일시
$r_cpon_auth_no   = "";     //쿠폰 승인 번호
$r_cpon_tran_date = "";     //쿠폰 승인 일시
$r_card_no        = "";     //카드번호
$r_issuer_cd      = "";     //발급사코드
$r_issuer_nm      = "";     //발급사명
$r_acquirer_cd    = "";     //매입사코드
$r_acquirer_nm    = "";     //매입사명
$r_install_period = "";     //할부개월
$r_noint          = "";     //무이자여부
$r_bank_cd        = "";     //은행코드
$r_bank_nm        = "";     //은행명
$r_account_no     = "";     //계좌번호
$r_deposit_nm     = "";     //입금자명
$r_expire_date    = "";     //계좌사용 만료일
$r_cash_res_cd    = "";     //현금영수증 결과코드
$r_cash_res_msg   = "";     //현금영수증 결과메세지
$r_cash_auth_no   = "";     //현금영수증 승인번호
$r_cash_tran_date = "";     //현금영수증 승인일시
$r_auth_id        = "";     //PhoneID
$r_billid         = "";     //인증번호
$r_mobile_no      = "";     //휴대폰번호
$r_ars_no         = "";     //ARS 전화번호
$r_cp_cd          = "";     //포인트사
$r_used_pnt       = "";     //사용포인트
$r_remain_pnt     = "";     //잔여한도
$r_pay_pnt        = "";     //할인/발생포인트
$r_accrue_pnt     = "";     //누적포인트
$r_remain_cpon    = "";     //쿠폰잔액
$r_used_cpon      = "";     //쿠폰 사용금액
$r_mall_nm        = "";     //제휴사명칭
$r_escrow_yn        = "";     //에스크로 사용유무
$r_canc_acq_data  = "";     //매입취소일시
$r_canc_date      = "";     //취소일시
$r_refund_date    = "";     //환불예정일시

/* -------------------------------------------------------------------------- */
/* ::: EasyPayClient 인스턴스 생성 [변경불가 !!].                             */
/* -------------------------------------------------------------------------- */
$easyPay = new EasyPay_Client;         // 전문처리용 Class (library에서 정의됨)
$easyPay->clearup_msg();

$easyPay->set_home_dir($HOME_DIR);
$easyPay->set_gw_url($GW_URL);
$easyPay->set_gw_port($GW_PORT);
$easyPay->set_log_dir($LOG_DIR);
$easyPay->set_log_level($LOG_LEVEL);
$easyPay->set_cert_file($CERT_FILE);
    
if( $TRAN_CD_NOR_PAYMENT == $tr_cd )
{

    /* ---------------------------------------------------------------------- */
    /* ::: 승인요청 전문 설정                                                 */
    /* ---------------------------------------------------------------------- */
    // 결제 주문 전문
    $order_data = $easyPay->set_easypay_item("order_data");
    $easyPay->set_easypay_deli_us( $order_data, "order_no"      , $order_no      );
    $easyPay->set_easypay_deli_us( $order_data, "user_type"     , $user_type     );
    $easyPay->set_easypay_deli_us( $order_data, "memb_user_no"  , $mgr_txtype    );
    $easyPay->set_easypay_deli_us( $order_data, "user_id"       , $user_id       );
    $easyPay->set_easypay_deli_us( $order_data, "user_nm"       , $user_nm       );
    $easyPay->set_easypay_deli_us( $order_data, "user_mail"     , $user_mail     );
    $easyPay->set_easypay_deli_us( $order_data, "user_phone1"   , $user_phone1   );
    $easyPay->set_easypay_deli_us( $order_data, "user_phone2"   , $user_phone2   );
    $easyPay->set_easypay_deli_us( $order_data, "user_addr"     , $user_addr     );
    $easyPay->set_easypay_deli_us( $order_data, "product_type"  , $product_type  );
    $easyPay->set_easypay_deli_us( $order_data, "product_nm"    , $product_nm    );
    $easyPay->set_easypay_deli_us( $order_data, "product_amt"   , $product_amt   );
    
    $easyPay->set_easypay_deli_us( $order_data, "user_define1"  , $user_define1  );
    $easyPay->set_easypay_deli_us( $order_data, "user_define2"  , $user_define2  );
    $easyPay->set_easypay_deli_us( $order_data, "user_define3"  , $user_define3  );
    $easyPay->set_easypay_deli_us( $order_data, "user_define4"  , $user_define4  );
    $easyPay->set_easypay_deli_us( $order_data, "user_define5"  , $user_define5  );
    $easyPay->set_easypay_deli_us( $order_data, "user_define6"  , $user_define6  );
    
    // 결제 승인 전문
    $pay_data = $easyPay->set_easypay_item("pay_data");
    $comm_data = $easyPay->set_easypay_item("common");
    $easyPay->set_easypay_deli_us( $comm_data, "tot_amt"       , $tot_amt      );
    $easyPay->set_easypay_deli_us( $comm_data, "currency"      , $curr_code    );
    $easyPay->set_easypay_deli_us( $comm_data, "client_ip"     , $client_ip    );
    $easyPay->set_easypay_deli_us( $comm_data, "escrow_yn"     , $escrow_yn    );
    $easyPay->set_easypay_deli_us( $comm_data, "complex_yn"    , $complex_yn   );
    $easyPay->set_easypay_deli_us( $comm_data, "cli_ver"       , "N8"          );
    $easyPay->set_easypay_deli_rs( $pay_data, $comm_data);

    $vacct_data = $easyPay->set_easypay_item("vacct");
    $easyPay->set_easypay_deli_us( $vacct_data, "vacct_txtype"    , $vacct_txtype      );
    $easyPay->set_easypay_deli_us( $vacct_data, "vacct_amt"       , $vacct_amt         );
    $easyPay->set_easypay_deli_us( $vacct_data, "bank_cd"         , $bank_cd           );
    $easyPay->set_easypay_deli_us( $vacct_data, "expire_date"     , $vacct_expr_date   );
    $easyPay->set_easypay_deli_us( $vacct_data, "expire_time"     , $vacct_expr_time   );
    $easyPay->set_easypay_deli_us( $vacct_data, "vacct_account"   , $vacct_account     );
    $easyPay->set_easypay_deli_us( $vacct_data, "cash_yn"         , $cash_yn           );
    
    // 현금영수증
    if( $cash_yn == "1" ) {
        $easyPay->set_easypay_deli_us( $vacct_data, "cash_issue_type" , $cash_issue_type  );
        $easyPay->set_easypay_deli_us( $vacct_data, "cash_auth_type"  , $cash_auth_type   );
        $easyPay->set_easypay_deli_us( $vacct_data, "cash_auth_value" , $cash_auth_value  );
    }
    $easyPay->set_easypay_deli_rs( $pay_data, $vacct_data );

}
else if( $TRAN_CD_NOR_MGR == $tr_cd )
{
    /* ---------------------------------------------------------------------- */
    /* ::: 변경관리 요청                                                      */
    /* ---------------------------------------------------------------------- */
    $mgr_data = $easyPay->set_easypay_item("mgr_data");
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_txtype"      , $mgr_txtype       );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_subtype"     , $mgr_subtype      );
    $easyPay->set_easypay_deli_us( $mgr_data, "org_cno"         , $org_cno          );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_amt"         , $mgr_amt          );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_rem_amt"     , $mgr_rem_amt      );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_tax_flg"     , $mgr_tax_flg      );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_tax_amt"     , $mgr_tax_amt      );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_free_amt"    , $mgr_free_amt     );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_vat_amt"     , $mgr_vat_amt      );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_bank_cd"     , $mgr_bank_cd      );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_account"     , $mgr_account      );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_depositor"   , $mgr_depositor    );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_socno"       , $mgr_socno        );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_telno"       , $mgr_telno        );
    $easyPay->set_easypay_deli_us( $mgr_data, "deli_corp_cd"    , $deli_corp_cd     );
    $easyPay->set_easypay_deli_us( $mgr_data, "deli_invoice"    , $deli_invoice     );
    $easyPay->set_easypay_deli_us( $mgr_data, "deli_rcv_nm"     , $deli_rcv_nm      );
    $easyPay->set_easypay_deli_us( $mgr_data, "deli_rcv_tel"    , $deli_rcv_tel     );
    $easyPay->set_easypay_deli_us( $mgr_data, "req_ip"          , $client_ip        );
    $easyPay->set_easypay_deli_us( $mgr_data, "req_id"          , $req_id           );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_msg"         , $mgr_msg          );

}

/* -------------------------------------------------------------------------- */
/* ::: 실행                                                                   */
/* -------------------------------------------------------------------------- */
$opt = "option value";
$easyPay->easypay_exec($g_mall_id, $tr_cd, $order_no, $client_ip, $opt);
$res_cd  = $easyPay->_easypay_resdata["res_cd"];    // 응답코드
$res_msg = $easyPay->_easypay_resdata["res_msg"];   // 응답메시지

/* -------------------------------------------------------------------------- */
/* ::: 결과 처리                                                              */
/* -------------------------------------------------------------------------- */
$r_cno             = $easyPay->_easypay_resdata[ "cno"             ];    //PG거래번호
$r_amount          = $easyPay->_easypay_resdata[ "amount"          ];    //총 결제금액
$r_tran_date       = $easyPay->_easypay_resdata[ "tran_date"       ];    //승인일시
$r_bank_cd         = $easyPay->_easypay_resdata[ "bank_cd"         ];    //은행코드
$r_bank_nm         = $easyPay->_easypay_resdata[ "bank_nm"         ];    //은행명
$r_account_no      = $easyPay->_easypay_resdata[ "account_no"      ];    //계좌번호
$r_deposit_nm      = $easyPay->_easypay_resdata[ "deposit_nm"      ];    //입금자명
$r_expire_date     = $easyPay->_easypay_resdata[ "expire_date"     ];    //계좌사용만료일
$r_cash_res_cd     = $easyPay->_easypay_resdata[ "cash_res_cd"     ];    //현금영수증 결과코드
$r_cash_res_msg    = $easyPay->_easypay_resdata[ "cash_res_msg"    ];    //현금영수증 결과메세지
$r_cash_auth_no    = $easyPay->_easypay_resdata[ "cash_auth_no"    ];    //현금영수증 승인번호
$r_cash_tran_date  = $easyPay->_easypay_resdata[ "cash_tran_date"  ];    //현금영수증 승인일시
$r_escrow_yn       = $easyPay->_easypay_resdata[ "escrow_yn"       ];    //에스크로 사용유무
$r_complex_yn      = $easyPay->_easypay_resdata[ "complex_yn"      ];    //복합결제 유무
$r_canc_date       = $easyPay->_easypay_resdata[ "canc_date"       ];    //취소일시
$r_refund_date     = $easyPay->_easypay_resdata[ "refund_date"     ];    //환불예정일시

/* -------------------------------------------------------------------------- */
/* ::: 가맹점 DB 처리                                                         */
/* -------------------------------------------------------------------------- */
/* 응답코드(res_cd)가 "0000" 이면 정상승인 입니다.                            */
/* r_amount가 주문DB의 금액과 다를 시 반드시 취소 요청을 하시기 바랍니다.     */
/* DB 처리 실패 시 취소 처리를 해주시기 바랍니다.                             */
/* -------------------------------------------------------------------------- */
echo $res_cd;
if ( $res_cd == "0000" )
{
    $temp = [];
    $temp["col"]["ba_num"] = $r_account_no;
    $temp["col"]["use_yn"] = 'N';
    $temp["col"]["bank_name"] = $_POST["bank_name"];
    $temp["table"] = "virt_ba_admin";

    $connectionPool = new ConnectionPool();
    $conn = $connectionPool->getPooledConnection();
    $conn->debug = 1;
    $dao = new CommonDAO();
    $ret = $dao->insertData($conn, $temp);
    $conn->debug = 0;
    
    if ( $ret === false )
    {
        // 승인요청이 실패 시 아래 실행
        if( $TRAN_CD_NOR_PAYMENT == $tr_cd )
        {
             $easyPay->clearup_msg();

             $tr_cd = $TRAN_CD_NOR_MGR;
             $mgr_data = $easyPay->set_easypay_item("mgr_data");
             $easyPay->set_easypay_deli_us( $mgr_data, "mgr_txtype"      , "40"   );
             $easyPay->set_easypay_deli_us( $mgr_data, "org_cno"         , $r_cno     );
             $easyPay->set_easypay_deli_us( $mgr_data, "req_ip"          , $client_ip );
             $easyPay->set_easypay_deli_us( $mgr_data, "req_id"          , "MALL_R_TRANS" );
             $easyPay->set_easypay_deli_us( $mgr_data, "mgr_msg"         , "DB 처리 실패로 망취소"  );

             $easyPay->easypay_exec($g_mall_id, $tr_cd, $order_no, $client_ip, $opt);
             $res_cd      = $easyPay->_easypay_resdata["res_cd"     ];    // 응답코드
             $res_msg     = $easyPay->_easypay_resdata["res_msg"    ];    // 응답메시지
             $r_cno       = $easyPay->_easypay_resdata["cno"        ];    // PG거래번호
             $r_canc_date = $easyPay->_easypay_resdata["canc_date"  ];    // 취소일시
        }
    }
}
?>


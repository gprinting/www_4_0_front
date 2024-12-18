<?php
    define("HOME_DIR", $_SERVER["DOCUMENT_ROOT"] . "/webpay_card");
    include(HOME_DIR . "/web/easypay_client.php");
    
    /* -------------------------------------------------------------------------- */
    /* ::: 처리구분 설정                                                          */
    /* -------------------------------------------------------------------------- */
    $TRAN_CD_NOR_PAYMENT    = "00101000";   // 승인(일반, 에스크로)
    $TRAN_CD_NOR_MGR        = "00201000";   // 변경(일반, 에스크로)

    /* -------------------------------------------------------------------------- */
    /* ::: 쇼핑몰 지불 정보 설정                                                  */
    /* -------------------------------------------------------------------------- */
    $g_gw_url    = "testpgapi.easypay.co.kr";               // Gateway URL ( test )
    //$g_gw_url               = "gw.easypay.co.kr";      // Gateway URL ( real )    
    $g_gw_port   = "80";                                           // 포트번호(변경불가) 

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
    
    $g_home_dir   = HOME_DIR;
    $g_cert_file  = HOME_DIR . "/cert/pg_cert.pem";
    $g_log_dir    = HOME_DIR . "/log";
    $g_log_level  = "1"; 
    
    $g_mall_id   = $_POST["EP_mall_id"];              // [필수]몰아이디
    
    /* -------------------------------------------------------------------------- */
    /* ::: 플러그인 응답정보 설정                                                 */
    /* -------------------------------------------------------------------------- */
    $tr_cd            = $_POST["EP_tr_cd"];           // [필수]요청구분
    $trace_no         = $_POST["EP_trace_no"];        // [필수]추적고유번호
    $sessionkey       = $_POST["EP_sessionkey"];      // [필수]암호화키
    $encrypt_data     = $_POST["EP_encrypt_data"];    // [필수]암호화 데이타
    
    $pay_type         = $_POST["EP_ret_pay_type"];    // [선택]결제수단
    $complex_yn       = $_POST["EP_ret_complex_yn"];  // [선택]복합결제유무    
    $card_code        = $_POST["EP_card_code"];       // [선택]신용카드 카드코드
    
    
    /* -------------------------------------------------------------------------- */
    /* ::: 결제 주문 정보 설정                                                    */
    /* -------------------------------------------------------------------------- */    
    $user_type        = $_POST["EP_user_type"];       // [선택]사용자구분구분[1:일반,2:회원]
    $order_no         = $_POST["EP_order_no"];        // [필수]주문번호
    $memb_user_no     = $_POST["EP_memb_user_no"];    // [선택]가맹점 고객일련번호
    $user_id          = $_POST["EP_user_id"];         // [선택]고객 ID
    $user_nm          = $_POST["EP_user_name"];       // [필수]고객명
    $user_mail        = $_POST["EP_user_mail"];       // [필수]고객 E-mail
    $user_phone1      = $_POST["EP_user_phone1"];     // [필수]가맹점 고객 연락처1
    $user_phone2      = $_POST["EP_user_phone2"];     // [선택]가맹점 고객 연락처2
    $user_addr        = $_POST["EP_user_addr"];       // [선택]가맹점 고객 주소
    $product_type     = $_POST["EP_product_type"];    // [필수]상품정보구분[0:실물,1:컨텐츠]
    $product_nm       = $_POST["EP_product_nm"];      // [필수]상품명
    $product_amt      = $_POST["EP_product_amt"];     // [필수]상품금액
    
    /* -------------------------------------------------------------------------- */
    /* ::: 변경관리 정보 설정                                                     */
    /* -------------------------------------------------------------------------- */
    $mgr_txtype       = $_POST["mgr_txtype"];         // [필수]거래구분
    $mgr_subtype      = $_POST["mgr_subtype"];        // [선택]변경세부구분
    $org_cno          = $_POST["org_cno"];            // [필수]원거래고유번호
    $mgr_amt          = $_POST["mgr_amt"];            // [선택]부분취소/환불요청 금액
    $mgr_bank_cd      = $_POST["mgr_bank_cd"];        // [선택]환불계좌 은행코드
    $mgr_account      = $_POST["mgr_account"];        // [선택]환불계좌 번호
    $mgr_depositor    = $_POST["mgr_depositor"];      // [선택]환불계좌 예금주명
    $mgr_socno        = $_POST["mgr_socno"];          // [선택]환불계좌 주민번호
    $mgr_telno        = $_POST["mgr_telno"];          // [선택]환불고객 연락처
    $deli_cd          = $_POST["deli_cd"];            // [선택]배송구분[자가:DE01,택배:DE02]
    $deli_corp_cd     = $_POST["deli_corp_cd"];       // [선택]택배사코드
    $deli_invoice     = $_POST["deli_invoice"];       // [선택]운송장 번호
    $deli_rcv_nm      = $_POST["deli_rcv_nm"];        // [선택]수령인 이름
    $deli_rcv_tel     = $_POST["deli_rcv_tel"];       // [선택]수령인 연락처
    $req_ip           = $_POST["req_ip"];             // [필수]요청자 IP
    $req_id           = $_POST["req_id"];             // [선택]요청자 ID
    $mgr_msg          = $_POST["mgr_msg"];            // [선택]변경 사유
    $mgr_paytype      = $_POST["mgr_paytype"];        // [선택]결제수단
    
    /* -------------------------------------------------------------------------- */
    /* ::: 전문                                                                   */
    /* -------------------------------------------------------------------------- */
    $mgr_data    = "";     // 변경정보
    $mall_data   = "";     // 요청전문
    
    /* -------------------------------------------------------------------------- */
    /* ::: 결제 결과                                                              */
    /* -------------------------------------------------------------------------- */
    $res_cd               = "";
    $res_msg              = "";
    
    
    /* -------------------------------------------------------------------------- */
    /* ::: EasyPayClient 인스턴스 생성 [변경불가 !!].                             */
    /* -------------------------------------------------------------------------- */
    $easyPay = new EasyPay_Client;         // 전문처리용 Class (library에서 정의됨)
    $easyPay->clearup_msg();

    $easyPay->set_home_dir($g_home_dir);
    $easyPay->set_gw_url($g_gw_url);
    $easyPay->set_gw_port($g_gw_port);
    $easyPay->set_log_dir($g_log_dir);
    $easyPay->set_log_level($g_log_level);
    $easyPay->set_cert_file($g_cert_file);
    
    /* -------------------------------------------------------------------------- */
    /* ::: IP 정보 설정                                                           */
    /* -------------------------------------------------------------------------- */
    $client_ip = $easyPay->get_remote_addr();    // [필수]결제고객 IP
    
    /* -------------------------------------------------------------------------- */
    /* ::: 승인요청(플러그인 암호화 전문 설정)                                    */
    /* -------------------------------------------------------------------------- */
    if( $TRAN_CD_NOR_PAYMENT == $tr_cd ) {
    
        //승인요청 전문 설정
        $easyPay->set_trace_no($trace_no);
        $easyPay->set_snd_key($sessionkey);
        $easyPay->set_enc_data($encrypt_data);  
        
    /* -------------------------------------------------------------------------- */
    /* ::: 변경관리 요청                                                          */
    /* -------------------------------------------------------------------------- */
    }else if( $TRAN_CD_NOR_MGR == $tr_cd ) {

    $mgr_data = $easyPay->set_easypay_item("mgr_data");    
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_txtype"      , $mgr_txtype       );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_subtype"     , $mgr_subtype      );
    $easyPay->set_easypay_deli_us( $mgr_data, "org_cno"         , $org_cno          );
    $easyPay->set_easypay_deli_us( $mgr_data, "order_no"        , $order_no         );
    $easyPay->set_easypay_deli_us( $mgr_data, "pay_type"        , $pay_type         );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_amt"         , $mgr_amt          );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_bank_cd"     , $mgr_bank_cd      );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_account"     , $mgr_account      );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_depositor"   , $mgr_depositor    );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_socno"       , $mgr_socno        );
    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_telno"       , $mgr_telno        );
    $easyPay->set_easypay_deli_us( $mgr_data, "deli_cd"         , $deli_cd          );
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
    $opt = "UTF-8";    
    $easyPay->easypay_exec($g_mall_id, $tr_cd, $order_no, $client_ip, $opt);
    $res_cd  = $easyPay->_easypay_resdata["res_cd"];    // 응답코드
    $res_msg = $easyPay->_easypay_resdata["res_msg"];   // 응답메시지
    
    /* -------------------------------------------------------------------------- */
    /* ::: 결과 처리                                                              */
    /* -------------------------------------------------------------------------- */

    $r_cno             = $easyPay->_easypay_resdata["cno"           ];  // PG거래번호 
    $r_amount          = $easyPay->_easypay_resdata["amount"        ];  //총 결제금액
    $r_order_no        = $easyPay->_easypay_resdata["order_no"      ];  //주문번호
    $r_auth_no         = $easyPay->_easypay_resdata["auth_no"       ];  //승인번호
    $r_tran_date       = $easyPay->_easypay_resdata["tran_date"     ];  //승인일시
    $r_escrow_yn       = $easyPay->_easypay_resdata["escrow_yn"     ];  //에스크로 사용유무
    $r_complex_yn      = $easyPay->_easypay_resdata["complex_yn"    ];  //복합결제 유무
    $r_stat_cd         = $easyPay->_easypay_resdata["stat_cd"       ];  //상태코드
    $r_stat_msg        = $easyPay->_easypay_resdata["stat_msg"      ];  //상태메시지
    $r_pay_type        = $easyPay->_easypay_resdata["pay_type"      ];  //결제수단
    $r_mall_id         = $easyPay->_easypay_resdata["mall_id"       ];  //결제수단
    $r_card_no         = $easyPay->_easypay_resdata["card_no"       ];  //카드번호
    $r_issuer_cd       = $easyPay->_easypay_resdata["issuer_cd"     ];  //발급사코드
    $r_issuer_nm       = $easyPay->_easypay_resdata["issuer_nm"     ];  //발급사명
    $r_acquirer_cd     = $easyPay->_easypay_resdata["acquirer_cd"   ];  //매입사코드
    $r_acquirer_nm     = $easyPay->_easypay_resdata["acquirer_nm"   ];  //매입사명
    $r_install_period  = $easyPay->_easypay_resdata["install_period"];  //할부개월
    $r_noint           = $easyPay->_easypay_resdata["noint"         ];  //무이자여부
    $r_join_no         = $easyPay->_easypay_resdata["join_no"       ];  //가맹점 번호
    $r_part_cancel_yn  = $easyPay->_easypay_resdata["part_cancel_yn"];  //부분취소 가능여부
    $r_card_gubun      = $easyPay->_easypay_resdata["card_gubun"     ]; //신용카드 종류
    $r_card_biz_gubun  = $easyPay->_easypay_resdata["card_biz_gubun" ]; //신용카드 구분
    $r_cpon_flag       = $easyPay->_easypay_resdata["cpon_flag"      ]; //쿠폰사용유무
    $r_van_tid         = $easyPay->_easypay_resdata["van_tid"        ]; //VAN Tid
    $r_cc_expr_date    = $easyPay->_easypay_resdata["cc_expr_date"   ]; //신용카드 유효기간
    $r_bank_cd         = $easyPay->_easypay_resdata["bank_cd"        ]; //은행코드
    $r_bank_nm         = $easyPay->_easypay_resdata["bank_nm"        ]; //은행명
    $r_account_no      = $easyPay->_easypay_resdata["account_no"     ]; //계좌번호
    $r_deposit_nm      = $easyPay->_easypay_resdata["deposit_nm"     ]; //입금자명
    $r_expire_date     = $easyPay->_easypay_resdata["expire_date"    ]; //계좌사용만료일
    $r_cash_res_cd     = $easyPay->_easypay_resdata["cash_res_cd"    ]; //현금영수증 결과코드
    $r_cash_res_msg    = $easyPay->_easypay_resdata["cash_res_msg"   ]; //현금영수증 결과메세지
    $r_cash_auth_no    = $easyPay->_easypay_resdata["cash_auth_no"   ]; //현금영수증 승인번호
    $r_cash_tran_date  = $easyPay->_easypay_resdata["cash_tran_date" ]; //현금영수증 승인일시
    $r_cash_issue_type = $easyPay->_easypay_resdata["cash_issue_type"]; //현금영수증발행용도
    $r_cash_auth_type  = $easyPay->_easypay_resdata["cash_auth_type" ]; //인증구분
    $r_cash_auth_value = $easyPay->_easypay_resdata["cash_auth_value"]; //인증번호
    $r_auth_id         = $easyPay->_easypay_resdata["auth_id"        ]; //PhoneID
    $r_billid          = $easyPay->_easypay_resdata["billid"         ]; //인증번호
    $r_mobile_no       = $easyPay->_easypay_resdata["mobile_no"      ]; //휴대폰번호
    $r_mob_ansim_yn    = $easyPay->_easypay_resdata["mob_ansim_yn"   ]; //안심결제 사용유무
    $r_ars_no          = $easyPay->_easypay_resdata["ars_no"         ]; //전화번호
    $r_cp_cd           = $easyPay->_easypay_resdata["cp_cd"          ]; //포인트사/쿠폰사
    $r_pnt_auth_no     = $easyPay->_easypay_resdata["pnt_auth_no"    ]; //포인트승인번호
    $r_pnt_tran_date   = $easyPay->_easypay_resdata["pnt_tran_date"  ]; //포인트승인일시
    $r_used_pnt        = $easyPay->_easypay_resdata["used_pnt"       ]; //사용포인트
    $r_remain_pnt      = $easyPay->_easypay_resdata["remain_pnt"     ]; //잔여한도
    $r_pay_pnt         = $easyPay->_easypay_resdata["pay_pnt"        ]; //할인/발생포인트
    $r_accrue_pnt      = $easyPay->_easypay_resdata["accrue_pnt"     ]; //누적포인트
    $r_deduct_pnt      = $easyPay->_easypay_resdata["deduct_pnt"     ]; //총차감 포인트
    $r_payback_pnt     = $easyPay->_easypay_resdata["payback_pnt"    ]; //payback 포인트
    $r_cpon_auth_no    = $easyPay->_easypay_resdata["cpon_auth_no"   ]; //쿠폰승인번호
    $r_cpon_tran_date  = $easyPay->_easypay_resdata["cpon_tran_date" ]; //쿠폰승인일시
    $r_cpon_no         = $easyPay->_easypay_resdata["cpon_no"        ]; //쿠폰번호
    $r_remain_cpon     = $easyPay->_easypay_resdata["remain_cpon"    ]; //쿠폰잔액
    $r_used_cpon       = $easyPay->_easypay_resdata["used_cpon"      ]; //쿠폰 사용금액
    $r_rem_amt         = $easyPay->_easypay_resdata["rem_amt"        ]; //잔액
    $r_bk_pay_yn       = $easyPay->_easypay_resdata["bk_pay_yn"      ]; //장바구니 결제여부
    $r_canc_acq_date   = $easyPay->_easypay_resdata["canc_acq_date"  ]; //매입취소일시
    $r_canc_date       = $easyPay->_easypay_resdata["canc_date"      ]; //취소일시
    $r_refund_date     = $easyPay->_easypay_resdata["refund_date"    ]; //환불예정일시    

    
    /* -------------------------------------------------------------------------- */
    /* ::: 가맹점 DB 처리                                                         */
    /* -------------------------------------------------------------------------- */
    /* 응답코드(res_cd)가 "0000" 이면 정상승인 입니다.                            */
    /* r_amount가 주문DB의 금액과 다를 시 반드시 취소 요청을 하시기 바랍니다.     */
    /* DB 처리 실패 시 취소 처리를 해주시기 바랍니다.                             */
    /* -------------------------------------------------------------------------- */
    /*
    if ( $res_cd == "0000" ) {
        $bDBProc = "true";     // DB처리 성공 시 "true", 실패 시 "false"
        if ( $bDBProc != "true" ) {
            // 승인요청이 실패 시 아래 실행
            if( $TRAN_CD_NOR_PAYMENT == $tr_cd ) {          
                $easyPay->clearup_msg();
                
                $tr_cd = $TRAN_CD_NOR_MGR; 
                $mgr_data = $easyPay->set_easypay_item("mgr_data");
                if ( $r_escrow_yn != "Y" ) {
                    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_txtype"  , "40"   );
                } else {
                    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_txtype"  , "61"   );
                    $easyPay->set_easypay_deli_us( $mgr_data, "mgr_subtype" , "ES02" );
                }
                $easyPay->set_easypay_deli_us( $mgr_data, "org_cno"  , $r_cno     );
                $easyPay->set_easypay_deli_us( $mgr_data, "order_no" , $order_no );
                $easyPay->set_easypay_deli_us( $mgr_data, "req_ip"   , $client_ip );
                $easyPay->set_easypay_deli_us( $mgr_data, "req_id"   , "MALL_R_TRANS" );
                $easyPay->set_easypay_deli_us( $mgr_data, "mgr_msg"  , "DB 처리 실패로 망취소"  );
                
                $easyPay->easypay_exec($g_mall_id, $tr_cd, $order_no, $client_ip, $opt);
                $res_cd      = $easyPay->_easypay_resdata["res_cd"     ];    // 응답코드
                $res_msg     = $easyPay->_easypay_resdata["res_msg"    ];    // 응답메시지
                $r_cno       = $easyPay->_easypay_resdata["cno"        ];    // PG거래번호 
                $r_canc_date = $easyPay->_easypay_resdata["canc_date"  ];    // 취소일시
            }
        }
    }
    */
?>
<html>
<meta name="robots" content="noindex, nofollow">
<body onload="window.parent.goComplete();">
    <input type="hidden" id="res_cd"           name="res_cd"          value="<?=$res_cd?>">              <!-- 결과코드 //-->
    <input type="hidden" id="res_msg"          name="res_msg"         value="<?=$res_msg?>">             <!-- 결과메시지 //-->
    <input type="hidden" id="cno"              name="cno"             value="<?=$r_cno?>">               <!-- PG거래번호 //-->
    <input type="hidden" id="amount"           name="amount"          value="<?=$r_amount?>">            <!-- 총 결제금액 //-->
    <input type="hidden" id="order_no"         name="order_no"        value="<?=$r_order_no?>">          <!-- 주문번호 //-->
    <input type="hidden" id="auth_no"          name="auth_no"         value="<?=$r_auth_no?>">           <!-- 승인번호 //-->
    <input type="hidden" id="tran_date"        name="tran_date"       value="<?=$r_tran_date?>">         <!-- 승인일시 //-->
    <input type="hidden" id="escrow_yn"        name="escrow_yn"       value="<?=$r_escrow_yn?>">         <!-- 에스크로 사용유무 //-->
    <input type="hidden" id="complex_yn"       name="complex_yn"      value="<?=$r_complex_yn?>">        <!-- 복합결제 유무 //-->
    <input type="hidden" id="stat_cd"          name="stat_cd"         value="<?=$r_stat_cd?>">           <!-- 상태코드 //-->
    <input type="hidden" id="stat_msg"         name="stat_msg"        value="<?=$r_stat_msg?>">          <!-- 상태메시지 //-->
    <input type="hidden" id="pay_type"         name="pay_type"        value="<?=$r_pay_type?>">          <!-- 결제수단 //-->
    <input type="hidden" id="mall_id"          name="mall_id"         value="<?=$r_mall_id?>">           <!-- 가맹점 Mall ID //-->
    <input type="hidden" id="card_no"          name="card_no"         value="<?=$r_card_no?>">           <!-- 카드번호 //-->
    <input type="hidden" id="issuer_cd"        name="issuer_cd"       value="<?=$r_issuer_cd?>">         <!-- 발급사코드 //-->
    <input type="hidden" id="issuer_nm"        name="issuer_nm"       value="<?=$r_issuer_nm?>">         <!-- 발급사명 //-->
    <input type="hidden" id="acquirer_cd"      name="acquirer_cd"     value="<?=$r_acquirer_cd?>">       <!-- 매입사코드 //-->
    <input type="hidden" id="acquirer_nm"      name="acquirer_nm"     value="<?=$r_acquirer_nm?>">       <!-- 매입사명 //-->
    <input type="hidden" id="install_period"   name="install_period"  value="<?=$r_install_period?>">    <!-- 할부개월 //-->
    <input type="hidden" id="noint"            name="noint"           value="<?=$r_noint?>">             <!-- 무이자여부 //-->
    <input type="hidden" id="join_no"          name="join_no"         value="<?=$r_join_no?>">           <!-- 가맹점 번호 //-->
    <input type="hidden" id="part_cancel_yn"   name="part_cancel_yn"  value="<?=$r_part_cancel_yn?>">    <!-- 부분취소 가능여부 //-->
    <input type="hidden" id="card_gubun"       name="card_gubun"      value="<?=$r_card_gubun?>">        <!-- 신용카드 종류 //-->
    <input type="hidden" id="card_biz_gubun"   name="card_biz_gubun"  value="<?=$r_card_biz_gubun?>">    <!-- 신용카드 구분 //-->
    <input type="hidden" id="cpon_flag"        name="cpon_flag"       value="<?=$r_cpon_flag?>">         <!-- 쿠폰사용유무 //-->
    <input type="hidden" id="van_tid"          name="van_tid"         value="<?=$r_van_tid?>">           <!-- VAN Tid //-->
    <input type="hidden" id="cc_expr_date"     name="cc_expr_date"    value="<?=$r_cc_expr_date?>">      <!-- 신용카드 유효기간 //-->
    <input type="hidden" id="bank_cd"          name="bank_cd"         value="<?=$r_bank_cd?>">           <!-- 은행코드 //-->
    <input type="hidden" id="bank_nm"          name="bank_nm"         value="<?=$r_bank_nm?>">           <!-- 은행명 //-->
    <input type="hidden" id="account_no"       name="account_no"      value="<?=$r_account_no?>">        <!-- 계좌번호 //-->
    <input type="hidden" id="deposit_nm"       name="deposit_nm"      value="<?=$r_deposit_nm?>">        <!-- 입금자명 //-->
    <input type="hidden" id="expire_date"      name="expire_date"     value="<?=$r_expire_date?>">       <!-- 계좌사용만료일 //-->
    <input type="hidden" id="cash_res_cd"      name="cash_res_cd"     value="<?=$r_cash_res_cd?>">       <!-- 현금영수증 결과코드 //-->
    <input type="hidden" id="cash_res_msg"     name="cash_res_msg"    value="<?=$r_cash_res_msg?>">      <!-- 현금영수증 결과메세지 //-->
    <input type="hidden" id="cash_auth_no"     name="cash_auth_no"    value="<?=$r_cash_auth_no?>">      <!-- 현금영수증 승인번호 //-->
    <input type="hidden" id="cash_tran_date"   name="cash_tran_date"  value="<?=$r_cash_tran_date?>">    <!-- 현금영수증 승인일시 //-->
    <input type="hidden" id="cash_issue_type"  name="cash_issue_type" value="<?=$r_cash_issue_type?>">   <!-- 현금영수증발행용도 //-->
    <input type="hidden" id="cash_auth_type"   name="cash_auth_type"  value="<?=$r_cash_auth_type?>">    <!-- 인증구분 //-->
    <input type="hidden" id="cash_auth_value"  name="cash_auth_value" value="<?=$r_cash_auth_value?>">   <!-- 인증번호 //-->
    <input type="hidden" id="auth_id"          name="auth_id"         value="<?=$r_auth_id?>">           <!-- PhoneID //-->
    <input type="hidden" id="billid"           name="billid"          value="<?=$r_billid?>">            <!-- 인증번호 //-->
    <input type="hidden" id="mobile_no"        name="mobile_no"       value="<?=$r_mobile_no?>">         <!-- 휴대폰번호 //-->
    <input type="hidden" id="mob_ansim_yn"     name="mob_ansim_yn"    value="<?=$r_mob_ansim_yn?>">      <!-- 안심결제 사용유무 //-->
    <input type="hidden" id="ars_no"           name="ars_no"          value="<?=$r_ars_no?>">            <!-- 전화번호 //-->
    <input type="hidden" id="cp_cd"            name="cp_cd"           value="<?=$r_cp_cd?>">             <!-- 포인트사/쿠폰사 //-->
    <input type="hidden" id="pnt_auth_no"      name="pnt_auth_no"     value="<?=$r_pnt_auth_no?>">       <!-- 포인트승인번호 //-->
    <input type="hidden" id="pnt_tran_date"    name="pnt_tran_date"   value="<?=$r_pnt_tran_date?>">     <!-- 포인트승인일시 //-->
    <input type="hidden" id="used_pnt"         name="used_pnt"        value="<?=$r_used_pnt?>">          <!-- 사용포인트 //-->
    <input type="hidden" id="remain_pnt"       name="remain_pnt"      value="<?=$r_remain_pnt?>">        <!-- 잔여한도 //-->
    <input type="hidden" id="pay_pnt"          name="pay_pnt"         value="<?=$r_pay_pnt?>">           <!-- 할인/발생포인트 //-->
    <input type="hidden" id="accrue_pnt"       name="accrue_pnt"      value="<?=$r_accrue_pnt?>">        <!-- 누적포인트 //-->
    <input type="hidden" id="deduct_pnt"       name="deduct_pnt"      value="<?=$r_deduct_pnt?>">        <!-- 총차감 포인트 //-->
    <input type="hidden" id="payback_pnt"      name="payback_pnt"     value="<?=$r_payback_pnt?>">       <!-- payback 포인트 //-->
    <input type="hidden" id="cpon_auth_no"     name="cpon_auth_no"    value="<?=$r_cpon_auth_no?>">      <!-- 쿠폰승인번호 //-->
    <input type="hidden" id="cpon_tran_date"   name="cpon_tran_date"  value="<?=$r_cpon_tran_date?>">    <!-- 쿠폰승인일시 //-->
    <input type="hidden" id="cpon_no"          name="cpon_no"         value="<?=$r_cpon_no?>">           <!-- 쿠폰번호 //-->
    <input type="hidden" id="remain_cpon"      name="remain_cpon"     value="<?=$r_remain_cpon?>">       <!-- 쿠폰잔액 //-->
    <input type="hidden" id="used_cpon"        name="used_cpon"       value="<?=$r_used_cpon?>">         <!-- 쿠폰 사용금액 //-->
    <input type="hidden" id="rem_amt"          name="rem_amt"         value="<?=$r_rem_amt?>">           <!-- 잔액 //-->
    <input type="hidden" id="bk_pay_yn"        name="bk_pay_yn"       value="<?=$r_bk_pay_yn?>">         <!-- 장바구니 결제여부 //-->
    <input type="hidden" id="canc_acq_date"    name="canc_acq_date"   value="<?=$r_canc_acq_date?>">     <!-- 매입취소일시 //-->
    <input type="hidden" id="canc_date"        name="canc_date"       value="<?=$r_canc_date?>">         <!-- 취소일시 //-->
    <input type="hidden" id="refund_date"      name="refund_date"     value="<?=$r_refund_date?>">       <!-- 환불예정일시 //-->
</body>
</html>

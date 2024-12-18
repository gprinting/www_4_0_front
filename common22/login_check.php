<?
define("INC_PATH", $_SERVER["INC"]);
if (empty($conn)) {
    include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
    $connectionPool = new ConnectionPool();
    $conn = $connectionPool->getPooledConnection();
}
if (empty($dao)) {
    include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");
    $dao = new FrontCommonDAO();
}

// 모바일 버전
/*
if ($is_mobile) {
    $btn_login = "<img id=\"m_login\" src=\"/design_template/images/common22/btn_header_login.png\" alt=\"로그인버튼\" />";
    
    if ($is_login) {
        $btn_login = "<img id=\"m_logout\" src=\"/design_template/images/common22/btn_header_logout.png\" alt=\"로그아웃버튼\" />";

    }

    $template->reg("btn_login", $btn_login);

} else {
*/
    /************************************* 비 로그인 상태 **********************************/
    $btn_join  = "<a href=\"#\" onclick=\"showLoginBox();\" class=\"link_login\">로그인</a>하시면 보다 많은 서비스를 이용하실 수 있습니다.";
    $btn_login = "<a href=\"#\" onclick=\"showLoginBox();\">
                    <div><img src=\"/design_template/images/common22/icon_header_login.png\" alt=\"로그인\"></div>
                    <div class=\"top_menu_text\">로그인</div>
                  </a>";

    $btn_mypage = "<div class=\"top_menu_mypage_div\"><a id=\"top_menu_mypage\" class=\"a_top_menu_mypage\" href=\"#none\" onclick=\"showMypageMenu();\">마이페이지<img style=\"margin-left:6px;\" src=\"../design_template/images/common22/icon_mypage_more.png\" alt=\"화살표\" /></a>
                    <ul id=\"top_menu_mypage_list\" class=\"ul_top_menu_mypage_list\" style=\"display:none;\">
                        <li>
                            <a href=\"#none\" onclick=\"requireLogin();\">전체메뉴</a>
                        </li>
                        <li>
                            <a href=\"#none\" onclick=\"requireLogin();\">장바구니</a>
                        </li>
                        <li>
                            <a href=\"#none\" onclick=\"requireLogin();\">주문 정보</a>
                        </li>
                        <li>
                            <a href=\"#none\" onclick=\"requireLogin();\">1:1 문의</a>
                        </li>
                        <li>
                            <a href=\"#none\" onclick=\"requireLogin();\">회원정보</a>
                        </li>
                    </ul>
                </div>";
    $btn_order = "<a class=\"a_top_menu\" href=\"#none\" onclick=\"requireLogin();\">주문/배송조회</a>";
    $btn_tnb_order = "<a class=\"a_quick_menu\" href=\"#none\" onclick=\"requireLogin();\">주문배송조회</a>";
    $btn_tnb_mypage = "<a class=\"a_quick_menu\" href=\"#none\" onclick=\"requireLogin();\">마이페이지</a>";

    $new_header = "";

$channel_rs = $dao->selectChannelInfo(
    $conn, ["sell_site" => $_SERVER["SELL_SITE"]]
);


    /* 멤버십 정보 */
    $side_common_info = <<<html
    <button type="button">멤버십 정보</button>
    <div class="contents">
        <div class="wrap">
            <header>
                <h2>멤버십정보</h2>
            </header>
            <footer>
                <ul class="login">
                    <li><button type="button" onclick="showLoginBox();">로그인</button></li>
                    <li><button type="button" onclick="showJoinBoxBef();">회원가입</button></li>
                </ul>
            </footer>
            <p>
                {$channel_rs['company_name']} 회원이 되시면<br>
                다양한 혜택을 받으실 수 있습니다.
            </p>
        </div>
    </div>
html;
    
    /* 배송 정보 */
    $side_delivery_info = <<<html
        <header>
            <h2>배송정보</h2>
        </header>
        <footer>
            <ul class="login">
                <li><button type="button" onclick="showLoginBox();">로그인</button></li>
                <li><button type="button" onclick="showJoinBoxBef();">회원가입</button></li>
            </ul>
        </footer>
        <p>
            {$channel_rs['company_name']} 회원이 되시면<br>
            다양한 혜택을 받으실 수 있습니다.
        </p>
html;
    
    /* 장바구니 */
    $side_cart_info = <<<html
        <header>
            <h2>장바구니</h2>
        </header>
        <footer>
            <ul class="login">
                <li><button type="button" onclick="showLoginBox();">로그인</button></li>
                <li><button type="button" onclick="showJoinBoxBef();">회원가입</button></li>
            </ul>
        </footer>
        <p>
            {$channel_rs['company_name']} 회원이 되시면<br>
            다양한 혜택을 받으실 수 있습니다.
        </p>
html;
    
    /* 마이페이지 */
    $side_mypage = <<<html
        <button type="button">마이페이지</button>
        <div class="contents">
            <div class="wrap">
                <header>
                    <h2>마이페이지</h2>
                </header>
                <footer>
                    <ul class="login">
                        <li><button type="button" onclick="showLoginBox();">로그인</button></li>
                        <li><button type="button" onclick="showJoinBoxBef();">회원가입</button></li>
                    </ul>
                </footer>
                <p>
                    {$channel_rs['company_name']} 회원이 되시면<br>
                    다양한 혜택을 받으실 수 있습니다.
                </p>
            </div>
        </div>
html;

    /* 월배송 신청 */
    $side_prepaidDelivery = <<<html
        <header>
            <h2>월배송 신청</h2>
        </header>
        <footer>
            <ul class="login">
                <li><button type="button" onclick="showLoginBox();">로그인</button></li>
                <li><button type="button" onclick="showJoinBoxBef();">회원가입</button></li>
            </ul>
        </footer>
        <p>
            <strong>
                월매출 33만원</strong>을 넘기면<br>
                무료로 이용하실 수 있는 서비스입니다.<br>
                (VAT포함 55,000원)
            </strong>
        </p>
html;
    

    /* TODO 테스트오픈 문의하기 */
    $ftf_sel = <<<html
        <a href="#none" onclick="showLoginBox();">
            <img src="/design_template/images/common22/icon_ftf.png" alt="1:1 문의" />
        </a>
html;
 
    /******************************** 비 로그인 상태 끝 **********************************/
    
    /******************************** 로그인 되었을 때 ************************************/
    if ($is_login) {
        $state_arr = $_SESSION["state_arr"];

        $name  = $_SESSION["name"];
        $grade = $_SESSION["grade"];
        if (empty($_SESSION["basic_addr"]) || empty($_SESSION["basic_addr_detail"])) {
            $basic_addr = "기본 배송지가 없습니다.";
        } else {
            $basic_addr = $_SESSION["basic_addr"] . "&nbsp" . $_SESSION["basic_addr_detail"];
        }

        $card_charge_html = '';
        if ($_SESSION["card_charge_yn"] === 'Y') {
            $card_charge_html = '<a href="#none" onclick="showPrepaymentPop();" class="btn_side_contents_01" style="margin-bottom:20px;">선입금 카드 결제</a>';
        }
    
        // 선입금
        if (empty($conn)) {
            include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
            $connectionPool = new ConnectionPool();
            $conn = $connectionPool->getPooledConnection();
        }
        if (empty($dao)) {
            include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");
            $dao = new FrontCommonDAO();
        }
        //$conn->debug = 1;

        $rs = $dao->selectMemberCalculInfo(
            $conn, ["member_seqno" => $_SESSION["member_seqno"]]
        );

        $ba_name = $rs->fields["bank_name"];
        $ba_num  = $rs->fields["ba_num"] ?? "가상계좌를 발급받아 주세요.";

        $rs = $dao->selectMemberPrepay(
            $conn, ["member_seqno" => $_SESSION["member_seqno"]]
        );

        $id = $_SESSION["id"];
        //if($id=='sungbang_test'){
            // 개발 테스트용
            $rskaka = new stdClass();
            $rskaka->fields = [
                0 => 99999999,
                "get_prepay('".$id."' ,'".date("Y-m-d")."')"=>99999999
            ];
            $rs = $rskaka;
        //}
        //else $rs     = $dao->selectPrepayPrice($conn, $id);
        //print_r($rs);   
        $fields = $rs->fields;

        $prepay_card  = intval($fields[0]);
        //$prepay_money = intval($rs["prepay_price_money"]);
        $prepay_bal   = number_format($prepay_card);
    
        $prdt_form  = "<tr>";
        $prdt_form .=     "<td>";
        $prdt_form .=         "<p class=\"contents_side_text_overflow\"><a href=\"%s\">%s</a></p>";
        $prdt_form .=     "</td>";
        $prdt_form .= "</tr>";

        // 배송정보 주문한 상품
        $dlvr_prdt_count = $_SESSION["dlvr_prdt_count"];
        $dlvr_prdt_arr   = $_SESSION["dlvr_prdt"];
        $dlvr_prdt = '';
        $url = "/mypage/order_all.html?state=" . $state_arr["배송중"];
        foreach ($dlvr_prdt_arr as $prdt) {
            $dlvr_prdt .= sprintf($prdt_form, $url, $prdt);
        }
        // 장바구니
        $cart_prdt_count = $_SESSION["cart_prdt_count"];
    
        $btn_join = "<a class=\"a_top_menu a_top_menu_name\" href=\"/mypage/member_modify.html\" onclick=\"\">$name</a>님<img class=\"top_menu_bar\" src=\"/design_template/images/common22/top_menu_bar.jpg\" alt=\"구분선\" />
                            <a class=\"a_top_menu a_top_menu_grade\" href=\"/mypage/main.html\" onclick=\"\">$grade</a>등급";
        /* 로그인 버튼 */
        $btn_login = "<a href=\"#\" onclick=\"logout();\">
                        <div><img src=\"/design_template/images/common22/icon_header_logout.png\" alt=\"로그인\"></div>
                        <div class=\"top_menu_text\">로그아웃</div>
                      </a>";
        /* 마이페이지 */
        $btn_mypage = "<div class=\"top_menu_mypage_div\"><a id=\"top_menu_mypage\" class=\"a_top_menu_mypage\" href=\"#none\" onclick=\"showMypageMenu();\">마이페이지<img style=\"margin-left:6px;\" src=\"../design_template/images/common22/icon_mypage_more.png\" alt=\"화살표\" /></a>
            <ul id=\"top_menu_mypage_list\" class=\"ul_top_menu_mypage_list\" style=\"display:none;\">
                <li>
                    <a href=\"/mypage/main.html\">전체메뉴</a>
                </li>
                <li>
                    <a href=\"/mypage/cart.html\">장바구니</a>
                </li>
                <li>
                    <a href=\"/mypage/order_all.html\">주문 정보</a>
                </li>
                <li>
                    <a href=\"/mypage/ftf_list.html\">1:1 문의</a>
                </li>
                <li>
                    <a href=\"/mypage/member_modify.html\">회원정보</a>
                </li>
            </ul>
        </div>";
        $btn_order = "<a class=\"a_top_menu\" href=\"/mypage/delivery_address.html\">주문/배송조회</a>";
        $btn_tnb_order = "<a class=\"a_quick_menu\" href=\"/mypage/delivery_address.html\">주문배송조회</a>";
        $btn_tnb_mypage = "<a class=\"a_quick_menu\" href=\"/mypage/main.html\">마이페이지</a>";
    
        /* 사이드 메뉴 선입금 */
        $side_common_info = <<<html
        <a href="/mypage22/main.html" target="_self" class="button">멤버십 정보</a>
        <div class="contents">
            <div class="wrap">
                <header>
                    <h2>멤버십정보</h2>
                </header>
                <!--p>
                    안녕하세요. <strong class="id">$name</strong>님.<br>
                </p-->
                <article class="bankAccount">
                    <h3>전용계좌</h3>
                    <dl>
                        <dt>{$ba_name}</dt>
                        <dd>{$ba_num}</dd>
                    </dl>
                </article>
                <article class="property">
                    <h3>계정 정보</h3>
                    <dl class="grade">
                        <dt>회원등급</dt>
                        <dd><strong class="$grade">$grade</strong></dd>
                    </dl>
                    <dl class="prepaid">
                        <dt>선입금</dt>
                        <dd><strong id="prepay_price_bal">$prepay_bal</strong> 원</dd>
                    </dl>
                    <dl class="point">
                        <dt>포인트</dt>
                        <dd><strong>0</strong> P</dd>
                    </dl>
                    <dl class="coupon">
                        <dt>쿠폰</dt>
                        <dd><strong>0</strong> 매</dd>
                    </dl>
                    {$card_charge_html}
                </article>
                <footer>
                    <button type="button" id="chk_depo" onclick="refreshDepo();">
                        입금 확인하기
                        <div class="tipButton" title="TIP">?</div>
                        <dl class="tipContents">
                            <dt>입금확인 안내</dt>
                            <dd>
                                가상계좌 입금 후 입금내역을 확인하실 수 있습니다.
                            </dd>
                        </dl>
                    </button>
                </footer>
    <!--
                <h3>계좌정보</h3>
                <p>이용 중인 전용계좌가 없습니다.</p>
                <footer>
                    <button type="button" onclick="" class="btn_side_contents_01">전용계좌 신청하기</button>
                </footer>
    -->
            </div>
        </div>
html;
                                            
        /* 사이드 메뉴 배송조회 */
        $side_delivery_info = <<<html
        <header>
            <h2>배송정보</h2>
        </header>
        <article class="defaultAddr">
            <h3>
                기본 배송지
                <a href="/mypage22/delivery_address.html" target="_self" class="softLight icon setting">배송지변경버튼</a>
            </h3>
            <p>$basic_addr</p>
        </article>
        <article class="ordered">
            <p>주문한 상품 <strong>{$dlvr_prdt_count}</strong>개</p>
            <footer>
                <button type="button" onclick="goOrderAll('배송');">배송 조회하기</button>
            </footer>
            <!--table class="table_side_delivery">
                {$dlvr_prdt}
            </table-->
        </article>
html;
    
        /* 사이드 메뉴 장바구니 */
        $side_cart_info = <<<html
        <header>
            <h2>장바구니</h2>
        </header>
        <p>장바구니 상품 <strong>{$cart_prdt_count}</strong>개</p>
        <footer>
            <a href="/mypage22/cart.html" class="btn_side_contents_01">주문하기</a>
        </footer>
html;
    
        /* 사이드 메뉴 마이페이지 */
        $side_mypage = <<<html
        <a href="/mypage22/main.html" target="_self" class="button">마이페이지</a>
        <div class="contents">
            <div class="wrap">
                <header>
                    <h2>마이페이지</h2>
                </header>
                <article>
                    <h3>주문정보</h3>
                    <a href="/mypage22/order_all.html" class="icon order">주문내역</a>
                    <a href="/mypage22/order_cancel.html" class="icon cancel">취소내역</a>
                    <a href="/mypage22/estimate_list.html" class="icon estimate">견적내역</a>
                </article>
                <article>
                    <h3>배송정보</h3>
                    <a href="/mypage22/delivery_address.html" class="icon delivery">배송관리</a>
                </article>
                <article>
                    <h3>회원정보</h3>
                    <!--a href="" class="icon payment">결제정보</a-->
                    <a href="/mypage22/payment_deal.html" class="icon deal">거래내역</a>
                    <a href="/mypage22/payment_list.html" class="icon payment">입출금내역</a>
                    <a href="/mypage22/benefits_virtual_mng.html" class="icon account">가상계좌</a>
                    <a href="/mypage22/benefits_receipt_mng.html" class="icon bill">현금영수증</a>
                    <a href="/mypage22/member_modify.html" class="icon memberInfo">정보변경</a>
                </article>
            </div>
        </div>
html;

        /*사이드메뉴 월배송 신청*/
        $side_prepaidDelivery = <<<html
        <header>
            <h2>월배송 신청</h2>
        </header>
        <p>
            <strong>월매출 33만원</strong>을 넘기면<br>
            무료로 이용하실 수 있는 서비스입니다.<br>
            (VAT포함 55,000원)
        </p>
        <footer>
            <a href="/mypage22/add_dlvr.html" target="_self">월배송(직배송) 신청하기</a>
        </footer>
html;

        /* TODO 테스트오픈 문의하기 */
        $ftf_sel = <<<html
        <a href="/mypage/ftf_list.html">
            <img src="/design_template/images/common22/icon_ftf.png" alt="1:1 문의" />
        </a>
html;
        
        if($ba_num == "가상계좌를 발급받아 주세요.") { $ba_num = $ba_num; }else{ $ba_num = "(".$ba_num.")";};

        $phantom_new_add_mypage = <<<html
                    <span class="top_submenu"><p class="virtual_account"><b>가상계좌</b>{$ba_name}{$ba_num}</p><p class="advance_deposit"><b>선입금</b>{$prepay_bal}원</p></span>
        html;

    }
if($_SERVER["SELL_SITE"] == "GP")
    $old_link = "http://30.gprinting.co.kr/sp_main/main.php";

if($_SERVER["SELL_SITE"] == "DP")
    $old_link = "http://30.dprinting.co.kr/sp_main/main.php";

$template->reg("old_link", $old_link);

$template->reg("header_logo", $channel_rs["logo_160_41"]);
$template->reg("logo_218_218", $channel_rs["logo_218_218"]);
$template->reg("logo_footer", $channel_rs["logo_footer"]);
$template->reg("e_commerce_info", $channel_rs["e_commerce_info"]);
$template->reg("addr", $channel_rs["addr"] . " " . $channel_rs["addr_detail"]);
$template->reg("indivi_info_admin", $channel_rs["indivi_info_admin"]);
$template->reg("company_name", $channel_rs["company_name"]);
$template->reg("opengraph", $channel_rs["opengraph"]);
$template->reg("favicon", $channel_rs["favicon"]);
$template->reg("homepage_address", $channel_rs["homepage_address"]);
$template->reg("repre_mail", $channel_rs["repre_mail"]);
$template->reg("repre_name", $channel_rs["repre_name"]);
$template->reg("repre_num", $channel_rs["repre_num"]);
$template->reg("licensee_num", $channel_rs["licensee_num"]);

    $template->reg("is_login", intval($is_login));
    $template->reg("name", $_SESSION["name"]);
    $template->reg("id", $_SESSION["id"]);
    $template->reg("grade", $_SESSION["grade"]);
    $template->reg("btn_join", $btn_join);
    $template->reg("btn_login", $btn_login);
    $template->reg("btn_mypage", $btn_mypage);
    $template->reg("btn_order", $btn_order);
    $template->reg("btn_tnb_order", $btn_tnb_order);
    $template->reg("btn_tnb_mypage", $btn_tnb_mypage);
    $template->reg("side_common_info", $side_common_info);
    $template->reg("side_delivery_info", $side_delivery_info);
    $template->reg("side_cart_info", $side_cart_info);
    $template->reg("side_mypage", $side_mypage);
    $template->reg("side_prepaidDelivery", $side_prepaidDelivery);
    $template->reg("ftf_selector", $ftf_sel);
    $template->reg("phantom_new_add_mypage", $phantom_new_add_mypage);

/*
}
*/
?>

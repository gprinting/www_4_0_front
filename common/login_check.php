<?

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
    $btn_login = "<img id=\"m_login\" src=\"/design_template/images/common/btn_header_login.png\" alt=\"로그인버튼\" />";

    if ($is_login) {
        $btn_login = "<img id=\"m_logout\" src=\"/design_template/images/common/btn_header_logout.png\" alt=\"로그아웃버튼\" />";

    }

    $template->reg("btn_login", $btn_login);

} else {
*/


/************************************* 비 로그인 상태 **********************************/
$btn_join  = "<a href=\"#\" onclick=\"showLoginBox();\" class=\"link_login\">로그인</a>하시면 보다 많은 서비스를 이용하실 수 있습니다.";
$btn_login = "<a href=\"#\" onclick=\"showLoginBox();\">
                    <div class=\"top_menu_text\">로그인</div>
                  </a>";

$btn_mypage = "<div class=\"top_menu_mypage_div\"><a id=\"top_menu_mypage\" class=\"a_top_menu_mypage\" href=\"#none\" onclick=\"showMypageMenu();\">마이페이지<img style=\"margin-left:6px;\" src=\"../design_template/images/common/icon_mypage_more.png\" alt=\"화살표\" /></a>
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
        <div class="contents_side_wrapper contents_side_member">
            <div class="contents_side_title">
                멤버십정보
            </div>
            <div class="contents_side_01_member_text">
                <p>{$channel_rs['company_name']} 회원이 되시면 좀 더</p>
                <p>다양한 혜택을 받으실 수 있습니다.</p>
            </div>
            <div class="btn_wrap">
                <a href="#none" onclick="showLoginBox();" class="c_btn-primary">로그인</a>
                <a href="#none" onclick="" class="c_btn-primary-reverse">ID/PW찾기</a>
                <a href="#none" onclick="showJoinBox();" class="c_btn-primary-reverse">회원가입</a>
            </div>
        </div>
html;

/* 배송 정보 */
$side_delivery_info = <<<html
        <div class="contents_side_wrapper contents_side_delivery">
            <div class="contents_side_title">
                배송정보
            </div>
            <div class="contents_side_01_member_text">
                <p>{$channel_rs['company_name']} 회원이 되시면 좀 더</p>
                <p>다양한 혜택을 받으실 수 있습니다.</p>
            </div>
            <div class="btn_wrap">
                <a href="#none" onclick="showLoginBox();" class="c_btn-primary">로그인</a>
                <a href="#none" onclick="" class="c_btn-primary-reverse">ID/PW찾기</a>
                <a href="#none" onclick="showJoinBox();" class="c_btn-primary-reverse">회원가입</a>
            </div>
        </div>
html;

/* 장바구니 */
$side_cart_info = <<<html
        <div class="contents_side_wrapper contents_side_basket">
            <div class="contents_side_title">
                장바구니
            </div>
            <div class="contents_side_01_member_text">
                <p>{$channel_rs['company_name']} 회원이 되시면 좀 더</p>
                <p>다양한 혜택을 받으실 수 있습니다.</p>
            </div>
            <div class="btn_wrap">
                <a href="#none" onclick="showLoginBox();" class="c_btn-primary">로그인</a>
                <a href="#none" onclick="" class="c_btn-primary-reverse">ID/PW찾기</a>
                <a href="#none" onclick="showJoinBox();" class="c_btn-primary-reverse">회원가입</a>
            </div>
        </div>
html;

/* 마이페이지 */
$side_mypage = <<<html
        <div class="contents_side_wrapper contents_side_mypage">
            <div class="contents_side_title">
                마이페이지
            </div>
            <div class="contents_side_01_member_text">
                <p>{$channel_rs['company_name']} 회원이 되시면 좀 더</p>
                <p>다양한 혜택을 받으실 수 있습니다.</p>
            </div>
            <div class="btn_wrap">
                <a href="#none" onclick="showLoginBox();" class="c_btn-primary">로그인</a>
                <a href="#none" onclick="" class="c_btn-primary-reverse">ID/PW찾기</a>
                <a href="#none" onclick="showJoinBox();" class="c_btn-primary-reverse">회원가입</a>
            </div>
        </div>
html;

/* TODO 테스트오픈 문의하기 */
$ftf_sel = <<<html
        <a href="#none" onclick="showLoginBox();">
            <img src="/design_template/images/common/icon_ftf.png" alt="1:1 문의" />
        </a>
html;

/******************************** 비 로그인 상태 끝 **********************************/

/******************************** 로그인 되었을 때 ************************************/
if ($is_login) {
    $state_arr = $_SESSION["state_arr"];

    $ba_name = $_SESSION["ba_name"] ?? '-';
    $ba_num  = $_SESSION["ba_num"] ?? "가상계좌가 존재하지 않습니다.";

    $name  = $_SESSION["name"];
    $grade = $_SESSION["grade"];
    if (empty($_SESSION["basic_addr"]) || empty($_SESSION["basic_addr_detail"])) {
        $basic_addr = "기본 배송지가 없습니다.";
    } else {
        $basic_addr = $_SESSION["basic_addr"] . "&nbsp" . $_SESSION["basic_addr_detail"];
    }

    $card_charge_html = '';
    if ($_SESSION["card_charge_yn"] === 'Y') {
        $card_charge_html = '<a href="#none" onclick="showPrepaymentPop();" class="c_btn-primary btn_side_contents_01" style="margin-bottom:20px;">선입금 카드 결제</a>';
    }

    // 선입금
    $rs = $dao->selectMemberPrepay(
        $conn, ["member_seqno" => $_SESSION["member_seqno"]]
    );
    $id = $_SESSION["id"];
    $rs     = $dao->selectPrepayPrice($conn, $id);
    $fields = $rs->fields;

    $prepay_money  = intval($fields[0]);
    $prepay_bal   = number_format($prepay_money);

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

    $btn_join = "<a class=\"a_top_menu a_top_menu_name\" href=\"/mypage/member_modify.html\" onclick=\"\">$name</a>님<img class=\"top_menu_bar\" src=\"/design_template/images/common/top_menu_bar.jpg\" alt=\"구분선\" />
                            <a class=\"a_top_menu a_top_menu_grade\" href=\"/mypage/main.html\" onclick=\"\">$grade</a>등급";
    /* 로그인 버튼 */
    $btn_login = "<a href=\"#\" onclick=\"logout();\">
                        <div class=\"top_menu_text\">로그아웃</div>
                      </a>";
    /* 마이페이지 */
    $btn_mypage = "<div class=\"top_menu_mypage_div\"><a id=\"top_menu_mypage\" class=\"a_top_menu_mypage\" href=\"#none\" onclick=\"showMypageMenu();\">마이페이지<img style=\"margin-left:6px;\" src=\"../design_template/images/common/icon_mypage_more.png\" alt=\"화살표\" /></a>
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
        <div class="contents_side_wrapper contents_side_member">
            <div class="contents_side_title">
                멤버십정보
            </div>
            <div class="contents_side_01_member_text">
                <p>안녕하세요. <span class="text_st_03">$name</span>님.</p>
                <p>고객님의 회원등급은 <span class="text_st_01">$grade</span> 입니다.</p>
            </div>
            <p class="contents_sub_title">고객지갑정보</p>
            <div class="contents_side_member_info">
                <table class="table_side_member">
                    <tr>
                        <td class="td_l_side_member">
                            <img src="/design_template/images/common/icon_side_member_cash.png" alt="아이콘" /><span class="span_side_member_menu span_side_text">선입금</span>
                        </td>
                        <td class="td_r_side_member">
                            <span id="prepay_price_bal" class="text_st_02 span_side_text">$prepay_bal</span><span class="span_side_text">&nbsp;원</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="td_l_side_member">
                            <img src="/design_template/images/common/icon_side_member_coupon.png" alt="아이콘" /><span class="span_side_member_menu span_side_text">포인트</span>
                        </td>
                        <td class="td_r_side_member">
                            <span class="text_st_02 span_side_text">0</span><span class="span_side_text">&nbsp;P</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="td_l_side_member" style="border:none;">
                            <img src="/design_template/images/common/icon_side_member_point.png" alt="아이콘" /><span class="span_side_member_menu span_side_text">쿠폰</span>
                        </td>
                        <td class="td_r_side_member" style="border:none;">
                            <span class="text_st_02 span_side_text">0</span><span class="span_side_text">&nbsp;매</span>
                        </td>
                    </tr>
                </table>
            </div>
            {$card_charge_html}

            <p class="contents_sub_title">계좌정보</p>
            <div class="contents_side_member_info">
                <table class="table_side_member">
                    <colgroup>
                        <col style="width:100px;">
                        <col style="width:170px;">
                    </colgroup>
                    <tr>
                        <td class="td_l_side_member" style="border:none;">
                            <img src="/design_template/images/common/icon_side_member_bank.png" alt="아이콘" /><span class="span_side_member_menu span_side_text">전용계좌</span>
                        </td>
                        <td class="td_r_side_member" style="border:none;">
                            <span class="span_side_text">&nbsp;{$ba_name} {$ba_num}</span>
                        </td>
                    </tr>
                </table>
            </div>
            <a id="chk_depo" href="#none" onclick="refreshDepo('{$ba_name}', '{$ba_num}');" class="c_btn-primary btn_side_contents_01">입금확인
                <img class="icon_check_depo" src="/design_template/images/common/icon_tip02.png" alt="tip">
                <table class="tipBox tip_check_depo" style="display: none;">
                    <tbody>
                        <tr>
                            <td class="title">입금확인 안내</td>
                        </tr>
                        <tr>
                            <td>
                                가상계좌 입금 후 입금내역을 확인하실 수 있습니다.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </a>
<!--
            <p class="contents_sub_title">계좌정보</p>
            <div class="contents_side_member_info">
                <table class="table_side_member">
                    <tr>
                        <td style="border:none;">
                            <span class="span_side_text">&nbsp;이용 중인 전용계좌가 없습니다.</span>
                        </td>
                    </tr>
                </table>
            </div>
            <a href="#none" onclick="" class="c_btn-primary btn_side_contents_01">전용계좌 신청하기</a>
-->
        </div>
html;

    /* 사이드 메뉴 배송조회 */
    $side_delivery_info = <<<html
        <div class="contents_side_wrapper contents_side_delivery">
            <div class="contents_side_title">
                배송정보
            </div>
            <p class="contents_sub_title" style="position:relative;">
                기본 배송지
                <a href="/mypage/delivery_address.html" class="btn_side_delivery_setting"><img src="/design_template/images/common/btn_circle_setting.svg" alt="배송지변경버튼" /></a>
            </p>
            <div class="contents_side_div">
                <p class="contents_side_text_break">$basic_addr</p>
            </div>
            <p class="contents_sub_title">주문한 상품(<span class="text_st_01">{$dlvr_prdt_count}</span>개)</p>
            <table class="table_side_delivery">
                {$dlvr_prdt}
            </table>
            <a href="#" onclick="goOrderAll('배송');" class="c_btn-primary btn_side_contents_01">배송조회</a>
        </div>
html;

    /* 사이드 메뉴 장바구니 */
    $side_cart_info = <<<html
        <div class="contents_side_wrapper contents_side_basket">
            <div class="contents_side_title">
                장바구니
            </div>
            <p class="contents_sub_title">장바구니 상품(<span class="text_st_01">{$cart_prdt_count}</span>개)</p>
            <a href="/mypage/cart.html" class="c_btn-primary btn_side_contents_01" style="margin-top:10px;">주문하기</a>
        </div>
html;

    /* 사이드 메뉴 마이페이지 */
    $side_mypage = <<<html
        <div class="contents_side_wrapper contents_side_mypage">
            <div class="contents_side_title">
                마이페이지
            </div>
            <p class="contents_sub_title">주문정보</p>
            <table class="table_side_mypage">
                <colgroup>
                    <col style="width:60px;">
                    <col style="width:60px;">
                    <col style="width:60px;">
                </colgroup>
                <tr>
                    <td class="">
                        <div class="div_side_mypage_icon_wrapper">
                            <a href="/mypage/order_all.html"><img src="/design_template/images/common/side_mypage_icon_01.png" alt="아이콘" /></a>
                        </div>
                    </td>
                    <td class="">
                        <div class="div_side_mypage_icon_wrapper">
                            <a href="/mypage/order_cancel.html"><img src="/design_template/images/common/side_mypage_icon_02.png" alt="아이콘" /></a>
                        </div>
                    </td>
                    <td class="">
                        <div class="div_side_mypage_icon_wrapper">
                            <a href="/mypage/estimate_list.html"><img src="/design_template/images/common/side_mypage_icon_03.png" alt="아이콘" /></a>
                        </div>
                    </td>
                </tr>
            </table>
            <p class="contents_sub_title">배송정보</p>
            <table class="table_side_mypage">
                <colgroup>
                    <col style="width:60px;">
                    <col style="width:60px;">
                    <col style="width:60px;">
                </colgroup>
                <tr>
                    <td class="">
                        <div class="div_side_mypage_icon_wrapper">
                            <a href="/mypage/delivery_address.html"><img src="/design_template/images/common/side_mypage_icon_04.png" alt="아이콘" /></a>
                        </div>
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                </tr>
            </table>
            <p class="contents_sub_title">회원정보</p>
            <table class="table_side_mypage">
                <colgroup>
                    <col style="width:60px;">
                    <col style="width:60px;">
                    <col style="width:60px;">
                </colgroup>
                <tr>
                    <td class="">
                        <div class="div_side_mypage_icon_wrapper">
                            <a href=""><img src="/design_template/images/common/side_mypage_icon_05.png" alt="아이콘" /></a>
                        </div>
                    </td>
                    <td class="">
                        <div class="div_side_mypage_icon_wrapper">
                            <a href="/mypage/payment_list.html"><img src="/design_template/images/common/side_mypage_icon_06.png" alt="아이콘" /></a>
                        </div>
                    </td>
                    <td class="">
                        <div class="div_side_mypage_icon_wrapper">
                            <a href="/mypage/payment_deal.html"><img src="/design_template/images/common/side_mypage_icon_07.png" alt="아이콘" /></a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="">
                        <div class="div_side_mypage_icon_wrapper">
                            <a href="/mypage/member_modify.html"><img src="/design_template/images/common/side_mypage_icon_08.png" alt="아이콘" /></a>
                        </div>
                    </td>
                    <td class="">
                        <div class="div_side_mypage_icon_wrapper">
                            <a href="/mypage/benefits_virtual_mng.html"><img src="/design_template/images/common/side_mypage_icon_09.png" alt="아이콘" /></a>
                        </div>
                    </td>
                    <td class="">
                        <div class="div_side_mypage_icon_wrapper">
                            <a href="/mypage/benefits_receipt_mng.html"><img src="/design_template/images/common/side_mypage_icon_10.png" alt="아이콘" /></a>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
html;

    /* TODO 테스트오픈 문의하기 */
    $ftf_sel = <<<html
        <a href="/mypage/ftf_list.html">
            <img src="/design_template/images/common/icon_ftf.png" alt="1:1 문의" />
        </a>
html;

}

$common_css = "common.css";
if($channel_rs["common_css"] != null) {
    $common_css = $channel_rs["common_css"];
}

$template->reg("header_logo", $channel_rs["logo_160_41"]);
$template->reg("logo_218_218", $channel_rs["logo_218_218"]);
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
$template->reg("common_css", $common_css);


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
$template->reg("ftf_selector", $ftf_sel);

/*
}
*/
?>

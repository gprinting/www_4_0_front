<?
/*
 *
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2017/10/19 이청산 생성
 * comment : DB연동을 시도할 시 추가로 코드작성이 필요함
 *============================================================================
 *
 */

define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();

//$conn->debug=1;
$fb = $fb->getForm();

$dvs = $fb["dvs"];
$cnt = $fb["count"];

// 상품 배열
$div_arr = array();
$div_arr[0] = array("envelope", "ncr", "form");
$div_arr[1] = array("lottery", "menuboard", "octopusflyer");
$div_arr[2] = array("doorknob", "magnetic", "squaretype");

$div_size = sizeof($div_arr);

// 상품 설명 배열
$cont_arr = array();
$cont_arr[0] = array("브랜드 아이덴티티를 어필할 수 있는 상품", "세금계산서 및 거래명세표에 주로 쓰이는 상품", "신청서, 영수증 등 다양한 형태로 활용가능한 상품");
$cont_arr[1] = array("이벤트로 고객참여를 유도할 수 있는 상품", "고객과 소통하기 위한 중요한 수단의 상품", "고객의 편의성을 배려한 전단지 상품");
$cont_arr[2] = array("아파트 및 대단위 주택가에서 높은 효율의 상품", "탈부착이 편리한 합리적인 가격의 판촉물 상품", "친환경을 생각한 비닐봉투 상품");

// 링크 배열
$link_arr = array();
$link_arr[0] = array("/product/ev.html?cs=006001001", "/product/mt_ncr.html?cs=007001001", "/product/mt_form.html?cs=007002001");
$link_arr[1] = array("/product/etc_lottery.html?cs=008001001", "/product/etc_menu.html?cs=008001002", "/product/etc_multiple.html?cs=008001003");
$link_arr[2] = array("/product/etc_door.html?cs=008001005", "/product/mg_paper.html?cs=008002001", "/product/gb.html?cs=009001002");

if ($cnt ==  "") {
    $cnt = 0;
}

$div = "";
for($i = 0; $i < $div_size; $i++ ) {
    $div .= getPlusHtml($div_arr[$cnt], $cont_arr[$cnt], $link_arr[$cnt]);
    $cnt++;
}

/*
if ($cnt == $div_size - 1) {
    $div .= "<input type=\"hidden\" id=\"lastOfMoreItem\" value=\"1\">";
}
*/

echo $div;
$conn->close();


/********** 함수 영역 ******************/
function getPlusHtml($div, $cont, $link) {
$html = <<<html
    <!-- 첫번째 -->
    <div id="hit_prdt_frame_{$div[0]}" class="hit_prdt_frame shadow_effect">
        <a href="{$link[0]}">
            <div class="hit_img_wrapper">
                <img class="hit_prdt_img" src="/design_template/images/main/hit_prdt_img_{$div[0]}.jpg" alt="인기상품이미지" />
            </div>
            <img class="hit_prdt_name" src="/design_template/images/main/hit_prdt_name_{$div[0]}.png" alt="{$div[0]}" />
            <img src="/design_template/images/main/hit_prdt_bar.png" alt="구분선" />
            <p class="hit_prdt_text basic_text">{$cont[0]}</p>
            <div id="hit_prdt_frame_on_{$div[0]}" class="hit_prdt_on">
                <img src="/design_template/images/main/hit_prdt_{$div[0]}_on.png" alt="인기상품이미지" />
            </div>
        </a>
    </div>
    <!-- 두번째 -->
    <div id="hit_prdt_frame_{$div[1]}" class="hit_prdt_frame shadow_effect" style="margin:0 12px;">
        <a href="{$link[1]}">
            <div class="hit_img_wrapper">
                <img class="hit_prdt_img" src="/design_template/images/main/hit_prdt_img_{$div[1]}.jpg" alt="인기상품이미지" />
            </div>
            <img class="hit_prdt_name" src="/design_template/images/main/hit_prdt_name_{$div[1]}.png" alt="{$arr[1]}" />
            <img src="/design_template/images/main/hit_prdt_bar.png" alt="구분선" />
            <p class="hit_prdt_text basic_text">{$cont[1]}</p>
            <div id="hit_prdt_frame_on_{$div[1]}" class="hit_prdt_on">
                <img src="/design_template/images/main/hit_prdt_{$div[1]}_on.png" alt="인기상품이미지" />
            </div>
        </a>
    </div>
    <!-- 세번째 -->
    <div id="hit_prdt_frame_{$div[2]}" class="hit_prdt_frame shadow_effect">
        <a href="{$link[2]}">
            <div class="hit_img_wrapper">
                <img class="hit_prdt_img" src="/design_template/images/main/hit_prdt_img_{$div[2]}.jpg" alt="인기상품이미지" />
            </div>
            <img class="hit_prdt_name" src="/design_template/images/main/hit_prdt_name_{$div[2]}.png" alt="{$arr[2]}" />
            <img src="/design_template/images/main/hit_prdt_bar.png" alt="구분선" />
            <p class="hit_prdt_text basic_text">{$cont[2]}</p>
            <div id="hit_prdt_frame_on_{$div[2]}" class="hit_prdt_on">
                <img src="/design_template/images/main/hit_prdt_{$div[2]}_on.png" alt="인기상품이미지" />
            </div>
        </a>
    </div>
html;

    return $html;
}


?>

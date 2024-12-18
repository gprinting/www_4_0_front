<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ProductCommonDAO();
$fb  = new FormBean();

$fb = $fb->getForm();

$cate_sortcode = $fb["cs"];
$cate_name     = urldecode($fb["cn"]);
$date = $fb["date"];
$time = $fb["time"];

$param = array();
$param["cate_sortcode"] = $cate_sortcode;

$rs = $dao->selectCateOrderList($conn, $param);

$tr_form = "<tr><td>%s</td><td>%s</td><td>%s</td><td>%s일</td></tr>";

$tr = '';
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $date = date_diff(new DateTime($fields["depo_finish_date"]),
                      new DateTime($fields["release_date"]));

    $tr .= sprintf($tr_form, $fields["member_name"]
                           , $fields["depo_finish_date"]
                           , $fields["release_date"]
                           , $date->days);

    $rs->MoveNext();
}

$html = <<<html
    <header class="popup">
        <h2>상품 출고일 안내</h2>
        <button type="button" class="close">닫기</button>
    </header>
    <article>
        <ul class="boxC guide">
            <li>모든 상품은 주문 후 <strong>접수와 결제가 완료된 시간을 기준</strong>으로 생산 공정별 작업 소요 시간을 계산하여 출고일을 자동으로 안내합니다.</li>
            <li>모든 묶음배송은 늦은 주문 건 또는 생산 공정별 작업 소요 시간이 오래 걸리는 순서를 기준으로 출고일을 자동으로 안내합니다.</li>
            <li>모든 상품은 수량, 사이즈 등 선택 옵션에 따라 출고일은 달라질 수 있습니다.</li>
            <li>일부 상품은 생산공정에 따라 출고일이 변경될 수 있습니다.</li>
        </ul>
        <p class="cVivid">본 상품을 지금 주문하시면 당사 출고일 기준으로 출고 예정입니다.</p>
        <dl class="productInfo">
            <dt>{$cate_name}</dt>
            <dd class="deliveryDate"><span>{$date}</span><span>{$time}</span></dd>
        </dl>
        <p class="cVivid"><strong>배송방법</strong>은 다음 방법으로 신청하실 수 있습니다.</p>
        <ul class="type">
            <li>
                <dl>
                    <dt>직접수령</dt>
                    <dd>출고실 방문 후 직접수령</dd>
                </dl>
            </li>
            <li>
                <dl>
                    <dt>택배</dt>
                    <dd>주문시 배송지 지정 후 택배발송.</dd>
                </dl>
            </li>
            <li>
                <dl>
                    <dt>퀵</dt>
                    <dd>지급을 요하는 물건으로 퀵서비스를 이용한 발송</dd>
                </dl>
            </li>
            <li>
                <dl>
                    <dt>직배송</dt>
                    <dd>회원사 직배송 서비스 대상 고객사에게 자동으로 직발송</dd>
                </dl>
            </li>
        </ul>
        <hr class="end">
        <ul class="guide">
            <li><strong>특별한 사유가 없으면 반드시 출고예정일에 출고를 약속 드립니다.</strong></li>
            <li>제작 후 배송을 위해 택배사에 물건이 전달되는 것을 발송이라 하며, 상품 발송일 다음날부터 1~2일 후에 상품을 받아보실 수 있습니다.(택배 배송 기준)</li>
            <li>도서 산간 지역의 경우, 해당 지역에 따라 배송 시간에 차이가 있을 수 있습니다.</li>
            <li>기본 배송 기간은 주말과 공휴일을 제외한 평일 기준 기간 입니다.</li>
        </ul>
    </article>
html;

echo $html;
?>

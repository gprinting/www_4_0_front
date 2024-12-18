<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ProductCommonDAO();

$session = $fb->getSession();

$email_domain_arr = EMAIL_DOMAIN;
$email_domain_arr_count = count($email_domain_arr);

$d_acc = '';
$d_dom = '';

$d_checked = '';
$m_checked = '';

$disabled = '';

if ($is_login === true) {
    $member_seqno = $session["member_seqno"];
    $mail = $dao->selectMemberInfo($conn, $member_seqno)["mail"];
    $mail = explode('@', $mail);

    $d_acc = $mail[0];
    $d_dom = $mail[1];

    $d_checked = "checked=\"checked\"";
    $m_checked = '';
} else {
    $disabled  = "disabled=\"disabled\"";
    $d_checked = '';
    $m_checked = "checked=\"checked\"";
}

$domain_option = '';

for ($i = 0; $i < $email_domain_arr_count; $i++) {
    $domain = $email_domain_arr[$i];

    $domain_option .= option($domain, $domain);
}

$html = <<<html
<header>
    <h2>이메일 발송</h2>
    <button class="close" title="닫기"><img src="/design_template/images/common/btn_circle_x_white.png" alt="X"></button>
</header>
<article>
    <ul class="_emailAddressType">
        <li class="_preset">
            <label>
                <input type="radio" {$d_checked} {$disabled} name="emailAddressType" value="d">
                <h3>기본 이메일</h3>
            </label>
            <input type="text" class="account" id="d_acc" readonly value="{$d_acc}">
            <span class="symbol">@</span>
            <input type="text" class="domain" id="d_dom" readonly value="{$d_dom}">
        </li>
        <li class="_custom">
            <label>
                <input type="radio" {$m_checked} {$disabled} name="emailAddressType" value="m">
                <h3>직접 입력</h3>
            </label>
            <input type="text" id="m_acc" class="account">
            <span class="symbol">@</span>
            <input type="text" id="m_dom" class="domain" readonly>
            <select class="domain">
                {$domain_option}
                <option class="_custom" selected="selected">직접입력</option>
            </select>
        </li>
    </ul>
    <div class="function center">
        <strong><button type="button" onclick="sendEmail();">이메일 발송</button></strong>
        <button type="button" class="close">취소</button>
    </div>
    <script>
        function emailAddressType () {
            $('._emailAddressType input[type=text], ._emailAddressType select').attr('disabled', true);
            $('._emailAddressType input[type=radio]:checked').closest('li').find('input[type=text], select').attr('disabled', false);
        }
        
        function emailDomainType () {
            //domain disabled by option
            $('._emailAddressType li._custom input[type=text].domain').attr('readonly', !$('._emailAddressType select.domain option:selected').hasClass('_custom'));
            
            if ($('._emailAddressType select.domain option:selected').hasClass('_custom')) {
                //custom일 경우
                $('._emailAddressType li._custom input.domain').val('');
            } else {
                $('._emailAddressType li._custom input.domain').val($('._emailAddressType select.domain option:selected').text());
            }
        }
        
        $('._emailAddressType input[type=radio]').on('click', function () {
            emailAddressType();
        });
        
        $('._emailAddressType select').on('change', function () {
            emailDomainType();
        });
        
        if ($('._emailAddressType input[type=radio]:checked').length == 0) {
            $('._emailAddressType > li:eq(0) input[type=radio]').attr('checked', true);
        }
        
        emailAddressType();
        emailDomainType();
    </script>
</article>
html;

echo $html;

$conn->Close();
exit;
?>

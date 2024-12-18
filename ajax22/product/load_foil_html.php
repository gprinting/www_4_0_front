<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");

$fb = new FormBean();

$dvs = $fb->form("dvs");
$el = $fb->form("el");
$i = $fb->form("i");
$ec_price = $fb->form("ec_price");
$next = $i + 1;

$html  = "\n    <dl>";
$html .= "\n        <dt>박</dt>";
$html .= "\n        <dd>";
$html .= "\n            <select id=\"" . $el . "_1_" . $i . "\" name=\"" . $el . "_1_" . $i . "\" style=\"width:85px;\" class=\"_color\"";
$html .= "\n                onchange=\"foilAreaInit('" . $dvs . "', this.value, '1_" . $i . "');\">";
$html .= "\n                <option selected=\"selected\" value=\"금박유광\">금박유광</option>";
$html .= "\n                <option value=\"은박유광\">은박유광</option>";
$html .= "\n                <option value=\"금박무광\">금박무광</option>";
$html .= "\n                <option value=\"은박무광\">은박무광</option>";
$html .= "\n                <option value=\"청박유광\">청박유광</option>";
$html .= "\n                <option value=\"적박유광\">적박유광</option>";
$html .= "\n                <option value=\"녹박유광\">녹박유광</option>";
$html .= "\n                <option value=\"먹박유광\">먹박유광</option>";
$html .= "\n                <option value=\"홀로그램 은펄\">홀로그램 은펄</option>";
$html .= "\n                <option value=\"홀로그램 별\">홀로그램 별</option>";
$html .= "\n                <option value=\"홀로그램 물방울\">홀로그램 물방울</option>";
$html .= "\n            </select>";
$html .= "\n            <select id=\"" . $el . "_dvs_1_" . $i . "\" name=\"" . $el . "_dvs_1_" . $i . "\" style=\"min-width:60px;\"";
$html .= "\n                onchange=\"changeFoilDvs('" . $dvs . "', this.value);\">";
$html .= "\n";
$html .= "\n                <option value=\"단면\">단면</option>";
$html .= "\n                <option value=\"양면\">양면</option>";
$html .= "\n                <option value=\"양면다름\">양면다름</option>";
$html .= "\n            </select>";
$html .= "\n";
$html .= "\n            <label>가로 <input id=\"" . $el . "_wid_1_" . $i . "\" name=\"" . $el . "_wid_1_" . $i . "\" type=\"text\" class=\"mm\"";
$html .= "\n                    onblur=\"getAfterPrice.common('foil', '" . $dvs . "');\" value=\"0\">mm</label>";
$html .= "\n            <label>세로 <input id=\"" . $el . "_vert_1_" . $i . "\" name=\"" . $el . "_vert_1_" . $i . "\" type=\"text\" class=\"mm\"";
$html .= "\n                    onblur=\"getAfterPrice.common('foil', '" . $dvs . "');\" value=\"0\">mm</label>";
$html .= "\n  ";
$html .= "\n            <button type=\"button\" class=\"minus-btn\" id=\"minusBtn" . $i . "\" onclick=\"removeFoilSection('" . $i . "', '" . $el . "', '" . $dvs . "');\">-</button>";
if ($i != 3) {
    $html .= "\n            <button type=\"button\" class=\"plus-btn\" id=\"plusBtn" . $i . "\" onclick=\"addFoilSection('" . $next . "', '" . $el . "', '" . $dvs . "');\" style=\"right:40px;\">+</button>";
}
$html .= "\n  ";
$html .= "\n        </dd>";
$html .= "\n        <dd class=\"br note\" style=\"float:left; width:100%;\">";
$html .= "\n            File에 박 부분을 먹1도로 업로드 해주세요.";
$html .= "\n        </dd>";
$html .= "\n        <dd id=\"" . $dvs . "_foil" . $i . "_price_dd\" class=\"price\"></dd>";
$html .= "\n        <input type=\"hidden\" id=\"" . $dvs . "_foil" . $i . "_price\" name=\"" . $dvs . "_foil" . $i . "_price\" />";
$html .= "\n    </dl>";

echo $html;
?>

<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/cscenter/GuideDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new GuideDAO();
$fb = new FormBean();
$fb = $fb->getForm();

$dvs = $fb["dvs"];

switch ($dvs) {
    case "normal":
        $dvs = "일반지";
        break;
    case "high":
        $dvs = "고급지";
        break;
    case "special":
        $dvs = "특수지";
        break;
    case "sticker":
        $dvs = "스티커지";
        break;
}

$param = [];
$param["top"] = $dvs;

$rs = $dao->selectPaperDscr($conn, $param);

$html_form  = "<table class=\"paperGuide\">";
$html_form .=     "<colgroup>";
$html_form .=         "<col width=\"45%%\">";
$html_form .=         "<col width=\"11%%\">";
$html_form .=         "<col width=\"\">";
$html_form .=     "</colgroup>";
$html_form .=     "<tbody>";
$html_form .=         "<tr>";
$html_form .=             "<th class=\"paperImg\" rowspan=\"5\"><img src=\"%s\" width=\"350\" height=\"250\"></th>"; //#2 img_src
$html_form .=             "<th>종이명</th>";
$html_form .=             "<td>%s</td>"; //#1 name, dvs
$html_form .=         "</tr>";
$html_form .=         "<tr>";
$html_form .=             "<th>색상</th>";
$html_form .=             "<td>%s</td>"; //#3 color
$html_form .=         "</tr>";
$html_form .=         "<tr>";
$html_form .=             "<th>평량</th>";
$html_form .=             "<td>%s</td>"; //#4 basisweight
$html_form .=         "</tr>";
$html_form .=         "<tr>";
$html_form .=             "<th>재질설명</th>";
$html_form .=             "<td>%s</td>"; //#5 dscr
$html_form .=         "</tr>";
$html_form .=         "<tr>";
$html_form .=             "<th>용도</th>";
$html_form .=             "<td>%s</td>"; //#6 purp
$html_form .=         "</tr>";
$html_form .=     "</tbody>";
$html_form .= "</table>";

$ret = '';
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $param["name"]  = $fields["name"];
    $param["dvs"]   = $fields["dvs"];
    $param["color"] = $fields["color"];
    $paper_dvs      = $fields["dvs"];

    if (trim($paper_dvs) == "-") {
        $paper_dvs = "";
    }

    $paper_preview = $dao->selectPaperPreviewInfo($conn, $param)->fields;

    $ret .= sprintf($html_form, $paper_preview["file_path"] .  '/' . $paper_preview["save_file_name"]
                              , $fields["name"] . ' ' . $paper_dvs
                              , $fields["color"]
                              , $fields["basisweight"]
                              , $fields["dscr"]
                              , $fields["purp"]);

    $rs->MoveNext();
}

echo $ret;

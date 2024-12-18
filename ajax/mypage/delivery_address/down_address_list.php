<?
/*
 * Copyright (c) 2018 Nexmotion, Inc.
 * All rights reserved.
 *
 * 주소록 csv 다운로드
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2018/03/13 이청산 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberDlvrDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberDlvrDAO();
$util = new CommonUtil();

$param = array();
$param["seqno"] = $fb->session("org_member_seqno");

// 파일관련
$path = $_SERVER["DOCUMENT_ROOT"] . "/excel_template/";
$name = uniqid() . ".csv";

$fd = fopen($path . $name, 'w');

if (!$fd) {
    echo "<script>alert('파일생성실패');</script>";
    exit;
}

// csv 전체
$list = array();

// csv 헤드
$csv_head = array();

$head_1 = iconv("UTF-8", "EUC-KR", "업체 및 상호");
$head_2 = iconv("UTF-8", "EUC-KR", "담당자");
$head_3 = iconv("UTF-8", "EUC-KR", "연락처");
$head_4 = iconv("UTF-8", "EUC-KR", "휴대전화");
$head_5 = iconv("UTF-8", "EUC-KR", "우편번호");
$head_6 = iconv("UTF-8", "EUC-KR", "주소");
$head_7 = iconv("UTF-8", "EUC-KR", "상세주소");

for ($i = 1; $i < 8; $i++) {
    array_push($csv_head, ${"head_" . $i});
}

// csv 헤드에 입력
$list[] = $csv_head;

// csv 메인

// 배송지 검색
$rs = $dao->selectDlvrList($conn, $param);

while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $body_0 = iconv("UTF-8", "EUC-KR", $fields["dlvr_name"]);
    $body_1 = iconv("UTF-8", "EUC-KR", $fields["recei"]);
    $body_2 = iconv("UTF-8", "EUC-KR", $fields["tel_num"]);
    $body_3 = iconv("UTF-8", "EUC-KR", $fields["cell_num"]);
    $body_4 = iconv("UTF-8", "EUC-KR", $fields["zipcode"]);
    $body_5 = iconv("UTF-8", "EUC-KR", $fields["addr"]);
    $body_6 = iconv("UTF-8", "EUC-KR", $fields["addr_detail"]);

    $csv_body = array();
    for ($j = 0; $j < 7; $j++) {
        array_push($csv_body, ${"body_" . $j});
    }

    $list[] = $csv_body;

    $rs->MoveNext();
}

foreach($list as $fields) {
    fputcsv($fd, $fields);
}

/*
$csv_form = "%s,%s,%s,%s,%s,%s,%s\r\n";

$csv_head = sprintf($csv_form, iconv("UTF-8", "EUC-KR", "업체 및 상호")
                             , iconv("UTF-8", "EUC-KR", "담당자")
                             , iconv("UTF-8", "EUC-KR", "연락처")
                             , iconv("UTF-8", "EUC-KR", "휴대전화")
                             , iconv("UTF-8", "EUC-KR", "우편번호")
                             , iconv("UTF-8", "EUC-KR", "주소")
                             , iconv("UTF-8", "EUC-KR", "상세주소"));
fwrite($fd, $csv_head);

$rs = $dao->selectBasicDlvr($conn, $param);

$csv_body = '';
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    

    // 엑셀 내용
    $body = sprintf($csv_form, iconv("UTF-8", "EUC-KR", $fields["dlvr_name"])
                             , iconv("UTF-8", "EUC-KR", $fields["recei"])
                             , iconv("UTF-8", "EUC-KR", $fields["tell_num"])
                             , iconv("UTF-8", "EUC-KR", $fields["cell_num"])
                             , iconv("UTF-8", "EUC-KR", $fields["zipcode"])
                             , iconv("UTF-8", "EUC-KR", $fields["addr"])
                             , iconv("UTF-8", "EUC-KR", $fields["addr_detail"]));

    $csv_body .= $body;
    fwrite($fd, $body);

    $rs->MoveNext();
}
*/

$member_name = $fb->session("name");

//엑셀파일명
$file_name = $member_name . "_배송지목록" . ".csv";

if ($util->isIe()) {
    $file_name = $util->utf2euc($file_name);
}

header("Pragma: public");
header("Expires: 0");
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=". $file_name ."");

// 파일생성 하고 다운
flush();
readfile($path . $name);

unlink($path . $name);

$conn->Close();
exit;
?>

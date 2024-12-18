<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/mypage/MemberDlvrDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberDlvrDAO();
$member_seqno = $fb->session("member_seqno");
$check = 1; // 1일 경우 성공

// csv 파일을 변환하여 DB입력

$csv = array();

if (isset($_FILES["csv"])) {

    if ($_FILES["csv"]["error"] == 0) {

        $name = $_FILES['csv']['name'];
        $ext  = strtolower(end(explode('.', $_FILES['csv']['name'])));
        $type = $_FILES['csv']['type'];
        $tmpName = $_FILES['csv']['tmp_name'];

        // csv 확장자인지 확인
        if ($ext === 'csv') {
            if (($handle = fopen($tmpName, 'r')) !== FALSE) {
                set_time_limit(0);

                // csv 확장자라면 먼저 기존에 있던 주소록을 싹 지운다
                $conn->StartTrans(); 

                $param = array();
                $param["member_seqno"] = $member_seqno;
                
                $rm_rs = $dao->deleteDlvrForCsv($conn, $param);

                if (!$rm_rs) {
                    // 0일 경우 제대로 지워지지 않음
                    $check = 0;
                    $conn->FailTrans(); 
                    $conn->RollbackTrans();
                }

                // 시작 로우
                $row = 0;
                $csv_row = "";
                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    
                    $col_count = count($data);

                    if ($row > 0) {

                        for ($i = 0; $i < $col_count; $i++) {
                            if (empty($data[$i])) {
                                $data[$i] = " ";
                            }
                        }

                        $csv_str  = "('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'),";
                        $csv_row .= sprintf($csv_str
                                            , iconv("EUC-KR", "UTF-8", $data[0])
                                            , iconv("EUC-KR", "UTF-8", $data[1])
                                            , iconv("EUC-KR", "UTF-8", $data[2])
                                            , iconv("EUC-KR", "UTF-8", $data[3])
                                            , iconv("EUC-KR", "UTF-8", $data[4])
                                            , iconv("EUC-KR", "UTF-8", $data[5])
                                            , iconv("EUC-KR", "UTF-8", $data[6])
                                            , 'N'
                                            , date("Y-m-d H:i:s")
                                            , $member_seqno);

                    }

                    $row++;
                }
                //echo substr($csv_row, 0, -1);
                // 완성된 데이터값
                $compl_csv = substr($csv_row, 0, -1);
                
                $ins_param = array();
                $ins_param["compl_csv"] = $compl_csv;
                $conn->debug = 1;
                $ins_rs = $dao->insertDlvrForCsv($conn, $ins_param);
                if (!$ins_rs) {
                    // 2일 경우 제대로 입력되지 않음
                    $check = 2;
                    $conn->FailTrans();
                    $conn->RollbackTrans();
                }

                $conn->CompleteTrans();
                fclose($handle);
            }

        } else {
            // 3일 경우 파일형식이 올바르지 않음
            $check = 3;
        }

    } else {
        echo "Return code : ". $_FILES["file_csv"]["error"] ."<br / >";
    }
} else {
    // 4일 경우 선택된 파일이 없음
    $check = 4;
}

if ($check == 1) {
    echo "<script>alert('주소록이 등록되었습니다.');</script>";
} else if ($check == 0) {
    echo "<script>alert('주소록 삭제에 실패했습니다.');</script>";
} else if ($check == 2) {
    echo "<script>alert('주소록 입력에 실패했습니다.');</script>";
} else if ($check == 3) {
    echo "<script>alert('파일 형식이 올바르지 않습니다.');</script>";
} else if ($check == 4) {
    echo "<script>alert('선택된 파일이 없습니다.');</script>";
}
echo "<script>history.back(-1);</script>";

$conn->close();

?>

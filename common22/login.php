<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common22/sess_common.php");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/doc/front/common/common22.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/PasswordEncrypt.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/LoginUtil.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$frontUtil = new FrontCommonUtil();
$commonUtil = new CommonUtil();
$dao = new FrontCommonDAO();

//$conn->debug=1;

$seqno = $fb->form("seqno");
$flag = $fb->form("flag");
$isadmin = $fb->form("isadmin");
$updateorder = $fb->form("updateorder");
$mode = $fb->form("mode");

$success = "true";


if($isadmin == "Y") {
    $fb->addSession("isadmin", true);
}

$idc = 'N';

// ERP에서 웹로그인 할 시 필요함(else 부분)
if (empty($seqno)) {
    $id = $fb->form("id");
    $pw = $fb->form("pw");
    $id_save = $fb->form("id_save");
    $rs = $dao->selectMember($conn, ["id" => $id]);

    $pw_hash = $rs->fields["passwd"];

    if (password_verify($pw, $pw_hash) === false) {
    //if (password_verify($pw, $pw_hash) === true) {
        $success = "false";
        goto END;
    }

    // 180614 추가 : 아이디 형식의 회원일 경우 2.0데이터 연동여부를 물어보는 로직 추가  
    $find_item = '@';
    $id_check  = strpos($id, $find_item);  

    if ($id_check === false) {
        $member_dvs = $rs->fields["member_dvs"];
        $cell_num   = $rs->fields["cell_num"];
        $tel_num    = $rs->fields["tel_num"];
        $addr       = $rs->fields["addr"];
        $mail       = $rs->fields["mail"];
        $member_seqno = $rs->fields["member_seqno"];
        if ($member_dvs == "개인회원") {
            // 메일 혹은 휴대폰, 전화번호 혹은 주소가 비었을 경우
            // 2.0 연동여부를 물어본다
            if (empty($mail) || (empty($cell_num) && empty($tel_num) || empty($addr))) {
                $idc = 'Y';
            } 
        } else if ($member_dvs == "기업회원") {
            $param = [];
            $param["seqno"] = $member_seqno;
            $licensee_rs = $dao->selectOfficeMemberInfo($conn, $param);

            if (empty($licensee_rs)) {
                $idc = 'Y';
            }  
        }
    }
    
} else {
    $rs = $dao->selectMember($conn, ["seqno" => $seqno]);

     if (password_verify(ADMIN_FLAG[0], $flag) === false) {
    //if (password_verify(ADMIN_FLAG[0], $flag) === true) {
        $success = "false";
        goto END;
    }

    $id = $rs->fields["id"];
   
}

if ($idc == "Y") {
    $_SESSION["sync_flag"] = "Y";
}

if ($id_save === "Y") {
    //expire 차후 조정
    setcookie("id", $id, time()+864000, "/");
} else {
    setcookie("id","",0, "/");
}

$loginUtil = new LoginUtil([
     "res" => $rs->fields
    ,"dvs" => "email"
]);

if (!$loginUtil->login()) {
    goto ERR;
}

goto END;

ERR: 
    echo $loginUtil->err_msg;
    exit;

END:
    if(empty($isadmin)) {
        $referer = $_SERVER["HTTP_REFERER"];
        if (strpos($referer, "login.html") !== false) {
            $referer = "/main22/main.html";
        }
        $referer = explode("#", $referer)[0];

        $ret  = '{';
        $ret .= " \"success\" : %s,";
        $ret .= " \"ref\"     : \"%s\",";
        $ret .= " \"current\"     : \"%s\",";
        $ret .= " \"gubun\"     : \"%s\"";
        $ret .= '}';
    
        echo sprintf($ret, $success, $referer, $_SERVER["SELL_SITE"], $channel);
        } else {
        //header("Location: http://goodprinting.co.kr/");
        //header("Location: http://devfront.goodprinting.co.kr/");
        if($mode == "order") {
            header("Location: http://new.gprinting.co.kr/product22/esti_sheet.html?cs=010001001");
        } else if (empty($updateorder)) {
            header("Location: http://new.gprinting.co.kr/");
        } else {
            $param['order_common_seqno'] = $updateorder;
            $page_rs = $dao->selectOrderCatePage($conn, $param);
            $page = $page_rs->fields["page"];
            $cate_sortcode = $page_rs->fields["cate_sortcode"];

            header("Location: http://new.gprinting.co.kr" . $page . "?cs=" . $cate_sortcode . "&order_common_seqno=" . $updateorder);
        }
    }

    $conn->Close();
    exit;
?>

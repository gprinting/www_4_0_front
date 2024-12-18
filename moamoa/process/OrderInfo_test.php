<?

define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/MoamoaDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MoamoaDAO();
$fb = new FormBean();

$param = [];
$param['ordernum'] = $fb->form("OrderNum");
$param['isaccept'] = $fb->form("IsAccept");
$param['empl_id'] = $fb->form("User");

$is_accept = $fb->form("IsAccept");

$state_code = "200";
$rs = $dao->selectProductInfo($conn, $param);
$rs["ExceptSide"] = 0;

if($rs == null) {
    $state_code = "400";
}

if(intval($rs["order_state"]) >= 2220)
    $state_code = "500";

if($rs["PaperName"] == null) {
    $param["order_common_seqno"] = $rs["order_common_seqno"];
    $rs1 = $dao->selectEstiPaperInfo($conn, $param);
    $rs["PaperName"] = $rs1->fields["paper_info"];
}

foreach($rs as $key => $value) {
    if(is_int($key)) {
        unset($rs[$key]);
    }
}
$param["order_common_seqno"] = $rs['order_common_seqno'];
$side = $rs["Side"];
if($rs["Side"] == "양면" || strpos($rs["ProductCode"], '004003') !== false) {
    $rs["Side"] = 2 * $rs["CaseValue"];
} else {
    $rs["Side"] = 1 * $rs["CaseValue"];
}

if($rs["ItemName"] == "명함") {
    $rs["PDFPath"] = "/명함";
    if($rs["ProductName"] == "일반명함") {
        if($rs["PaperName"] == "비코팅일반명함 스노우지 백색(무코팅) 250g") {
            $rs["PDFPath"] .= "/무코팅/" . $rs["stan_name"] . "/" . $rs["Tmpt"] . "도";
        }

        if($rs["PaperName"] == "코팅일반명함 스노우지 백색(무광코팅) 250g") {
            $rs["PDFPath"] .= "/코팅/". $rs["stan_name"] . "/" . $rs["Tmpt"] . "도";
        }
    }
    else if($rs["ProductName"] == "수입명함") {
        $rs["PDFPath"] .= "/수입지/" . $rs["PaperName"] . "/" . $rs["stan_name"];
        $rs["PDFPath"] .= "/" . $side;
    }
    else if($rs["ProductName"] == "카드명함") {
        $rs["PDFPath"] .= "/수입지/" . $rs["PaperName"];
        $rs["PDFPath"] .= "/" . $side;

        if(strpos($rs["PaperName"],"골드플러스") !== false ||
            strpos($rs["PaperName"],"실버플러스") !== false ||
            (strpos($rs["PaperName"],"금펄반누드") === false && strpos($rs["PaperName"],"은펄반누드") === false && strpos($rs["PaperName"],"반누드") !== false) ||
            strpos($rs["PaperName"],"누드플러스") !== false) {
            $rs["Side"] = $rs["Side"] * 2;
        }
    }
    $rs["PDFPath"] .= "/" . $rs["QuantityName"];
} else if($rs["ProductName"] == "합판전단지") {
    if($rs["PaperName"] == "아트지 백색 90g" && $rs["SizeCode"] != "2절" && $rs["SizeCode"] != "A2") {
        $rs["PDFPath"] = "/합판전단지";
        if($rs["SizeCode"][0] == 'A') {
            $rs["PDFPath"] .= "/국전지/";
        } else {
            $rs["PDFPath"] .= "/46전지/";
        }

        $rs["SizeCode"] = explode("(", $rs["SizeCode"])[0];
        $rs["SizeName"] = explode("(", $rs["SizeName"])[0];
        $sizecode = $rs["SizeCode"];

        if($rs["SizeCode"] == "A4" || $rs["SizeCode"] == "A5" || $rs["SizeCode"] == "A6") {
            $sizecode = "A4";
        }
        if($rs["SizeCode"] == "16절" || $rs["SizeCode"] == "32절" || $rs["SizeCode"] == "64절") {
            $sizecode = "16절";
        }

        $rs["PDFPath"] .= $side . "/" . $sizecode;

        if($rs["SizeCode"] == "A4" || $rs["SizeCode"] == "A5" || $rs["SizeCode"] == "A6") {
            if($rs["SizeCode"] == "A4" && $rs["QuantityValue"] <= 2000) $rs["PDFPath"] .= "/2,000매(0.5연)";
            if($rs["SizeCode"] == "A4" && $rs["QuantityValue"] > 2000) $rs["PDFPath"] .= "/4,000매(1연)";
            if($rs["SizeCode"] == "A5" && $rs["QuantityValue"] <= 4000) $rs["PDFPath"] .= "/2,000매(0.5연)";
            if($rs["SizeCode"] == "A5" && $rs["QuantityValue"] > 4000) $rs["PDFPath"] .= "/4,000매(1연)";
            if($rs["SizeCode"] == "A6" && $rs["QuantityValue"] <= 8000) $rs["PDFPath"] .= "/2,000매(0.5연)";
            if($rs["SizeCode"] == "A6" && $rs["QuantityValue"] > 8000) $rs["PDFPath"] .= "/4,000매(1연)";
        } else if($rs["SizeCode"] == "A3") {
            if($rs["SizeCode"] == "A3" && $rs["QuantityValue"] % 4000 == 0) $rs["PDFPath"] .= "/4,000매(2연)";
            if($rs["SizeCode"] == "A3" && $rs["QuantityValue"] % 4000 == 2000) $rs["PDFPath"] .= "/2,000매(1연)";
        } else if($rs["SizeCode"] == "8절") {
            if($rs["SizeCode"] == "8절" && $rs["QuantityValue"] % 8000 == 0) $rs["PDFPath"] .= "/8,000매(2연)";
            if($rs["SizeCode"] == "8절" && $rs["QuantityValue"] % 8000 == 4000) $rs["PDFPath"] .= "/4,000매(1연)";
        } else if($rs["SizeCode"] == "16절" || $rs["SizeCode"] == "32절" || $rs["SizeCode"] == "64절") {
            if($rs["SizeCode"] == "16절" && $rs["QuantityValue"] <= 4000) $rs["PDFPath"] .= "/4,000매(0.5연)";
            if($rs["SizeCode"] == "16절" && $rs["QuantityValue"] > 4000) $rs["PDFPath"] .= "/8,000매(1연)";
            if($rs["SizeCode"] == "32절" && $rs["QuantityValue"] <= 8000) $rs["PDFPath"] .= "/4,000매(0.5연)";
            if($rs["SizeCode"] == "32절" && $rs["QuantityValue"] > 8000) $rs["PDFPath"] .= "/8,000매(1연)";
            if($rs["SizeCode"] == "64절" && $rs["QuantityValue"] <= 16000) $rs["PDFPath"] .= "/4,000매(0.5연)";
            if($rs["SizeCode"] == "64절" && $rs["QuantityValue"] > 16000) $rs["PDFPath"] .= "/8,000매(1연)";
        }
        $rs["QuantityName"] = number_format($rs["QuantityValue"]) . "매(" . $rs["amt"] * 10 / 10 . $rs["amt_unit_dvs"] . ")";
    } else {
        $rs["ExceptSide"] = 1;
        $rs["PDFPath"] = "/상업인쇄/합판";
        if($rs["SizeCode"][0] == 'A') {
            $rs["PDFPath"] .= "/국전지/";
        } else {
            $rs["PDFPath"] .= "/46전지/";
        }

        $rs["PDFPath"] .= $rs["PaperName"];
        $rs["QuantityName"] = number_format($rs["QuantityValue"]) . "매(" . $rs["amt"] * 10 / 10 . $rs["amt_unit_dvs"] . ")";
    }
} else if($rs["ProductName"] == "초소량인쇄") {
    $rs["ExceptSide"] = 1;
    $rs["PDFPath"] .= "/상업인쇄/초소량인쇄/" . $rs["PaperName"];
} else if($rs["ProductName"] == "A형 철재입간판 SET") {
    $rs["ExceptSide"] = 1;
    $rs["PDFPath"] .= "/실사출력/A형 입간판/" . $rs["PaperName"];
} else if($rs["ProductName"] == "독판전단지") {
    $rs["ExceptSide"] = 1;
    $rs["PDFPath"] = "/상업인쇄/독판";
    if($rs["SizeCode"][0] == 'A') {
        $rs["PDFPath"] .= "/국전지/";
    } else {
        $rs["PDFPath"] .= "/46전지/";
    }

    $rs["PDFPath"] .= $rs["PaperName"];
    $rs["QuantityName"] = number_format($rs["QuantityValue"]) . "매(" . $rs["amt"] * 10 / 10 . $rs["amt_unit_dvs"] . ")";
} else if($rs["ProductName"] == "메모지") {
    $rs["PDFPath"] = "/상업인쇄/메모지/";
    $rs["PDFPath"] .= $rs["PaperName"];
    $rs["QuantityName"] = number_format($rs["QuantityValue"]) . "매(" . $rs["amt"] * 10 / 10 . $rs["amt_unit_dvs"] . ")";
} else if($rs["ProductName"] == "문고리") {
    $rs["PDFPath"] = "/상업인쇄/문고리/";
    $rs["PDFPath"] .= $rs["PaperName"];
    $rs["QuantityName"] = number_format($rs["QuantityValue"]) . "매(" . $rs["amt"] * 10 / 10 . $rs["amt_unit_dvs"] . ")";
} else if($rs["ItemName"] == "스티커") {
    if(strpos($rs["WMemo"], "독판") > -1) {
        $rs["PDFPath"] = "/스티커/독판/" . $rs["RDiv"] ;
    } else {
        $rs["PDFPath"] = "/스티커/" . $rs["RDiv"] ;
    }
    $coating = $dao->selectCoatingInfo($conn, $param);
    if($coating == "코팅" && $rs["PaperName"] == "아트지 백색 90g") {
        $rs["PaperName"] .= "(유광코팅)";
    }

    if($rs["PaperName"] == "아트지 백색 90g") {
        $rs["PDFPath"] .= "/무코팅";
    } else if($rs["PaperName"] == "아트지 백색 90g(유광코팅)") {
        $rs["PDFPath"] .= "/코팅";
    } else {
        $rs["PDFPath"] .= "/특수지/" . $rs["PaperName"];
    }

    if(strpos($rs["ProductCode"], "004001") !== false || strpos($rs["ProductCode"], "004002") !== false) {
        if(strpos($rs["Detail"], "원터치") !== false) $rs["SizeName"] = $rs["RDiv"] = "원터치";
        if(strpos($rs["Detail"], "투터치") !== false) $rs["SizeName"] = $rs["RDiv"] = "투터치";
        if($rs["PaperName"] == "아트지 백색 90g" || $rs["PaperName"] == "아트지 백색 90g(유광코팅)") {
            $rs["PDFPath"] .= "/사각재단";
        }
    } else if(strpos($rs["ProductCode"], "004003") !== false) {
        if($rs["ProductCode"] == "004003001") {
            $rs["Side"] = 1 * $rs["CaseValue"];
            $rs["SizeName"] = "보험도무송";
            $rs["PDFPath"] .= "/보험도무송";
            $rs["RDiv"] = "도무송";
        }
        else {
            $rs["Side"] = 2 * $rs["CaseValue"];
            $rs["SizeName"] = "도무송";
            $rs["RDiv"] = "도무송";
            if($rs["PaperName"] == "아트지 백색 90g" || $rs["PaperName"] == "아트지 백색 90g(유광코팅)") {
                $rs["PDFPath"] .= "/도무송";
            }
        }
    }

    if(strpos($rs["ProductCode"], "004003001") === false)
        $rs["PDFPath"] .= "/" . number_format($rs["QuantityValue"]) . "매";

} else if($rs["ItemCode"] == "006002" && $rs["ItemName"] == "봉투") {
    /*
     * case "레자크 #91(체크)-백색-110-0":
                                this.PaperName = "#91 백색 레쟈크";
                                break;
                            case "레자크 #92(줄)-백색-110-0":
                                this.PaperName = "#92 백색 레쟈크";
                                break;
                            case "모조지-백색-100-0":
                                this.PaperName = "100 모조";
                                break;
                            case "모조지-백색-120-0":
                                this.PaperName = "120 모조";
                                break;
                            case "모조지-백색-150-0":
                                this.PaperName = "150 모조";
                                break;
     */
    $paper_name = $rs["PaperName"];
    if(strpos($rs["PaperName"],"레자크") !== false) {
        $paper_name = $rs["PaperDvs"];
    }

    switch ($paper_name) {
        case "#91(체크)":
            $paper_name = "#91 백색 레쟈크";
            break;
        case "#92(줄)":
            $paper_name = "#92 백색 레쟈크";
            break;
        case "모조지 백색 100g":
            $paper_name = "100 모조";
            break;
        case "모조지 백색 120g":
            $paper_name = "120 모조";
            break;
        case "모조지 백색 150g":
            $paper_name = "150 모조";
            break;
    }
    $rs["PaperName"] = $paper_name;
    $rs["PDFPath"] .= "/마스타" . $rs["ItemName"] . "/" . $rs["PaperName"];
} else if($rs["ItemName"] == "봉투") {
    /*
     * case "레자크 #91(체크)-백색-110-0":
                                this.PaperName = "#91 백색 레쟈크";
                                break;
                            case "레자크 #92(줄)-백색-110-0":
                                this.PaperName = "#92 백색 레쟈크";
                                break;
                            case "모조지-백색-100-0":
                                this.PaperName = "100 모조";
                                break;
                            case "모조지-백색-120-0":
                                this.PaperName = "120 모조";
                                break;
                            case "모조지-백색-150-0":
                                this.PaperName = "150 모조";
                                break;
     */
    $paper_name = $rs["PaperName"];
    if(strpos($rs["PaperName"],"레자크") !== false) {
        $paper_name = $rs["PaperDvs"];
    }

    switch ($paper_name) {
        case "#91(체크)":
            $paper_name = "#91 백색 레쟈크";
            break;
        case "#92(줄)":
            $paper_name = "#92 백색 레쟈크";
            break;
        case "모조지 백색 100g":
            $paper_name = "100 모조";
            break;
        case "모조지 백색 120g":
            $paper_name = "120 모조";
            break;
        case "모조지 백색 150g":
            $paper_name = "150 모조";
            break;
        case "모조지 백색(합판) 100g":
            $paper_name = "100 모조";
            break;
        case "모조지 백색(합판) 120g":
            $paper_name = "120 모조";
            break;
        case "모조지 백색(합판) 150g":
            $paper_name = "150 모조";
            break;
    }
    $rs["PaperName"] = $paper_name;
    $rs["PDFPath"] .= "/" . $rs["ItemName"] . "/" . $rs["PaperName"];
} else if($rs["ProductName"] == "복권") {
    $cnt = $rs["QuantityValue"] / 500;
    $rs["CaseValue"] = $cnt;
    $rs["CaseCode"] = $cnt;
    $rs["CaseName"] = $cnt . "건";
    $rs["QuantityName"] = "500매";
    $rs["QuantityCode"] = "500";
    $rs["QuantityValue"] = "500";
    $rs["Side"] = $cnt * ($side == "양면" ? 2 : 1);
    $rs["PDFPath"] = "/명함/코팅/복권/" . $rs["Tmpt"] . "도" . "/" . $rs["QuantityName"];

} else if($rs["ProductName"] == "합판 문어발") {
    $rs["PDFPath"] = "/합판전단지/국전지/" . $rs["ProductName"] . "/" . $side . "/" . $rs["PaperName"];

} else {
    if($rs["PaperName"] == null)
        $rs["PaperName"] = explode(" / ", $rs["Detail"])[1];
    $rs["PDFPath"] .= "/" . $rs["ItemName"] . "/" . $rs["ProductName"] . "/" . $rs["PaperName"];
}

if(startsWith($rs["ProductCode"], "006002")
    || startsWith($rs["ProductCode"], "007")
    || startsWith($rs["ProductCode"], "002")) {
    $rs["ExceptSide"] = 1;
}

$rs["Aworks"] = $dao->selectOrderAfterInfo($conn, $param);
if($rs["ItemName"] == "봉투")
    $rs["Aworks"] = str_replace("접착","양면테입",$rs["Aworks"]);
if($rs['member_seqno'] == "1976" && strpos($rs["Aworks"],"오시") !== false) {
    $rs["Aworks"] = str_replace("오시","도무송오시",$rs["Aworks"]);
}
if($rs["ItemName"] == "봉투")
    $rs["Aworks"] = str_replace("접착","양면테입",$rs["Aworks"]);

if($rs["ProductName"] == "합판 문어발") {
    if($rs["Aworks"] != "")
        $rs["Aworks"] .= ",";
    $rs["Aworks"] .= explode(" / ", $rs["Detail"])[2];
}

if($rs["ProductCode"] == "004001001") {
    if($rs["Aworks"] == "후지반칼 - 지정안함") {
        $rs["Aworks"] = "";
    }
}

if($rs["ProductCode"] == "008001005")
    $rs["Aworks"] = "문고리가공";
$rs["PDFPath"] .= "/";

if($rs["ProductCode"] == "008001005")
    $rs["Aworks"] = "문고리가공";

if($rs["member_seqno"] == 6178)
    $rs["PDFPath"] = "/이중하테스트" . $rs["PDFPath"];

// 당일판
$opt = $dao->selectOrderOptInfo($conn, $param);
if(strpos($opt, '당일판') !== false) {
    $rs["PDFPath"] = "/당일판" . $rs["PDFPath"];
    $rs["Aworks"] = "당일판" . ($rs["Aworks"] == '' ? "" : " / ") . $rs["Aworks"];
} else if(strpos($opt, '빠른출고') !== false) {
    $rs["PDFPath"] = "/디지털 빠른출고" . $rs["PDFPath"];
    $rs["Aworks"] = "빠른출고" . ($rs["Aworks"] == '' ? "" : " / ") . $rs["Aworks"];
}

//↙↖↗↘
$rs["Aworks"] = str_replace("좌상","↖",$rs["Aworks"]);
$rs["Aworks"] = str_replace("우상","↗",$rs["Aworks"]);
$rs["Aworks"] = str_replace("좌하","↙",$rs["Aworks"]);
$rs["Aworks"] = str_replace("우하","↘",$rs["Aworks"]);


if ($rs["dlvr_way"] == "01") {
    if($rs["dlvr_sum_way"] == "01") {
        $rs["Method"] = "택선";
    } else if ($rs["dlvr_sum_way"] == "02") {
        $rs["Method"] = "택착";
    } else {
        $rs["Method"] = "택배";
    }
} else if ($rs["dlvr_way"] == "02"){
    $rs["Method"] = $rs["invo_cpn"];
} else if ($rs["dlvr_way"] == "03"){
    $rs["Method"] = "화물";
} else if ($rs["dlvr_way"] == "04"){
    $rs["Method"] = "퀵(오토바이)";
} else if ($rs["dlvr_way"] == "05"){
    $rs["Method"] = "퀵(지하철)";
} else if ($rs["dlvr_way"] == "06"){
    $rs["Method"] = "방문(인현동)";
} else if ($rs["dlvr_way"] == "07"){
    $rs["Method"] = "방문(성수동)";
}

if($rs["BaseDelivery"] == "롯데택배" || $rs["BaseDelivery"] == "CJ택배")
    $rs["BaseDelivery"] = "택배";

$rs["BaseDelivery"] = str_replace("서울배송 ","A",$rs["BaseDelivery"]);
$rs["BaseDelivery"] = str_replace("서울배송","A",$rs["BaseDelivery"]);
$rs["BaseDelivery"] = str_replace("직배 ","B",$rs["BaseDelivery"]);
$rs["BaseDelivery"] = str_replace("인현동방문 ","인현",$rs["BaseDelivery"]);
$rs["BaseDelivery"] = str_replace("성수동방문 ","성수",$rs["BaseDelivery"]);

$param["size"] = $rs['size'];
$param["member_seqno"] = $rs['member_seqno'];
$param["origin_file_name"] = $rs['origin_file_name'];

if($rs["ProductCode"] == "007001001" || $rs["ProductCode"] == "007001002" || $rs["ProductCode"] == "007001003") {
    if(strpos($rs["Detail"], '내용 같음') !== false) {
        $rs["Detail"] = str_replace(' / 내용 같음','',$rs["Detail"]);
        $rs["Detail"] = "내용 같음 / " .$rs["Detail"] ;
    }

    if(strpos($rs["Detail"], '내용 다름') !== false) {
        $rs["Detail"] = str_replace(' / 내용 다름','',$rs["Detail"]);
        $rs["Detail"] = "내용 다름 / " .$rs["Detail"];
    }
}

if($param['isaccept'] == 'Y') {
    $rs["OneFile"] = $dao->selectOneFile($conn, $param);
    $dao->updateOrderManager($conn, $param);
    $rs["ProductName"] = $rs["Detail"];
} else {
    $rs["ProductName"] = $rs["Detail"];

}

$result = array();
$result["code"] = $state_code;
$result["message"] = "ok";

$data = array();
$data["data"] = $rs;

$ret["result"] = $result;
$ret["data"] = $rs;

echo json_encode($ret);

$conn->Close();

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}
?>

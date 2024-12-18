<?

define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/classes/dprinting/PriceCalculator/Common/DPrintingFactory.php');
include_once(INC_PATH . "/com/nexmotion/job/front/order/CartDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new CartDAO();
$fb = new FormBean();
$fb = $fb->getForm();

//$fb["cate_sortcode"];
$enable_auto = false;
$has_after = false;
switch ($fb["Ca_Code"]) {
    case "N11" :
    case "N12" :
    case "N14" :
        $fb["cate_sortcode"] = "003001001"; //V
        $enable_auto = true;
        break;
    case "N20" :
        $fb["cate_sortcode"] = "003002001"; //V
        $enable_auto = true;
        break;
    case "N30" :
        $fb["cate_sortcode"] = "003003001"; //V
        break;
    case "C02" :
        $fb["cate_sortcode"] = "001002001"; //V
        break;
    case "C04" :
        $fb["cate_sortcode"] = "001003001"; // V
        break;
    case "B01" :
        $fb["cate_sortcode"] = "005001001"; //V
        $enable_auto = true;
        break;
    case "B03" :
        $fb["cate_sortcode"] = "011002001"; // V
        break;
    case "X12" :
        $fb["cate_sortcode"] = "002002003";
        break;
    case "X18" :
        $fb["cate_sortcode"] = "002006001";
        break;
    default :
        $fb["cate_sortcode"] = "";
}

if($fb["Ca_Code"] == "E10") {
    if($fb["S_Code"] == "EC1") {
        $fb["cate_sortcode"] = "006001001"; //V
    }

    if($fb["S_Code"] == "EC4") {
        $fb["cate_sortcode"] = "006001003"; //V
    }

    if($fb["S_Code"] == "EC5") {
        $fb["cate_sortcode"] = "006001005"; //V
    }

    if($fb["S_Code"] == "EC7") {
        $fb["cate_sortcode"] = "006001004"; //V
    }
}

$factory = new DPrintingFactory();
$product = $factory->create($fb["cate_sortcode"]);


$AGroup_arr = $fb["AGroup_1_Sel"];
$count_AGroup_arr = count($AGroup_arr);
$sortcode_t = substr($fb["cate_sortcode"], 0, 3);

$param = array();

if($fb["cate_sortcode"] == "001002001") {
    if($fb["Coating"] == "단면유광코팅" || $fb["Coating"] == "단면무광코팅") {
        $param["after_name"] .= "coating|";
        $param["aft_depth"] .= "코팅|";
        if($fb["Coating"] == "단면유광코팅")
            $param["aft_depth1_val"] .= "859|";
        if($fb["Coating"] == "단면무광코팅")
            $param["aft_depth1_val"] .= "861|";
        $param["aft_depth1_wid"] .= "|";
        $param["aft_depth1_vert"] .= "|";
        $param["aft_depth2_wid"] .= "|";
        $param["aft_depth2_vert"] .= "|";
        $param["aft_depth1_dvs"] .= "|";
        $param["aft_depth2_dvs"] .= "|";
    }
}

if($fb["cate_sortcode"] == "011002001") {
    if($fb["Coating"] == "단면유광코팅" || $fb["Coating"] == "단면무광코팅") {
        $param["after_name"] .= "coating|";
        $param["aft_depth"] .= "코팅|";
        if($fb["Coating"] == "단면유광코팅")
            $param["aft_depth1_val"] .= "8212|";
        if($fb["Coating"] == "단면무광코팅")
            $param["aft_depth1_val"] .= "8218|";
        $param["aft_depth1_wid"] .= "|";
        $param["aft_depth1_vert"] .= "|";
        $param["aft_depth2_wid"] .= "|";
        $param["aft_depth2_vert"] .= "|";
        $param["aft_depth1_dvs"] .= "|";
        $param["aft_depth2_dvs"] .= "|";
    }
}

for ($j = 0; $j < $count_AGroup_arr; $j++) {
    if($fb["cate_sortcode"] == "001002001" && $AGroup_arr[$j] == "31") {
        $param["after_name"] .= "foldline|";

        if($fb["AGroup_2"][1] == "03") {
            $param["aft_depth"] .= "2단접지|";
            $param["aft_depth1_val"] .= "8357|";
        }
        if($fb["AGroup_2"][1] == "04") {
            $param["aft_depth"] .= "3단접지|";
            $param["aft_depth1_val"] .= "8361|";
        }
        if($fb["AGroup_2"][1] == "05") {
            $param["aft_depth"] .= "N접지|";
            $param["aft_depth1_val"] .= "8362|";
        }

        $param["aft_depth1_val"] .= "|";
        $param["aft_depth1_wid"] .= "|";
        $param["aft_depth1_vert"] .= "|";
        $param["aft_depth1_dvs"] .= "|";
        $param["aft_depth2_wid"] .= "|";
        $param["aft_depth2_vert"] .= "|";
        $param["aft_depth2_dvs"] .= "|";
    }

    if($AGroup_arr[$j] == "01") {
        if($sortcode_t == "003") {
            // 귀돌이
            $param["after_name"] .= "rounding|";
            $param["aft_depth"] .= "귀도리|";
            $param["aft_depth1_wid"] .= "|";
            $param["aft_depth1_vert"] .= "|";
            $param["aft_depth2_wid"] .= "|";
            $param["aft_depth2_vert"] .= "|";
            $param["aft_depth1"] .= "|";
            $param["aft_depth2_dvs"] .= "|";
            $i = 0;

            if($fb["GD_LT"] == "Y") {
                $param["aft_depth1_dvs"] .= "좌상,";
                $i++;
            }

            if($fb["GD_LB"] == "Y") {
                $param["aft_depth1_dvs"] .= "좌하,";
                $i++;
            }

            if($fb["GD_RT"] == "Y") {
                $param["aft_depth1_dvs"] .= "우상,";
                $i++;
            }

            if($fb["GD_RB"] == "Y") {
                $param["aft_depth1_dvs"] .= "우하,";
                $i++;
            }

            $param["aft_depth1_dvs"] .= "|";
            if($fb["cate_sortcode"] == "003001001")
                $param["aft_depth1_val"] .= (915 + $i) . "|";
            if($fb["cate_sortcode"] == "003002001")
                $param["aft_depth1_val"] .= (1283 + $i) . "|";
        }

        if($sortcode_t == "006") {
            // 양면테잎
            $param["after_name"] .= "envelopetape|";
            $param["aft_depth"] .= "양면테이프|";
            $param["aft_depth1_val"] .= "852|";
            $param["aft_depth1_wid"] .= "|";
            $param["aft_depth1_vert"] .= "|";
            $param["aft_depth2_wid"] .= "|";
            $param["aft_depth2_vert"] .= "|";
            $param["aft_depth1_dvs"] .= "|";
            $param["aft_depth2_dvs"] .= "|";
        }
    }

    if($AGroup_arr[$j] == "03") {
        if($sortcode_t == "003") {
            // 미싱
            $param["after_name"] .= "dotline|";
            $param["aft_depth"] .= "미싱|";
            $param["aft_depth1_val"] .= "921|";
            $param["aft_depth1_wid"] .= "|";
            $param["aft_depth1_vert"] .= "|";
            $param["aft_depth2_wid"] .= "|";
            $param["aft_depth2_vert"] .= "|";
            $param["aft_depth1_dvs"] .= "|";
            $param["aft_depth2_dvs"] .= "|";
            $enable_auto = false;
        }
    }

    if($AGroup_arr[$j] == "06") {
        if($sortcode_t == "003") {
            $enable_auto = false;
            $has_after = true;
            // 금박
            $ag = $fb["AGroup_2"][5];
            $pressure = $fb["Pressure2"];
            if($ag[1] == "01") {
                $param["after_name"] .= "foil1|";
                $param["aft_depth"] .= "박|";
                $param["aft_depth1_dvs"] .= "단면|";
                $param["aft_depth2_dvs"] .= "단면|";
                $param["aft_depth1_wid"] .= $fb["after_W06"] . "|";
                $param["aft_depth1_vert"] .= $fb["after_H06"] . "|";
                $param["aft_depth2_wid"] .= "|";
                $param["aft_depth2_vert"] .= "|";
                switch ($ag[0]) {
                    case "0" :
                        $param["aft_depth1"] .= "금박유광|";
                        break;
                    case "1" :
                        $param["aft_depth1"] .= "은박유광|";
                        break;
                    case "2" :
                        $param["aft_depth1"] .= "청박유광|";
                        break;
                    case "3" :
                        $param["aft_depth1"] .= "적박유광|";
                        break;
                    case "4" :
                        $param["aft_depth1"] .= "녹박유광|";
                        break;
                    case "5" :
                        $param["aft_depth1"] .= "먹박유광|";
                        break;
                    case "6" :
                        $param["aft_depth1"] .= "홀로그램박|";
                        break;
                    case "7" :
                        $param["aft_depth1"] .= "금박무광|";
                        break;
                    case "8" :
                        $param["aft_depth1"] .= "은박무광|";
                        break;
                }

                $param["aft_depth1_val"] .= "920|";
            }
            if($ag[1] == "03" || $ag[1] == "3") {
                $param["after_name"] .= "foil1|foil2|";
                $param["aft_depth"] .= "박|박|";
                $param["aft_depth1_wid"] .= $fb["after_W06"] . "|". $fb["after_W06_sub"] ."|";
                $param["aft_depth1_vert"] .= $fb["after_H06"] . "|". $fb["after_H06_sub"] ."|";
                $param["aft_depth1_dvs"] .= "단면|단면|";
                $param["aft_depth2_dvs"] .= "단면|단면|";
                $param["aft_depth2_wid"] .= "||";
                $param["aft_depth2_vert"] .= "||";

                switch ($ag[0]) {
                    case "0" :
                        $param["aft_depth1"] .= "금박유광|";
                        break;
                    case "1" :
                        $param["aft_depth1"] .= "은박유광|";
                        break;
                    case "2" :
                        $param["aft_depth1"] .= "청박유광|";
                        break;
                    case "3" :
                        $param["aft_depth1"] .= "적박유광|";
                        break;
                    case "4" :
                        $param["aft_depth1"] .= "녹박유광|";
                        break;
                    case "5" :
                        $param["aft_depth1"] .= "먹박유광|";
                        break;
                    case "6" :
                        $param["aft_depth1"] .= "홀로그램박|";
                        break;
                    case "7" :
                        $param["aft_depth1"] .= "금박무광|";
                        break;
                    case "8" :
                        $param["aft_depth1"] .= "은박무광|";
                        break;
                }
                switch ($pressure) {
                    case "76" :
                        $param["aft_depth1"] .= "금박유광|";
                        break;
                    case "77" :
                        $param["aft_depth1"] .= "은박유광|";
                        break;
                    case "78" :
                        $param["aft_depth1"] .= "청박유광|";
                        break;
                    case "79" :
                        $param["aft_depth1"] .= "적박유광|";
                        break;
                    case "81" :
                        $param["aft_depth1"] .= "녹박유광|";
                        break;
                    case "82" :
                        $param["aft_depth1"] .= "먹박유광|";
                        break;
                    case "85" :
                        $param["aft_depth1"] .= "홀로그램박|";
                        break;
                    case "86" :
                        $param["aft_depth1"] .= "금박무광|";
                        break;
                    case "87" :
                        $param["aft_depth1"] .= "은박무광|";
                        break;
                }
                $param["aft_depth1_val"] .= "920|920|";
            }
        }
    }

    if($AGroup_arr[$j] == "07") {
        if($sortcode_t == "003") {
            $has_after = true;
            $enable_auto = false;
            // 형압
            $param["after_name"] .= "press|";
            $param["aft_depth"] .= "형압|";
            $param["aft_depth1_wid"] .= $fb["after_W07"] . "|";
            $param["aft_depth1_vert"] .= $fb["after_H07"] . "|";
            $param["aft_depth2_wid"] .= "|";
            $param["aft_depth2_vert"] .= "|";
            $param["aft_depth1"] .= "앞으로 돌출|";
            $param["aft_depth2"] .= "|";
            $param["aft_depth2_dvs"] .= "|";
        }
    }

    if($AGroup_arr[$j] == "21") {
        $has_after = true;
        if($sortcode_t == "001") {
            // 오시
            $param["after_name"] .= "foldline|";
            $param["aft_depth"] .= "오시|";
            $param["aft_depth1_val"] .= "994|";
            $param["aft_depth1_wid"] .= "|";
            $param["aft_depth1_vert"] .= "|";
            $param["aft_depth2_wid"] .= "|";
            $param["aft_depth2_vert"] .= "|";
            $param["aft_depth1_dvs"] .= "|";
            $param["aft_depth2_dvs"] .= "|";
        }
    }
}

$product->makeBizhowsOrderCommonInsertParam($fb);
$product->makeBizhowsOrderDetailInsertParam($fb);
$param["cut_wid_size"] = $fb["CutSizeW"];
$param["cut_vert_size"] = $fb["CutSizeH"];
$param["amt"] = $fb["Qty"];
$param["count"] = $fb["Num"];
$param["paper"] = $product->order_detail_param["cate_paper_mpcode"];
$param["size"] = $product->stan_mpcode;
$param["bef_tmpt"] = $product->order_detail_param["cate_bef_print_mpcode"];
$param["paper_info"] = $product->paper_info;

$product1 = $product->setInfo($param);
$p = $product1->getProduct();
$product->order_detail_param["cate_paper_mpcode"] = $product->order_detail_param["cate_paper_tot_mpcode"] = $p->order_detail_param["cate_paper_mpcode"];
$product->order_detail_param["cate_bef_print_mpcode"] = $p->order_detail_param["cate_bef_print_mpcode"];

if($fb["cate_sortcode"] == "003001001"
    || $fb["cate_sortcode"] == "003002001"
    || $fb["cate_sortcode"] == "003003001") {
    $price = $product1->cost()["sell_price"];
    //var_dump($product1->cost());
    if($fb["cate_sortcode"] == "003002001"
        && (($param["cut_wid_size"] == "86" && $param["cut_vert_size"] == "52") || ($param["cut_wid_size"] == "52" && $param["cut_vert_size"] == "86"))) {
        $price = ($product1->cost()["print"] / 2) + $product1->cost()["add_after_price"];
    }
    if($has_after) $product->order_common_param["dlvr_dvs"] = "leaflet";
    $product->order_common_param["sell_price"] = ceil($price * 0.95);
    $product->order_common_param["pay_price"] = ceil($price * 0.95);
}

$product->order_common_param["receipt_dvs"] = $enable_auto ? "Auto" : "Manual";

$dao->insertOrderCommonBizhows($conn, $product->order_common_param);
$order_common_seqno = $conn->Insert_ID("order_common");

$tmp_param = array();
$tmp_param["order_common_seqno"] = $order_common_seqno;
$order_num = $dao->selectOrderNum($conn, $tmp_param);

$product->order_common_param["order_num"] = $order_num;
$product->order_detail_param["order_detail_dvs_num"] = "S" . $order_num . "01";
$product->order_common_param["order_common_seqno"] = $product->order_detail_param["order_common_seqno"] = $order_common_seqno;

$dao->insertOrderDetail($conn, $product->order_detail_param);
$order_detail_dvs_num = $product->order_detail_param["order_detail_dvs_num"];
$order_num = $product->order_common_param["order_num"];
$member_seqno = $product->order_common_param["member_seqno"];
$pay_price = $product->order_common_param["pay_price"];
$title = $product->order_common_param["title"];

$product1->makeOrderAfterHistoryInsertParam($fb);
$p = $product1->getProduct();
$cnt = count($p->order_after_history_param);

for($i = 0; $i < $cnt; $i++) {
    $p->order_after_history_param[$i]['order_detail_dvs_num'] = $order_detail_dvs_num;
    $p->order_after_history_param[$i]['order_common_seqno'] = $order_common_seqno;
    $product->order_after_history_param[$i]['price'] = 0;

    $dao->insertOrderAfterHistory($conn, $p->order_after_history_param[$i]);
}

$util = new CommonUtil();
$base_path = SITE_DEFAULT_ORDER_FILE
    . DIRECTORY_SEPARATOR
    . $util->getYmdDirPath();
$save_path = $_SERVER["SiteHome"]
    . SITE_NET_DRIVE
    . $base_path;

$param = array();
$param["dvs"]              = '1';
$param["file_path"]        = $base_path;
$param["save_file_name"]   = $order_num . "." .pathinfo($fb["DSOriFile"], PATHINFO_EXTENSION);
$param["origin_file_name"] = $fb["DSOriFile"];
$param["size"]             = 0;
$param["member_seqno"]     = $product->order_common_param["member_seqno"];
$param["order_common_seqno"]     = $order_common_seqno;
$dao->insertOrderFile($conn, $param);


/*
$state_code = "200";

$result = array();
$result["code"] = $state_code;
$result["message"] = "ok";

$ret["result"] = $result;
$ret["order_common_seqno"] = $order_common_seqno;
*/

$param = array();
$param["member_seqno"] = $member_seqno;
$param["order_num"]    = $order_num;
$param["exist_prepay"] = '0';
$param["cur_date"]     = date('Y-m-d');
$param["dvs"]             = "매출증가";
$param["sell_price"]      = $pay_price;
$param["sale_price"]      = '0';
$param["pay_price"]       = $pay_price;
$param["card_pay_price"]  = '0';
$param["depo_price"]      = '0';
$param["card_depo_price"] = '0';
$param["input_typ"]       = '000';
$param["prepay_bal"]      = '0';
$param["state"]           = '';
$param["deal_num"]        = '';
$param["order_cancel_yn"] = 'N';
$param["pay_year"]        = date('Y');
$param["pay_mon"]         = date('m');
$param["cont"]            = $title . '(' . $order_num . ") 구매";
$param["prepay_use_yn"]   = 'Y';
$param["public_dvs"]         = '세금계산서';
$param["public_state"]         = '대기';
$param["dvs_detail"]   = '';
$dao->insertMemberPayHistory($conn, $param);


echo $order_num. "." .pathinfo($fb["DSOriFile"], PATHINFO_EXTENSION) . "/".$order_common_seqno;


$conn->Close();
?>
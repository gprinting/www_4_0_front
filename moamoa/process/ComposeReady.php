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
$param['state'] = "2120";
$param['accept_file_path'] = '/attach/gp' . $fb->form("AcceptFilePath") . '/';
$param['accept_file_name'] = $fb->form("AcceptFileName");
$param['preview_file_path'] = '/attach/gp' . $fb->form("PreviewFilePath") . '/';
$param['preview_file_name'] = $fb->form("PreviewFileNames");

// 출력완료 이후는 조판대기로 상태변경이 불가함
$rs = $dao->selectProductStatecode($conn, $param);
$state = $rs['order_state'];
$cate_sortcode = $rs['cate_sortcode'];
$cate_paper_mpcode = $rs['cate_paper_mpcode'];

//외주로 보낼품목
if(startsWith($cate_sortcode, "002")
    || ($cate_sortcode == "003003001" && $cate_paper_mpcode != "198") // 카드명함
    || $cate_sortcode == "003007002" || $cate_sortcode == "003007003"
    || $cate_sortcode == "008002001" || $cate_sortcode == "008002002" || $cate_sortcode == "008002003" // 자석
    || $cate_sortcode == "006002001" || $cate_sortcode == "006002002" || $cate_sortcode == "006002003" // 마스터봉투
    || $cate_sortcode == "006002004" || $cate_sortcode == "006002005" || $cate_sortcode == "006002006" // 마스터봉투
    || $cate_sortcode == "006002007" || $cate_sortcode == "006002008" || $cate_sortcode == "006002009" || $cate_sortcode == "006002010" // 마스터봉투
    || $cate_sortcode == "007001001" || $cate_sortcode == "007001002" || $cate_sortcode == "007001003"  // 마스터NCR
    || $cate_sortcode == "007002001" // 모조양식지
    || $cate_sortcode == "009001001" || $cate_sortcode == "009001002"
    || ($cate_sortcode == "003002001" && ($cate_paper_mpcode == "1004" || $cate_paper_mpcode == "1005" || $cate_paper_mpcode == "1006" || $cate_paper_mpcode == "1007" || $cate_paper_mpcode == "1008" ||  $cate_paper_mpcode == "1009" || $cate_paper_mpcode == "1010")) // 수입지(엑스트라계열)
    //VIP명함
) {
    $rs = $dao->updateProductState($conn, $param);
    $rs = $dao->insertStateHistory($conn, $param);

    $param['state'] = "2220";
}

$result_code = "200";
if($state != '1320' && $state != '1380' && $state != '1385' && $state != '2120') {
    $result_code = "400";
} else {
    $rs = $dao->updateProductState($conn, $param);
    $rs = $dao->insertStateHistory($conn, $param);
}

$result = array();
$result["code"] = $result_code;
$result["message"] = "ok";

$data = array();
$ret["result"] = $result;

echo json_encode($ret);

$conn->Close();

function startsWith($haystack, $needle){
    return strncmp($haystack, $needle, strlen($needle)) === 0;
}
?>
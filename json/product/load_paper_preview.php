<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_dao/ProductCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProductCommonDAO();

$fb = $fb->getForm();

$paper_mpcode = $fb["paper_mpcode"];

$rs = $dao->selectCatePaperInfo($conn, $paper_mpcode);

$param = array();
$param["name"]  = $rs["name"];
$param["dvs"]   = $rs["dvs"];
$param["color"] = $rs["color"];

$rs = $dao->selectPaperPreviewInfo($conn, $param);

$json = "{\"zoom\" : \"%s\", \"thumb\" : \"%s\"}";

if ($rs->EOF) {
    $json = sprintf($json, NO_IMAGE, NO_IMAGE);
} else{
    $rs = $rs->fields;

    $save_file_arr = explode('.', $rs["save_file_name"]);

    $zoom = $rs["file_path"] . DIRECTORY_SEPARATOR . $rs["save_file_name"];
    $thumb = $rs["file_path"] . DIRECTORY_SEPARATOR .
             $save_file_arr[0] . "_400_313." . $save_file_arr[1];

    $json = sprintf($json, $zoom
                         , $thumb);
}

echo $json;
?>

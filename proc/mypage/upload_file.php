<?php
define("INC_PATH", $_SERVER["INC"]);

include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/common_define/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/FrontCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/front/order/SheetDAO.inc");

/*
 * Copyright (c) 2016 Nexmotion, Inc
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 * =============================================================================
 * 2016/12/27 엄준현 추가
 * 2018/02/13 이청산 수정
 * =============================================================================
 */

/*
This is an upload script for SWFUpload that attempts to properly handle uploaded files
in a secure way.

Notes:

	SWFUpload doesn't send a MIME-TYPE. In my opinion this is ok since MIME-TYPE is no better than
	 file extension and is probably worse because it can vary from OS to OS and browser to browser (for the same file).
	 The best thing to do is content sniff the file but this can be resource intensive, is difficult, and can still be fooled or inaccurate.
	 Accepting uploads can never be 100% secure.

	You can't guarantee that SWFUpload is really the source of the upload.  A malicious user
	 will probably be uploading from a tool that sends invalid or false metadata about the file.
	 The script should properly handle this.

	The script should not over-write existing files.

	The script should strip away invalid characters from the file name or reject the file.

	The script should not allow files to be saved that could then be executed on the webserver (such as .php files).
	 To keep things simple we will use an extension whitelist for allowed file extensions.  Which files should be allowed
	 depends on your server configuration. The extension white-list is _not_ tied your SWFUpload file_types setting

	For better security uploaded files should be stored outside the webserver's document root.  Downloaded files
	 should be accessed via a download script that proxies from the file system to the webserver.  This prevents
	 users from executing malicious uploaded files.  It also gives the developer control over the outgoing mime-type,
	 access restrictions, etc.  This, however, is outside the scope of this script.

	SWFUpload sends each file as a separate POST rather than several files in a single post. This is a better
	 method in my opinions since it better handles file size limits, e.g., if post_max_size is 100 MB and I post two 60 MB files then
	 the post would fail (2x60MB = 120MB). In SWFupload each 60 MB is posted as separate post and we stay within the limits. This
	 also simplifies the upload script since we only have to handle a single file.

	The script should properly handle situations where the post was too large or the posted file is larger than
	 our defined max.  These values are not tied to your SWFUpload file_size_limit setting.

*/

if ($is_login === false) {
    HandleError("No Login");
    exit(0);
}

// Code for Session Cookie workaround
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	} else if (isset($_GET["PHPSESSID"])) {
		session_id($_GET["PHPSESSID"]);
	}

// Check post_max_size (http://us3.php.net/manual/en/features.file-upload.php#73762)
	$POST_MAX_SIZE = ini_get('post_max_size');
	$unit = strtoupper(substr($POST_MAX_SIZE, -1));
	$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

	if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
		header("HTTP/1.1 500 Internal Server Error"); // This will trigger an uploadError event in SWFUpload

		HandleError("POST exceeded maximum allowed size.");
		exit(0);
	}

    // Settings
    //TODO 파일 저장 경로
    $connectionPool = new ConnectionPool();
    $conn = $connectionPool->getPooledConnection();

	$upload_name = "file";

    $state_arr       = $_SESSION["state_arr"];
    $member_seqno    = $_SESSION["org_member_seqno"];
    $order_seqno_arr = explode('!', $_GET["seqno"]);

    // 상태 안맞으면 걸러내는 용도
    //$state_str = sprintf("%s|%s|%s|%s|%s|%s", $state_arr["주문대기"]
    //                                     , $state_arr["입금대기"]
    //                                     , $state_arr["입금완료"]
    //                                     , $state_arr["접수중"]
    //                                     , $state_arr["파일에러"]
    //                                     , $state_arr["접수보류"]);
    $state_str = "1120|1220|1325|1385";

    // 기본경로
    $base_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE;


    // 업로드 파일 검증용
	$tmp_name = pathinfo($_FILES[$upload_name]['name']);
	$ext      = strtolower($tmp_name["extension"]);

// Other variables
	$uploadErrors = array(
        0=>"There is no error, the file uploaded with success",
        1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini",
        2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
        3=>"The uploaded file was only partially uploaded",
        4=>"No file was uploaded",
        6=>"Missing a temporary folder"
	);

// Validate the upload
	if (!isset($_FILES[$upload_name])) {
		HandleError("No upload found in \$_FILES for " . $upload_name);
		exit(0);
	} else if (isset($_FILES[$upload_name]["error"]) && $_FILES[$upload_name]["error"] != 0) {
		HandleError($uploadErrors[$_FILES[$upload_name]["error"]]);
		exit(0);
	} else if (!isset($_FILES[$upload_name]["tmp_name"]) || !@is_uploaded_file($_FILES[$upload_name]["tmp_name"])) {
		HandleError("Upload failed is_uploaded_file test.");
		exit(0);
	} else if (!isset($_FILES[$upload_name]['name'])) {
		HandleError("File has no name.");
		exit(0);
	}

    $max_file_size_in_bytes = 2147483648; // 2GB in bytes
	$file_size = @filesize($_FILES[$upload_name]["tmp_name"]);
	if (!$file_size || $file_size > $max_file_size_in_bytes) {
		HandleError("File exceeds the maximum allowed size", -600);
		exit(0);
	}

	if ($file_size <= 0) {
		HandleError("File size outside allowed lower bound");
		exit(0);
	}

    // 업로드 파일 검증
    $oper_sys = "IBM";
    if ($ext === "sit") {
        $oper_sys = "MAC";
    }

	$extension_whitelist = array("zip","egg","rar","jpg","jpeg",
                                 "sit","zip","ai", "png","alz",
                                 "cdr","cdt","eps","cmx","7z","pdf"); // Allowed file extensions
	$is_valid_extension = false;
	foreach ($extension_whitelist as $extension) {
		if (strcasecmp($ext, $extension) == 0) {
			$is_valid_extension = true;
			break;
		}
	}
	if (!$is_valid_extension) {
		HandleError("Invalid file extension");
		exit(0);
	}

    $tmp_path = $_SERVER["DOCUMENT_ROOT"] . "/tmp/" . uniqid();
	if (!@move_uploaded_file($_FILES[$upload_name]["tmp_name"], $tmp_path)) {
		HandleError("File could not be saved.");
		exit(0);
	}

    $dao = new SheetDAO();
    $util = new FrontCommonUtil();

    // 주문번호가 여러개 넘어왔을 경우 원파일 그룹생성
    $group_arr = [];
    $group_num = null;
    if (count($order_seqno_arr) > 1) {
        $group_num = $util->makeOnefileGroupNum($member_seqno);
    }

    // 주문번호별 파일경로/이름
    $data_arr = [];

    $param = [];
    $param["member_seqno"] = $member_seqno;
    foreach ($order_seqno_arr as $order_seqno) {
        //#1 파일명으로 사용할 주문번호와
        //   재업로드가 가능한 상태인지 확인하기 위한 주문상태 검색
        $param["order_common_seqno"] = $order_seqno;

        $order_rs = $dao->selectOrderCommon($conn,
                                            $param,
                                            "order_state, order_num");

        if ($order_rs->EOF) {
            @unlink($tmp_path);
            HandleError("Not Exist Order");
            exit(0);
        }
        $order_rs = $order_rs->fields;

        $order_state = $order_rs["order_state"];

        // 접수를 넘어갔거나 접수중이면 불가
        if (strpos($state_str, $order_state) === false) {
            @unlink($tmp_path);
            HandleError("Incorrect State");
            exit(0);
        }

        //#2 기업로드된 파일 있는지 확인
        $param["order_seqno"] = $order_seqno;
        $file_rs = $dao->selectOrderFile($conn, $param);

        if (!empty($file_rs)) {
            $save_path = $file_rs["file_path"];
            $file_name = $file_rs["save_file_name"];

            $file_path = $base_path . $save_path . $file_name;

            @unlink($file_path);
        } else {
            $save_path = SITE_DEFAULT_ORDER_FILE
                         . DIRECTORY_SEPARATOR
                         . $util->getYmdDirPath();
        }

        // create save_path if not exists
        if(!is_dir($base_path . $save_path)) {
            mkdir($base_path . $save_path, 0755, true);
        }

        $file_name  = $order_rs["order_num"] . '.' . $ext;

        if (!copy($tmp_path, $base_path . $save_path . $file_name)) {
            @unlink($tmp_path);
            HandleError("File Copy Fail");
            exit(0);
        }

        // success
        $conn->StartTrans();
        $temp = [];
        if (!empty($file_rs)) {
            $temp["save_file_name"]   = $file_name;
            $temp["origin_file_name"] = $tmp_name['filename'] . " " .date('Y-m-d H시i분s초') . "." . $tmp_name['extension'];
            $temp["size"]             = $file_size;
            $temp["order_file_seqno"] = $file_rs["order_file_seqno"];

            $dao->updateOrderFile($conn, $temp);
        } else {
            $temp["dvs"]              = '1';
            $temp["file_path"]        = $save_path;
            $temp["save_file_name"]   = $file_name;
            $temp["origin_file_name"] = $_FILES[$upload_name]['name'];
            $temp["size"]             = $file_size;
            $temp["member_seqno"]     = $_SESSION["org_member_seqno"];
            $temp["order_common_seqno"] = $order_seqno;

            $dao->insertOrderFile($conn, $temp);
        }

        if ($conn->HasFailedTrans() === true) {
            $conn->FailTrans();
            $conn->RollbackTrans();
            @unlink($tmp_path);
            HandleError("Data Update Faild");
            exit(0);
        }

        // 주문상태 접수대기로 업데이트 하는 부분 추가 필요
        if ($order_state === intval($state_arr["재주문"])) {
            $param["order_state"] = $state_arr["입금완료"];

            $ret = $dao->updateOrderCommonState($conn, $param);
            $ret = $dao->updateOrderDetailState($conn, $param);

            $detail_rs = $dao->selectOrderDetailSeqno($conn, $order_seqno);

            while ($detail_rs && !$detail_rs->EOF) {
                $param["order_detail_seqno"] =
                    $detail_rs->fields["order_detail_seqno"];
                $ret = $dao->updateOrderDetailCountFileState($conn, $param);

                $detail_rs->MoveNext();
            }
        }

        if ($order_state == "1325" || $order_state == "1385") {
            $param["order_state"] = "1320";
            $ret = $dao->updateOrderCommonState($conn, $param);

            $record_param = array();
            $record_param["state"] = "1320";
            $record_param["empl_id"] = "";
            $record_param["kind"] = "재업로드";
            $record_param["before"] = "";
            $record_param["after"] = "고객이 파일 재업로드완료";
            $record_param["order_common_seqno"] = $order_seqno;
            $dao->insertOrderInfoHistory($conn, $record_param);
        }

        unset($temp);
        $temp["order_common_seqno"] = $order_seqno;
        $temp["group_num"] = $group_num;
        $temp["order_num"] = $order_rs["order_num"];

        if (empty($group_num)) {
            // 그룹번호가 없는경우(파일 재업로드, 하나만 체크하고 일괄업로드)
            // 그룹정보 삭제
            $ret = $dao->deleteOnefileOrderGroup($conn, $temp);
        } else {
            // 그룹번호가 있는경우(선택상품 일괄업로드)
            // 그룹정보 입력
            $ret = $dao->insertOnefileOrderGroup($conn, $temp);
        }

        $conn->CompleteTrans();
    }

    @unlink($tmp_path);
	showResult(true, "upload completed");

	exit(0);

function showResult($success, $message)
{
	$result = array('success'    => $success,
                    'message'    => $message);
	echo json_encode($result);
}

/* Handles the error output. This error message will be sent to the uploadSuccess event handler.  The event handler
will have to check for any error messages and react as needed. */
function HandleError($message, $code = '') {
	$result = array('success'    => false,
                    'code'       => $code,
                    'message'    => $message);
	echo json_encode($result);
}
?>

<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/common_define/common_config.inc");
include_once($_SERVER["INC"] . "/common_lib/CommonUtil.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/product/ProductEstiDAO.inc");

/*
 * Copyright (c) 2015 Nexmotion, Inc
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 * =============================================================================
 * 2017/01/10 엄준현 추가
 * 2017/09/11 엄준현 수정
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
    $util = new CommonUtil();

	$base_path = SITE_DEFAULT_ESTI_FILE
                 . DIRECTORY_SEPARATOR
                 . $util->getYmdDirPath();
	$save_path = $_SERVER["SiteHome"]
                 . SITE_NET_DRIVE
                 . $base_path;

	$upload_name = "file";
	$max_file_size_in_bytes = 524288000; // 500MB in bytes
	$extension_whitelist = array("zip","egg","rar","jpg","jpeg",
                                 "sit","zip","ai", "png","alz",
                                 "cdr","cdt","eps","cmx","7z","pdf"); // Allowed file extensions
	$valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-'; // Characters allowed in the file name (in a Regular Expression format)

// Other variables
	$MAX_FILENAME_LENGTH = 260;
	$file_name = "";
	$file_extension = "";
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

// Validate the file size (Warning: the largest files supported by this code is 2GB)
	$file_size = @filesize($_FILES[$upload_name]["tmp_name"]);
	if (!$file_size || $file_size > $max_file_size_in_bytes) {
		HandleError("File exceeds the maximum allowed size", -600);
		exit(0);
	}

	if ($file_size <= 0) {
		HandleError("File size outside allowed lower bound");
		exit(0);
	}

// Validate file name (for our purposes we'll just remove invalid characters)
// 	$file_name = preg_replace('/[^'.$valid_chars_regex.']|\.+$/i', "", basename($_FILES[$upload_name]['name']));
    $file_name = explode('.', $_FILES[$upload_name]['name']);
    $ext       = array_pop($file_name);
    $file_name = md5(uniqid() . time());

	if (strlen($file_name) == 0 || strlen($file_name) > $MAX_FILENAME_LENGTH) {
		HandleError("Invalid file name");
		exit(0);
	}


// Validate that we won't over-write an existing file
    /*
	if (file_exists($save_path . $file_name)) {
		HandleError("File with this name already exists");
		exit(0);
	}
    */

// Validate file extension
	$path_info = pathinfo($_FILES[$upload_name]['name']);
	$file_extension = strtolower($path_info["extension"]);
	$is_valid_extension = false;
	foreach ($extension_whitelist as $extension) {
		if (strcasecmp($file_extension, $extension) == 0) {
			$is_valid_extension = true;
			break;
		}
	}
	if (!$is_valid_extension) {
		HandleError("Invalid file extension");
		exit(0);
	}
/*
*/
// Validate file contents (extension and mime-type can't be trusted)
	/*
		Validating the file contents is OS and web server configuration dependant.  Also, it may not be reliable.
		See the comments on this page: http://us2.php.net/fileinfo

		Also see http://72.14.253.104/search?q=cache:3YGZfcnKDrYJ:www.scanit.be/uploads/php-file-upload.pdf+php+file+command&hl=en&ct=clnk&cd=8&gl=us&client=firefox-a
		 which describes how a PHP script can be embedded within a GIF image file.

		Therefore, no sample code will be provided here.  Research the issue, decide how much security is
		 needed, and implement a solution that meets the needs.
	*/

// create save_path if not exists
if( !is_dir($save_path) )
{
	$old = umask(0);
	mkdir($save_path, 0777, true);
	umask($old);
} else {
	chmod($save_path, 0777);
}

// Process the file
	/*
		At this point we are ready to process the valid file. This sample code shows how to save the file. Other tasks
		 could be done such as creating an entry in a database or generating a thumbnail.

		Depending on your server OS and needs you may need to set the Security Permissions on the file after it has
		been saved.
	*/
	if (!@move_uploaded_file($_FILES[$upload_name]["tmp_name"], $save_path.$file_name)) {
		HandleError("File could not be saved.");
		exit(0);
	}

	// success

    // 주문_파일 테이블에 값 입력
    $connectionPool = new ConnectionPool();
    $conn = $connectionPool->getPooledConnection();

    $dao = new ProductEstiDAO();

    $param = [];
    $param["member_seqno"]     = $_SESSION["org_member_seqno"];
    $param["file_path"]        = $base_path;
    $param["save_file_name"]   = $file_name;
    $param["origin_file_name"] = $_FILES[$upload_name]['name'];
    $param["size"]             = $file_size;

    $conn->StartTrans();
    $dao->insertEstiFile($conn, $param);

    $file_seqno = $conn->Insert_ID("esti_file");

    if ($conn->HasFailedTrans() === true) {
        $conn->FailTrans();
        $conn->RollbackTrans();
        HandleError("Data Insert Faild");
        exit(0);
    }

    unset($param);
    $conn->CompleteTrans();

	showResult(true, $file_seqno, "upload completed");

	exit(0);

function showResult($success, $file_seqno, $message)
{
	$result = array('success'    => $success,
                    'file_seqno' => $file_seqno,
                    'message'    => $message);
	echo json_encode($result);
}

/* Handles the error output. This error message will be sent to the uploadSuccess event handler.  The event handler
will have to check for any error messages and react as needed. */
function HandleError($message, $code = '') {
	$result = array('success'    => false,
                    'file_seqno' => '',
                    'code'       => $code,
                    'message'    => $message);
	echo json_encode($result);
}
?>

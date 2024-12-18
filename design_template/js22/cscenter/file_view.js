/**
 * @brief 공유자료실 삭제
 */
var delFileList = function (seqno) {

    var url = "/proc/service/file_list/del_file_list.php";
    var data = {
        "seqno": seqno
    };
    var callback = function (result) {
        if (result == 1) {
            location.href = "/cscenter/file_list.html";
            alert("삭제 되었습니다.");
        } else {
            alert("삭제를 실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
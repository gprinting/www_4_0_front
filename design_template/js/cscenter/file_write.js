/**
 * @brief 공유자료실 등록
 */
var regiFileList = function () {

    if (checkBlank($("#title").val())) {
        alert("제목을 입력 해주세요.");
        $("#title").focus();
        return false;
    }

    if (checkBlank($("#cont").val())) {
        alert("내용을 입력 해주세요.");
        $("#cont").focus();
        return false;
    }

    if (checkBlank($("#file").val())) {
        alert("파일을 첨부 해주세요.");
        $("#file").focus();
        return false;
    }

    var formData = new FormData();

    formData.append("file", $("#file")[0].files[0]);
    formData.append("title", $("#title").val());
    formData.append("cont", $("#cont").val());

    var url = "/proc/service/file_list/regi_file_list.php";
    var callback = function (result) {
        hideMask();
        if (result == 1) {
            location.href = "/cscenter/file_list.html";
            alert("공유자료를 등록하였습니다.");
        } else {
            alert("공유자료 등록을 실패하였습니다.");
        }
    };

    showMask();
    $.ajax({
        type: "POST",
        data: formData,
        url: url,
        dataType: "html",
        processData: false,
        contentType: false,
        success: function (result) {
            callback(result);
        },
        error: getAjaxError
    });
}

/**
 * @brief 공유자료실 수정
 */
var modiFileList = function (seqno) {

    if (checkBlank($("#title").val())) {
        alert("제목을 입력 해주세요.");
        $("#title").focus();
        return false;
    }

    if (checkBlank($("#cont").val())) {
        alert("내용을 입력 해주세요.");
        $("#cont").focus();
        return false;
    }

    var formData = new FormData();

    if (checkBlank($("#file").val()) == false) {
        formData.append("file", $("#file")[0].files[0]);
        formData.append("file_yn", "Y");
    } else {
        formData.append("file_yn", "N");
    }
    formData.append("title", $("#title").val());
    formData.append("cont", $("#cont").val());
    formData.append("seqno", seqno);

    var url = "/proc/service/file_list/modi_file_list.php";
    var callback = function (result) {
        hideMask();
        if (result == 1) {
            location.href = "/service/file_view.html?seqno=" + seqno;
            alert("공유자료를 수정하였습니다.");
        } else {
            alert("공유자료 수정을 실패하였습니다.");
        }
    };

    showMask();
    $.ajax({
        type: "POST",
        data: formData,
        url: url,
        dataType: "html",
        processData: false,
        contentType: false,
        success: function (result) {
            callback(result);
        },
        error: getAjaxError
    });
}
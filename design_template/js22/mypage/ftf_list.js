var file;
var filename = '';
var filesize = '';
var s3;

var strArray = '';
var cvt_file = '';
var filesizeH = '';
var savefile = '';
var org_file = '';

$(document).ready(function () {
    //lnb 선택 효과
    mypageLnbEffect();

    //전화번호에 숫자만 입력 가능
    numKeyCheck('tel_num2');
    numKeyCheck('tel_num3');
    numKeyCheck('cell_num2');
    numKeyCheck('cell_num3');

    //일자별 검색 datepicker 기본 셋팅
    $("#from").datepicker({
        dateFormat: 'yy-mm-dd',
        prevText: '이전 달',
        nextText: '다음 달',
        monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        dayNames: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
        showMonthAfterYear: true,
        yearSuffix: '년'
    });

    $("#to").datepicker({
        dateFormat: 'yy-mm-dd',
        prevText: '이전 달',
        nextText: '다음 달',
        monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        dayNames: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
        showMonthAfterYear: true,
        yearSuffix: '년'
    });


    dateSet('0');
    initSearchParam();
    ftfSearch(10, 1);


    $("#ftf_file").on("click", function () {
        $("#uploader").trigger("click");
    });

    $("#uploader").on("change", function(e) {
        file = e.target.files[0];
        filename = e.target.files[0].name;
        filesize = e.target.files[0].size;
        console.log(file);


        var fn = file.name;
        var ext = fn.split(".")[1].toLowerCase();
        // 봉투일 때 PDF 파일 업로드 불가
        /*
        if ($("#prdt_dvs").val() == "ev") {
            if (ext == 'pdf') {
                removeFile();
                return alertReturnFalse("봉투주문은 PDF 파일을 업로드 할 수 없습니다.");
            }
        }
        */

        if (file.size > 2147483648) {
            removeFile();
            return alertReturnFalse("2GB를 넘는 파일은 웹하드를 이용해주세요.");
        }

        $('#file_content').html(
            "<div id=\"file_id\">" +
            file.name + " (" +
            humanFileSize(file.size) +
            ")<b></b>" +
            "&nbsp;" +
            "<img src=\"/design_template/images/common/btn_circle_x_red.png\"" +
            "     id=\"esti_file_del" +
            "     alt=\"X\"" +
            "     onclick=\"removeFile();\"" +
            "     style=\"cursor:pointer; top:-1px; position:relative;\" /></div>"
        );
        $("#file_id").val(file.id);
        $("#file_uploading_name").html(file.name + " (" + humanFileSize(file.size) + ")");
        $("#file_name").val(file.name);
        $("#file_size").val(file.size);
        $('#uploader').val('');
    });
});

function uploadToS3(file, file_path, key) {
    AWS.config.update({
        region: "ap-northeast-2",
        credentials: new AWS.CognitoIdentityCredentials({
            IdentityPoolId: "ap-northeast-2:b95f4416-871e-42b0-8a26-68098cbd9aa0"
        })
    });

    AWS.config.httpOptions.timeout = 0;
    key_name = key + "." + $("#file_name").val().split('.').pop();
    var contentdisposition = 'attachment; filename ="' + encodeURI($("#file_name").val()) + '"';
    var params = {
        Bucket : "orderplatform",
        Key : file_path + "/" + key_name,
        Body : file,
        ContentDisposition : contentdisposition,
        ACL:'public-read-write'
    };

    //openFileProgress();
    s3 = new AWS.S3();
    s3.putObject(params).on('httpUploadProgress',
        function (evt) {
            var ratio = (evt.loaded * 100) / evt.total;
            ratio -= 1;
            $(".file_progress_wrapper").css("width",ratio + "%");

            if(ratio < 0) ratio = 0;
            ratio = Math.ceil(ratio);
            $("#ratio").html(ratio + "%");
            if(ratio < 100) {
                //$(".ajax-file-upload-bar").css("width", ratio.toFixed(0) + '%');
                //$(".ajax-file-upload-bar").html(ratio.toFixed(0) + '%');
            }
        }).send(function (err, data) {
        if(err != null) {
            alert("파일 전송중 네트워크 오류가 발생했습니다. 잠시 후 다시 시도해주십시오.");
            deleteUpload();
        } else {
            $(".file_progress_wrapper").css("width","100%");
            $("#ratio").html("100%");
            $("#ftf_form").submit();
            //fileNextFunc();
            /*
            $("#savefile").val(savefile);
            $("#org_file").val(org_file);
            $("#cvt_file").val(org_file);
            $("#filesizeH").val(filesizeH);
            $("#up_sort").val($("#upType").val());	//	시안/후가공 업로드

            $(".ajax-file-upload-bar").css("width", "100%");
            $(".ajax-file-upload-bar").html("100%");

            $("#ajax-file-upload-abort").hide();
            $("#ajax-file-upload-done").show();
            $("#ajax-file-upload-delete").hide();

            if ($("#upType").val() == 'estimate') {						//	견적서 업로드
                var f = document.forderlist;
                f.action = "/adsMng/upload/upload_estimate.php";
                f.target = "";
                f.submit();
            } else {												//	시안/후가공 업로드
                var f = document.forderlist;
                f.action = "/adsMng/upload/upload_after.php";
                f.target = "";
                f.submit();
            }
             */
        }
    });
}

function removeFile() {
    $("#file_content").html("");
}

function humanFileSize(bytes, si=true, dp=1) {
    const thresh = si ? 1000 : 1024;

    if (Math.abs(bytes) < thresh) {
        return bytes + ' B';
    }

    const units = si
        ? ['KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
        : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
    let u = -1;
    const r = 10**dp;

    do {
        bytes /= thresh;
        ++u;
    } while (Math.round(Math.abs(bytes) * r) / r >= thresh && u < units.length - 1);


    return bytes.toFixed(dp) + ' ' + units[u];
}

/**
 * @brief 선택조건으로 검색 클릭시
 */
var ftfSearch = function (listSize, page) {

    var url = "/ajax22/mypage/ftf_list/load_ftf_list.php";
    var blank = "<tr><td colspan=\"6\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "from": $("#from").val(),
        "to": $("#to").val(),
        "answ_yn": $("#answ_yn").val(),
        "inq_typ": $("#inq_typ").val(),
        "title": $("#title").val(),
    };
    var callback = function (result) {
        var rs = result.split("♪");
        var para = $("#searchParam").val();
        //if (rs[0].trim() == "") {
        if ($.trim(rs[0]) == "") {

            $("#list").html(blank);
            $("#paging").html("<li><button class='on'>1</button><li>");
            $("#resultNum").html("<em>0</em>건의 검색결과가 있습니다.");
            return false;

        } else {

            $("#list").html(rs[0]);
            $("#paging").html(rs[1]);
            $("#resultNum").html(rs[2]);

            return false;

        }
    };

    data.list_num = listSize;
    data.page = page;

    showMask();
    ajaxCall(url, "html", data, callback);
}

var listCnt = "";

/**
 * @brief 보여줄 페이지 수 설정
 */
var changeListNum = function (val) {
    listCnt = val;
    ftfSearch(listCnt, 1);
}

/**
 * @brief 페이지 이동
 */
var movePage = function (val) {

    ftfSearch(listCnt, val);
}

/**
 * @brief 페이지 이동
 */
var ftfSelectMove = function () {

    var url = "/mypage22/ftf_write.html";
    $(location).attr('href', url);
    return false;
};

var initSearchParam = function () {

    if (!$("#searchParam").val())
        return false;
    var params = $("#searchParam").val().split("&");

    $.each(params, function (i, v) {
        var tmp = v.split("=");
        $("#" + tmp[0]).val(tmp[1]);
    });
};

var goList = function () {
    var url = "/mypage22/ftf_list.html";
    $("#frm").attr("action", url);
    $("#frm").attr("method", "post");
    $("#frm").submit();
};


/**
 * @brief 페이지 이동
 */
var ftfWrite = function () {

    var url = "/mypage22/ftf_write.html";
    $("#frm").attr("action", url);
    $("#frm").attr("method", "post");
    $("#frm").submit();

}


/**
 * @brief 페이지 이동
 */
var ftfView = function (seq) {

    var url = "/mypage22/ftf_view.html";
    $("#seq").val(seq);
    $("#searchParam").val($("#searchFrm").serialize());

    $("#frm").attr("action", url);
    $("#frm").attr("method", "post");
    $("#frm").submit();

}

/**
 * @brief 조건 검색
 */
var searchKey = function (event) {
    event.stopPropagation();

    if (event.keyCode != 13) {
        return false;
    }

    ftfSearch(listCnt, 1);
}

/**
 * @brief 조건 검색
 */
var searchTxt = function () {
    ftfSearch(listCnt, 1);
}

var validation = function () {

    if ($("#title").val() == "") {
        alert("제목을 입력해주세요.");
        $("#title").focus();
        return false;
    }


    if (($("#cell_num2").val() == "" || $("#cell_num3").val() == "") &&
        ($("#tel_num2").val() == "" || $("#tel_num3").val() == "") &&
        ($("#mail").val() == "" || $("#mail2").val() == "")) {
        alert("연락처를 최소 한개는 남겨주셔야 합니다.");
        return false;
    }


    if ($("#cont").val() == "") {
        alert("내용을 입력해주세요.");
        $("#cont").focus();
        return false;
    }

    return true;
}

/*
 * @comment 171204 이청산 주석처리(사용하지 않음! ie8 대응으로 변경)
var regiReq = function() {

    if (!validation())
        return false;

    showMask();
    var formData = new FormData();

    formData.append("title", $("#title").val());
    formData.append("inq_typ", $("#inq_typ").val());
    if ($("#tel_num2").val() != "" && $("#tel_num3").val() != "")
        formData.append("tel_num", $("#tel_num").val() + "-" + $("#tel_num2").val() + "-" + $("#tel_num3").val());
    if ($("#cell_num2").val() != "" && $("#cell_num3").val() != "")
        formData.append("cell_num", $("#cell_num").val() + "-" + $("#cell_num2").val() + "-" + $("#cell_num3").val());
    if ($("#mail").val()) {
        formData.append("mail", $("#mail").val() + "@" + $("#mail2").val());
        formData.append("answ_mail_yn", "Y");
    } else {
        formData.append("answ_mail_yn", "N");
    }
    formData.append("cont", $("#cont").val());
    formData.append("file", $("#file")[0].files[0]);
    if ($("#file").val())
        formData.append("upload_yn", "Y");
    else
        formData.append("upload_yn", "N");


    $.ajax({
        type: "POST",
        data: formData,
        url: "/proc/mypage/ftf_write/regi_ftf_list.php",
        dataType : "html",
        processData : false,
        contentType : false,
        success: function(result) {
            alert($.trim(result));
            hideMask();
            location.href = "/mypage/ftf_list.html";
        },
        error    : getAjaxError
    });


}
*/

/**
 * @brief 1:1문의 등록
 */
var regiReq = function (seqno) {

    // validation check
    var title = $("#title").val(); // 문의 제목
    var telNum2 = $("#tel_num2").val(); // 연락처 중간번호
    var telNum3 = $("#tel_num3").val(); // 연락처 끝번호
    var cellNum2 = $("#cell_num2").val(); // 휴대전화 중간번호
    var cellNum3 = $("#cell_num3").val(); // 휴대전화 끝번호
    var cont = $("#cont").val(); // 문의 내용

    if (checkBlank(title)) {
        alert("제목을 입력해주세요.");
        $("#title").focus();
        return false;
    }

    if ((checkBlank(telNum2) || checkBlank(telNum3)) && (checkBlank(cellNum2) || checkBlank(cellNum3))) {
        alert("연락처나 휴대전화 둘 중 하나는 제대로 입력하셔야 합니다.");
        return false;
    }

    if (checkBlank(cont)) {
        alert("내용을 입력해주세요.");
        $("#title").focus();
        return false;
    }

    var url = "/proc/order/upload_ftf_file_info.php";
    var data = {
        "file_name": $("#file_name").val(),
        "file_size": $("#file_size").val()
    };
    var callback = function (result) {
        var asd = JSON.parse(result);
        $("#oto_file_seqno").val(asd.file_seqno);
        uploadToS3(file,asd.file_path, asd.file_seqno);
    };

    ajaxCall(url, "text", data, callback);

};

/**
 * @brief 1:1문의 삭제
 */
var deleteReq = function (seqno, id) {
    var processing = "<button class=\"function\">진행중...</button>";
    $(".red").html(processing);
    var url = "/proc/mypage/ftf_view/delete_ftf.php";
    var data = {
        "seqno": seqno
    };

    var callback = function (result) {
        var rs = result.split('@');

        if (rs[0] == "1") {
            alert(rs[1]);
            location.href = "/mypage22/ftf_list.html";
        } else {
            alert(rs[1]);
            return false;
        }
    };

    ajaxCall(url, "html", data, callback);
}
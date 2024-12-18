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

        // 그린백일 때 일러스트, 코렐만 가능
        if ($("#prdt_dvs").val() == "gb") {
            if (ext == 'ai' || ext == "eps" ||
                ext == "cdr") {
            } else {
                removeFile();
                return alertReturnFalse("그린백 주문은 ai, eps, cdr(일러스트, 코렐)파일 외엔 업로드 할 수 없습니다.");
            }
        }

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

function removeFile() {
    $("#file_content").html("");
}
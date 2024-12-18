$(document).ready(function () {
    //기본페이지는 명함 으로 설정
    getCateList('001');

});

function getCateList(sortcode) {
    //ul 태그 전체 자식li 태그의 class=on 제거
    $(".category li").attr("class", "");
    //ul 의 마지막 태그의 class=wtp 설정
    $('.category li:last-child').attr("class", "wtp");
    //선택한 li 태그의 class=on 설정
    $("#" + sortcode).parent().attr("class", "on");

    var url = "/ajax/eventmall/load_nowadays_list.php";
    var data = {
        "sortcode": sortcode
    }
    var callback = function (result) {
        $("#list_ajax").html(result);
    }

    //ajax 통신
    ajaxCall(url, "html", data, callback);
}
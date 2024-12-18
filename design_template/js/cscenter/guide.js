$(document).ready(function () {
    $(".paper_dscr").on("click", function () {
        var dvs = $(this).attr("dvs");
        $(".paper_dscr").removeClass("btn_active");
        $(".paper_dscr." + dvs).addClass("btn_active");

        loadPaperDscr(dvs);
    });

    loadPaperDscr("normal");
});

/**
 * @brief 재질설명 검색
 *
 * @param dvs = 구분값
 */
var loadPaperDscr = function (dvs) {
    var url = "/ajax/cscenter/guide/load_paper_dscr.php";
    var data = {
        "dvs": dvs
    };
    var callback = function (result) {
        $("#paper_dscr_list").html(result);
    };

    ajaxCall(url, "html", data, callback);
};
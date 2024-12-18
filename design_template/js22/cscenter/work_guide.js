$(document).ready(function () {
    $(".cate_tab").on("click", function () {
        var dvs = $(this).attr("dvs");
        $(".cate_tab").removeClass("btn_active");
        $(".cate_tab." + dvs).addClass("btn_active");

        var url = "/design_template/images/cscenter/work_guide/work_guide_" + dvs + ".jpg";
        $("#img").attr("src", url);
    });

});
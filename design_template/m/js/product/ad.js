$(document).ready(function() {
    $(".after_option_title").click(function () {
        $(".after_option_table").show();
        $(".after_option_title").hide();
        $(".after_option_title_on").show();
    });

    $(".after_option_title_on").click(function () {
        $(".after_option_table").hide();
        $(".after_option_title_on").hide();
        $(".after_option_title").show();
    });
});

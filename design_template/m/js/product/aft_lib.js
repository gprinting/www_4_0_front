$(document).ready(function() {
    //# 오시
    var $impression = $(".after_impression");
    
    $impression.find(".br").hide();
    var cls = $impression.find("select:eq(0)").find("option:selected").attr("class");
    $impression.find('.' + cls).show();

    // 줄 수 변경시 처리
    $impression.find("select:eq(0)").on("change", function() {
        var cls = $(this).find("option:selected").attr("class");

        $impression.find(".br").hide();
        $impression.find('.' + cls).show();
    });
    // 라디오버튼 클릭시 처리
    $impression.find("input[type='radio']").on("click", function() {
        var dvs = $(this).attr("dvs");
        var $customTr = $(this).parents(".br").next();

        if (dvs === 'C') {
            $customTr.find(".input_type_text_after_size").prop("readonly", false);
        } else {
            $customTr.find(".input_type_text_after_size").prop("readonly", true);
        }
    });

    //# 미싱
    var $dotline = $(".after_dotline");
    
    $dotline.find(".br").hide();
    var cls = $dotline.find("select:eq(0)").find("option:selected").attr("class");
    $dotline.find('.' + cls).show();

    // 줄 수 변경시 처리
    $dotline.find("select:eq(0)").on("change", function() {
        var cls = $(this).find("option:selected").attr("class");

        $dotline.find(".br").hide();
        $dotline.find('.' + cls).show();
    });
    // 라디오버튼 클릭시 처리
    $dotline.find("input[type='radio']").on("click", function() {
        var dvs = $(this).attr("dvs");
        var $customTr = $(this).parents(".br").next();

        if (dvs === 'C') {
            $customTr.find(".input_type_text_after_size").prop("readonly", false);
        } else {
            $customTr.find(".input_type_text_after_size").prop("readonly", true);
        }
    });

    //# 타공
    var $punching = $(".after_punching");

    $punching.find(".br").hide();
    var count = parseInt($punching.find("select:eq(0)").val());
    $punching.find(".br").hide();
    $punching.find(".br").each(function(i) {
        if (count <= i) {
            return false;
        }

        $(this).show();
    });

    // 개수 변경시 처리
    $punching.find("select:eq(0)").on("change", function() {
        var count = parseInt($(this).val());

        $punching.find(".br").hide();
        $punching.find(".br").each(function(i) {
            if (count <= i) {
                return false;
            }

            $(this).show();
        });
    });

    //# 귀도리
    var $rounding = $(".after_rounding");
    var $chkArr = $rounding.find("._chk_round");

    if ($rounding.find("._num").find("option:selected").hasClass("_all")) {
        $chkArr.prop("checked", true);
    }
    
    // 귀도리 수 변경시 처리
    $rounding.find("._num").on("change", function() {
        if ($(this).find("option:selected").hasClass("_all")) {
            $chkArr.prop("checked", true);
        } else {
            $chkArr.prop("checked", false);
        }
    });

    // 네귀도리일 때
    $chkArr.on("click", function() {
        if ($rounding.find("._num").find("option:selected").hasClass("_all")) {
            $chkArr.prop("checked", true);
            alert('네귀도리는 체크를 해제 할 수 없습니다.');
            return false;
        }
    });

    //# 재단
    var $cutting = $(".after_cutting");

    $cutting.find(".cutting_label").hide();

    // 라벨재단 일 때
    $cutting.find("select:eq(0)").on("change", function() {
        var num = $(this).find("option:selected").attr("num");

        $cutting.find(".cutting_label").hide();

        if (num === "label") {
            $cutting.find(".cutting_label").show();
        }
    });
});

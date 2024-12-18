//카테고리 대분류 버튼 클릭시
/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/06/01 임종건 생성
 *============================================================================
 *
 */

$(document).ready(function () {
    //lnb 선택 효과
    mypageLnbEffect();
});

var selectCateBtn = function (sortcode, el) {

    $(".category").removeClass("on");
    $(el).addClass("on");
    var url = "/ajax/mypage/main/load_category_list.php";
    var data = {
        "cate_sortcode": sortcode
    };
    var callback = function (result) {
        $("#tb_html").html(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
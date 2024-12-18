var curSlideNo = 0; // 서브배너 현재 위치

$(document).ready(function(){
    //마우스오버시 인기상품 css 효과
    $(".hit_prdt_frame").hover(function() {
        var dvs = this.id;
        var res = dvs.split("_");
        res = res[3];
        $(".hit_prdt_frame").find("#hit_prdt_frame_on_" + res).fadeIn(200);
    }, function() {
        $(".hit_prdt_frame").find(".hit_prdt_on").fadeOut(100);
    });

    $("#main_prdt_more").click(function() {
        $(".hit_prdt_frame").unbind();
    });

    //마우스오버시 회원맞춤 서비스 효과
    $(".member_service_frame").hover(function() {
        var member_dvs = this.id;
        var member_res = member_dvs.split("_");       
        member_res = member_res[1];
        $(".member_service_frame").find("#member_service_on_" + member_res).fadeIn(200);
    }, function() {
        $(".member_service_frame").find(".member_service_on").fadeOut(100);
    });

    //메인배너 슬라이드
    (function () {
        var mainbanner = $('.mainBanner');
        var list = mainbanner.children('.list');
        var lists = list.children('li');
        var nav = mainbanner.children('.mainBanner_navi');
        var nav_sub = mainbanner.children('nav');
        var navUl = nav.children('ul');
        var prev = nav_sub.children('.prev');
        var next = nav_sub.children('.next');
        var rollingInterval = 6000;
        var autoRolling;
            
        var prevFunc = function() {
            var obj = null;
            if (navUl.children('.mainBanner_navi_on').prev().length > 0) {
                obj = navUl.children('.mainBanner_navi_on').prev().children('a');
            } else {
                obj = navUl.children('li:last-child').children('a');
            }
            
            var idx = $(obj).parent("li").index() + 1;
            var target = $(".list > li:nth-child(" + idx + ")");
            btnFunc(obj, target);
            
            clearTimeout(autoRolling);
            autoRolling = setTimeout(function () { prevFunc(); }, rollingInterval);
        };
        var nextFunc = function() {
            var obj = null;
            if (navUl.children('.mainBanner_navi_on').next().length > 0) {
                obj = navUl.children('.mainBanner_navi_on').next().children('a');
            } else {
                obj = navUl.children('li:first-child').children('a');
            }
            
            var idx = $(obj).parent("li").index() + 1;
            var target = $(".list > li:nth-child(" + idx + ")");
            btnFunc(obj, target);
            
            clearTimeout(autoRolling);
            autoRolling = setTimeout(function () { nextFunc(); }, rollingInterval);
        };
        var btnFunc = function(btnObj, liObj) {
            if (!$(btnObj).hasClass('mainBanner_navi_on')) {
                list.children('.previous').remove();
                list.append(list.children('.on').clone().addClass('previous'));

                list.children('.on').removeClass('on');
                navUl.children('.mainBanner_navi_on').removeClass('mainBanner_navi_on');

                $(btnObj).parent().addClass('mainBanner_navi_on');
                $(liObj).addClass('on');
            }
        };

        lists.each(function(idx) {
            var target = $(this);

            //navUl.append('<li><button></button></li>');
            navUl.find('li:nth-child(' + ++idx + ') > a').on('click', function () {
                btnFunc(this, target);

                clearTimeout(autoRolling);
                autoRolling = setTimeout(function () { nextFunc(); }, rollingInterval);
            });
        });

        //prev
        prev.on('click', function () {
            prevFunc();

            clearTimeout(autoRolling);
            autoRolling = setTimeout(function () { nextFunc(); }, rollingInterval);
        });
        //next
        next.on('click', function () {
            nextFunc();

            clearTimeout(autoRolling);
            autoRolling = setTimeout(function () { nextFunc(); }, rollingInterval);
        });

        //initialize
        list.append(list.children('li:first-child').clone().addClass('previous'));
        list.children('li:first-child').addClass('on');
        navUl.children('li:first-child').addClass('mainBanner_navi_on');

        autoRolling = setTimeout(function () { nextFunc(); }, rollingInterval);
    })();

    // 180615 추가
    showPopupSyncFlag();
});


/**
 * @brief 더보기 버튼 함수 
 *
 */
var showMoreItems = function(dvs) {
    var url = "/ajax/main/load_hit_prdt.php";
    var data = {
        "dvs"   : dvs,
        "count" : $("#more_count").val()
    };
    var callback = function(result) {
        $("#main_more").append(result);
        showMoreCountUp();
        $(".hit_prdt_frame").hover(function() {
            var dvs = this.id;
            var res = dvs.split("_");
            res = res[3];
            $(".hit_prdt_frame").find("#hit_prdt_frame_on_" + res).fadeIn(200);
        }, function() {
            $(".hit_prdt_frame").find(".hit_prdt_on").fadeOut(100);
        });


        //var endDvs = $("#lastOfMoreItem").val();
        //kif (endDvs == "1"){
            $("#main_prdt_more").hide();
        //}
    };

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 더보기 버튼 함수
 *
 */
var showMoreCountUp = function() {
    var cnt = $("#more_count").val();
    if (cnt == "") {
        $("#more_count").val("1");
    } else {
        ++cnt;
        $("#more_count").val(cnt);
    }
};


/**
 * @brief 로그인
 *
 */
var memberLogin = function() {
    window.open('about:blank');

}

var closePop = function() {
    if(document.pop_form.chkbox.checked) {

    }
    document.all['mainPop'].style.visibility = "hidden";
}

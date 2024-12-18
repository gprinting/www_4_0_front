var popup = null;
    var interval = null;

        // 메인페이지 소셜로그인
    var showSocialLogin = function(dvs) {
        switch(dvs) {
            case "naver" :
                popup = window.open('/oauth/info_redir.php?dvs=naver','','width=500, height=760, scrollbars=no');
                break;
            
            case "kakao" :
                loginWithKakao();
                break;

            case "fb" :
                fbLogin();
                break;

            case "google" :
                popup = window.open('/oauth/info_redir.php?dvs=google','','width=500, height=760, scrollbars=no');
                break;
        }
    };

    window.fbAsyncInit = function() {
        FB.init({
            appId   : '1976307912686315',
            cookie  : true,
            xfbml   : true,
            version : 'v2.10'
        });

        FB.AppEvents.logPageView();   
    };
    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/ko_KR/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    } (document, 'script', 'facebook-jssdk')); 

    var fbLogin = function() {
        var callback = function(res1) {

            FB.api(
                "/me?fields=id,name,email",
                "post",
                {
                    "access_token" : res1.authResponse.accessToken,
                    "fields" : "name,email"
                },
                function(res2) {
                    $.ajax({
                        type     : "POST",
                        url      : "/oauth/oauth_callback_facebook.php",
                        data     : res2,
                        //dataType : "text",
                        success  : function(result) {
                            location.reload();
                        }
                    });
                }
            );

            //FB.logout(function(){});
        };

        FB.login(callback, {"scope" : "public_profile,email"});
    };

    Kakao.init('d8ed4e0171350fa18a476a22ab1c2412');
    function loginWithKakao() {
        Kakao.Auth.login({
            success: function(authObj) {
                Kakao.API.request({
                    url: '/v1/user/me',
                    success: function(res) {
                        $.ajax({
                            type    : "POST",
                            url     : "/oauth/oauth_callback_kakao.php", 
                            data    : res,
                            success : function(result) {
                                if (checkBlank(result)) {
                                    location.reload();
                                } else {
                                    return alertReturnFalse(result);
                                }
                            }
                        });
                    },
                    fail: function(error) {
                        alert(JSON.stringify(error));
                    }
                });
            },
            fail: function(err) {
                alert(JSON.stringify(err));
            }
        });
    };
/*
*/

    // 상품상세페이지 팁 노출
    $(function(){
        $('.icon_delivery_price').mouseenter(function(){
            $(".tip_delivery_price").show();
        });
        $('.icon_delivery_price').mouseleave(function(){
            $(".tip_delivery_price").hide();
        });

        $('.icon_delivery_scheduled').mouseenter(function(){
            $(".tip_delivery_scheduled").show();
        });
        $('.icon_delivery_scheduled').mouseleave(function(){
            $(".tip_delivery_scheduled").hide();
        });

        $('.icon_favorite_product').mouseenter(function(){
            $(".tip_favorite_product").show();
        });
        $('.icon_favorite_product').mouseleave(function(){
            $(".tip_favorite_product").hide();
        });

        $('.icon_cutting_label').mouseenter(function(){
            $(".tip_cutting_label").show();
        });
        $('.icon_cutting_label').mouseleave(function(){
            $(".tip_cutting_label").hide();
        });

        $('.icon_title_thomson').mouseenter(function(){
            $(".tip_thomson").show();
        });
        $('.icon_title_thomson').mouseleave(function(){
            $(".tip_thomson").hide();
        });
        
        $('.icon_title_thomson_type').mouseenter(function(){
            $(".tip_thomson_type").show();
        });
        $('.icon_title_thomson_type').mouseleave(function(){
            $(".tip_thomson_type").hide();
        });

        $('.icon_check_depo').mouseenter(function(){
            $(".tip_check_depo").show();
        });
        $('.icon_check_depo').mouseleave(function(){
            $(".tip_check_depo").hide();
        });

    });

    // 선입금 정보 박스
    $(function(){
        $(".myCash").mouseenter(function(){
			$(".tip_myCash").show();
		});
		$(".myCash_wrapper").mouseleave(function(){
			$(".tip_myCash").hide();
		});
    });


    // IE 버전 체크
    var ieVersionChk = function() {

        var word;
        var version = -1;

        var agent = navigator.userAgent.toLowerCase();
        var name = navigator.appName;

        // IE 구버전(10또는 그 이하)
        if (name == "Microsoft Internet Explorer") {
            word = "msie ";
        } else {
            // IE 11
            if (agent.search("trident") > -1) {
                word = "trident/.*rv:";
            // IE 12
            } else if (agent.search("edge/") > -1) {
                word = "edge/";
            }
        }

        var reg = new RegExp(word + "([0-9]{1,})(\\.{0,}[0-9]{0,1})");
        if (reg.exec(agent) != null) {
            version = RegExp.$1;
        }

        return parseInt(version);
    };
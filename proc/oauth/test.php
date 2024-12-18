<html>
    <head>
        <script src="/design_template/js/lib/jquery-1.11.2.min.js"></script>
        <script src="https://developers.kakao.com/sdk/js/kakao.min.js"></script>

        <script>
            window.fbAsyncInit = function() {
                FB.init({
                    appId   : '1942240162459456',
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
                    console.log(res1);

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
                                url      : "/test-devel/oauth_callback_facebook.php",
                                data     : res2,
                                //dataType : "text",
                                success  : function(result) {
                                    console.log(result);
                                }
                            });
                        }
                    );

                    //FB.logout(function(){});
                };

                FB.login(callback, {"scope" : "public_profile,email"});
            };
        </script>
        <script>
            Kakao.init('d8ed4e0171350fa18a476a22ab1c2412');
            function loginWithKakao() {
                Kakao.Auth.login({
                    success: function(authObj) {
                        Kakao.API.request({
                            url: '/v1/user/me',
                            success: function(res) {
                                $.ajax({
                                    type    : "POST",
                                    url     : "/test-devel/oauth_callback_kakao.php", 
                                    data    : res,
                                    success : function(result) {
                                        console.log(result);
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
        </script>
    </head>
    <body>
        <button type="button" onclick="window.open('/test-devel/test_redir.php?dvs=google','','width=500, height=760, scrollbars=no');">구글 로그인</button>
        <button type="button" onclick="window.open('/test-devel/test_redir.php?dvs=naver','','width=500, height=760, scrollbars=no');">네이버 로그인</button>
        <button type="button" onclick="loginWithKakao();">카카오 로그인</button>
        <button type="button" onclick="fbLogin();">페이스북 로그인 js</button>
        <button type="button" onclick="window.open('/test-devel/com_login.html','','width=500, height=760, scrollbars=no');">통상 로그인</button>
    </body>
</html>

<!doctype html>
<html lang="kr">

<head>
    <meta name="csrf-token" content="H0cwvI37H7v6CgPhJ2qDjYkMP7SVd3e86KvDykTM">
    <!--
	<meta name="app-url" content="http://gprinting.printfy.co.kr">
	-->
    <meta name="app-url" content="http://dev2.gprinting.co.kr/">

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Favicon -->
    <title></title>

    <!-- google font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700">

    <!-- DAON core css -->
    <link rel="stylesheet" href="http://gprinting.printfy.co.kr/public/assets/css/vendors.css">
    <link rel="stylesheet" href="http://gprinting.printfy.co.kr/public/assets/css/daon-core.css">
    <link rel="stylesheet" href="http://gprinting.printfy.co.kr/public/assets/css/custom-style.css">

    <script>
        var DAON = DAON || {};
    </script>
</head>

<body>
    <div class="daon-main-wrapper d-flex">
        <div class="flex-grow-1">
            <div class="container">
                <div class="row">
                    <div class="col-xl-6 col-lg-8 col-md-9 col-sm-12 p-1">

                        <div>
                            <h3>책자</h3>
                        </div>
                        <iframe id="estimate-iframe"
                            src="http://gprinting.printfy.co.kr/embed/estimate/book-normal?path=website&type=estimate_cost"
                            allowfullscreen frameborder="1" scrolling="no"
                            style="width:100%;height:100%;border:none;overflow:hidden;"></iframe>

                    </div>

                    <div class="col-xl-3 col-lg-4 col-md-3 col-sm-6 p-1">
                        <div id="pnl_estimate"></div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- .daon-main-wrapper -->

    <script src="http://gprinting.printfy.co.kr/public/assets/js/vendors.js"></script>
    <script src="http://gprinting.printfy.co.kr/public/assets/js/daon-core.js"></script>
    <script src="http://gprinting.printfy.co.kr/public/assets/js/php.js"></script>
    <script src="http://gprinting.printfy.co.kr/public/assets/js/daon-estimate.js"></script>

    <script type="text/javascript">
        var previous;

        $(document).ready(function () {
            resizeIframe();
        });

        function resizeIframe() {
            var iframe = document.getElementById("estimate-iframe");
            var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
            var body = innerDoc.body,
                html = innerDoc.documentElement;

            // 내용의 실제 높이를 구함
            var height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight) + parseInt(20);

            // iframe의 높이를 설정
            iframe.style.height = height + 'px';
        }

        window.addEventListener("message", function (e) {

            if (e.origin == 'http://gprinting.printfy.co.kr') {
                if (e.data.path == 'estimate') {
                    resizeIframe();
                    console.log(e.data.data);
                    $('#pnl_estimate').html(e.data.data.printsummary);
                }
            }
        }, false);

    </script>

    <script type="text/javascript">
    </script>
</body>

</html>
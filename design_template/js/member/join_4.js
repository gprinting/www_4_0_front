//로그인 하기
var goLogin = function (goUrl) {

    var url = "/common/login.php";
    var data = {
        "id": $("#join_id").val(),
        "pw": $("#join_pw").val()
    };

    var callback = function (result) {
        if (result === false) {
            alert("로그인에 실패했습니다.");
            location.href = "/member/login.html";
            return false;
        } else {
            location.href = goUrl;
        }
    };

    ajaxCall(url, "json", data, callback);
}
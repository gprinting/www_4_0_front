$(document).ready(function () {
    //lnb 선택 효과
    mypageLnbEffect();
    loadDeliveryGroup();
});

var loadDeliveryGroup = function () {
    var url = "/ajax/mypage/delivery_group/load_delivery_group.php";
    var data = {};
    var callback = function (result) {
        $("#group_list").html(result.group);
        $("#group_count").html(result.group_count);
        $("#group_order_count").html(result.group_order_count);

        $("#unit_list").html(result.unit);
        $("#unit_order_count").html(result.unit_order_count);
        hideMask();
    };

    showMask();
    ajaxCall(url, "json", data, callback);
}

var modiGroup = function (dlvrNum, orderSeqno, dvs) {
    var url = "/proc/mypage/delivery_group/modi_delivery_group.php";
    var data = {
        "dlvr_num": dlvrNum,
        "order_seqno": orderSeqno,
        "dvs": dvs
    };
    var callback = function (result) {
        if (!checkBlank(result.msg)) {
            alert(result.msg);
        }

        location.reload();
        return false;
    };

    showMask();
    ajaxCall(url, "json", data, callback);
};

var goOrderAll = function (orderSeqno) {
    var url = "/mypage/order_all.html";
    url += "?order_seqno=" + orderSeqno;
    url += "&from=NONE";
    url += "&to=NONE";

    window.location.href = url;
};
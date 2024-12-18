/**
 * @brief 제목 수정
 *
 * @param seq = 일련번호
 */
var modiTitle = {
    "seq": null,
    "title": null,
    "exec": function (seq) {
        var orgTitle = $("#title_td_" + seq).html();
        var title = escapeHtml($("#title_" + seq).val());

        if (checkBlank(title) === true) {
            alert("제목을 입력해주세요.");
            $("#title_" + seq).val(orgTitle);
            return false;
        }

        this.seq = seq;
        this.title = title;

        var url = "/ajax/order/modi_order_title.php";
        var data = {
            "val": title,
            "seq": seq
        };
        var callback = function (result) {
            if (result === 'F') {
                alert("제목 수정에 실패했습니다.");
                return false;
            }

            var seq = modiTitle.seq;
            var title = modiTitle.title;
            $("#title_td_" + seq).html(title);

            modiTitle.seq = null;
            modiTitle.title = null;

            deliveryGroupSetting();
        }

        ajaxCall(url, "text", data, callback);
    }
}
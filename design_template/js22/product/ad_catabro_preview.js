$(document).ready(function () {
    // 미리보기 추가로 초기화
    preview.addContainer.inner1 = $('#preview1');
    preview.addContainer.inner2 = $('#preview2');
    preview.addContainer.inner3 = $('#preview3');

    addPreviewInit("inner1");
    addPreviewInit("inner2");
    addPreviewInit("inner3");

    $('#preview2').hide();
    $('#preview3').hide();

    var prdtDvsArr = $("#prdt_dvs").val().split('|');
    var prdtDvsLen = prdtDvsArr.length;

    for (var i = 1; i < prdtDvsLen; i++) {
        var dvs = prdtDvsArr[i];
        var selector = '.' + dvs + '_after';

        // 2016-12-13
        // 이부분이 클로저이기 때문에
        // 셀렉터는 다르지만 callback 함수가 해당 반복문 내의
        // 스코프를 공유하기 때문에 마지막 dvs만 적용된다
        // 따라서 dvs를 분리하기 위해서는 별도의 콜백함수를 이용해
        // 해당 콜백함수별 스코프(콜백함수별로 dvs 별도로 가짐)를 적용해서 처리해야한다
        $(selector + ' > ._closed').on('click', slideDownCallback(dvs));
        $(selector + ' > ._opened').on('click', slideUpCallback(dvs));

        initAfter(dvs);
    }
});

/**
 * @brief 내지 1, 2, 3 추가로 초기화
 */
var addPreviewInit = function (dvs) {
    var paperClone = preview.paper.clone();

    preview.addContainer[dvs].append(paperClone);
};
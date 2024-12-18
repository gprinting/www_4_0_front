$(document).ready(function () {
    $(window).resize(function () {
        windowWidth = $(window).width();
        windowHeight = $(window).height();
        bodyWidth = $('body').width(),
            windowScroll();
    });

    $(window).scroll(function () {
        windowScroll();
    });

    windowScroll();

    //미리보기
    preview.container = $('div.preview');
    preview.cuttingSizeW = $('._cuttingSize input[type=text]:eq(0)');
    preview.cuttingSizeH = $('._cuttingSize input[type=text]:eq(1)');
    preview.workingSizeW = $('._workingSize input[type=text]:eq(0)');
    preview.workingSizeH = $('._workingSize input[type=text]:eq(1)');
    preview.roundingDd = $('.after .option._rounding dd');
    preview.impressionDd = $('.after .option._impression dd');
    preview.impressionMM = preview.impressionDd.find('input[type=text]');
    preview.thomsonImpressionDd = $('.after .option._thomson_impression dd');
    preview.thomsonImpressionMM = preview.thomsonImpressionDd.find('input[type=text]');
    preview.dotlineDd = $('.after .option._dotline dd');
    preview.dotlineMM = preview.dotlineDd.find('input[type=text]');
    preview.foldlineDd = $('.after .option._foldline dd');
    preview.foldlineMM = preview.foldlineDd.find('input[type=text]');
    preview.punchingDd = $('.after .option._punching dd');
    preview.punchingMM = preview.punchingDd.find('input[type=text]');
    preview.cuttingDd = $('.after .option._cutting dd');
    preview.initialize();
    preview.cuttingArea = preview.content.find('div.cutting');

    //재단선, 작업선 버튼 on
    preview.btns.find('.cuttingLine')
        .on('click', function () {
            $(this).toggleClass('on');

            if (preview.cuttingline.hasClass("reverse")) {
                preview.workingline.toggleClass('on');
            } else {
                preview.cuttingline.toggleClass('on');
            }
        }).trigger("click");
    preview.btns.find('.workingLine')
        .on('click', function () {
            $(this).toggleClass('on');

            if (preview.workingline.hasClass("reverse")) {
                preview.cuttingline.toggleClass('on');
            } else {
                preview.workingline.toggleClass('on');
            }
        }).trigger("click");

    // number only
    var numberOnlys = $('input[type=text].mm, input[type=text].page, ._cuttingSize input[type=text], ._workingSize input[type=text]');

    numberOnlys.on('keydown', function (key) {
        /*if (key.keyCode == 16 || key.keyCode == 17 || key.keyCode == 18 || key.keyCode == 91 || (key.keyCode > 36 && key.keyCode < 41) ) {
            optionKey ++;
        }*/

        if ( /*!optionKey && */ !(key.keyCode > 47 && key.keyCode < 58 || key.keyCode > 95 && key.keyCode < 106 || key.keyCode == 8 || key.keyCode == 46 || key.keyCode > 36 && key.keyCode < 41 || key.keyCode == 190) && key.keyCode != 9) {
            return false;
        }
    });

    numberOnlys.on('blur', function () {
        /*if (optionKey > 0) {
            optionKey -= 1;
        } else {*/
        $(this).val($(this).val().replace(/^0*/g, '').replace(/[^0-9\.]/g, '').replace(/\.+/g, '.'));
        //}
    });

    $("._cuttingSize input[type=text]").on("focus", function () {
        $(this).select();
    });

    $('.contents .after ._folder ul').add('.contents .after .option').add('.contents .price ._folder').css('display', 'block');
    $('.contents .opt ._folder ul').add('.contents .opt .option').add('.contents .price ._folder').css('display', 'block');
    //3depth menu 변경
    $('._productName').on('change', function () {
        var menuText = $(this).children(':selected').text();
        $('ol.location li:last-child option').each(function () {
            this.selected = $(this).text() == menuText;
        });
    });

    //후공정(후가공) 기본 접기
    //$('.after ._folder').add('.price ._folder').add('.pages > ._folder').slideUp(0);
    // 펴게 할 때는 주석 반대로
    // -> 180503 수정, 기본 펼치기로 변경
    $('.aft_sec._folding ._closed').trigger("click");
    $('.price ._folder').slideUp(0);

    $('.selection .option ._closed').on('click', function () {
        $(this).closest('.option').find('._folder').stop().animate({
            'width': '195px'
        }, 300);
    });
    $('.selection .option ._opened').on('click', function () {
        $(this).closest('.option').find('._folder').stop().animate({
            'width': '0'
        }, 300);
    });

    if (!$('.selection .option').hasClass('_on')) {
        $('.selection .option ._opened').closest('.option').find('._folder').stop().animate({
            'width': '0'
        }, 0);
    }

    $('.after .option').slideUp(0);

    $('.pages ._add').on('click', function () {
        $(this).closest('._folding').removeClass("_off").addClass('_on').children('._folder').stop().slideDown(300);
        orderSummary();
    });
    $('.pages ._del').on('click', function () {
        $(this).closest('._folding').removeClass('_on').addClass('_off').children('._folder').stop().slideUp(300);
        orderSummary();
    });

    // option 보이기
    $('select._toOption').on('change', function () {
        $('._byOption._on').removeClass('_on');
        $('._byOption._' + $(this).children(':selected').text().replace(/\s/g, '').replace(/\(/g, '').replace(/\)/g, '')).addClass('_on');
        size();
    });
    $('select._toOption').each(function () {
        $('._byOption._on').removeClass('_on');
        $('._byOption._' + $(this).children(':selected').text().replace(/\s/g, '').replace(/\(/g, '').replace(/\)/g, '')).addClass('_on');
    });

    //사이즈
    size();

    $('.size select').on('change', function () {
        if ($(this).hasClass("ao_size_type")) {
            return false;
        }
        size();
    });

    $('.size .wings ._cutSize input[type=text]').on('keyup', function () {
        size();
        calcManuPosNum.exec();
    });

    $('.size .wings dd.seneca button').on('click', function () {
        size();
    });

    //사이즈 빈칸일 경우
    $('.size input[type=text]').each(function () {
        if ($(this).val() == '') {
            $(this).val(0);
        }
    });

    $('.size input[type=text]').on('blur', function () {
        if ($(this).prop("readonly")) {
            return false;
        }

        if ($(this).val() == '') {
            $(this).val(0);
        }

        size();
        calcManuPosNum.exec();
    });

    //후공정
    if ($("#prdt_dvs").length === 0) {
        return false;
    }
    var prdtDvsArr = $("#prdt_dvs").val().split('|');
    // 171025 수정 prdt_dvs 첫번 째 것만 초기화하고 나머지는 별도 js에서 처리
    var dvs = prdtDvsArr[0];
    var selector = '.' + dvs + '_after';

    $(selector + ' > ._closed').on('click', slideDownCallback(dvs));
    $(selector + ' > ._opened').on('click', slideUpCallback(dvs));

    initAfter(dvs);

    //수량
    //수량 range bar 설정
    if ($('.amount .range .mark').length !== 0) {
        //수량 select 변경 시 하단 range bar 이동
        $('.contents ._amount').on('change', function () {
            rangeBarBySelect();
        });

        rangeBarText();
    }

    //주문 내역 변경
    $('select._relatedSummary, ._amount, ._set, ._size, ._preset, ._toOption').on('change', function () {
        orderSummary();
    });
    $('input[type=checkbox]._relatedSummary, .size .wings dd.seneca button').on('click', function () {
        orderSummary();
    });
    $('._relatedSummary input[type=text]').on('keyup', function () {
        orderSummary();
    });

    //가격
    $('.price ._closed').on('click', function () {
        $('.price ._folder').stop();
        $('.price ._folder').slideDown(300);
    });
    $('.price ._opened').on('click', function () {
        $('.price ._folder').stop();
        $('.price ._folder').slideUp(300);
    });

    //제품 사진
    $('div.picture .thumb').on('click', function () {
        $(this).closest('ul').children('._on').removeClass('_on');
        $(this).closest('li').addClass('_on');
        var target = $(this).attr('target');
        var target2 = $(this).attr('target2');
        $("#pic_view").attr("src", target);
        $("#pic_view").attr("data-zoom-image", target2);
        $("#zoom_pic .zoomWindow").css("background-image", "url(" + target2 + ")");
    });

    //초기화
    $('div.picture li:first-child .thumb').click();
});

//사이즈
function size() {
    var size = $('.size'),
        sizeSelect = $('select._size'),
        presetSelect = $('select._preset'),
        cuttingSize = $('dd._cuttingSize'),
        workingSize = $('dd._workingSize'),
        designSize = $('dd._designSize'),
        thomsonSize = $('dd._thomsonSize'),
        roomNumber = $('div._roomNumber'), // 자리수 계산을 위해 추가합니다.
        number = new Array(),
        switcher = new Boolean(),
        wings = $('.wings')[0] ? $('.wings') : false,
        sizeSelected = sizeSelect.children('option:selected'),
        totalW = 0,
        totalH = 0,
        descriptionOption;

    var gap = 0;
    if ($('.size dd._workingSize').length > 0) {
        gap = Number($('.size dd._workingSize').attr('class')
            .replace('_workingSize', '')
            .replace('/\s/g', '')
            .replace('_gap', ''));
    }

    if (sizeSelected.hasClass('_preset') || sizeSelected.length === 0) {
        //규격사이즈
        cuttingSize.find('input[type=text]').attr('readonly', true);
        presetSelect.addClass('_on');
        roomNumber.removeClass('_on');

        if (sizeSelected.length === 0 ||
            sizeSelected.attr('class').indexOf('_cuttingWH') == -1) { //preset select에서 선택하는 경우
            number = presetSelect.children('option:selected').attr('class');
        } else {
            //size select 따라 size가 정해지는 경우
            number = sizeSelected.attr('class');
        }

        if (number) {
            number = number.split(' ');
            for (var iCutting = 0; iCutting < number.length; iCutting++) {
                if (number[iCutting].indexOf('_cuttingWH') != -1) {
                    number = number[iCutting].replace('_cuttingWH', '').split('-');
                }
            }
            cuttingSize.find('input[type=text]:eq(0)').val(Number(number[0]));
            cuttingSize.find('input[type=text]:eq(1)').val(Number(number[1]));

            totalW += Number(number[0]);
            totalH += Number(number[1]);
        }
    } else {
        presetSelect.removeClass('_on');
        roomNumber.addClass('_on');

        if ($("#show_size").length > 0) {
            presetSelect.addClass('_on');
        }

        //비규격사이즈
        cuttingSize.find('input[type=text]').attr('readonly', false);
        number[0] = cuttingSize.find('input[type=text]:eq(0)').val();
        number[1] = cuttingSize.find('input[type=text]:eq(1)').val();

        if(number[0] == "") number[0] = 50;
        if(number[1] == "") number[1] = 50;
        /*
        if ($("#bl_cutting").prop("checked") === true) {
            preview.cutting();
        }
        */

        totalW += Number(number[0]);
        totalH += Number(number[1]);
    }

    if (sizeSelected.length > 0 &&
        sizeSelected.attr('class').indexOf('_workingWH') != -1) {
        //size select에서 정하는 경우
        number = sizeSelected.attr('class').split(' ');
        for (var iWorking = 0; iWorking < number.length; iWorking++) {
            if (number[iWorking].indexOf('_workingWH') != -1) {
                number = number[iWorking].replace('_workingWH', '').split('-');
            }
        }
        $('.size dd._workingSize input[type=text]:eq(0)').val(Number(number[0]));
        $('.size dd._workingSize input[type=text]:eq(1)').val(Number(number[1]));
    } else if (presetSelect.hasClass('_on') &&
        presetSelect.children('option:selected').attr('class').indexOf('_workingWH') != -1) {
        var tempNumber = number;
        //preset select에서 정하는 경우
        number = presetSelect.children('option:selected').attr('class').split(' ');
        for (var iWorking = 0; iWorking < number.length; iWorking++) {
            if (number[iWorking].indexOf('_workingWH') != -1) {
                number = number[iWorking].replace('_workingWH', '').split('-');
            }
        }

        // 16-10-27 엄준현 자유형 도무송 때문에 추가함
        if (number[0] === '0' && number[1] === '0') {
            var dvs = $("#prdt_dvs").val();
            $("input[type='checkbox'][name='" + dvs + "_chk_after[]']").each(function () {
                if ($(this).prop("checked") === true) {
                    var aftKoName = $(this).val();
                    var info = {};

                    if (aftKoName === "재단") {
                        tempNumber[0] = $("#st_cutting_wid").val();
                        tempNumber[1] = $("#st_cutting_vert").val();
                    }
                }
            });


            number[0] = Number(tempNumber[0]) + gap;
            number[1] = Number(tempNumber[1]) + gap;
        }

        $('.size dd._workingSize input[type=text]:eq(0)').val(Number(number[0]));
        $('.size dd._workingSize input[type=text]:eq(1)').val(Number(number[1]));
    } else {
        if (number) {
            $('.size dd._workingSize input[type=text]:eq(0)').val(Number(number[0]) + gap);
            $('.size dd._workingSize input[type=text]:eq(1)').val(Number(number[1]) + gap);
        }
    }

    //디자인 사이즈
    size.find('._size option:selected, select._on option:selected').each(function (i) {
        number = $(this).attr('class');
        if (number) {
            if (number.indexOf('_designWH') != -1) {
                number = number.split(' ');
                for (var iDesign = 0; iDesign < number.length; iDesign++) {
                    if (number[iDesign].indexOf('_designWH') != -1) {
                        number = number[iDesign].replace('_designWH', '').split('-');
                    }
                }
                designSize.find('input[type=text]:eq(0)').attr('readonly', true).val(Number(number[0]));
                designSize.find('input[type=text]:eq(1)').attr('readonly', true).val(Number(number[1]));
            }
        }
    });
    designSize.find('input[type=text]').attr('readonly', true);

    //도무송 사이즈
    switcher = false;
    size.find('._size option:selected, select._on option:selected').each(function (i) {
        number = $(this).attr('class');
        if (number) {
            if (number.indexOf('_thomsonWH') != -1) {
                number = number.split(' ');
                for (var iDesign = 0; iDesign < number.length; iDesign++) {
                    if (number[iDesign].indexOf('_thomsonWH') != -1) {
                        number = number[iDesign].replace('_thomsonWH', '').split('-');
                    }
                }
                thomsonSize.find('input[type=text]:eq(0)').val(Number(number[0]));
                thomsonSize.find('input[type=text]:eq(1)').val(Number(number[1]));
                switcher = true;
            }
        }
    });
    thomsonSize.find('input[type=text]').attr('readonly', switcher);

    //날개 사이즈
    if (wings && wings.hasClass('_on')) {
        wings.find('._cutSize').each(function () {
            $(this).find('input[type=text]:eq(0)').val(cuttingSize.find('input[type=text]:eq(0)').val());
            totalW += Number($(this).find('input[type=text]:eq(1)').val());
            $(this).find('input[type=text]:eq(2)').val(cuttingSize.find('input[type=text]:eq(1)').val());
            totalH += Number($(this).find('input[type=text]:eq(3)').val());
        });
        wings.find('._workSize').each(function () {
            $(this).find('input[type=text]:eq(0)').val(Number($(this).prev().prev().find('input[type=text]:eq(0)').val()));
            $(this).find('input[type=text]:eq(1)').val(Number($(this).prev().prev().find('input[type=text]:eq(1)').val()) + gap);
            $(this).find('input[type=text]:eq(2)').val(Number($(this).prev().prev().find('input[type=text]:eq(2)').val()));
            $(this).find('input[type=text]:eq(3)').val(Number($(this).prev().prev().find('input[type=text]:eq(3)').val()) + gap);
        });
    }

    //description
    descriptionOption = $('select._size, select._size + select._on').children('option._description:selected');
    $('.size ul._description li._on').removeClass('_on');
    $('.size ul._description li._' + descriptionOption.text().replace(/\s/g, '').replace(/\(/g, '').replace(/\)/g, '')).addClass('_on');

    //총 사이즈
    totalW += Number($('.size dd.seneca input[type=text]').val());
    $('.size .total input[type=text]:eq(0)').val(totalW);
    $('.size .total input[type=text]:eq(1)').val(totalH);

    preview.paperSize();

    dvs = $("#prdt_dvs").val();
    if(dvs == "dt" || dvs == "dt_st_tomson") {
        changeDigitalCuttingSize();
    }
}

function wingSize(that) {
    $(that).closest('dd').next().next().find('input[type=text]:eq(' + $(that).closest('dd').find('input[type=text]').index($(that)) + ')').val(Number($(that).val()) + gap);
}

/**
 * @brief 반복문 클로저 문제 때문에 콜백분리
 */
var slideDownCallback = function (dvs) {
    return function () {
        down(dvs);
    };
};
var down = function (dvs) {
    $('.' + dvs + '_after_list').stop().slideDown(300);
};
/**
 * @brief 반복문 클로저 문제 때문에 콜백분리
 */
var slideUpCallback = function (dvs) {
    return function () {
        up(dvs);
    };
};
var up = function (dvs) {
    $('.' + dvs + '_after_list').stop().slideUp(300);
};

//수량 select에 따른 하단 바 설정
var optionPosition;

function rangeBarBySelect() {
    var value = optionPosition[$('.contents ._amount option:selected').index('.contents ._amount option')];

    $('.contents .range ._anchor').css('left', value);
    $('.contents .range .hr.on').css('width', value);
}

function rangeBarText() {
    var marks = $('.amount .range .mark').html('');

    var amountOptions = $('.amount ._amount option');
    var amountOptionNum = amountOptions.length;
    var markNum2 = Math.floor((amountOptionNum - 1) / 3);
    var markNum3 = Math.floor((amountOptionNum - 1) * 2 / 3);
    var optionRange = new Array(amountOptionNum - 1);
    var rangeUnit = 100 / (amountOptionNum - 1) / 2;
    var rangeAnchor = $('.range ._anchor');
    var rangeBar = $('.range .hr.on');
    var hrWidth = $('.hr').width();
    var anchorLeft = Number(rangeAnchor.css('left').replace('px', ''));
    var selectedOption = $('.contents ._amount option:selected').index('.contents ._amount option');
    var rangeBarTimer;
    // 16.01.07 드래그로 수량변경시 가격검색하기 위해서 추가
    var amt;

    optionPosition = new Array(amountOptionNum);

    //수량 option별 range bar 구간
    for (var iOption = 0; iOption < amountOptionNum - 1; iOption++) {
        optionRange[iOption] = hrWidth / 100 * rangeUnit * ((iOption + 1) * 2 - 1);
        optionPosition[iOption] = hrWidth / 100 * rangeUnit * ((iOption + 1) * 2 - 2);
    }
    optionPosition[amountOptionNum - 1] = hrWidth;

    //mark 배치
    marks.append('<li>' + $(amountOptions[0]).text() + '</li>');
    marks.append('<li style="left: ' + optionPosition[markNum2] + 'px;">' + $(amountOptions[markNum2]).text() + '</li>');
    marks.append('<li style="left: ' + optionPosition[markNum3] + 'px;">' + $(amountOptions[markNum3]).text() + '</li>');
    marks.append('<li>' + $(amountOptions[amountOptionNum - 1]).text() + '</li>');

    //초기화
    rangeBarBySelect();

    //anchor drag
    rangeAnchor.draggable({
        addClasses: false,
        axis: 'x',
        containment: '.range .bar',
        cursor: false
    }, {
        start: function () {
            rangeAnchor.addClass('_on');
        },
        drag: function () {
            anchorLeft = Number(rangeAnchor.css('left').replace('px', ''));
            rangeBar.width(anchorLeft);
            amt = selectByRangeBar();
        },
        stop: function () {
            selectByRangeBar();

            rangeAnchor.removeClass('_on');
            rangeBarBySelect();
            orderSummary();
            // 가격검색용 함수
            changeAmt(amt);
        }
    });

    //수량 range bar에 따른 수량 선택
    function selectByRangeBar() {
        selectedOption = 0;
        while (optionRange[selectedOption] < anchorLeft) {
            selectedOption += 1;
        }
        var amt;
        $(amountOptions).each(function (i) {
            if (i == selectedOption) {
                amountOptions[i].selected = true;
                amt = amountOptions[i].value;
            } else {
                amountOptions[i].selected = false;
            }
        });

        return amt;
    }
}

//후공정
function afterOverview(dvs) {
    var html = '',
        separator = '';
    var selector = '.' + dvs + "_after_list";

    // 추가 후공정 부분 html 생성
    var afterAddStr = "";

    var afterAddHtml = "";
    $(selector + ' > .option._on dl').each(function (idx) {
        var after = $(this).children('dt').text();

        afterAddStr += after + '|';

        afterAddHtml += '<li>';
        afterAddHtml += after;
        afterAddHtml += ' [';

        // 16-12-17 ujh 박부분 가격 위치 변경한 것 때문에 요약 안나와서 추가
        if (after === '박') {
            var temp = '';
            $(this).find("select").each(function () {
                var val = $(this).find("option:selected").text();
                temp += val + '/';
            });
            afterAddHtml += temp.substr(0, temp.length - 1);
        } else {
            $(this).children('dd.price + dd').each(function () {
                $(this).children().each(function () {
                    if ($(this).is('select')) {
                        afterAddHtml += separator + $(this).children('option:selected').text();
                    } else if ($(this).is('input[type=text]')) {
                        afterAddHtml += separator + $(this).val();
                    }
                    /*else if ($(this).is('label')) {
                                               if ($(this).children(':first-child').is('input[type=text]')) {
                                                   html += separator + $(this).val();
                                               } else if ($(this).children(':first-child').is('input[type=checkbox]:checked')) {
                                                   html += separator + $(this).text();
                                               } else if ($(this).children(':first-child').is('input[type=radio]:checked')) {
                                                   html += separator + $(this).text();
                                               }
                                           }*/
                    separator = '/';
                });
            });
        }

        afterAddHtml += ']</li> ';
        separator = '';
    });

    var html = afterAddHtml;

    if (html == '') {
        if ($(".aft_chkbox").length === 0) {
            html = '<li>본 상품은 추가 후가공이 없습니다.</li>';
        } else {
            html = '<li>후가공을 선택해주세요.</li>';
        }
    }
    $('.' + dvs + '_overview ul').html(html);

    orderSummary()
}

//주문내역
function orderSummary() {
    $('.summary ul').html('');

    // 책자형은 divs 다음에 후공정, 나머지는 div 다음에 옵션
    // 그래서 책자형 후공정 요약시 분기치는 용도로 사용
    var isBook = $("div.selection").length > 1 ? true : false;
    var afterOverview = $('.aft_sec .overview ul li').clone().addClass('after');

    $("div.selection").each(function () {
        if ($(this).hasClass("_off")) {
            return true;
        }
        var title = $(this).find("h3").text();

        var html = '<li>';

        if (!checkBlank(title)) {
            html += "<strong style=\"font-weight: bold;\">" + title + "</strong> : ";
        }

        var pageFlag = false;
        var inner = '';
        $(this).find('._relatedSummary').each(function () {
            if ($(this).hasClass('size')) {
                //!! 사이즈
                // 책자형 상품 날개있음으로 변경시
                if ($(this).find('.wings').hasClass('_on')) {
                    inner += $(this).find('.wings .total input[type=text]:eq(0)').val() + '*' + $(this).find('.wings .total input[type=text]:eq(1)').val();
                } else {
                    var sizeDvs = $(this).find("._size").val();
                    var stanName = $(this).find("._preset > option:selected").text();
                    var wid = $("._cuttingSize input[type=text]:eq(0)").val();
                    var vert = $("._cuttingSize input[type=text]:eq(1)").val();

                    if (isFunc("getWid")) {
                        wid = getWid().val();
                    }
                    if (isFunc("getVert")) {
                        vert = getVert().val();
                    }

                    if (sizeDvs === "manu") {
                        inner += wid + '*' + vert;
                    } else {
                        inner += stanName + '(' + wid + '*' + vert + ')';
                    }
                }
            } else if ($(this).hasClass('amount')) {
                //!! 수량
                if ($(this).find('._amount option:selected').val() === '0') {
                    pageFlag = true;
                    return true;
                } else {
                    pageFlag = false;
                }

                var amt = parseFloat($(this).find('._amount option:selected').val());
                amt = amt < 0.0 ? 0 : amt;
                inner += amt.format();
                inner += $(this).find('._amount').attr("amt_unit");
                // 건수 객체
                var $countObj = $(this).find('._set option:selected');
                if ($countObj.length !== 0) {
                    inner += " * " + $countObj.val() + "건";
                }
            } else {
                //!! 그 외
                if (pageFlag) {
                    return true;
                }
                inner += $(this).children('option:selected').text();
            }
            inner += ', ';

            // 후공정 추가
        });

        html += inner.substr(0, inner.length - 2);

        html += '</li> ';

        $('.summary ul').append(html);

        var after = $(this).next('.aft_sec').find('.overview ul li').clone().addClass('after');
        if (!checkBlank(after.text())) {
            appendAfterSummary(after);
        }
    });

    if (!isBook) {
        appendAfterSummary(afterOverview);
    }
}

var appendAfterSummary = function (obj) {
    $(obj).each(function () {
        if ($(this).text() !== '후가공을 선택해주세요.' &&
            $(this).text() !== '본 상품은 추가 후가공이 없습니다.') {
            $('.summary ul').append($(this));
        }
    });
}

var initAfter = function (dvs) {
    var selector = '.' + dvs + "_after_list";
    var content = preview.content;
    // 171025 - 카다로그 브로셔 후공정 미리보기 로직 관련
    if (dvs.indexOf("inner") > -1) {
        content = preview.addContainer[dvs].find(".content");
    }

    $(selector + ' > ul input[type=checkbox]').on('click', function () {
        if (this.checked) {
            $(selector + ' > .option.' + $(this).closest('li').attr('class')).addClass('_on').slideDown(300);
        } else {
            $(selector + ' > .option.' + $(this).closest('li').attr('class')).removeClass('_on').slideUp(300);
        }

        afterOverview(dvs);
        loadAfterPrice.exec(this.checked, this.value, dvs);
        /*
        if (dvs.indexOf("ao") > -1) {
            loadAoAfterPrice.exec(this.checked, this.value, dvs);
        } else {
            loadAfterPrice.exec(this.checked, this.value, dvs);
        }
        */
    });
    $(selector + ' > .option select').on('change', function () {
        afterOverview(dvs);
    });

    //코팅
    $(selector + ' li._coating input[type=checkbox]').on('click', function () {
        content.children('.after').children('.coating').toggleClass('on');
    });

    $(selector + ' ._coating select._part').on('change', function () {
        if ($(this).find('option:selected').hasClass('_part')) {
            $(this).closest('dl').find('p.note._part').addClass('_on');
            content.children('.after').children('.coating').addClass('part');
        } else {
            $(this).closest('dl').find('p.note._part').removeClass('_on');
            content.children('.after').children('.coating').removeClass('part');
        }
    });

    //preview - 귀도리 **수정필요
    $(selector + ' li._rounding input[type=checkbox]').on('click', function () {
        if ($(this).prop('checked')) {
            preview.borderRadius = $(selector + ' ._rounding select._num')
                .next().find('option:selected')
                .text().replace('mm', '');

            if (preview.roundingDd.find('select._num').find('option:selected').hasClass('_all')) {
                preview.roundingDd.find('select._num')
                    .closest('dl').find('input[type=checkbox]')
                    .prop('checked', true)
                    .off('click')
                    .on('click', function () {
                        alert('네귀도리는 체크를 해제 할 수 없습니다.');
                        return false;
                    });
            }

            $(".paper").css("background", "#a9a9a9");
        } else {
            //귀도리 해제
            //$(selector + ' .option._rounding input[type=checkbox]').prop('checked', false);
            $(".paper").css("background", "#ffffff");
        }

        preview.rounding();
    });
    preview.roundingDd.find('select._num').on('change', function () {
        //네귀도리
        if ($(this).find('option:selected').hasClass('_all')) {
            $(this).closest('dl').find('input[type=checkbox]').prop('checked', true)
                .off('click')
                .on('click', function () {
                    alert('네귀도리는 체크를 해제 할 수 없습니다.');
                    return false;
                });
        } else {
            //나머지
            $(this).closest('dl').find('input[type=checkbox]').prop('checked', false)
                .off('click')
                .on('click', function () {
                    preview.rounding();
                });
        }

        preview.rounding();
    });

    //preview - 귀도리 mm 
    $('.after ._rounding select._num').next().on('change', function () {
        preview.borderRadius = $(this).find('option:selected').text().replace('mm', '');
        preview.rounding();
    });

    //오시
    //초기화
    $(selector + ' ._impression dd.' + $(selector + ' ._impression select option:selected').attr('class')).addClass('_on');
    preview.impressionDd.find('input._custom').each(function () {
        if ($(this).is(':checked')) {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', false);
        } else {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', true);
        }
    });

    $(selector + ' li._impression input[type=checkbox]').on('click', function () {
        //선택 시
        if ($(this).prop('checked')) {
            preview.impression(dvs);
        } else {
            //해제 시
            content.find('.impression').removeClass('on');
        }
    });
    //줄 수
    $(selector + ' .impression_cnt').on('change', function () {
        var dd = $(selector + ' .option._impression dd');
        dd.filter('._on').removeClass('_on');
        dd.filter('.' + $(this).children('option:selected').attr('class')).addClass('_on');
        preview.impression(dvs);
    });
    //기본 위치
    preview.impressionDd.find('input[type=radio]').on('click', function () {
        if ($(this).hasClass('_custom')) {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', false);
        } else {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', true);
        }
        preview.impression(dvs);
    });
    //지정 위치
    preview.impressionDd.find('input[type=text]').on('keyup', function () {
        preview.impression(dvs);
    });
    // 기본값 관련 처리
    if (preview.impressionDd.find('input[type=radio]:checked').length === 0) {
        preview.impressionDd.find('label:first-child input[type=radio]').prop('checked', true);
    } else {
        preview.impression(dvs);
    }

    //도무송 오시
    //초기화
    $(selector + ' ._thomson_impression dd.' + $(selector + ' ._thomson_impression select option:selected').attr('class')).addClass('_on');
    preview.thomsonImpressionDd.find('input._custom').each(function () {
        if ($(this).is(':checked')) {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', false);
        } else {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', true);
        }
    });

    $(selector + ' li._thomson_impression input[type=checkbox]').on('click', function () {
        //선택 시
        if ($(this).prop('checked')) {
            preview.thomson_impression(dvs);
        } else {
            //해제 시
            content.find('.impression').removeClass('on');
        }
    });
    //줄 수
    $(selector + ' .thomson_impression_cnt').on('change', function () {
        var dd = $(selector + ' .option._thomson_impression dd');
        dd.filter('._on').removeClass('_on');
        dd.filter('.' + $(this).children('option:selected').attr('class')).addClass('_on');
        preview.thomson_impression(dvs);
    });
    //기본 위치
    preview.thomsonImpressionDd.find('input[type=radio]').on('click', function () {
        if ($(this).hasClass('_custom')) {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', false);
        } else {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', true);
        }
        preview.thomson_impression(dvs);
    });
    //지정 위치
    preview.thomsonImpressionDd.find('input[type=text]').on('keyup', function () {
        preview.thomson_impression(dvs);
    });
    // 기본값 관련 처리
    if (preview.thomsonImpressionDd.find('input[type=radio]:checked').length === 0) {
        preview.thomsonImpressionDd.find('label:first-child input[type=radio]').prop('checked', true);
    } else {
        preview.thomson_impression(dvs);
    }

    //미싱
    //초기화
    $(selector + ' ._dotline dd.' + $(selector + ' ._dotline select option:selected').attr('class')).addClass('_on');
    preview.dotlineDd.find('input._custom').each(function () {
        if ($(this).is(':checked')) {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', false);
        } else {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', true);
        }
    });
    //preview.dotlineMM.val(0);

    $(selector + ' li._dotline input[type=checkbox]').on('click', function () {
        //선택 시
        if ($(this).prop('checked')) {
            preview.dotline();
        } else {
            //해제 시
            content.find('.dotline').removeClass('on');
        }
    });
    //줄 수
    $('.dotline_cnt').on('change', function () {
        preview.dotlineDd.filter('._on').removeClass('_on');
        preview.dotlineDd.filter('.' + $(this).children('option:selected').attr('class')).addClass('_on');
        preview.dotline();
    });
    //preview.dotlineDd.find('select').change();

    preview.dotlineDd.find('input[type=radio]').on('click', function () {
        if ($(this).hasClass('_custom')) {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', false);
        } else {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', true);
        }
        preview.dotline();
    });
    //지정 위치
    preview.dotlineDd.find('input[type=text]').on('keyup', function () {
        preview.dotline();
    });
    // 기본값 관련 처리
    if (preview.dotlineDd.find('input[type=radio]:checked').length === 0) {
        preview.dotlineDd.find('label:first-child input[type=radio]').prop('checked', true);
    } else {
        preview.dotline();
    }

    //타공
    //preview.punchingMM.val(0);

    $(selector + ' li._punching input[type=checkbox]').on('click', function () {
        if ($(this).prop('checked')) {
            //선택 시
            preview.punching();
        } else {
            //해제 시
            content.find('.punching').removeClass('on');
        }
    });

    preview.punchingDd.find('select').on('change', function () {
        if ($(this).hasClass('_num')) {
            var num = $(this).val();
            $(this).closest('dl').find('dd.br').each(function (i) {
                if (num > i) {
                    $(this).addClass('_on');
                } else {
                    $(this).removeClass('_on');
                }
            })
        }
        preview.punching();
    });
    preview.punchingDd.find('input[type=text]').on('keyup', preview.punching);
    //preview.punchingDd.find('select._num').change();

    //접지
    //초기화
    preview.foldlineDd.find('._custom').each(function () {
        if ($(this).prop('selected')) {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', false);
        } else {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', true);
        }
    });

    //preview.foldlineMM.val(0);

    $(selector + ' li._foldline input[type=checkbox]').on('click', function () {
        if ($(this).prop('checked')) {
            //선택 시
            preview.foldline();
        } else {
            //해제 시
            content.find('.foldline').removeClass('on');
        }
    });

    //줄 수
    preview.foldlineDd
        .filter('._col' + preview.foldlineDd
            .find('select:eq(0)')
            .children('option:selected')
            .attr('col'))
        .addClass('_on');

    preview.foldlineDd.find('select:eq(0)').on('change', function () {
        preview.foldlineDd.filter('._on').removeClass('_on');
        preview.foldlineDd
            .filter('._col' + $(this).children('option:selected').attr('col'))
            .addClass('_on');
        preview.foldline();
    });
    //preview.foldlineDd.find('select').change();

    preview.foldlineDd.find('select:eq(1)').on('change', function () {
        if ($(this).find("option:selected").hasClass('_custom')) {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', false);
        } else {
            $(this).closest('dd').next().find('input[type=text]').attr('readonly', true);
        }
        preview.foldline();
    });
    //지정 위치
    preview.foldlineDd.find('input[type=text]').on('keyup', function () {
        preview.foldline();
    });

    //엠보싱
    $(selector + ' li._embossing input[type=checkbox]').on('click', function () {
        content.children('.after').children('.embossing').toggleClass('on');
    });

    $(selector + ' .option._embossing label:eq(0) input[type=radio]').prop('checked', true);

    $(selector + ' .option._embossing label:eq(0) input[type=radio]').on('click', function () {
        content.children('.after').children('.embossing').removeClass('color');
    });

    $(selector + ' .option._embossing label:eq(1) input[type=radio]').on('click', function () {
        content.children('.after').children('.embossing').addClass('color');
    });

    //박
    $(selector + ' li._foil input[type=checkbox]').on('click', function () {
        content.children('.after').children('.foil').toggleClass('on');
        changeData();
    });

    $(selector + ' .option._foil select._color').on('change', function () {
        var color = $(this).children('option:selected').text().substr(0, 1);

        content.children('.after').children('.foil').attr('class', 'foil on');

        if (color == '은') {
            content.children('.after').children('.foil').addClass('silver');
        } else if (color == '먹') {
            content.children('.after').children('.foil').addClass('black');
        } else if (color == '청') {
            content.children('.after').children('.foil').addClass('blue');
        } else if (color == '적') {
            content.children('.after').children('.foil').addClass('red');
        } else if (color == '녹') {
            content.children('.after').children('.foil').addClass('green');
        } else if (color == '홀') {
            content.children('.after').children('.foil').addClass('hologram');
        }
    });

    //형압
    $(selector + ' li._press input[type=checkbox]').on('click', function () {
        content.children('.after').children('.press').toggleClass('on');
    });

    //도무송
    $(selector + ' li._thomson input[type=checkbox]').on('click', function () {
        content.children('.after').children('.thomson').toggleClass('on');
    });
    $(selector + ' ._thomson select._type').on('change', function () {
        $(this).closest('dl').find('dd._on').removeClass('_on');
        $(this).closest('dl').find('dd._' + $(this).val()).addClass('_on');
    });
    //$('.after ._thomson select._type').change();

    //넘버링
    $(selector + ' ._numbering select._num').on('change', function () {
        var target = $(this);
        $(this).closest('dl').find('dd.br').each(function (i) {
            if (target.find('option').index(target.find('option:selected')) >= i) {
                $(this).addClass('_on');
            } else {
                $(this).removeClass('_on');
            }
        });

    });
    //$('.after ._numbering select._num').change();

    //재단
    //16-11-01 엄준현 수정
    //preview.cuttingDd.find('input[type=text]').val('1');
    preview.cuttingDd.find('input[type=text]').val('40');
    $(selector + ' ._cutting input[type=checkbox]').on('click', function () {
        if ($(this).prop('checked')) {
            preview.cutting();
        } else {
            preview.cuttingArea.children().each(function () {
                var target = $(this);
                target.removeClass('on');
                setTimeout(function () {
                    target.remove();
                }, 300);
            });
        }
    });
    //재단 옵션 수정 시    
    $(selector + ' ._cutting select').on('change', function () {
        preview.cutting();
    });
    //재단 > 라벨재단 숫자 입력 시
    preview.cuttingDd.find('input[type=text]').on('blur', function () {
        //preview.cuttingDd.find('input[type=text]:eq(2)').val(preview.cuttingDd.find('input[type=text]:eq(0)').val() * preview.cuttingDd.find('input[type=text]:eq(1)').val());
        preview.cutting();
    });
    //재단 > 라벨재단 0 입력 시
    preview.cuttingDd.find('input[type=text]').on('blur', function () {
        if ($(this).val() == 0) $(this).val(40);
    })

    //접착
    //16-11-01 엄준현 수정
    /*
    $('.after ._bonding select.type').on('change', function () {
        if ($(this).val() == 'bothside') {
            $(this).closest('dl').find('dd._bothside').addClass('_on');
            $(this).closest('dl').find('dd._oneside').removeClass('_on');
        } else {
            $(this).closest('dl').find('dd._bothside').removeClass('_on');
            $(this).closest('dl').find('dd._oneside').addClass('_on');
        }
    });
    */
    //$('.after ._bonding select.type').change();
    $(selector + ' ._bonding select.type').on('change', function () {
        if ($(this).val() == 'bothside') {
            $(this).closest('dl').find('dd._bothside').addClass('_on');
            $(this).closest('dl').find('dd._oneside').removeClass('_on');
        } else {
            $(this).closest('dl').find('dd._bothside').removeClass('_on');
            $(this).closest('dl').find('dd._oneside').addClass('_on');
        }
    });
    //$('.after ._bonding select.type').change();

    //복권실크
    $(selector + ' li._silk input[type=checkbox]').on('click', function () {
        content.children('.after').children('.silk').toggleClass('on');
    });

    // 자주쓰는 옵션으로 체크박스 선택되어 있는경우 노출
    $(selector + ' > ul input[type=checkbox]:checked').each(function () {
        $(selector + ' > .option.' + $(this).closest('li').attr('class')).addClass('_on').show();
    });

    afterOverview(dvs);
};

// 미리보기
//종이 크기
var preview = {
    dvs: null,
    container: null,
    addContainer: {
        "inner1": null,
        "inner2": null,
        "inner3": null
    },
    cuttingSizeW: null,
    cuttingSizeH: null,
    workingSizeW: null,
    workingSizeH: null,
    paperSizeW: 0,
    paperSizeH: 0,
    contentSizeW: 0,
    contentSizeH: 0,
    paper: $('<div class="paper"></div>'),
    content: $('<div class="content"></div>'),
    workingline: $('<div class="line working"></div>'),
    workingSize: $('<div class="size"><span class="width"></span><span class="height"></span></div>'),
    cuttingline: $('<div class="line cutting"></div>'),
    cuttingSize: $('<div class="size"><span class="width"></span><span class="height"></span></div>'),
    //cuttingBtn : $('<li class="cuttingLine"><button type="button">재단선 확인</button></li>'),
    //workingBtn : $('<li class="workingLine"><button type="button">작업선 확인</button></li>'),
    cuttingBtn: $('<li class="cuttingLine"><a href=\"#none\">재단선 확인</a></li>'),
    workingBtn: $('<li class="workingLine"><a href=\"#none\">작업선 확인</a></li>'),
    designBtn: $('<li class="designLine"><button type="button">디자인선</button></li>'),
    thomsonBtn: $('<li type="thomson" class="tomsonLine"><button>도무송선</button></li>'),
    baseDesignBtn: $('<li class="baseDesign"><button type="button">기본디자인</button></li>'),
    customDesignBtn: $('<li class="customDesign"><button type="button">내디자인</button></li>'),
    PPM: null,
    btns: $('<ul class="btns"></ul>'),
    btnsLeft: $('<ul class="btns_left"><li class="cuttingLine">재단선</li><li class="workingLine">작업선</li></ul>'),
    initialize: function () {
        preview.container.append(preview.paper);
        preview.paper.append(preview.content);
        preview.container
            .append(preview.cuttingline)
            .append(preview.workingline);
        preview.workingline.append(preview.workingSize);
        preview.cuttingline.append(preview.cuttingSize);
        preview.container.append(preview.btnsLeft);
        preview.container.append(preview.btns);
        preview.btns
            .append(preview.cuttingBtn)
            .append(preview.workingBtn);
        preview.content
            .append('<div class="line impression"></div>')
            .append('<div class="line impression"></div>')
            .append('<div class="line impression"></div>')
            .append('<div class="line impression"></div>')
            .append('<div class="line impression vertical"></div>')
            .append('<div class="line impression vertical"></div>')
            .append('<div class="line impression vertical"></div>')
            .append('<div class="line impression vertical"></div>')
            .append('<div class="line dotline"></div>')
            .append('<div class="line dotline"></div>')
            .append('<div class="line dotline"></div>')
            .append('<div class="line dotline"></div>')
            .append('<div class="line dotline vertical"></div>')
            .append('<div class="line dotline vertical"></div>')
            .append('<div class="line dotline vertical"></div>')
            .append('<div class="line dotline vertical"></div>')
            .append('<div class="line foldline"><div class="size horizontal"></div></div>')
            .append('<div class="line foldline"><div class="size horizontal"></div></div>')
            .append('<div class="line foldline"><div class="size horizontal"></div></div>')
            .append('<div class="line foldline"><div class="size horizontal"></div></div>')
            .append('<div class="line foldline"><div class="size horizontal"></div></div>')
            .append('<div class="line foldline"><div class="size horizontal"></div></div>')
            .append('<div class="line foldline vertical"><div class="size vertical"></div></div>')
            .append('<div class="line foldline vertical"><div class="size vertical"></div></div>')
            .append('<div class="line foldline vertical"><div class="size vertical"></div></div>')
            .append('<div class="line foldline vertical"><div class="size vertical"></div></div>')
            .append('<div class="line foldline vertical"><div class="size vertical"></div></div>')
            .append('<div class="line foldline vertical"><div class="size vertical"></div></div>')
            .append('<div class="line punching"></div>')
            .append('<div class="line punching"></div>')
            .append('<div class="line punching"></div>')
            .append('<div class="line punching"></div>')
            .append('<div class="line punching"></div>')
            .append('<div class="cutting"></div>')
            .append('<div class="after"></div>');
        preview.content.children('.after')
            .prepend('<div class="silk">')
            .prepend('<div class="foil">')
            .prepend('<div class="embossing">')
            .prepend('<div class="coating">')
            .prepend('<div class="press">')
            .prepend('<div id="sampleText" class="sampleText">')
            .prepend('<div class="thomson">')

        $("#sampleText").css("background-image", 'url('+$("#logo_218_218").val()+')');
        //$("#sampleText").css("opacity", 1);
    },
    paperSize: function () {
        var workW = parseInt(preview.workingSizeW.val());
        var workH = parseInt(preview.workingSizeH.val());
        var cutW = parseInt(preview.cuttingSizeW.val());
        var cutH = parseInt(preview.cuttingSizeH.val());

        if (isFunc("getWid")) {
            cutW = parseInt(getWid().val());
        }
        if (isFunc("getVert")) {
            cutH = parseInt(getVert().val());
        }

        if (isNaN(workW) && !checkBlank(cutW)) {
            workW = cutW
        }

        if (isNaN(workH) && !checkBlank(cutH)) {
            workH = cutH
        }

        var gap = -10;

        // 재단 작업사이즈 같거나 미리보기 있으면 간격 X
        if (cutW === workW && cutH === workH) {
            gap = 0;
        }
        if ($(".paper .content").css("display") === "none") {
            gap = 0;
        }

        if (workH < cutH && workW < cutW) {
            var tmp = workH;
            workH = cutH;
            cutH = tmp;

            tmp = workW;
            workW = cutW;
            cutW = tmp;

            preview.workingline.addClass("reverse");
            preview.cuttingline.addClass("reverse");
        }

        if (workW >= workH) {
            preview.PPM = 350 / workW;
        } else {
            preview.PPM = 350 / workH;
        }

        var aplyWorkW = workW * preview.PPM;
        var aplyWorkH = workH * preview.PPM;
        var aplyCutW = cutW * preview.PPM;
        var aplyCutH = cutH * preview.PPM;

        if (isNaN(workW)) {
            workW = 0;
        }
        if (isNaN(workH)) {
            workH = 0;
        }
        if (isNaN(cutW)) {
            cutW = 0;
        }
        if (isNaN(cutH)) {
            cutH = 0;
        }

        preview.workingSize.children('.width').text(workW);
        preview.workingSize.children('.height').text(workH);
        preview.cuttingSize.children('.width').text(cutW);
        preview.cuttingSize.children('.height').text(cutH);

        // 뒤쪽 회색
        preview.paper.width(aplyWorkW);
        preview.paper.height(aplyWorkH);
        preview.workingline.width(aplyWorkW);
        preview.workingline.height(aplyWorkH);

        // 앞쪽 흰색
        preview.content.width(aplyCutW + gap);
        preview.content.height(aplyCutH + gap);
        preview.cuttingline.width(aplyCutW + gap);
        preview.cuttingline.height(aplyCutH + gap);

        preview.paperSizeW = aplyWorkW;
        preview.paperSizeH = aplyWorkH;
        preview.contentSizeW = aplyCutW + gap;
        preview.contentSizeH = aplyCutH + gap;
    },
    rounding: function () {
        preview.borderRadiusStyle = '';

        preview.roundingDd.find('input[type=checkbox]').each(function () {
            if ($(this).prop('checked')) {
                preview.borderRadiusStyle += ' ' + (preview.borderRadius * preview.PPM) + 'px';
            } else {
                preview.borderRadiusStyle += ' 0';
            }
        });
        preview.content.add(preview.content.children('.after')).css('border-radius', preview.borderRadiusStyle);
    },
    thomson_impression: function (dvs) {
        var aftSelector = '.' + dvs + "_after_list";

        if (!$(aftSelector + ' li._thomson_impression input[type=checkbox]').prop('checked')) {
            return;
        }
        var content = preview.content;
        if (dvs.indexOf("inner") > -1) {
            content = preview.addContainer[dvs].find(".content");
        }

        var dd = $(aftSelector + ' .option._thomson_impression dd');
        var style = "left";
        var selector = ".impression";

        var vh = $("#" + preview.dvs + "_thomson_impression_vh").val();
        if (vh === 'H') {
            style = "top";
            selector += ".vertical";
        }

        content.find('.impression').removeClass('on');
        //최대 값
        preview.thomsonImpressionMM.each(function () {
            if (Number($(this).val()) > Number(preview.cuttingSizeW.val())) {
                $(this).val(preview.cuttingSizeW.val());
            }
        });

        //직접입력
        if (dd.filter('._on').find('input[type=radio]:checked').hasClass('_custom')) {
            dd.filter('._on').find('input[type=text]').each(function (i) {
                content.find(selector + ':eq(' + i + ')')
                    .addClass('on')
                    .css(style, preview.PPM * Number($(this).val()) + 'px');
            });
            //2줄 십자
        } else if (dd.find('select option:selected').hasClass('_two') && dd.filter('._on').find('input[type=radio]:eq(1)').is(':checked')) {
            content.find('.impression:eq(0)')
                .addClass('on')
                .css('left', '50%');
            content.find('.impression.vertical:eq(0)')
                .addClass('on');

        } else {
            // ujh.16-12-09 비중앙 없는경우 때문에 처리
            var flag = true;
            dd.filter('._on').find('input[type=text]').each(function (i) {
                content.find(selector + ':eq(' + i + ')')
                    .addClass('on')
                    .css(style, 100 / (dd.filter('._on').find('input[type=text]').length + 1) * (i + 1) + '%');
                // ujh.16-12-09 비중앙 없는경우 때문에 처리
                flag = false;
            });

            // ujh.16-12-09 비중앙 없는경우 때문에 처리
            if (flag) {
                var count = dd.find('select option:selected')
                    .attr('class');
                if (count === "_one") {
                    count = 1;
                } else if (count === "_two") {
                    count = 2;
                } else if (count === "_three") {
                    count = 3;
                } else if (count === "_four") {
                    count = 4;
                }

                for (var i = 0; i < count; i++) {
                    var pos = 100 / (count + 1) * (i + 1) + '%';

                    content.find(selector + ':eq(' + i + ')')
                        .addClass('on')
                        .css(style, pos);
                }
            }
        }
    },
    impression: function (dvs) {
        var aftSelector = '.' + dvs + "_after_list";

        if (!$(aftSelector + ' li._impression input[type=checkbox]').prop('checked')) {
            return;
        }
        var content = preview.content;
        if (dvs.indexOf("inner") > -1) {
            content = preview.addContainer[dvs].find(".content");
        }

        var dd = $(aftSelector + ' .option._impression dd');

        var style = "left";
        var selector = ".impression";

        var vh = $("#" + preview.dvs + "_impression_vh").val();
        if (vh === 'H') {
            style = "top";
            selector += ".vertical";
        }

        content.find('.impression').removeClass('on');
        //최대 값
        preview.impressionMM.each(function () {
            if (Number($(this).val()) > Number(preview.cuttingSizeW.val())) {
                $(this).val(preview.cuttingSizeW.val());
            }
        });

        //직접입력
        if (dd.filter('._on').find('input[type=radio]:checked').hasClass('_custom')) {
            dd.filter('._on').find('input[type=text]').each(function (i) {
                content.find(selector + ':eq(' + i + ')')
                    .addClass('on')
                    .css(style, preview.PPM * Number($(this).val()) + 'px');
            });
            //2줄 십자
        } else if (dd.find('select option:selected').hasClass('_two') && dd.filter('._on').find('input[type=radio]:eq(1)').is(':checked')) {
            content.find('.impression:eq(0)')
                .addClass('on')
                .css('left', '50%');
            content.find('.impression.vertical:eq(0)')
                .addClass('on');

        } else {
            // ujh.16-12-09 비중앙 없는경우 때문에 처리
            var flag = true;
            dd.filter('._on').find('input[type=text]').each(function (i) {
                content.find(selector + ':eq(' + i + ')')
                    .addClass('on')
                    .css(style, 100 / (dd.filter('._on').find('input[type=text]').length + 1) * (i + 1) + '%');
                // ujh.16-12-09 비중앙 없는경우 때문에 처리
                flag = false;
            });

            // ujh.16-12-09 비중앙 없는경우 때문에 처리
            if (flag) {
                var count = dd.find('select option:selected')
                    .attr('class');
                if (count === "_one") {
                    count = 1;
                } else if (count === "_two") {
                    count = 2;
                } else if (count === "_three") {
                    count = 3;
                } else if (count === "_four") {
                    count = 4;
                }

                for (var i = 0; i < count; i++) {
                    var pos = 100 / (count + 1) * (i + 1) + '%';

                    content.find(selector + ':eq(' + i + ')')
                        .addClass('on')
                        .css(style, pos);
                }
            }
        }
    },
    dotline: function (dvs) {
        if (!$('.after li._dotline input[type=checkbox]').prop('checked')) {
            return;
        }

        var style = "left";
        var selector = ".dotline";

        var vh = $("#" + preview.dvs + "_dotline_vh").val();
        if (vh === 'H') {
            style = "top";
            selector += ".vertical";
        }

        preview.content.find('.dotline').removeClass('on');
        //최대 값
        preview.dotlineMM.each(function () {
            if (Number($(this).val()) > Number(preview.cuttingSizeW.val())) $(this).val(preview.cuttingSizeW.val());
        });

        //직접입력
        if (preview.dotlineDd.filter('._on').find('input[type=radio]:checked').hasClass('_custom')) {
            preview.dotlineDd.filter('._on').find('input[type=text]').each(function (i) {
                preview.content.find(selector + ':eq(' + i + ')')
                    .addClass('on')
                    .css(style, preview.PPM * Number($(this).val()) + 'px');
            });
            //2줄 십자
        } else if (preview.dotlineDd.find('select option:selected').hasClass('_two') && preview.dotlineDd.filter('._on').find('input[type=radio]:eq(1)').is(':checked')) {
            preview.content.find('.dotline:eq(0)')
                .addClass('on')
                .css('left', '50%');
            preview.content.find('.dotline.vertical:ep(0)')
                .addClass('on')
        } else {
            // ujh.16-12-09 비중앙 없는경우 때문에 처리
            var flag = true;
            preview.dotlineDd.filter('._on').find('input[type=text]').each(function (i) {
                preview.content.find(selector + ':eq(' + i + ')')
                    .addClass('on')
                    .css(style, 100 / (preview.dotlineDd.filter('._on').find('input[type=text]').length + 1) * (i + 1) + '%');
                // ujh.16-12-09 비중앙 없는경우 때문에 처리
                flag = false;
            });

            // ujh.16-12-09 비중앙 없는경우 때문에 처리
            if (flag) {
                var count = preview.impressionDd
                    .find('select option:selected')
                    .attr('class');
                if (count === "_one") {
                    count = 1;
                } else if (count === "_two") {
                    count = 2;
                } else if (count === "_three") {
                    count = 3;
                } else if (count === "_four") {
                    count = 4;
                }

                for (var i = 0; i < count; i++) {
                    var pos = 100 / (count + 1) * (i + 1) + '%';

                    preview.content
                        .find(selector + ':eq(' + i + ')')
                        .addClass('on')
                        .css(style, pos);
                }
            }
        }
    },
    foldlineDd: null,
    foldlineMM: null,
    foldline: function () {
        if (!$('.after li._foldline input[type=checkbox]').prop('checked')) return;

        var target,
            depth1,
            lineNum = 0,
            zigzag = true,
            vertical = false,
            insideStart = true;

        depth1 = preview.foldlineDd.find('select:eq(1) option:selected').text();

        lineNum = Number(preview.foldlineDd.find('select:eq(0) option:selected').attr('col'));

        var foldlineSizeArr = [];
        var workSize = parseInt(preview.cuttingSizeW.val());

        var style = "left";
        var selector = ".foldline";
        var lineDiv = ".foldline.vertical";
        var vh = $("#" + preview.dvs + "_foldline_vh").val();
        if (vh === 'H') {
            style = "top";
            selector += ".vertical";
            lineDiv = ".foldline";
            workSize = parseInt(preview.cuttingSizeH.val());
        }
        workSize /= lineNum;


        preview.content.find('.foldline').removeClass('on').removeClass('outside');
        //최대 값
        preview.foldlineMM.each(function () {
            if (Number($(this).val()) > Number(preview.cuttingSizeW.val())) $(this).val(preview.cuttingSizeW.val());
        });

        //직접입력
        if (preview.foldlineDd.find('._custom').prop("selected")) {
            preview.foldlineDd.filter('._on').find('input[type=text]').each(function (i) {
                preview.content.find(selector + ':eq(' + i + ')')
                    .addClass('on')
                    .css(style, preview.PPM * Number($(this).val()) + 'px');
            });
            return;
            //3단 접지
        } else if (lineNum === 3) {
            switch (depth1) {
                case "정접지": //정접지
                    foldlineSizeArr.push(workSize + 1);
                    foldlineSizeArr.push(workSize + 1);
                    foldlineSizeArr.push(workSize - 2);
                    zigzag = false;
                    break;
                case "정접지후반접지": //정접지 후 반접지
                    foldlineSizeArr.push(workSize + 1);
                    foldlineSizeArr.push(workSize + 1);
                    foldlineSizeArr.push(workSize - 2);
                    zigzag = false;
                    vertical = true;
                    break;
                case "N접지": //N접지
                    foldlineSizeArr.push(workSize);
                    foldlineSizeArr.push(workSize);
                    foldlineSizeArr.push(workSize);
                    break;
                case "N접지후반접지": //N접지 후 반접지
                    foldlineSizeArr.push(workSize);
                    foldlineSizeArr.push(workSize);
                    foldlineSizeArr.push(workSize);
                    vertical = true;
                    break;
                case "반접지후정접지": //반접지 후 정접지
                    foldlineSizeArr.push(workSize + 1);
                    foldlineSizeArr.push(workSize + 1);
                    foldlineSizeArr.push(workSize - 2);
                    zigzag = false;
                    vertical = true;
                    break;
                case "반접지후N접지": //반접지 후 N접지
                    foldlineSizeArr.push(workSize);
                    foldlineSizeArr.push(workSize);
                    foldlineSizeArr.push(workSize);
                    vertical = true;
                    break;
            }
            //4단 접지
        } else if (lineNum === 4) {
            switch (depth1) {
                case "두루마리접지": //두루마리접지
                    foldlineSizeArr.push(workSize + 1);
                    foldlineSizeArr.push(workSize + 1);
                    foldlineSizeArr.push(workSize - 1);
                    foldlineSizeArr.push(workSize - 1);
                    insideStart = false;
                    zigzag = false;
                    break;
                case "정접지": //정접지
                    foldlineSizeArr.push(workSize + 1);
                    foldlineSizeArr.push(workSize + 1);
                    foldlineSizeArr.push(workSize - 1);
                    foldlineSizeArr.push(workSize - 1);
                    zigzag = false;
                    break;
                case "정접지후반접지": //정접지 후 반접지
                    foldlineSizeArr.push(workSize + 1);
                    foldlineSizeArr.push(workSize + 1);
                    foldlineSizeArr.push(workSize - 1);
                    foldlineSizeArr.push(workSize - 1);
                    zigzag = false;
                    vertical = true;
                    break;
                case "병풍접지": //병풍접지
                    foldlineSizeArr.push(workSize);
                    foldlineSizeArr.push(workSize);
                    foldlineSizeArr.push(workSize);
                    foldlineSizeArr.push(workSize);
                    break;
                case "병풍접지후반접지": //병풍접지 후 반접지
                    foldlineSizeArr.push(workSize);
                    foldlineSizeArr.push(workSize);
                    foldlineSizeArr.push(workSize);
                    foldlineSizeArr.push(workSize);
                    vertical = true;
                    break;
                case "대문접지": //대문접지
                    foldlineSizeArr.push(workSize - 1);
                    foldlineSizeArr.push(workSize + 1);
                    foldlineSizeArr.push(workSize + 1);
                    foldlineSizeArr.push(workSize - 1);
                    zigzag = false;
                    break;
                case "십자접지": //십자접지
                    zigzag = false;
                    vertical = true;
                    break;
            }
            //5단 6단 접지
        } else if (lineNum > 4) {
            switch (depth1) {
                case "두루마리접지": //두루마리접지
                    insideStart = false;
                    zigzag = false;
                    break;
                case "병풍접지": //병풍접지
                    break;
                case "병풍접지후반접지": //병풍접지 후 반접지
                    vertical = true;
                    break;
            }
        }

        var paperSize = preview.contentSizeW;
        if (style === "top") {
            paperSize = preview.contentSizeH;
        }

        var posPx = 8;
        var val = null;

        for (var i = 0; i < lineNum; i++) {
            if (i === 0) {
                target = preview.content.find(selector + ':eq(' + i + ')');
                target.addClass('on')
                    .css(style, '0%')
                    .css("border-color", "transparent");
                target.find(".size").css(style, posPx + "px");

                val = foldlineSizeArr.pop();
                if (checkBlank(val)) {
                    target.find(".size").html('');
                } else {
                    target.find(".size").html(val);
                }

                continue;
            }

            var j = i - 1;

            target = preview.content.find(selector + ':eq(' + i + ')');
            target.find(".size").css(style, posPx + "px");

            val = foldlineSizeArr.pop();
            if (checkBlank(val)) {
                target.find(".size").html('');
            } else {
                target.find(".size").html(val);
            }

            if (depth1 === "십자접지") {
                target.addClass('on').css(style, '50%');
                break;
            } else {
                target.addClass('on').css(style, 100 / lineNum * i + '%');
            }

            if (j % 2 == 0 || !zigzag || (!insideStart && j % 2 != 0)) {
                target.removeClass('outside');
            } else {
                target.addClass('outside');
            }

        }

        if (vertical) {
            var reverse = style === "top" ? "left" : "top";
            preview.content.find(lineDiv + ':eq(1)')
                .addClass('on').css(reverse, "50%");
        }

        foldlineSizeArr = null;
    },
    punching: function () {
        if (!$('.after li._punching input[type=checkbox]').prop('checked')) return;

        var R = preview.punchingDd
            .find('select:eq(1) > option:selected')
            .text().replace('mm', '');

        //최대 값
        preview.punchingMM.each(function () {
            if ($(this).parent().is(':first-of-type')) {
                if (Number($(this).val()) > Number(preview.cuttingSizeW.val())) $(this).val(preview.cuttingSizeW.val());
            } else {
                if (Number($(this).val()) > Number(preview.cuttingSizeH.val())) $(this).val(preview.cuttingSizeH.val());
            }
        });

        preview.content.find('.punching')
            .width(Math.ceil(R * preview.PPM) + 'px')
            .height(Math.ceil(R * preview.PPM) + 'px')
            .removeClass('on');

        preview.punchingDd.filter('._on').each(function (i) {
            preview.content.find('.punching:eq(' + i + ')')
                .addClass('on')
                .css({
                    'left': ($(this).find('input[type=text]:eq(0)').val() - R / 2) * preview.PPM,
                    'top': ($(this).find('input[type=text]:eq(1)').val() - R / 2) * preview.PPM
                });
        });
    },
    cuttingDd: null,
    cuttingArea: null,
    cutting: function (dvs) {
        var cuttingNum = preview.cuttingDd
                .find('select > option:selected').attr("num"),
            xNum = preview.cuttingDd.find('input[type=text]:eq(0)').val(),
            yNum = preview.cuttingDd.find('input[type=text]:eq(1)').val(),
            totalNum = preview.cuttingDd.find('input[type=text]:eq(2)').val(),
            newLine = new Array(0);

        var vh = $("#" + preview.dvs + "_cutting_vh").val();
        $("#st_cutting_wid").prop('disabled','');
        $("#st_cutting_vert").prop('disabled','');
        if(cuttingNum == 'label') {
            /*** 라벨재단 ***/
            // 라벨 재단 입력 보이기
            preview.cuttingDd.filter('.br').addClass('_on');

            // 기존 줄 제거
            preview.cuttingArea.children().each(function () {
                var target = $(this);
                target.removeClass('on');
                setTimeout(function () {
                    target.remove()
                }, 300);
            });

            var paperWidth = parseInt(preview.cuttingline.find('span')[0].innerText);
            var paperHeight = parseInt(preview.cuttingline.find('span')[1].innerText);

            if (xNum < 40) {
                alertReturnFalse("40보다 작을 수 없습니다.");
                preview.cuttingDd.find('input[type=text]:eq(0)').val(40);
            }

            if (yNum < 40) {
                alertReturnFalse("40보다 작을 수 없습니다.");
                preview.cuttingDd.find('input[type=text]:eq(1)').val(40);
            }

            if (xNum > paperWidth) {
                alertReturnFalse("종이 가로값보다 클 수 없습니다.");
            }

            if (yNum > paperHeight) {
                alertReturnFalse("종이 세로값보다 클 수 없습니다.");
            }

            xNum = parseInt(paperWidth / xNum);
            yNum = parseInt(paperHeight / yNum);

            preview.cuttingDd.find('input[type=text]:eq(2)').val(xNum * yNum);

            var width = (100 / xNum),
                height = (100 / yNum);

            if (totalNum == 1 || totalNum == 0) return; //일등분일 경우 줄 생성 안 함

            newLine = [];
            for (var i = 0; i < xNum; i++) {
                preview.cuttingArea.append('<div class="line" style="width: ' + width + '%; left: ' + (width * (i % xNum)) + '%;"></div>');
                if (i == 0) preview.cuttingArea.children(':last-child').addClass('noLine');
                newLine[newLine.length] = preview.cuttingArea.children(':last-child');
            }
            for (var i = 0; i < yNum; i++) {
                preview.cuttingArea.append('<div class="line vertical" style="height: ' + height + '%; top: ' + (height * (i % yNum)) + '%;"></div>');
                if (i == 0) preview.cuttingArea.children(':last-child').addClass('noLine');
                newLine[newLine.length] = preview.cuttingArea.children(':last-child');
            }
            setTimeout(function () {
                $(newLine).each(function () {
                    $(this).addClass('on');
                });
            }, 0);
        } else if(cuttingNum == 'sticker') {
            /*** 스티커 추가재단 ***/
            // 라벨 재단 입력 보이기
            preview.cuttingDd.filter('.br').addClass('_on');

            // 정재단 사이즈
            var cuttingWidth = parseInt($("#st_cutting_wid").val());
            var cuttingHeight = parseInt($("#st_cutting_vert").val());

            // 도무송 사이즈
            var tomsonWidth = parseInt($("#st_cut_wid_size").val());
            var tomsonHeight = parseInt($("#st_cut_vert_size").val());

            if(tomsonWidth > cuttingWidth - 6) {
                $("#st_cutting_wid").val(tomsonWidth + 6);
                cuttingWidth = tomsonWidth + 6;
            }

            if(tomsonHeight > cuttingHeight - 6) {
                $("#st_cutting_vert").val(tomsonHeight + 6);
                cuttingHeight = tomsonHeight + 6;
            }

            $("#work_wid_size").val(cuttingWidth + 6);
            $("#work_vert_size").val(cuttingHeight + 6);

        } else if(cuttingNum == 'sticker2') {
            /*** 작업선제거재단 ***/
            // 라벨 재단 입력 보이기
            preview.cuttingDd.filter('.br').addClass('_on');

            // 정재단 사이즈
            var cuttingWidth = parseInt($("#st_cutting_wid").val());
            var cuttingHeight = parseInt($("#st_cutting_vert").val());

            // 도무송 사이즈
            var tomsonWidth = parseInt($("#st_cut_wid_size").val());
            var tomsonHeight = parseInt($("#st_cut_vert_size").val());

            if(tomsonWidth > cuttingWidth - 6) {
                $("#st_cutting_wid").val(tomsonWidth + 6);
                cuttingWidth = tomsonWidth + 6;
            }

            if(tomsonHeight > cuttingHeight - 6) {
                $("#st_cutting_vert").val(tomsonHeight + 6);
                cuttingHeight = tomsonHeight + 6;
            }

            $("#work_wid_size").val(cuttingWidth + 6);
            $("#work_vert_size").val(cuttingHeight + 6);

            $("#st_cutting_wid").prop('disabled','disabled');
            $("#st_cutting_vert").prop('disabled','disabled');
        }
        else {
            //라벨 재단 입력 숨기기
            preview.cuttingDd.filter('.br').removeClass('_on');
            //기존 줄 제거
            preview.cuttingArea.children().each(function () {
                var target = $(this);
                target.removeClass('on');
                setTimeout(function () {
                    target.remove()
                }, 300);
            });

            var style1 = "left";
            var style2 = "width";
            var cls = "";
            if (vh === "H") {
                style1 = "top";
                style2 = "height";
                cls = "vertical";
            }

            //등분 갯수만큼 줄 생성
            newLine = [];
            for (var i = 0; i < cuttingNum; i++) {
                preview.cuttingArea.append('<div class="line ' + cls + '" style="' + style2 + ': ' + (100 / cuttingNum) + '%;' + style1 + ':' + (100 / cuttingNum * i) + '%;"></div>');

                if (i == 0) preview.cuttingArea.children(':last-child').addClass('noLine');
                newLine[newLine.length] = preview.cuttingArea.children(':last-child');
            }
            //신규 줄 보이기
            setTimeout(function () {
                $(newLine).each(function () {
                    $(this).addClass('on');
                });
            }, 0);
        }
    }
}

function windowScroll() {
    var scrollTop = $(window).scrollTop(),
        scrollLeft = $(window).scrollLeft(),
        headerMargin = $(".div_header_wrapper").outerHeight() + 140 - 10, // 이부분 140을 영역잡는걸로 바꿔야함. 도저히 안잡힘
        quickEstimate = $('.quickEstimate'),
        quickEstimateHeight = quickEstimate.outerHeight(),
        quickEstimateHiddenHeight,
        baseMargin = 50,
        //baseMargin = 30,
        bodyWidth = $('body').width(),
        bodyHeight = $(document).height(),
        footerHeight = $('footer').outerHeight(),
        bottomDistance,
        windowWidth = $(window).width(),
        windowHeight = $(window).height(),
        topForHidden = 0;

    //상하
    scrollTop = $(window).scrollTop();
    bodyHeight = $(document).height();

    if (scrollTop > headerMargin - baseMargin) { //스크롤이 빠른견적서 상단보다 더 내려갈 경우 top 조정
        quickEstimate.css('top', baseMargin);
    } else {
        quickEstimate.css('top', headerMargin - scrollTop);
    }
    quickEstimateHiddenHeight = quickEstimateHeight - windowHeight + baseMargin; //빠른견적서보다 창이 작은 경우

    if (quickEstimateHiddenHeight > 0 && scrollTop > headerMargin - baseMargin) {
        if (scrollTop - (headerMargin - baseMargin) > quickEstimateHiddenHeight) {
            topForHidden = quickEstimateHiddenHeight * -1;
            quickEstimate.css('top', topForHidden);
        } else {
            topForHidden = quickEstimate.css('top').replace('px', '') - (scrollTop - (headerMargin - baseMargin));
            quickEstimate.css('top', topForHidden);
        }
        quickEstimateHiddenHeight = quickEstimateHiddenHeight + baseMargin;
    } else if (quickEstimateHiddenHeight <= 0) {
        quickEstimateHiddenHeight = 0;
    }

    bottomDistance = bodyHeight - (quickEstimateHeight + baseMargin * 2 + scrollTop + footerHeight - quickEstimateHiddenHeight); //하단과의 거리
    if (bottomDistance > 0) {
        quickEstimate.css('margin-top', 0);
    } else {
        quickEstimate.css('margin-top', bottomDistance);
    }

    //좌우
    if (windowWidth > bodyWidth) {
        quickEstimate.css('right', 0);
    } else {
        scrollLeft = $(window).scrollLeft();
        quickEstimate.css('right', windowWidth - bodyWidth + scrollLeft);
    }
}

$(document).ready(function () {
    if ($('.special').length > 0) {
        var serverYear = $('input[type=hidden].serverYear').val(),
            serverMonth = $('input[type=hidden].serverMonth').val(),
            serverDay = $('input[type=hidden].serverDay').val(),
            serverHour = $('input[type=hidden].serverHour').val(),
            serverMinute = $('input[type=hidden].serverMinute').val(),
            serverSecond = $('input[type=hidden].serverSecond').val();
        $('.remainTime').each(function () {
            var Month = $(this).find('input[type=hidden].Month').val(),
                Day = $(this).find('input[type=hidden].Day').val(),
                Hour = $(this).find('input[type=hidden].Hour').val(),
                Minute = $(this).find('input[type=hidden].Minute').val(),
                textH = $(this).find('.hour'),
                textM = $(this).find('.minute'),
                textS = $(this).find('.second');

            // 월이 다를 경우 다음에 돌아오는 월로 인식 - 1년 이상 이벤트가 지속되지 않기 때문에 현재 12월일 때 종료일이 1월일 경우 내년 1월로 인식, 남은 시간이 99 이상일 경우 오류 발생 가능
            if (Month == serverMonth && (Day < serverDay || (Day == serverDay && Hour < serverHour) || (Day == serverDay && Hour == serverHour && Minute < serverMinute))) {
                // 종료 시
                textH.text('00');
                textM.text('00');
                textS.text('00');
            } else {
                var endYear = serverYear;
                if (Month < serverMonth) {
                    endYear++;
                }

                var now = new Date(serverYear, serverMonth, serverDay, serverHour, serverMinute, serverSecond),
                    end = new Date(endYear, Month, Day, Hour, Minute, 60),
                    nowTime = now.getTime(),
                    endTime = end.getTime(),
                    sec,
                    day,
                    hour,
                    min;

                sec = parseInt(endTime - nowTime) / 1000;
                day = parseInt(sec / 60 / 60 / 24);
                sec = (sec - (day * 60 * 60 * 24));
                hour = parseInt(sec / 60 / 60);
                sec = (sec - (hour * 60 * 60));
                min = parseInt(sec / 60);
                sec = parseInt(sec - (min * 60));

                hour = Number(hour) + day * 24;
                leadZero();

                var discountTimer;

                // main list 분기
                if ($(this).is('div')) {
                    //main
                    clock = $(this).FlipClock(endTime / 1000 - nowTime / 1000, {
                        clockFace: 'HourlyCounter',
                        countdown: true,
                        showDays: false
                    });
                } else {
                    //list
                    textH.text(hour);
                    textM.text(min);
                    textS.text(sec);

                    discountTimer = setInterval(function () {
                        discountList();
                    }, 1000);
                }

                function leadZero() {
                    hour = hour.toString();
                    if (hour.length < 2) {
                        hour = '0' + hour;
                    }
                    min = min.toString();
                    if (min.length < 2) {
                        min = '0' + min;
                    }
                    sec = sec.toString();
                    if (sec.length < 2) {
                        sec = '0' + sec;
                    }
                };

                function discountList() {
                    if (Number(sec) > 0) {
                        sec -= 1;
                    } else {
                        if (Number(min) > 0) {
                            min -= 1;
                        } else {
                            if (Number(hour) > 0) {
                                hour -= 1;
                            } else {
                                clearInterval(discountTimer);
                                return;
                            }
                            min = 59;
                        }
                        sec = 59;
                    }

                    leadZero();
                    textH.text(hour);
                    textM.text(min);
                    textS.text(sec);
                }
            }
        });
    }
});
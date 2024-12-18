/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/04/11 임종건
 *============================================================================
 *
 */

var d = new Date();

$(document).ready(function () {
    //lnb 선택 효과
    mypageLnbEffect();

    getChart(d.getFullYear());
});

var getChart = function (val) {

    var url = "/json/mypage/benefits_grade/load_chart_data.php";
    var data = {
        "year": val
    };
    var callback = function (result) {
        $('#chart').highcharts({
            title: {
                text: '',
                x: -20 //center
            },
            xAxis: {
                categories: result.cate
            },
            yAxis: {
                categories: ['', 'WELCOME', 'GENERAL', 'BRONZE', 'SILVER', 'SILVERLITE', 'GOLD', 'GOLDLITE', 'PLATINUM', 'VIP', 'VVIP'],
                title: {
                    text: ''
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                enabled: false
            },
            legend: {
                enabled: false
            },
            credits: {
                enabled: false
            },
            series: [{
                name: '등급',
                data: result.data
            }]
        });
    };

    showMask();
    ajaxCall(url, "json", data, callback);
}
var dayBoardMsg = "해당 조건은 당일판이 불가능합니다. 고객센터를 참조하세요.";
/**
 * @brief 카테고리별 옵션 제약사항 스크립트
 */
var optRestrict = {
    "msg": '',
    "alertFlag": null,
    "all": function (dvs) {
        var ret = true;
        optRestrict.msg = '';

        $("input[name='chk_opt']").each(function () {
            if ($(this).prop("checked") === false) {
                return true;
            }

            var opt = $(this).val();
            funcObj = optRestrict[opt];

            if (checkBlank(funcObj) === true) {
                return true;
            }

            var idx = parseInt($(this).attr("id").split('_')[1]);

            ret = funcObj.common(dvs, idx);

            if (ret === false) {
                ret = false;
                alert(optRestrict.msg);
                setOptPrice(idx, 0);
                return true;
            }
        });

        return ret;
    },
    "unchecked": function (idx) {
        var obj = "#opt_" + idx
        $(obj).prop("checked", false);
        $(obj).trigger("click");
        optSlideUp(idx);
    },
    "당일판": {
        "common": function (dvs, idx, alertFlag) {
            var prefix = getPrefix(dvs);
            var sortcode = $(prefix + "cate_sortcode").val();
            var sortcodeT = sortcode.substr(0, 3);

            var ret = true;
            if (!checkBlank(this[sortcodeT])) {
                ret = this[sortcodeT](dvs, idx);
            }

            /*
            if (sortcodeT === "003" || sortcodeT === "004") {
                // 명함, 스티커류 2000장까지 가능
                var amt = parseInt($(prefix + "amt").val());

                if (amt > 2000) {
                    optRestrict.msg = "2000매 초과는 당일판이 불가합니다.";
                    optRestrict.unchecked(idx);
                    return false;
                }
            } else if (sortcodeT === "005") {
                // 전단류 A3, 8절일 때 1R, 3R 안됨
                // 5R 초과 안됨
                var size  = $(prefix + "size > option:selected").text();
                var amt   = parseFloat($(prefix + "amt").val());
                var paper = $(prefix + "paper > option:selected").text();
                paper = paper.split(' ');
                var basisweight = parseInt(paper[paper.length - 1]);

                // 주석 해제할경우 여기만 따로 주석 필요
                if (amt > 5) {
                    optRestrict.msg = "5R 초과는 당일판이 불가합니다.";
                    optRestrict.unchecked(idx);
                    return false;
                }

                if (basisweight > 90) {
                    //$(this).prop("disabled", true);

                    optRestrict.msg = "평량 90g이상은 당일판이 불가합니다.";
                    optRestrict.unchecked(idx);
                    return false;
                } 

                //$(this).prop("disabled", false);

                if (size === "A4" || size === "8절") {
                    if (amt === 0.5 || amt === 3.0) {
                        optRestrict.msg = size + "사이즈 " +
                                          amt + "R은 당일판이 불가합니다.";

                        if (alertFlag === false) {
                            optRestrict.alertFlag = alertFlag;
                            optRestrict.msg = '';
                        }
                        optRestrict.unchecked(idx);
                        return false;
                    }
                }
            }
            */

            return ret;
        },
        "003": function (dvs, idx) {
                // 명함
                var prefix = getPrefix(dvs);
                // 명함, 스티커류 2000장까지 가능
                var amt = parseInt($(prefix + "amt").val());

                if (amt > 2000) {
                    optRestrict.msg = "2,000매 초과는 당일판이 불가합니다.";
                    optRestrict.msg = dayBoardMsg;
                    optRestrict.unchecked(idx);
                    return false;
                }
                var paper = $(prefix + "paper > option:selected").text()
                    .split(' ')[0];

                // 일부 종이는 당일판이 불가함
                if ("샤인스페셜|갤럭시로얄|스코틀랜드|유포지".indexOf(paper) > -1) {
                    //optRestrict.msg = dayBoardMsg;
                    optRestrict.unchecked(idx);
                    return false;
                }

            // 일부 후공정은 당일판이 불가함
            var foilChk = $(prefix + "foil").prop("checked");
            var embossingChk = $(prefix + "embossing").prop("checked");
            var pressChk = $(prefix + "press").prop("checked");
            var coatingChk = $(prefix + "coating").prop("checked");

            var aft = false;
            if (foilChk) {
                aft = "박";
            } else if (embossingChk) {
                aft = "엠보싱";
            } else if (pressChk) {
                aft = "형압";
            } else if (coatingChk) {
                aft = "코팅";
            }

            if (aft) {
                optRestrict.msg = "후가공 " + aft + "은 빠른출고가 불가합니다.";
                //optRestrict.msg = dayBoardMsg;
                optRestrict.unchecked(idx);
            }

                return true;
            }
            // 스티커
            ,
        "004": function (dvs, idx) {
            var prefix = getPrefix(dvs);
            // 명함, 스티커류 2000장까지 가능
            var amt = parseInt($(prefix + "amt").val());

            if (amt > 500) {
                optRestrict.msg = "500매 초과는 당일판이 불가합니다.";
                //optRestrict.msg = dayBoardMsg;
                optRestrict.unchecked(idx);
                return false;
            }

            var paper = $(prefix + "paper > option:selected").val();
            // 일부 종이는 당일판이 불가함
            if ("투명데드롱|은무데드롱|유포지".indexOf(paper) > -1) {
                //optRestrict.msg = dayBoardMsg;
                optRestrict.unchecked(idx);
                return false;
            }

            // 일부 후공정은 당일판이 불가함
            var foilChk = $(prefix + "foil").prop("checked");
            var embossingChk = $(prefix + "embossing").prop("checked");
            var pressChk = $(prefix + "press").prop("checked");
            var halfChk = $(prefix + "halfknife").prop("checked");

            var aft = false;
            if (foilChk) {
                aft = "박";
            } else if (embossingChk) {
                aft = "엠보싱";
            } else if (pressChk) {
                aft = "형압";
            } else if (halfChk) {
                aft = "후지반칼";
            }

            if (aft) {
                //optRestrict.msg = aft + " 후가공은 당일판이 불가합니다.";
                optRestrict.msg = dayBoardMsg;
                optRestrict.unchecked(idx);
            }

            return true;

        },
        "005": function (dvs, idx) {
            var prefix = getPrefix(dvs);
            var sortcode = $(prefix + "cate_sortcode").val();
            var sortcodeB = sortcode.substr(0, 6);

            var amt = $(prefix + "amt").val();
            //var cnt = $("#count").val();
            //var totalAmt = amt * cnt;

            var dayPaperSel = $(prefix + "paper option:selected").text();
            var dayPaperSize = dayPaperSel.split(" ")[1];
            // 종이 평량
            dayPaperSize = dayPaperSize.substr(0, 3);

            // 귀도리
            var roundingChk = $(prefix + "rounding").prop("checked");
            // 오시
            var impressChk = $(prefix + "impression").prop("checked");
            // 미싱
            var dotlineChk = $(prefix + "dotline").prop("checked");
            // 타공
            var punchingChk = $(prefix + "punching").prop("checked");
            // 박
            var foilChk = $(prefix + "foil").prop("checked");
            // 엠보싱
            var embossingChk = $(prefix + "embossing").prop("checked");
            // 형압
            var pressChk = $(prefix + "press").prop("checked");
            // 접지
            var foldlineChk = $(prefix + "foldline").prop("checked");
            // 재단
            var cuttingChk = $(prefix + "cutting").prop("checked");
            // 코팅
            var coatingChk = $(prefix + "coating").prop("checked");
            // 도무송
            var thomsonChk = $(prefix + "thomson").prop("checked");
            // 라미넥스
            var laminexChk = $(prefix + "laminex").prop("checked");
            // 넘버링
            var numberingChk = $(prefix + "numbering").prop("checked");

            // 합판전단
            if (sortcodeB == "005001") {

                if (amt > 5 || amt < 1) {
                    //optRestrict.msg = "5연 초과 주문은 당일판이 불가합니다.";
                    // 5연 초과, 1연 미만의 경우 당일판 안 되도록 해놓음
                    optRestrict.msg = dayBoardMsg;
                    optRestrict.unchecked(idx);

                } else {

                    // 재단, 2/3단 접지 외엔 당일판이 불가함
                    if (roundingChk === true || impressChk === true ||
                        dotlineChk === true || punchingChk === true ||
                        foilChk === true || embossingChk === true ||
                        pressChk === true || foldlineChk === true ||
                        laminexChk === true || numberingChk === true) {
                        var aft = "";
                        if (roundingChk === true) {
                            aft = "귀도리";
                        } else if (impressChk === true) {
                            aft = "오시";
                        } else if (dotlineChk === true) {
                            aft = "미싱";
                        } else if (punchingChk === true) {
                            aft = "타공";
                        } else if (foilChk === true) {
                            aft = "박";
                        } else if (embossingChk === true) {
                            aft = "엠보싱";
                        } else if (pressChk === true) {
                            aft = "형압";
                        } else if (foldlineChk === true) {

                            var foldDvs = $("#bl_foldline_dvs > option:selected").attr('col');
                            if (foldDvs > 3) {
                                //optRestrict.msg = "4단 이상의 접지 후가공은 당일판이 불가합니다.";
                                optRestrict.msg = dayBoardMsg;
                                optRestrict.unchecked(idx);

                                return false;
                            } else {
                                return false;
                            }
                        } else if (numberingChk === true) {
                            aft = "넘버링";
                        } else if (laminexChk === true) {
                            aft = "라미넥스";
                        } else {}
                        //optRestrict.msg = aft + "후가공은 당일판이 불가합니다.";
                        optRestrict.msg = dayBoardMsg;
                        optRestrict.unchecked(idx);

                    }
                }

                // 독판전단
            } else if (sortcodeB == "005002") {
                // 접지 dvs
                var foldDvs = $("#bl_foldline_dvs > option:selected").attr('col');

                // 5연이 넘어가는 경우
                if (amt > 5) {
                    //optRestrict.msg = "5연이 넘는 주문은 당일판이 불가합니다.";
                    optRestrict.msg = dayBoardMsg;
                    optRestrict.unchecked(idx);
                    // 1연 ~ 5연
                } else if (amt > 1 && amt <= 5) {
                    var infoMsg = "1연 이상, 5연 이하 주문은 재단 후가공만 당일판이 가능합니다.";
                    // 재단 이외에는 다 거른다
                    if (coatingChk === true) {
                        //optRestrict.msg = infoMsg;
                        optRestrict.msg = dayBoardMsg;
                        optRestrict.unchecked(idx);
                    } else if (thomsonChk === true) {
                        //optRestrict.msg = infoMsg;
                        optRestrict.msg = dayBoardMsg;
                        optRestrict.unchecked(idx);

                    } else if (impressChk === true) {
                        //optRestrict.msg = infoMsg;
                        optRestrict.msg = dayBoardMsg;
                        optRestrict.unchecked(idx);

                    } else if (foldlineChk === true) {
                        //optRestrict.msg = infoMsg;
                        optRestrict.msg = dayBoardMsg;
                        optRestrict.unchecked(idx);

                    } else if (dotlineChk === true) {
                        //optRestrict.msg = infoMsg;
                        optRestrict.msg = dayBoardMsg;
                        optRestrict.unchecked(idx);

                    } else if (foilChk === true) {
                        //optRestrict.msg = infoMsg;
                        optRestrict.msg = dayBoardMsg;
                        optRestrict.unchecked(idx);

                    } else if (pressChk === true) {
                        //optRestrict.msg = infoMsg;
                        optRestrict.msg = dayBoardMsg;
                        optRestrict.unchecked(idx);

                    } else if (embossingChk === true) {
                        //optRestrict.msg = infoMsg;
                        optRestrict.msg = dayBoardMsg;
                        optRestrict.unchecked(idx);

                    } else if (numberingChk === true) {
                        //optRestrict.msg = infoMsg;
                        optRestrict.msg = dayBoardMsg;
                        optRestrict.unchecked(idx);

                    } else if (laminexChk === true) {
                        //optRestrict.msg = infoMsg;
                        optRestrict.msg = dayBoardMsg;
                        optRestrict.unchecked(idx);

                    } else {}

                    // 0연 초과 1연 이하
                } else if (amt > 0 && amt <= 1) {
                    if (dayPaperSize <= 180) {
                        var infoMsg = "평량 180g 이하의 1연 이하 주문은재단, 2/3단 접지 후가공만 당일판이 가능합니다.";

                        // 재단, 2/3단 접지 이외에는 다 거른다
                        // 코팅
                        if (coatingChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);
                            // 도무송 
                        } else if (thomsonChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);
                            // 오시 
                        } else if (impressChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);
                            // 미싱
                        } else if (dotlineChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);
                            // 박
                        } else if (foilChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);
                            // 형압
                        } else if (pressChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);
                            // 엠보싱
                        } else if (embossingChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);
                            // 라미넥스
                        } else if (laminexChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);
                            // 넘버링
                        } else if (numberingChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);
                            // 재단(4단이상)
                        } else if (foldlineChk === true) {
                            if (foldDvs > 3) {
                                optRestrict.msg = dayBoardMsg;
                                optRestrict.unchecked(idx);
                            }

                        } else {

                        }

                    } else if (dayPaperSize >= 200) {
                        var infoMsg = "평량 200g 이상인 1연 이하 주문은 재단 후가공만 당일판이 가능합니다.";

                        // 재단 이외에는 다 거른다
                        if (coatingChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);
                        } else if (thomsonChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);

                        } else if (impressChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);

                        } else if (foldlineChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);

                        } else if (dotlineChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);

                        } else if (foilChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);

                        } else if (pressChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);

                        } else if (embossingChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);

                        } else if (laminexChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);

                        } else if (numberingChk === true) {
                            //optRestrict.msg = infoMsg;
                            optRestrict.msg = dayBoardMsg;
                            optRestrict.unchecked(idx);

                        } else {}
                    }
                }

                // 초소량인쇄
            } else {

            }

        }
    },
    "포장방법": {
        "common": function (dvs, idx) {
                var prefix = getPrefix(dvs);
                var sortcode = $(prefix + "cate_sortcode").val();
                var sortcodeT = sortcode.substr(0, 3);

                var ret = true;
                if (!checkBlank(this[sortcodeT])) {
                    ret = this[sortcodeT](dvs, idx);
                }

                return ret;
                /*(
                var size = $(prefix + "size > option:selected").text();
                // A1, A2, 2절 별도포장 안됨
                if (size === "A1" ||
                        size === "A2" ||
                        size === "2절") {
                    optRestrict.msg = size + " 사이즈는 별도포장이 불가합니다.";
                    optRestrict.unchecked(idx);
                    return false;
                }


                return true;
                */
            }
            // 초소량인쇄
            ,
        "005": function (dvs, idx) {
            var prefix = getPrefix(dvs);
            var sortcode = $(prefix + "cate_sortcode").val();
            var sortcodeB = sortcode.substr(0, 6);
            var size = $(prefix + "size > option:selected").text();

            if (sortcodeB == "005003") {
                if (size.indexOf("A1") > -1 ||
                    size.indexOf("A2") > -1 ||
                    size.indexOf("2절") > -1 ||
                    size.indexOf("4절") > -1) {
                    $("#opt_" + idx + "_val option[value='256']").each(function () {
                        $(this).show();
                    });
                } else {
                    console.log(idx);
                    $("#opt_" + idx + "_val option[value='256']").each(function () {
                        optRestrict.unchecked(idx);
                        optRestrict.msg = size + "  사이즈는 별도포장이 불가능합니다.";
                        $(this).hide();
                        return false;
                    });
                }
            }

            return true;
        }
    }
};
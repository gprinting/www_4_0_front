$(document).ready(function () {
    //최초 첫번째 받는 주소에 모두 체크

    //배송지 단일/복수 설정
    $('.sheet .delivery ._toNum input[type=radio]').on('click', function () {
        if ($(this).hasClass('_single')) {
            $(this).closest('ul').find('select')[0].disabled = true;
            toTableSetting(1);
            setDlvrCost();
        } else {
            $(this).closest('li').find('select')[0].disabled = false;
            var toNum = Number($(this).closest('ul').find('select').children('option:selected').text());
            toTableSetting(toNum);
            setDlvrCost();
        }
    });

    $("#multi_to_num").on('change', function () {
        var toNum = Number($(this).children('option:selected').val())
        toTableSetting(toNum);
        setDlvrCost();
    });

    $('.sheet .delivery ._toNum ._single').click();
    $('#multi_to_num option:first-child').attr('selected', true);

    //결제 방법
    if ($('input._paymentType:checked').length == 0) {
        $('input._paymentType._prepaid').attr('checked', true);
    }

    //금액 요약
    $('._paymentType input._prepaid').on('click', function () {
        priceSummaryByType();
    });

    priceSummaryByType();
    //setProductNames(1);
});

function copyToTable(idx) {
    var $clone = $($('.delivery table.input.to.addr')[0]).clone();
    var deliverySection = $('div.delivery');

    $clone.attr({
        "id": "to_" + idx,
        "style": ""
    });
    // 새로운 정보 입력부분 이름변경, check
    $clone.find(".to_1_preset_memb")
        .attr({
            "name": "to_" + idx + "_preset",
            "onclick": "changeTo('" + idx + "', 'memb')",
            "class": "to_" + idx + "_preset_memb"
        })
        .prop("checked", true);
    $clone.find(".to_1_preset_new")
        .attr({
            "name": "to_" + idx + "_preset",
            "onclick": "changeTo('" + idx + "', 'new')",
            "class": "to_" + idx + "_preset_new"
        })
        .prop("checked", true);

    $clone.find("#to_1_new_dlvr")
        .attr({
            "id": "to_" + idx + "_new_dlvr"
        });
    // 주소입력 라디오버튼 바인드 함수 변경
    $clone.find(".postcode_btn").attr("onclick",
        "getPostcode('to_" + idx + "');");
    // 나의배송지 선택 바인드 함수 변경
    $clone.find(".dlvr_addr_pop").attr({
        "onclick": "showDlvrAddrListPop('to_" + idx + "');",
        "id": "to_" + idx + "_select_mydlvr"
    });
    // 나의배송지로 등록 바인드 함수 변경
    $clone.find(".addressRegist").attr("onclick",
        "showDlvrAddrRegiPop('to_" + idx + "');");
    // 배송방법
    $clone.find("#to_1_dlvr_way").attr({
        "id": "to_" + idx + "_dlvr_way",
        "name": "to_" + idx + "_dlvr_way",
        "onchange": "getDlvrCost.exec('to_" + idx + "');"
    });
    // 배송비
    $clone.find("#to_1_dlvr_price").attr({
        "id": "to_" + idx + "_dlvr_price",
        "name": "to_" + idx + "_dlvr_price",
        "val": "0"
    });
    // 배송비 지불
    $clone.find("input[name='to_1_dlvr_sum_way']").attr({
        "name": "to_" + idx + "_dlvr_sum_way",
        "onchange": "getDlvrCost.exec('to_" + idx + "');"
    });
    // 성명/상호
    $clone.find("#to_1_name").attr({
        "id": "to_" + idx + "_name",
        "name": "to_" + idx + "_name"
    });
    // 연락처
    $clone.find("#to_1_tel_num1").attr({
        "id": "to_" + idx + "_tel_num1",
        "name": "to_" + idx + "_tel_num1"
    });
    $clone.find("#to_1_tel_num2").attr({
        "id": "to_" + idx + "_tel_num2",
        "name": "to_" + idx + "_tel_num2"
    });
    $clone.find("#to_1_tel_num3").attr({
        "id": "to_" + idx + "_tel_num3",
        "name": "to_" + idx + "_tel_num3"
    });
    // 휴대전화
    $clone.find("#to_1_cell_num1").attr({
        "id": "to_" + idx + "_cell_num1",
        "name": "to_" + idx + "_cell_num1"
    });
    $clone.find("#to_1_cell_num2").attr({
        "id": "to_" + idx + "_cell_num2",
        "name": "to_" + idx + "_cell_num2"
    });
    $clone.find("#to_1_cell_num3").attr({
        "id": "to_" + idx + "_cell_num3",
        "name": "to_" + idx + "_cell_num3"
    });
    // 주소
    $clone.find("#to_1_zipcode").attr({
        "id": "to_" + idx + "_zipcode",
        "name": "to_" + idx + "_zipcode"
    });
    $clone.find("#to_1_addr_top").attr({
        "id": "to_" + idx + "_addr_top",
        "name": "to_" + idx + "_addr_top"
    });
    $clone.find("#to_1_addr_detail").attr({
        "id": "to_" + idx + "_addr_detail",
        "name": "to_" + idx + "_addr_detail"
    });
    //배송료
    $clone.find("#to_1_dlvrcost").attr({
        "id": "to_" + idx + "_dlvrcost",
        "name": "to_" + idx + "_dlvrcost"
    });

    $clone.find("#to_" + idx + "_dlvrcost").text("");

    $clone.find("#to_product_" + idx).attr({
        "onchange": "changeProductCheck(" + "this.id, " + idx + ")"
    });

    $clone.find("#removeTo_1").attr({
        "id": "removeTo_" + idx,
        "onclick": "removeTo(" + idx + ")"
    });

    $clone.find("#removeTo_" + idx).show();

    $clone.find("#product_select_1").attr({
        "id": "product_select_" + idx,
        "onclick": "showSelectProductPopup('to_" + idx + "')"
    });

    $clone.find("#to_1_dlvr_way").attr({
        "id": "to_" + idx + "_dlvr_way",
        "name": "to_" + idx + "_dlvr_way",
        "onchange": "getDlvrCost.exec('to_" + idx + "');"
    });

    $clone.find("#to_1_bl_group").attr({
        "id": "to_" + idx + "_bl_group",
        "name": "to_" + idx + "_bl_group",
        "value": ""
    });

    $clone.find("#to_1_nc_group").attr({
        "id": "to_" + idx + "_nc_group",
        "name": "to_" + idx + "_nc_group",
        "value": ""
    });

    $clone.find("#to_1_bl_price").attr({
        "id": "to_" + idx + "_bl_price",
        "name": "to_" + idx + "_bl_price",
        "value": ""
    });

    $clone.find("#to_1_nc_price").attr({
        "id": "to_" + idx + "_nc_price",
        "name": "to_" + idx + "_nc_price",
        "value": ""
    });

    $clone.find("#to_1_nc_expec_weight").attr({
        "id": "to_" + idx + "_nc_expec_weight",
        "name": "to_" + idx + "_nc_expec_weight",
        "value": ""
    });

    $clone.find("#to_1_bl_expec_weight").attr({
        "id": "to_" + idx + "_bl_expec_weight",
        "name": "to_" + idx + "_bl_expec_weight",
        "value": ""
    });

    $clone.find("#to_1_nc_boxcount").attr({
        "id": "to_" + idx + "_nc_boxcount",
        "name": "to_" + idx + "_nc_boxcount",
        "value": ""
    });

    $clone.find("#to_1_bl_boxcount").attr({
        "id": "to_" + idx + "_bl_boxcount",
        "name": "to_" + idx + "_bl_boxcount",
        "value": ""
    });

    $clone.find("#to_1_dlvr_warning").attr({
        "id": "to_" + idx + "_dlvr_warning",
        "name": "to_" + idx + "_dlvr_warning",
        "value": ""
    });

    $clone.find("#to_1_dlvr_req_sel").attr({
        "id": "to_" + idx + "_dlvr_req_sel",
        "onchange": "setDlvrReq('" + idx + "', this);"
    }).val('1');

    $clone.find("#to_1_dlvr_req").attr({
        "id": "to_" + idx + "_dlvr_req",
        "name": "to_" + idx + "_dlvr_req",
        "value": ""
    });

    $clone.find("#to_1_hide_dlvr_req_txt").attr({
        "id": "to_" + idx + "_hide_dlvr_req_txt",
        "onclick": "showDlvrReqSel('" + idx + "');",
        "value": ""
    });

    $clone.find("#addTo_1").attr({
        "id": "addTo_" + idx,
        "onclick": "addToTable(" + idx + ")"
    });

    $clone.find("#addTo_" + idx).show();

    $clone.find("#to_" + idx + "_name").val("");
    $clone.find("#to_" + idx + "_tel_num2").val("");
    $clone.find("#to_" + idx + "_tel_num3").val("");

    $clone.find("#to_" + idx + "_cell_num2").val("");
    $clone.find("#to_" + idx + "_cell_num3").val("");

    $clone.find("#to_" + idx + "_zipcode").val("");
    $clone.find("#to_" + idx + "_addr_top").val("");

    $clone.find("#to_" + idx + "_addr_detail").val("");

    $clone.find("#to_" + idx + "_bl_group").attr("value", "");
    $clone.find("#to_" + idx + "_nc_group").attr("value", "");
    $clone.find("#to_" + idx + "_bl_expec_weight").attr("value", "");
    $clone.find("#to_" + idx + "_nc_expec_weight").attr("value", "");
    $clone.find("#to_" + idx + "_bl_boxcount").attr("value", "");
    $clone.find("#to_" + idx + "_nc_boxcount").attr("value", "");
    $clone.find("#to_" + idx + "_dlvr_warning").attr("value", "");

    $clone.find("#to_" + idx + "_dlvrcost").text("");
    $clone.find("#to_" + idx + "_dlvr_way option:eq(0)").attr("selected", "selected");

    $clone.find("._selected").remove();

    deliverySection.append($clone);
}

//받는 사람 table 설정
function toTableSetting(toNum) {
    var currentToNum = $('.delivery table.input.to.addr').length,
        deliverySection = $('div.delivery');

    if (currentToNum > toNum) {
        deliverySection.find('table.input.to.addr').each(function (i) {
            if (i >= toNum) {
                // 삭제되는 테이블에 속한 라디오 버튼이 사라지면 체크도 없어짐
                // 그래서 삭제되는 라디오 버튼을 맨 위쪽으로 체크해줌
                $(this).find(".orderGroup:checked").each(function () {
                    var name = $(this).attr("name");
                    $("#to_1").find("input[type='radio'][name='" + name + "']")
                        .prop("checked", true);
                });

                $(this).remove();
            }
        });
    } else {
        var tableHtml = $($('.delivery table.input.to.addr')[0]).clone();
        // 회원정보와 동일 부분 삭제
        tableHtml.find('label[member_preset=member_preset]').remove();
        tableHtml.find('input[type=text]').val('');
        tableHtml.find('.orderGroup').attr('checked', false);
        tableHtml.find('select').children('option:first-child').attr('selected', true);

        for (var i = currentToNum; i < toNum; i++) {
            var idx = i + 1;
            var $clone = tableHtml.clone();
            $clone.attr("id", "to_" + idx);
            // 새로운 정보 입력부분 이름변경, check
            $clone.find(".to_1_preset_memb")
                .attr({
                    "name": "to_" + idx + "_preset",
                    "onclick": "changeTo('" + idx + "', 'memb')",
                    "class": "to_" + idx + "_preset_memb"
                })
                .prop("checked", true);
            $clone.find("#to_1_new_dlvr")
                .attr({
                    "id": "to_" + idx + "_new_dlvr"
                });

            $clone.find(".to_1_preset_new")
                .attr({
                    "name": "to_" + idx + "_preset",
                    "onclick": "changeTo('" + idx + "', 'new')",
                    "class": "to_" + idx + "_preset_new"
                })
                .prop("checked", true);
            // 주소입력 라디오버튼 바인드 함수 변경
            $clone.find(".postcode_btn").attr("onclick",
                "getPostcode('to_" + idx + "');");
            // 나의배송지 선택 바인드 함수 변경
            $clone.find(".dlvr_addr_pop").attr({
                "onclick": "showDlvrAddrListPop('to_" + idx + "');",
                "id": "to_" + idx + "_select_mydlvr"
            });
            // 나의배송지로 등록 바인드 함수 변경
            $clone.find(".addressRegist").attr("onclick",
                "showDlvrAddrRegiPop('to_" + idx + "');");
            // 배송방법
            $clone.find("#to_1_dlvr_way").attr({
                "id": "to_" + idx + "_dlvr_way",
                "name": "to_" + idx + "_dlvr_way",
                "onchange": "getDlvrCost.exec('to_" + idx + "');"
            });
            // 배송비
            $clone.find("#to_1_dlvr_price").attr({
                "id": "to_" + idx + "_dlvr_price",
                "name": "to_" + idx + "_dlvr_price",
                "val": "0"
            });
            // 배송비 지불
            $clone.find("input[name='to_1_dlvr_sum_way']").attr({
                "name": "to_" + idx + "_dlvr_sum_way",
                "onchange": "getDlvrCost.exec('to_" + idx + "')"
            });
            // 성명/상호
            $clone.find("#to_1_name").attr({
                "id": "to_" + idx + "_name",
                "name": "to_" + idx + "_name"
            });
            // 연락처
            $clone.find("#to_1_tel_num1").attr({
                "id": "to_" + idx + "_tel_num1",
                "name": "to_" + idx + "_tel_num1"
            });
            $clone.find("#to_1_tel_num2").attr({
                "id": "to_" + idx + "_tel_num2",
                "name": "to_" + idx + "_tel_num2"
            });
            $clone.find("#to_1_tel_num3").attr({
                "id": "to_" + idx + "_tel_num3",
                "name": "to_" + idx + "_tel_num3"
            });
            // 휴대전화
            $clone.find("#to_1_cell_num1").attr({
                "id": "to_" + idx + "_cell_num1",
                "name": "to_" + idx + "_cell_num1"
            });
            $clone.find("#to_1_cell_num2").attr({
                "id": "to_" + idx + "_cell_num2",
                "name": "to_" + idx + "_cell_num2"
            });
            $clone.find("#to_1_cell_num3").attr({
                "id": "to_" + idx + "_cell_num3",
                "name": "to_" + idx + "_cell_num3"
            });
            // 주소
            $clone.find("#to_1_zipcode").attr({
                "id": "to_" + idx + "_zipcode",
                "name": "to_" + idx + "_zipcode"
            });
            $clone.find("#to_1_addr").attr({
                "id": "to_" + idx + "_addr",
                "name": "to_" + idx + "_addr"
            });
            $clone.find("#to_1_addr_detail").attr({
                "id": "to_" + idx + "_addr_detail",
                "name": "to_" + idx + "_addr_detail"
            });
            //배송료
            $clone.find("#to_1_dlvrcost").attr({
                "id": "to_" + idx + "_dlvrcost",
                "name": "to_" + idx + "_dlvrcost"
            });
            // 주문선택 위치지정
            $clone.find(".orderGroup").attr("pos", "to_" + idx);

            $clone.find("#removeTo_1").attr({
                "onclick": "removeTo(" + idx + ")",
                "id": "removeTo_" + idx
            });

            $clone.find("#product_select_1").attr({
                "id": "product_select_" + idx,
                "onclick": "showSelectProductPopup('to_" + idx + "')"
            });

            $clone.find("#to_1_bl_group").attr({
                "id": "to_" + idx + "_bl_group",
                "name": "to_" + idx + "_bl_group",
                "value": ""
            });

            $clone.find("#to_1_nc_group").attr({
                "id": "to_" + idx + "_nc_group",
                "name": "to_" + idx + "_nc_group",
                "value": ""
            });

            $clone.find("#to_1_nc_expec_weight").attr({
                "id": "to_" + idx + "_nc_expec_weight",
                "name": "to_" + idx + "_nc_expec_weight",
                "value": ""
            });

            $clone.find("#to_1_bl_expec_weight").attr({
                "id": "to_" + idx + "_bl_expec_weight",
                "name": "to_" + idx + "_bl_expec_weight",
                "value": ""
            });

            $clone.find("#to_1_nc_boxcount").attr({
                "id": "to_" + idx + "_nc_boxcount",
                "name": "to_" + idx + "_nc_boxcount",
                "value": ""
            });

            $clone.find("#to_1_bl_boxcount").attr({
                "id": "to_" + idx + "_bl_boxcount",
                "name": "to_" + idx + "_bl_boxcount",
                "value": ""
            });

            $clone.find("#to_1_dlvr_warning").attr({
                "id": "to_" + idx + "_dlvr_warning",
                "name": "to_" + idx + "_dlvr_warning",
                "value": ""
            });



            deliverySection.append($clone);

            //배송비 0원으로 초기화
            $("#to_" + idx + "_dlvr_price").val(0);

            setDlvrCost();
        }
    }

    calcPrice();
}

function resetToTable(before, after) {
    var tbl_before = $("#to_" + before);

    tbl_before.attr({
        "id": "to_" + after
    });
    // 새로운 정보 입력부분 이름변경, check
    tbl_before.find(".to_" + before + "_preset_memb")
        .attr({
            "name": "to_" + after + "_preset",
            "onclick": "changeTo('" + after + "', 'memb')",
            "class": "to_" + after + "_preset_memb"
        })
        .prop("checked", true);
    tbl_before.find(".to_" + before + "_preset_new")
        .attr({
            "name": "to_" + after + "_preset",
            "onclick": "changeTo('" + after + "', 'new')",
            "class": "to_" + after + "_preset_new"
        })
        .prop("checked", true);
    tbl_before.find("#to_" + before + "_new_dlvr")
        .attr({
            "id": "to_" + after + "_new_dlvr"
        });
    // 주소입력 라디오버튼 바인드 함수 변경
    tbl_before.find(".postcode_btn").attr("onclick",
        "getPostcode('to_" + after + "');");
    // 나의배송지 선택 바인드 함수 변경
    tbl_before.find(".dlvr_addr_pop").attr({
        "onclick": "showDlvrAddrListPop('to_" + after + "');",
        "id": "to_" + after + "_select_mydlvr"
    });
    // 나의배송지로 등록 바인드 함수 변경
    tbl_before.find(".addressRegist").attr("onclick",
        "showDlvrAddrRegiPop('to_" + after + "');");
    // 배송방법
    tbl_before.find("#to_" + before + "_dlvr_way").attr({
        "id": "to_" + after + "_dlvr_way",
        "name": "to_" + after + "_dlvr_way",
        "onchange": "getDlvrCost.exec('to_" + after + "');"
    });
    // 배송비
    tbl_before.find("#to_" + before + "_dlvr_price").attr({
        "id": "to_" + after + "_dlvr_price",
        "name": "to_" + after + "_dlvr_price"
    });
    // 배송비 지불
    tbl_before.find("input[name='to_" + before + "_dlvr_sum_way']").attr({
        "name": "to_" + after + "_dlvr_sum_way",
        "onchange": "getDlvrCost.exec('to_" + after + "');"
    });
    // 성명/상호
    tbl_before.find("#to_" + before + "_name").attr({
        "id": "to_" + after + "_name",
        "name": "to_" + after + "_name"
    });
    // 연락처
    tbl_before.find("#to_" + before + "_tel_num1").attr({
        "id": "to_" + after + "_tel_num1",
        "name": "to_" + after + "_tel_num1"
    });
    tbl_before.find("#to_" + before + "_tel_num2").attr({
        "id": "to_" + after + "_tel_num2",
        "name": "to_" + after + "_tel_num2"
    });
    tbl_before.find("#to_" + before + "_tel_num3").attr({
        "id": "to_" + after + "_tel_num3",
        "name": "to_" + after + "_tel_num3"
    });
    // 휴대전화
    tbl_before.find("#to_" + before + "_cell_num1").attr({
        "id": "to_" + after + "_cell_num1",
        "name": "to_" + after + "_cell_num1"
    });
    tbl_before.find("#to_" + before + "_cell_num2").attr({
        "id": "to_" + after + "_cell_num2",
        "name": "to_" + after + "_cell_num2"
    });
    tbl_before.find("#to_" + before + "_cell_num3").attr({
        "id": "to_" + after + "_cell_num3",
        "name": "to_" + after + "_cell_num3"
    });
    // 주소
    tbl_before.find("#to_" + before + "_zipcode").attr({
        "id": "to_" + after + "_zipcode",
        "name": "to_" + after + "_zipcode"
    });
    tbl_before.find("#to_" + before + "_addr").attr({
        "id": "to_" + after + "_addr",
        "name": "to_" + after + "_addr"
    });
    tbl_before.find("#to_" + before + "_addr_detail").attr({
        "id": "to_" + after + "_addr_detail",
        "name": "to_" + after + "_addr_detail"
    });
    //배송료
    tbl_before.find("#to_" + before + "_dlvrcost").attr({
        "id": "to_" + after + "_dlvrcost",
        "name": "to_" + after + "_dlvrcost"
    });

    tbl_before.find("#to_product_" + after).attr({
        "onchange": "changeProductCheck(" + "this.id, " + after + ")"
    });

    tbl_before.find("#removeTo_" + before).attr({
        "id": "removeTo_" + after,
        "onclick": "removeTo(" + after + ")"
    });

    tbl_before.find("#product_select_" + before).attr({
        "id": "product_select_" + after,
        "onclick": "showSelectProductPopup('to_" + after + "')"
    });

    tbl_before.find("#addTo_" + before).attr({
        "id": "addTo_" + after,
        "onclick": "addToTable(" + after + ")"
    });

    tbl_before.find("#to_" + before + "_bl_group").attr({
        "id": "to_" + after + "_bl_group",
        "name": "to_" + after + "_bl_group",
        "value": ""
    });

    tbl_before.find("#to_" + before + "_nc_group").attr({
        "id": "to_" + after + "_nc_group",
        "name": "to_" + after + "_nc_group"
    });

    tbl_before.find("#to_" + before + "_nc_price").attr({
        "id": "to_" + after + "_nc_price",
        "name": "to_" + after + "_nc_price"
    });

    tbl_before.find("#to_" + before + "_bl_price").attr({
        "id": "to_" + after + "_bl_price",
        "name": "to_" + after + "_bl_price"
    });

    tbl_before.find("#to_" + before + "_dlvr_way").attr({
        "id": "to_" + after + "_dlvr_way",
        "name": "to_" + after + "_dlvr_way",
        "onchange": "getDlvrCost.exec('to_" + after + "');"
    });

    tbl_before.find("#to_" + before + "_nc_expec_weight").attr({
        "id": "to_" + after + "_nc_expec_weight",
        "name": "to_" + after + "_nc_expec_weight"
    });

    tbl_before.find("#to_" + before + "_bl_expec_weight").attr({
        "id": "to_" + after + "_bl_expec_weight",
        "name": "to_" + after + "_bl_expec_weight"
    });

    tbl_before.find("#to_" + before + "_nc_boxcount").attr({
        "id": "to_" + after + "_nc_boxcount",
        "name": "to_" + after + "_nc_boxcount"
    });

    tbl_before.find("#to_" + before + "_bl_boxcount").attr({
        "id": "to_" + after + "_bl_boxcount",
        "name": "to_" + after + "_bl_boxcount"
    });

    tbl_before.find("#to_" + before + "_dlvr_warning").attr({
        "id": "to_" + after + "_dlvr_warning",
        "name": "to_" + after + "_dlvr_warning"
    });
    /*
        tbl_before.find("#to_"+before+"_name").val("");
        tbl_before.find("#to_"+before+"_tel_num2").val("");
        tbl_before.find("#to_"+before+"_tel_num3").val("");

        tbl_before.find("#to_"+before+"_cell_num2").val("");
        tbl_before.find("#to_"+before+"_cell_num3").val("");

        tbl_before.find("#to_"+before+"_zipcode").val("");
        tbl_before.find("#to_"+before+"_addr").val("");

        tbl_before.find("#to_"+before+"_addr_detail").val("");

        tbl_before.find("#to_"+before+"_bl_group").attr("value", "");
        tbl_before.find("#to_"+before+"_nc_group").attr("value", "");

        tbl_before.find("#to_"+before+"_dlvrcost").text("");
        tbl_before.find("#to_"+before+"_dlvr_way option:eq(0)").attr("selected", "selected");

        tbl_before.find("._selected").remove();
    */
    setUnSelectedValue();
}

// 주문서 - 결제방법 선입금 선택 시 주문부족금액
function priceSummaryByType() {
    if ($('._paymentType input._prepaid')[0] && $('._paymentType input._prepaid')[0].checked) {
        $('.sheet .priceSummary').addClass('_prepaid');
    } else {
        $('.sheet .priceSummary').removeClass('_prepaid');
    }
}
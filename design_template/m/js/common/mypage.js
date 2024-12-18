//테이블 상세
$(document).ready(function(){
    //최상단 체크박스 클릭
    $("#chk_list_all").click(function(){
        var check = $("#chk_list_all").prop("checked");
        $("input[name=chk_list]").prop("checked", check);
    });

    $(".btn_modify_01").click(function () {
        $(".btn_modify_01").hide();
        $(".btn_modify_01_on").show();
        $(".detail_table_01").show();
    });
    $(".btn_modify_01_on").click(function () {
        $(".detail_table_01").hide();
        $(".btn_modify_01_on").hide();
        $(".btn_modify_01").show();
    });

    var fileTarget = $('.filebox .upload-hidden'); 
    fileTarget.on('change', function(){
        if(window.FileReader){
            var filename = $(this)[0].files[0].name;
        }
        else {
            var filename = $(this).val().split('/').pop().split('\\').pop();
        } 
        $(this).siblings('.upload-name').val(filename);
    });

});




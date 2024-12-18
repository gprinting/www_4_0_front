$(window).on('load', function () {
    $('button.dailycheck').on('click', function () {
        $(this).closest('li').addClass('checked');
    });
});
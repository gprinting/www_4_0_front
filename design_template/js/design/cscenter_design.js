$(document).ready(function () {
    $('.faq .list dd').slideUp(0);
    $('.faq .list dt').bind('click', function () {
        if ($(this).closest('li').hasClass('on')) {
            $(this).closest('ul').children('.on').removeClass('on')
                .find('dd').slideUp(300);
        } else {
            $(this).closest('ul').children('.on').removeClass('on')
                .find('dd').slideUp(300);
            $(this).closest('li').addClass('on');
            $(this).next().slideDown(300);
        }
    });
});
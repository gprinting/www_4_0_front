 $(window).ready(function () {
	// LNB
	$("#body nav h3").click(function () {
		if(!$(this).hasClass('on')) {
			$(this).addClass('on');
		} else {
			$(this).removeClass('on');
		}
		$("#body nav ul").animate({
			opacity: 'toggle',
			height: 'toggle'
		});
	});

	// System Status
    $('ul.tab li p').on('click', function () {
        var divid = $(this).attr('class');
		if ($(this).closest('li').hasClass('on')) return;
        $(this).closest('ul').find('.on').removeClass('on');
		$(this).closest('li').addClass('on');
		$(this).closest('article').find('div.on').removeClass('on');
		$(this).closest('article').find('#'+divid).addClass('on');
    });
 });
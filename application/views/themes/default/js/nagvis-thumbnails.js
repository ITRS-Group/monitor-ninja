function show_thumbnail(element)
{
	$('body').append('<div id="popup_thumbnail" style="position: absolute; top: '
		+ (element.offset().top + element.outerHeight()) + 'px; left: ' + element.offset().left
		+ 'px; background-image: url(\'css/default/images/bg.png\'); background-color: #f5f5f5;'
		+ ' border: 1px solid #d0d0d0;"><img src="/nagvis/var/'
		+ ((element.text() == 'Automap')? '__automap' : element.text()) + '-thumb.png" /></div>');
}

function hide_thumbnail()
{
	$('#popup_thumbnail').remove();
}

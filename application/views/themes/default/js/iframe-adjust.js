
$(document).ready(function() {
	adjust_height();

	if ($('#iframe') || $('#nagvis') || $('#hypermap')) {
		var elements = $('#iframe, #nagvis, #hypermap');
		
		var ua = navigator.userAgent.toLowerCase();

		if (ua.indexOf('ipad') != -1 || ua.indexOf('android') != -1) {
			
			var agent = (ua.indexOf('ipad') != -1) ? 'iPad' : 'Android';

			elements.load(function () {

				var note = $('<div style="position: relative; border: 1px solid #eebb55; padding: 8px; background: #fea; font-size: 9pt;"></div>');

				if (elements.attr('id') === 'iframe') {
					note.css('top', '28px');
				}

				note.append('<span>Known issues on ' + agent + ' with iframes </span>');
				note.append($('<a href="#"></a>').html('Open in a new window').click(function () {
					window.open('https://' + window.location.hostname + elements.attr('src'), '_blank');
				}))

				var first = $(elements.contents().find('body')[0].firstChild);
				first.before(note);
			});
		}

	}

});

function adjust_height() {
	var iframe_pos = $('#iframe').position();
	var new_height = parseInt(document.documentElement.clientHeight) - iframe_pos.top;
	$('#iframe').css('height', new_height+'px');
	$('#nagvis').css('height', new_height+'px');
	$('#hypermap').css('height', new_height+'px');
	$('#content').css('height', new_height+'px');
	$('body').css('overflow-y', 'hidden');
};

window.onresize = function (){
	adjust_height();
};

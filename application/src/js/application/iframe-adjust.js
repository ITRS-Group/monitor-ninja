

$(document).ready(function() {

	function adjust_height_frame( selector ) {

		var content_div = $( "body > .container > #content" ),
			header_div = $( "body > .container >#header" ),
			body = $( "body" );

		var height = body.height() - header_div.outerHeight();
		var iframe = $( selector );

		if( iframe ) {
			iframe.css( 'height', ( height - 4 ) + 'px' );
			content_div.css( "height", (height) + "px" );
		}

	}

	function adjust_height() {

		adjust_height_frame('#iframe');
		adjust_height_frame('#nagvis');
		adjust_height_frame('#content');

		$('body').css('overflow-y', 'hidden');

	}

	adjust_height();

	if ($('#iframe') || $('#nagvis')) {
		var elements = $('#iframe, #nagvis');

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
				}));

				var first = $(elements.contents().find('body')[0].firstChild);
				first.before(note);
			});
		}

	}

	$(window).on("resize", adjust_height);

});

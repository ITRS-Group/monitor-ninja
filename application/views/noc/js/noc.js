$(document).ready(function() {
	$("#settings_icon").unbind('click');
	$("#settings_icon").click(function() {
		if ($("#page_settings").is(':hidden')) {
			$("#page_settings").show();
			if ($('#infobar').is(':visible')) {
				var top = 92;
				$('#version_info').css('top', (top + 3) + 'px');
				$('#page_settings').css('top', (top + 3) + 'px');
			}
		} else {
			$("#page_settings").hide();
		}
		return false;
	});

	$('#jmenu').jmenu({animation:'fade',duration:100});
});


var _top_menu_occupied = false;

function callback() {
	_top_menu_occupied = false;
}

function show_info(action) {
	if ($('#version_info').is(':visible')) {
		$('#version_info').hide();
	} else {
		if ($('#infobar').is(':visible')) {
			var top = 92;
			$('#version_info').css('top', (top + 3) + 'px');
		}
		$('#version_info').show();
	}
}

var default_menu_state = 'show';
function collapse_menu(action) {
	if (action == 'hide') {
		$('#menu').css('width', '35px');
		$('#close-menu').hide();
		$('#show-menu').show();
		$('.ninja_menu_links').hide();
		$('li.header cite').hide();
		$('#content').css('margin-left', '35px');
		save_ninja_menu_state('hide');
	}
	if (action == 'show') {
		$('#menu').css('width', '166px');
		$('#close-menu').show();
		$('#show-menu').hide();
		$('.ninja_menu_links').show();
		$('li.header cite').show();
		$('#content').css('margin-left', '166px');
		save_ninja_menu_state('show');
	}

}

function settings(action) {
	if (action == 'hide') {
		document.getElementById('page_settings').style.display = 'none';
	}
	else {
		if (document.getElementById('page_settings').style.display == 'block')
			document.getElementById('page_settings').style.display = 'none';
		else
			document.getElementById('page_settings').style.display = 'block';
	}
}

window.onload = function() {
	collapse_menu('');
}
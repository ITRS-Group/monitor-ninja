var default_menu_state = 'show';
function collapse_menu(action, save) {

	// check if the master template wants to prevent
	// the menu from moving parts of the page
	if (typeof _no_menu_refresh != 'undefined' && _no_menu_refresh) {
		return;
	}
	if (action == 'hide') {
		var menuwidth = (parseInt($('#menu ul').height()) > parseInt(document.documentElement.clientHeight-68)) ? 50 : 37;
		$('#menu').css('width', menuwidth+'px');
		$('#content').css('margin-left', parseInt(menuwidth+2)+'px');
		$('#close-menu').hide();
		$('#show-menu').show();
		$('.ninja_menu_links').hide();
		$('li.header cite').hide();
		$('li.header em').css('display','block');
	}
	if (action == 'show') {
		$('#menu').css('width', '185px');
		$('#content').css('margin-left', '187px');
		$('#close-menu').show();
		$('#show-menu').hide();
		$('.ninja_menu_links').show();
		$('li.header cite').show();
		$('li.header em').hide();
	}

	if(save) {
		$.ajax(
			_site_domain + _index_page + "/ajax/save_page_setting/",
			{
				data: {
					page: escape('/'),
					type: 'ninja_menu_state',
					setting: action
				},
				type: 'POST',
				dataType: 'json'
			}
		);
	}
}

function settings(action) {
	var settings = document.getElementById('page_settings');
	if (action == 'hide') {
		settings.style.display = 'none';
	} else if (settings.style.display == 'block') {
		settings.style.display = 'none';
	} else {
		settings.style.display = 'block';
	}
}

function show_info(action) {
	if ($('#version_info').is(':visible')) {
		$('#version_info').hide();
	} else {
		if ($('#infobar').is(':visible')) {
			var top = 125;
			$('#version_info').css('top', (top + 3) + 'px');
		}
		$('#version_info').show();
	}
}

function collapse_section(section, save){
	$('.'+section).slideToggle(200,function(){
		$(this).toggleClass(section + '_hidden');
	});

	if (save) {
		// we use 'show' and 'hide' but the only thing used when
		// page reloads is 'hide' since they are visible by default
		// save menu section state
		$.ajax(
			_site_domain + _index_page + "/ajax/save_page_setting/",
			{
				data: {
					page: escape('/'), type: 'ninja_menusection_'+section,
					setting: $('.' + section + "_hidden").text() ? 'show' : 'hide'
				},
				dataType: 'json',
				type: 'POST'
			}
		);
	}
}

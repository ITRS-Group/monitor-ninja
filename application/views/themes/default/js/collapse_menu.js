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
		if (save) {
			save_ninja_menu_state('hide');
		}
	}
	if (action == 'show') {
		$('#menu').css('width', '185px');
		$('#content').css('margin-left', '187px');
		$('#close-menu').show();
		$('#show-menu').hide();
		$('.ninja_menu_links').show();
		$('li.header cite').show();
		$('li.header em').hide();
		if (save) {
			save_ninja_menu_state('show');
		}
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
		if ($(this).hasClass(section + '_hidden'))
			$(this).removeClass(section + "_hidden");
		else
			$(this).addClass(section + "_hidden");
	});

	if (save) {
		// save menu section state
		if ($('.' + section + "_hidden").text()) {
			// save section state visible
			save_menu_section_state(section, 1);
		} else {
			// save section state hidden
			save_menu_section_state(section, 0);
		}
	}
}

/**
*	Save the current state of a menu state to database
*	for current user
*/
function save_menu_section_state(section, state)
{
	// we use 'show' and 'hide' but the only thing used when
	// page reloads is 'hide' since they are visible by default
	var state_str = state ? 'show' : 'hide';
	var url = _site_domain + _index_page + "/ajax/save_page_setting/";
	var page_name = '/';
	var data = {page: escape(page_name), type: 'ninja_menusection_'+section, setting: state_str};
	$.post(url, data)
}

function get_ninja_menu_state()
{
	// don't use ajax call if already
	// defined in master template
	if (typeof _ninja_menu_state != 'undefined') {
		return _ninja_menu_state;
	}

	var url = _site_domain + _index_page + "/ajax/get_setting/";
	var page_name = '/';
	var data = {page: escape(page_name), type: 'ninja_menu_state'};
	var ret_val;

	$.ajax({
		url: url,
		dataType:'json',
		type: 'POST',
		data: data,
		success: function(data) {
			if (data.ninja_menu_state != false) {
				ret_val = data.ninja_menu_state;
			}
			return ret_val;
		},
		error: function(obj, msg){/*alert(msg)*/}
	});
}

function save_ninja_menu_state(state)
{
	var url = _site_domain + _index_page + "/ajax/save_page_setting/";
	var page_name = '/';
	var data = {page: escape(page_name), type: 'ninja_menu_state', setting: state};
	$.post(url, data);
}

window.onload = function() {
	var state = get_ninja_menu_state();
	action = state != false ? state : default_menu_state;
	collapse_menu(action);
}

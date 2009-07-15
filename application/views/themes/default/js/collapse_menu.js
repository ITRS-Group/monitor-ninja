var default_menu_state = 'show';
function collapse_menu(action) {
	if (action == 'hide') {
		$('#menu').css('width', '37px');
		$('#menu-scroll').css('width', '37px');
		$('#menu-slider').css('left', '37px');
		$('#close-menu').hide();
		$('#show-menu').show();
		$('.ninja_menu_links').hide();
		$('li.header cite').hide();
		$('li.header em').css('display','block');
		$('#content').css('margin-left', '37px');
		save_ninja_menu_state('hide');
	}
	if (action == 'show') {
		$('#menu').css('width', '166px');
		$('#menu-scroll').css('width', '166px');
		$('#menu-slider').css('left', '166px');
		$('#close-menu').show();
		$('#show-menu').hide();
		$('.ninja_menu_links').show();
		$('li.header cite').show();
		$('li.header em').hide();
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

function collapse_section(section){
	$('.'+section).slideToggle(200);
}

function get_ninja_menu_state()
{
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
				collapse_menu(data.ninja_menu_state);
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
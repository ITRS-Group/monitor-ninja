var sURL = window.location.pathname + location.search;
var _interval = 0;
var _save_page_interval = 0;
var current_interval = 0;
var edit_visible = 0;
var _save_scroll = true;

$(document).ready(function() {

	var content_div = $( "body > .container > #content" ),
		header_div = $( "body > .container >#header" ),
		body = $( "body" );

	function fit_content () {
		var height = body.height() - header_div.outerHeight();
		content_div.css( "height", height + "px" );
	}

	$(window).bind( "resize", fit_content );
	fit_content();

	// make scroll memory cookie to be reset
	// when actively clicking on a link.
	$('body').on('click', 'a', function() {
		_save_scroll = false;
	});

	if ( content_div ) {
		content_div.click();
		content_div.focus();
	}

	// stop widgets from trying to reload once user clicked
	// on a menu
	$('#menu a').click(function() {_is_refreshing = true;});

	if ($.fn.contextMenu) {
		$("body").contextMenu({
				menu: 'property_menu', use_prop:true
			},
			function(action, elem){
				object_action(action, elem.attr('id'));
			}, ".obj_properties:not(.white)");

		$("body").contextMenu({
				menu: 'svc_property_menu', use_prop:true
			},
			function(action, elem){
				object_action(action, elem.attr('id'));
			},".svc_obj_properties");
	}

	// refresh helper code
	var old_refresh = 0;
	var refresh_is_paused = false;
	$("#ninja_refresh_control").bind('change', function() {
		if ($("#ninja_refresh_control").attr('checked')) {
			// save previous refresh rate
			// to be able to restore it later
			old_refresh = current_interval;
			$('#ninja_refresh_lable').css('font-weight', 'bold');
			ninja_refresh(0);
			refresh_is_paused = true;
		} else {
			// restore previous refresh rate
			ninja_refresh(old_refresh);
			refresh_is_paused = false;
			$('#ninja_refresh_lable').css('font-weight', '');
		}
	});
	if ($('#ninja_refresh_edit').text()!=='') {
		create_slider('ninja_page_refresh');
		$('#ninja_page_refresh_slider').on('slidechange', function() {
			var delay = parseInt($('#ninja_page_refresh_value').val(), 10);
			$.jGrowl(sprintf(_page_refresh_msg, delay), { header: _success_header });
			ninja_refresh(delay);
		});
	}
	$('#ninja_refresh_edit').bind('click', function() {
		if (!edit_visible) {
			$('#ninja_page_refresh_slider').show();
			edit_visible = 1;
		} else {
			$('#ninja_page_refresh_slider').hide();
			edit_visible = 0;
		}
	});
	// -- end refresh helper code

	// listview refresh helper code
	$("#listview_refresh_control").bind('change', function() {
		if ($("#listview_refresh_control").attr('checked')) {
			// save previous refresh rate
			// to be able to restore it later
			$('#listview_refresh_lable').css('font-weight', 'bold');
			clearTimeout(lsfilter_storage.list.autorefresh_timer);
			$.jGrowl(_listview_refresh_paused_msg, { header: _success_header });
		} else {
			// restore previous refresh rate
			$('#listview_refresh_lable').css('font-weight', '');
			lsfilter_storage.list.start_autorefresh_timer();
			$.jGrowl(_listview_refresh_unpaused_msg, { header: _success_header });
		}
	});
	$("#listview_refresh_value").bind('change', function() {
		$("#listview_refresh_slider").slider("value", this.value);
	});
	if ($('#listview_refresh_edit').text()!=='') {
		create_slider('listview_refresh');
		$('#listview_refresh_slider').on('slidechange', function() {
			var delay = parseInt($('#listview_refresh_value').val(), 10);
			$.jGrowl(sprintf(_listview_refresh_msg, delay), { header: _success_header });
			clearTimeout(lsfilter_storage.list.autorefresh_timer);
			if (delay > 0) {
				lsfilter_storage.list.config.autorefresh_enabled = true;
			} else {
				lsfilter_storage.list.config.autorefresh_enabled = false;
			}
			lsfilter_storage.list.config.autorefresh_delay = delay * 1000;
			lsfilter_storage.list.start_autorefresh_timer();
		});
	}
	// -- end listview refresh helper code

	$('.host_comment').each(function() {
		var anchor = $(this);
		var obj_name = anchor.data('obj_name');
		if (!obj_name) {
			return false;
		}


		// Remove the tooltip of the inner element since it overlays qtip
		anchor.find('span').attr('title', '');

		anchor.qtip($.extend(true, {}, qtip_default, {
			content: {
				text: function(ev, api) {
					$.ajax({
						url: _site_domain + _index_page + "/ajax/fetch_comments/",
						data: {host: obj_name}
					})
					.done(function(html) {
						api.set('content.text', html);
					})
					.fail(function(xhr, status, error) {
						api.set('content.text', status + ': ' + error);
					});

					return '<img src="' + _site_domain + loading_img + '" alt="' + _loading_str + '" />';
				}
			}
		}));
	});

	$(".helptext_target").each(function(){

		// split the id into controller, key
		var the_id = $(this).attr('id');
		var part = the_id.split('|');
		if (!part.length) {
			return false;
		}
		var controller = part[1];
		var key = part[2];
		var elem_id = the_id;

		$(this).qtip($.extend(true, {}, qtip_default, {
			content: {
				text: function(ev, api) {
					$.ajax({
						url: _site_domain + _index_page + "/ajax/get_translation/",
						data: {controller: controller, key: key},
						type: 'POST',
					})
					.done(function(html) {
						api.set('content.text', html);
					})
					.fail(function(xhr, status, error) {
						api.set('content.text', status + ': ' + error);
					});

					return '<img src="' + _site_domain + loading_img + '" alt="' + _loading_str + '" />';
				}
			}
		}));
	});
	$(".helptext_target").click(function() {return false;});

	$('#multi_action_select').bind('change', function() {
		multi_action_select($(this).find('option:selected').val());
	});

	$('.select_all_items_service').live('click', function() {
		if ($(this).attr('checked')) {
			$(this).parents('table').find(".item_select_service input[type='checkbox']").not('.select_all_items_service').each(function() {
				if (!$(this).attr('disabled') && !$(this).is(':hidden')) {
					$(this).attr('checked', true);
				}
				else if ($(this).is(':hidden')) {
					$(this).attr('checked', false);
				}
			});
		} else {
			$(this).parents('table').find(".item_select_service input[type='checkbox']").not('.select_all_items_service').each(function() {
				$(this).attr('checked', false);
			});
		}
	});
	// Toggle visibility for quick menu items
	$("#page_settings_icon, #global_notifications_icon").click(function() {
		var menu_item = $(this);
		var li = menu_item.parents('li');
		var submenu = $('#'+menu_item[0].id.replace(/_icon$/, ''));
		if (submenu.is(':hidden')) {
			li.addClass("selected");
			submenu
				.show()
				.css('top', '49px')
				.css('left', (menu_item.offset().left - 10) + 'px');

		} else {
			li.removeClass("selected");
			submenu.hide();
		}
		return false;
	});

	// are we using keyboard commands or not
	if (_keycommands_active) {
		if (typeof _keycommand_forward !== 'undefined' && _keycommand_forward !== '') {
			jQuery(document).bind('keydown', _keycommand_forward, function (evt){
				if (typeof $('.nextpage').attr('href') != 'undefined') {
					// reset scroll memory to start at top for next page
					_save_scroll = false;
					self.location.href=$('.nextpage').attr('href');
				}
				return false;
			});
		}

		if (typeof _keycommand_back !== 'undefined' && _keycommand_back !== '') {
			jQuery(document).bind('keydown', _keycommand_back, function (evt){
				if (typeof $('.prevpage').attr('href') != 'undefined') {
					// reset scroll memory to start at top for previous page
					_save_scroll = false;
					self.location.href=$('.prevpage').attr('href');
				}
				return false;
			});
		}

		if (typeof _keycommand_search !== 'undefined' && _keycommand_search !== '') {
			jQuery(document).bind('keydown', _keycommand_search, function (evt){$('#query').focus(); return false; });
		}

		if (typeof _keycommand_pause !== 'undefined' && _keycommand_pause !== '') {
			jQuery(document).bind('keydown', _keycommand_pause, function (evt){
				toggle_refresh();
				return false;
			});
		}
	}

	/**
	*	Toggle page refresh and show a jGrowl message to user about state
	*/
	function toggle_refresh()
	{
		if ($("#ninja_refresh_control").attr('checked')) {
			// restore previous refresh rate
			ninja_refresh(old_refresh);
			refresh_is_paused = false;
			$('#ninja_refresh_lable').css('font-weight', '');
			$("#ninja_refresh_control").attr('checked', false);

			// inform user
			$.jGrowl(_refresh_unpaused_msg, { header: _success_header });
		} else {
			// Prevent message from showing up when no pause is available
			if ($("#ninja_page_refresh").html() === null) {
				return false;
			}

			$("#ninja_refresh_control").attr('checked', true);
			// save previous refresh rate
			// to be able to restore it later
			old_refresh = current_interval;
			$('#ninja_refresh_lable').css('font-weight', 'bold');
			ninja_refresh(0);
			refresh_is_paused = true;

			// inform user
			$.jGrowl(_refresh_paused_msg, { header: _success_header });
		}
	}

	$('#multi_object_submit_service').click(function() {
		// check that we have any selected items
		if (!$('.item_select_service input[name=object_select\\[\\]]').is(':checked')) {
			show_message("multi_object_submit_progress_service", _nothing_selected_error);
			return false;
		}

		// Check if we actually set an action
		if ($('#multi_action_select_service').val() === "") {
			show_message("multi_object_submit_progress_service", _no_action_error);
			return false;
		}

		show_progress("multi_object_submit_progress_service", _wait_str);
	});

	$('#multi_object_submit').click(function() {
		// check that we have any selected items
		if (!$('.item_select input[name=object_select\\[\\]]').is(':checked')) {
			show_message("multi_object_submit_progress", _nothing_selected_error);
			return false;
		}

		// Check if we actually set an action
		if ($('#multi_action_select').val() === "") {
			show_message("multi_object_submit_progress", _no_action_error);
			return false;
		}

		show_progress("multi_object_submit_progress", _wait_str);
	});

});

function _(text)
{
	// console.log('To translate: '+ text);
	return text;
}

var loadimg_sml = new Image(16,16);
loadimg_sml.src = _site_domain + 'application/media/images/loading_small.gif';

/**
*	cache the progress indicator image to show faster...
*/
var Image1 = new Image(16,16);
Image1.src = _site_domain + 'application/media/images/loading.gif';

/**
*	Show a progress indicator to inform user that something
*	is happening...
*/
function show_progress(the_id, info_str, size_str) {
	switch (size_str) {
		case "small": case "tiny":
			size_str = loadimg_sml.src;
			break;
		case "large": case "big":
			size_str = Image1.src;
			break;
		default:
			size_str = loadimg_sml.src;
			break;
	}
	$("#" + the_id).html('<img id="progress_image_id" src="' + size_str + '"> <em>' + info_str +'</em>').show();
}

function show_message(the_id, info_str) {
	$("#" + the_id).html('<em>' + info_str +'</em>').show();
}

function switch_image(html_id, src)
{
	$('#' + html_id).attr('src', src);
}

function object_action(action,the_id)
{
	var parts = the_id.split('|');
	var type = false;
	var name = false;
	var service = false;
	switch(parts.length) {
		case 0: case 1:
			return false;
		case 2: // host or groups
			name = parts[1];
			break;
		case 3: // service
			name = parts[1];
			service = parts[2];
			break;
		case 4: // service
			name = parts[1];
			service = parts[3];
			break;
	}

	type = parts[0];

	var cmd = false;
	switch(action) {
		case 'schedule_host_downtime':
		case 'schedule_svc_downtime':
		case 'del_host_downtime':
		case 'del_svc_downtime':
		case 'acknowledge_host_problem':
		case 'acknowledge_svc_problem':
		case 'disable_host_svc_notifications':
		case 'disable_host_check':
		case 'disable_svc_check':
		case 'enable_host_check':
		case 'enable_svc_check':
		case 'schedule_host_check':
		case 'schedule_host_svc_checks':
		case 'schedule_svc_check':
		case 'add_host_comment':
		case 'add_svc_comment':
			cmd = action.toUpperCase();
			break;
		case 'remove_acknowledgement':
			cmd = type == 'host' ? 'REMOVE_HOST_ACKNOWLEDGEMENT' : 'REMOVE_SVC_ACKNOWLEDGEMENT';
			break;
		case 'disable_notifications':
			cmd = type == 'host' ? 'DISABLE_HOST_NOTIFICATIONS' : 'DISABLE_SVC_NOTIFICATIONS';
			break;
		case 'enable_notifications':
			cmd = type == 'host' ? 'ENABLE_HOST_NOTIFICATIONS' : 'ENABLE_SVC_NOTIFICATIONS';
			break;
	}

	// return if we couldn't figure out what command to run
	if (cmd === false) {
		return false;
	}

	var target = _site_domain + _index_page + '/command/submit?cmd_typ=' + cmd + '&host_name=' + name;
	if (service !== false) {
		target += '&service=' + service;
	}
	self.location.href = target;
}

/**
*	Handle multi select of different actions
*/
function multi_action_select(action, type)
{
	// start by enabling all checkboxes in case
	// they have been previously disabled
	var field = 'item_select';
	var prop_field = 'obj_prop';
	if (type == 'service') {
		$(".item_select_service input[type='checkbox']").attr('disabled', false);
		field = 'item_select_service';
		prop_field = 'obj_prop_service';
	} else {
		$(".item_select input[type='checkbox']").attr('disabled', false);
	}

	if (action === '')
		return false;

	var ACKNOWLEDGED = 1;
	var NOTIFICATIONS_ENABLED = 2;
	var CHECKS_ENABLED = 4;
	var SCHEDULED_DT = 8;

	$('.' + prop_field).each(function() {
		var that = $(this);
		var test = false;
		switch (action) {
			case 'ACKNOWLEDGE_HOST_PROBLEM':
			case 'ACKNOWLEDGE_SVC_PROBLEM':
				test = that.text() & ACKNOWLEDGED || !(that.text() & 16);
				break;
			case 'REMOVE_HOST_ACKNOWLEDGEMENT':
			case 'REMOVE_SVC_ACKNOWLEDGEMENT':
				test = !(that.text() & ACKNOWLEDGED);
				break;
			case 'DISABLE_HOST_NOTIFICATIONS':
			case 'DISABLE_SVC_NOTIFICATIONS':
				test = that.text() & NOTIFICATIONS_ENABLED;
				break;
			case 'ENABLE_HOST_NOTIFICATIONS':
			case 'ENABLE_SVC_NOTIFICATIONS':
				test = !(that.text() & NOTIFICATIONS_ENABLED);
				break;
			case 'ENABLE_HOST_CHECK':
			case 'ENABLE_SVC_CHECK':
				test = !(that.text() & CHECKS_ENABLED);
				break;
			case 'DISABLE_HOST_CHECK':
			case 'DISABLE_SVC_CHECK':
				test = that.text() & CHECKS_ENABLED;
				break;
			case 'DEL_HOST_DOWNTIME':
			case 'DEL_SVC_DOWNTIME':
				test = !(that.text() & SCHEDULED_DT);
				break;
		}
		if (test) {
			that.closest('tr').find("." + field + " input[type='checkbox']").attr('disabled', true).attr('checked', false);
		}
	});
}

function create_slider(the_id)
{
	var last_update_request = false;
	var id = $('#' + the_id + '_value');
	var key = id.data('key');
	var interval = id.val();
	$("#" + the_id + "_slider").slider({
		value: interval,
		min: 0,
		max: 500,
		step: 10,
		slide: function(event, ui) {
			$("#" + the_id + "_value").val(ui.value);
		},
		stop: function(event, ui) {
			interval = ui.value;
			if(last_update_request !== false) {
				last_update_request.abort();
			}
			last_update_request = $.ajax(
				_site_domain + _index_page + "/ajax/save_page_setting/",
				{
					data: {
						page: '*',
						setting: interval,
						type: key
					},
					complete: function() {
						last_update_request = false;
						id.val(interval);
					},
					type: 'POST'
				}
			);
		}
	});
	id.val($("#" + the_id + "_slider").slider("value"));
}

function ninja_refresh(val)
{
	if (_interval) {
		clearInterval(_interval);
	}
	var refresh_val = (val === null) ? _refresh : val;
	current_interval = refresh_val;
	if (val>0) {
		_interval = setInterval( "refresh()", refresh_val*1000 );
	}
}

function jgrowl_message(message_str, header_str)
{
	if (message_str!=='') {
		$.jGrowl(message_str, { header: header_str });
	}
}


// ===========================================================
// code for remembering scroll position between page reloads
// adapted from http://www.huntingground.freeserve.co.uk/main/mainfram.htm?../scripts/cookies/scrollpos.htm
// ===========================================================

cookieName = "page_scroll";
expdays = 5;

function setCookie(name, value, expires, path, domain, secure) {
	if (!expires) {
		expires = new Date();
	}
	document.cookie = name + "=" + escape(value) +
	((expires === null) ? "" : "; expires=" + expires.toGMTString()) +
	((path === null) ? "" : "; path=" + path) +
	((domain === null) ? "" : "; domain=" + domain) +
	((secure === null) ? "" : "; secure");
}

function getCookie(name) {
	var arg = name + "=";
	var alen = arg.length;
	var clen = document.cookie.length;
	var i = 0;
	while (i < clen) {
		var j = i + alen;
		if (document.cookie.substring(i, j) == arg){
			return getCookieVal(j);
		}
		i = document.cookie.indexOf(" ", i) + 1;
		if (i === 0) {
			break;
		}
	}
	return null;
}

function getCookieVal(offset) {
	var endstr = document.cookie.indexOf (";", offset);
	if (endstr == -1) {
		endstr = document.cookie.length;
	}
	return unescape(document.cookie.substring(offset, endstr));
}

function deleteCookie(name,path,domain) {
	document.cookie = name + "=" + ((path === null) ? "" : "; path=" + path) + ((domain === null) ? "" : "; domain=" + domain) + "; expires=Thu, 01-Jan-00 00:00:01 GMT";
}

function saveScroll() {
	var expdate = new Date();
	expdate.setTime (expdate.getTime() + (expdays*24*60*60*1000)); // expiry date

	if (!_save_scroll) {
		// reset scroll memory to top
		setCookie(cookieName,'0_0',expdate);
		return;
	}

	var x = $(window).scrollLeft();
	var y = $(window).scrollTop();
	Data = x + "_" + y;

	setCookie(cookieName,Data,expdate);
}

$(window).bind('beforeunload', saveScroll);

function loadScroll() { // added function
	inf = getCookie(cookieName);
	if(!inf) {
		return;
	}
	var ar = inf.split("_");
	if (ar.length == 2) {
		$(window).scrollLeft(parseInt(ar[0], 10));
		$(window).scrollTop(parseInt(ar[1], 10));
	}
}

$(window).bind('load', loadScroll);

function trigger_cb_on_nth_call(cb, n) {
	return function() {
		if (--n <= 0)
			cb();
	};
}

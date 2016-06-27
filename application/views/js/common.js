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

	var object_action = function(cmd, table, obj) {
		var en = encodeURIComponent;
		var target = _site_domain + _index_page + '/cmd?command=' + en(cmd) + '&table=' + en(table) + '&object=' + en(obj);
		self.location.href = target;
	};

	if ($.fn.contextMenu) {
		$("body").contextMenu(
			{menu: 'property_menu'},
			object_action,
			".obj_properties:not(.white)"
		);
		$("body").contextMenu(
			{menu: 'svc_property_menu'},
			object_action,
			".svc_obj_properties"
		);
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
			Notify.message(_page_refresh_msg.replace('{delay}', delay), {type: "success"});
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
			Notify.message(_listview_refresh_paused_msg, {type: "success"});
		} else {
			// restore previous refresh rate
			$('#listview_refresh_lable').css('font-weight', '');
			lsfilter_storage.list.start_autorefresh_timer();
			Notify.message(_listview_refresh_unpaused_msg, {type: "success"});
		}
	});
	$("#listview_refresh_value").bind('change', function() {
		$("#listview_refresh_slider").slider("value", this.value);
	});
	if ($('#listview_refresh_edit').text()!=='') {
		create_slider('listview_refresh');
		$('#listview_refresh_slider').on('slidechange', function() {
			var delay = parseInt($('#listview_refresh_value').val(), 10);
			Notify.message(_listview_refresh_msg.replace('{delay}', delay), {type: "success"});
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
	*	Toggle page refresh and show a notify message to user about state
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
			Notify.message(_refresh_unpaused_msg, {type: "success"});
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
			Notify.message(_refresh_paused_msg, {type: "success" });
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
						type: key,
						csrf_token: _csrf_token
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


(function (_site_domain, _index_page) {

	"use strict";

	/* QUICKLINK EXTENSION */
	var uh_prob_title = "Unhandled Problems";
	function query_uh_objects(link) {

		var basepath = _site_domain + _index_page,
			query = link.attr('href'),
			shield_class = 'icon-16 x16-shield-ok',
			uh_prob_title = '',
			totals = 0;

		query = query.split('q=')[1];
		var obj_type = link.attr('id').split('_')[1];

		$.ajax({
			url : basepath + "/listview/fetch_ajax",
			dataType : 'json',
			data : {
				"query" : query,
				"limit" : 100,
				"columns": ['description']
			},
			success : function(data) {

				if (!data) {
					return;
				}

				if (obj_type === 'host') {
					totals = data.totals.host_all[1];
					uh_prob_title = totals + ' unacknowledged host(s) in Down state!';
					if (data.totals.host_state_down[1] > 0) {
						shield_class = 'icon-16 x16-shield-critical';
					} else if (data.totals.host_state_unreachable[1] > 0) {
						shield_class = 'icon-16 x16-shield-unknown';
					}
				} else if (obj_type === 'service') {
					totals = data.totals.service_all[1];
					uh_prob_title = totals + ' unacknowledged service(s) in Critical/Warning state!';
					if (data.totals.service_state_critical[1] > 0) {
						shield_class = 'icon-16 x16-shield-critical';
					} else if (data.totals.service_state_warning[1] > 0) {
						shield_class = 'icon-16 x16-shield-warning';
					} else if (data.totals.service_state_unknown[1] > 0) {
						shield_class = 'icon-16 x16-shield-unknown';
					}
				}

				var uh_prob_state_element = link.find(':nth-child(2)');

				if(totals < 100) {
					// Only set text if there are less than 100 to prevent overflow
					uh_prob_state_element.text(totals);
				}

				link.attr('title', uh_prob_title);
				link.find(':first-child').removeClass().addClass(shield_class);
			}
		});
	}

	function query_for_states() {
		var links = $('#uh_host_problems,#uh_service_problems').each(function () {
			query_uh_objects($(this));
		});
	}

	setInterval(query_for_states, 10000);
	$(window).on('load', function () {
		query_for_states();
	});

	var global_quicklinks = [];

	function quicklinks_save_all () {
		$.ajax(_site_domain + _index_page + '/ajax/save_page_setting', {
			data: {
				'type': 'dojo-quicklinks',
				'page': 'tac',
				'setting': JSON.stringify(global_quicklinks),
				'csrf_token': _csrf_token
			},
			type: 'POST',
			complete: function() {
				$('#dojo-add-quicklink-href').attr('value','');
				$('#dojo-add-quicklink-title').attr('value','');
				$('#dojo-add-quicklink-icon').attr('value','');
			}
		});
	}

	$(window).on('load', function () {
		$('#dojo-icon-container').on('click', 'span', function() {
			var span = $(this);
			$('#dojo-add-quicklink-icon').val(span.data('icon'));

			// we have to change the background of the td, since the span already
			// has the icon image as its background
			var all_tds = $('#dojo-icon-container td');
			all_tds.removeClass('highlight');
			span.parents('td').addClass('highlight');
		});

		$('#dojo-add-quicklink').fancybox({
			titleShow: false,
			overlayOpacity: 0,
			onComplete: function() {
				$('#dojo-quicklink-remove').html('');
				for (var i = 0; i < global_quicklinks.length; i += 1) {
					var l = global_quicklinks[i];
					var vid = l.title + ':'+ l.href;
					var quicklink = $('<li><label></label> (<a target="_blank" class="external"></a>)</li>');
					quicklink
					.find('label')
					.text(l.title)
					.prepend($('<span class="icon-16"></span>').addClass('x16-'+l.icon))
					.prepend($('<input type="checkbox" />')
									 .attr('value', vid)
									 .attr('id', vid)
									 .attr('title', l.title)
									);
									quicklink
									.find('a')
									.attr('href', l.href)
									.text(l.href);
									$('#dojo-quicklink-remove').append(quicklink);
				}
			},
			onClose: function() {
				$('#dojo-add-quicklink-href').attr('value','');
				$('#dojo-add-quicklink-title').attr('value','');
				$('#dojo-add-quicklink-icon').attr('value','');
			}
		});

		$('#dojo-add-quicklink-menu form').submit(function (ev) {
			ev.preventDefault();
			var href = $('#dojo-add-quicklink-href').attr('value'),
				title = $('#dojo-add-quicklink-title').attr('value'),
				icon = $('#dojo-add-quicklink-icon').attr('value'),
				target = $('#dojo-add-quicklink-target').attr('value'),
				changed = false;
			var error = '';
			if (href && title && icon) {
				var i = global_quicklinks.length;
				for (i; i--;) {
					if (global_quicklinks[i].href === href) {
						error += 'This href is already used in a quicklink. <br />';
					}
					if (global_quicklinks[i].title === title) {
						error += 'This title is already in use, titles must be unique. <br />';
					}
				}
				if (error.length === 0) {
					global_quicklinks.push({'href': href,'title': title,'icon': icon,'target': target});
					var quicklink = $('<li><a class="image-link"><span class="icon-16 x16-' + icon + '"></span></a></li>');
					quicklink
					.find('a')
					.attr('target', target)
					.attr('href', href)
					.attr('title', title);
					$('#dojo-add-quicklink').parent().before(quicklink);
					changed = true;
				} else {
					Notify.message(error, {type: "error"});
					return;
				}
			}
			$('#dojo-quicklink-remove input[type="checkbox"]').each(function () {
				var i = global_quicklinks.length;
				var vid = '';
				if (this.checked) {
					for (i; i--;) {
						vid = global_quicklinks[i].title + ':' + global_quicklinks[i].href;
						if (this.value === vid) {
							$('#quicklinks li a[title="'+this.title+'"]').parent().remove();
							global_quicklinks.splice(i, 1);
							changed = true;
						}
					}
				}

			});
			if (changed)  {
				quicklinks_save_all();
			}
			if(!error) {
				$.fancybox.close();
			}
		});
	});

	$.ajax(_site_domain + _index_page + '/ajax/get_setting', {
			data: {
				'type': 'dojo-quicklinks',
				'page': 'tac',
				'csrf_token': _csrf_token
			},
			type: 'POST',
			success: function (obj) {

				var links = [];

				if (obj['dojo-quicklinks']) {
					links = obj['dojo-quicklinks'];
					for (var i = 0; i < links.length; i += 1) {
						var quicklink = $('<li><a class="image-link"><span class="icon-16 x16-'+links[i].icon+'"></span></a></li>');
						quicklink
							.find('a')
								.attr('target', links[i].target)
								.attr('href', links[i].href)
								.attr('title', links[i].title);

						$('#dojo-add-quicklink').parent().before(quicklink);
					}
				}
				global_quicklinks = links;
			}
		});

}(window._site_domain, window._index_page));


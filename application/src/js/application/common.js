var loadimg_sml = new Image(16, 16);
loadimg_sml.src = _site_domain + 'application/media/images/loading_small.gif';

function _(text)
{
	// console.log('To translate: '+ text);
	return text;
}

var ninja_refresh = (function () {
	var _interval = 0;
	return function ninja_refresh(val) {
		if (_interval) {
			clearInterval(_interval);
		}
		var refresh_val = (val === null) ? _refresh : val;
		current_interval = refresh_val;
		if (val>0) {
			_interval = setInterval( "refresh()", refresh_val*1000 );
		}
	}
}());

/**
 * Cache the progress indicator image to show faster
 */
var Image1 = new Image(16,16);
Image1.src = _site_domain + 'application/media/images/loading.gif';

/**
 * Show a progress indicator to inform user that something is happening
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

var current_interval = 0;
$(document).ready(function() {

	"use strict";
	var _save_page_interval = 0;
	var _save_scroll = true;

	var edit_visible = 0;

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

	$(document).on('click', '.select_all_items_service', function() {
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
	 * Toggle page refresh and show a notify message to user about state
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

	function create_slider(the_id) {
		var last_update_request = false;
		var id = $('#' + the_id + '_value');
		var key = id.data('key');
		var interval = _lv_refresh_delay;
		id.val(interval);
		$("#" + the_id + "_slider").slider({
			value: interval,
			min: 0,
			max: 500,
			step: 10,
			slide: function(event, ui) {
				$("#" + the_id + "_value").val(ui.value);
			},
			change: function(event, ui) {
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
	}

	function trigger_cb_on_nth_call(cb, n) {
		return function() {
			if (--n <= 0)
				cb();
		};
	}

});

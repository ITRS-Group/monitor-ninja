var sURL = unescape(window.location.pathname + location.search);
var _interval = 0;
var _save_page_interval = 0;
var current_interval = 0;
var edit_visible = 0;

$(document).ready(function() {
	collapse_menu(_ninja_menu_state);
	/**
	*	Show the checkbox to show/hide "page header" if
	*	we find the content-header div in the current page
	*/
	if ($('#content-header').text()!='') {
		$('#noheader_ctrl').show();
		$('#settings_icon').show();
		$('#menu_global_settings').show();

		// Let checkbox state reflect visibility of the #content-header div
		if ($('#content-header').is(':visible')) {
			// force unchecked checkbox
			$('#noheader_chbx').attr('checked', false);
		} else {
			// mark current state by checking the checkbox
			$('#noheader_chbx').attr('checked', true);
		}
	}

	/**
	*	Bind some functionality to the checkbox state change event
	*	This involves setting the correct value for the noheader GET parameter
	*	and passing the new value to the refresh script so that the value
	*	will persist between refreshes.
	*/
	$('#noheader_chbx').bind('change', function() {
		var noheader = $.query.get('noheader');
		if ($(this).attr('checked')) {
			$('#content-header').hide();
			var new_url = $.query.set('noheader', 1);
		} else {
			$('#content-header').show();
			var new_url = $.query.set('noheader', 0);
		}
		sURL = new_url.toString();
	});

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
	if ($('#ninja_refresh_edit').text()!='') {
		create_slider('ninja_page_refresh');
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

	// ==========================
	// check menu section status
	// ==========================
	// find all menu sections identified by
	// the text in cite tags
	$('cite.menusection').each(function() {
		var section = $(this).text();
		var section_state = window['_ninja_menusection_'+ section];
		if (section_state.length) {
			// hide the sections set to 'hide'
			if (section_state=='hide') {
				// using collapse_section() from
				// collapse_menu.js
				collapse_section(section);
			}
		}
	});

	// menu scroll/slider init
	$("#menu-slider").slider({
	orientation: 'vertical',
		animate: true,
	change: handleSliderChange,
	slide: handleSliderSlide,
	min: -100,
	max: 0,
	value: -2.7
	});

	// check if show or hide the scroll/slider
	scroll_control();

	jQuery('#service_table').floatHeader({
		fadeIn: 25,
		fadeOut: 25
	});
	jQuery('#host_table').floatHeader({
		fadeIn: 25,
		fadeOut: 25
	});
	jQuery('.group_grid_table').floatHeader({
		fadeIn: 25,
		fadeOut: 25
	});
	jQuery('#group_summary_table').floatHeader({
		fadeIn: 25,
		fadeOut: 25
	});
	jQuery('.group_overview_table').floatHeader({
		fadeIn: 25,
		fadeOut: 25
	});

	$('.pnp_graph_icon').each(function() {
		//$(this).mouseover(function() {
			var pnp_link = $(this).parent().attr('href');
			if (!pnp_link) {
				pnp_link = $(this).attr('src');
			}
			var link_parts = pnp_link.split('?');
			if (!link_parts.length) {
				return false;
			}
			// ex: host=myhost&srv=PING
			pnp_link = link_parts[1];

			var loading_img = '/application/media/images/loading.gif';

			$(this).qtip({
			content: {
				url: _site_domain + _index_page + "/ajax/pnp_image/",
				data: {param: pnp_link},
				method: 'post',
				text: '<img src="' + _site_domain + loading_img + '" alt="' + _loading_str + '" />'
			},
			position: {
				corner: {
				target: 'bottomMiddle', // Position the tooltip above the link
				tooltip: 'topLeft'
			},
				adjust: {
					screen: true // Keep the tooltip on-screen at all times
				}
			},
			show: {
				when: 'mouseover',
				solo:true
			},
			hide: {
				effect: 'slide',
				when: {
					event: 'unfocus',
					delay:2000
				}
			},
			style: {
				width: 620,
				tip: true, // Apply a speech bubble tip to the tooltip at the designated tooltip corner
					border: {
					width: 0,
					radius: 4
				},
				name: 'light' // Use the default light style
			}
		});
		//});
	});

	$('.host_comment').each(function() {
			var obj_name = $(this).attr('href');
			var link_parts = obj_name.split('/');
			if (!link_parts.length) {
				return false;
			}

			obj_name = link_parts[link_parts.length-1];
			obj_name = obj_name.replace('#comments', '');

			var loading_img = '/application/media/images/loading.gif';

			$(this).qtip({
			content: {
				url: _site_domain + _index_page + "/ajax/fetch_comments/",
				data: {host: obj_name},
				method: 'post',
				text: '<img src="' + _site_domain + loading_img + '" alt="' + _loading_str + '" />'
			},
			position: {
				corner: {
				target: 'topMiddle', // Position the tooltip
				tooltip: 'bottomLeft'
			},
			adjust: {
				screen: true // Keep the tooltip on-screen at all times
			}
			},
			show: {
				when: 'mouseover',
				solo:true
			},
			hide: {
				effect: 'slide',
				when: {
					event: 'unfocus',
					delay:2000
				}
			},
			style: {
				width: 500,
				tip: true, // Apply a speech bubble tip to the tooltip at the designated tooltip corner
					border: {
					width: 0,
					radius: 4
				},
				name: 'light' // Use the default light style
			}
		});
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

		var loading_img = '/application/media/images/loading.gif';
		$(this).qtip({
			content: {
				url: _site_domain + _index_page + "/ajax/get_translation/",
				data: {controller: controller, key: key},
				method: 'post',
				text: '<img src="' + _site_domain + loading_img + '" alt="' + _loading_str + '" />'
			},
			position: {
				corner: {
				target: 'bottomMiddle', // Position the tooltip above the link
				tooltip: 'topLeft'
			},
				adjust: {
					screen: true // Keep the tooltip on-screen at all times
				}
			},
			show: {
				when: 'click',
				solo:true
			},
			hide: {
				effect: 'slide',
				when: {
					event: 'unfocus',
					delay:2000
				}
			},
			style: {
				tip: true, // Apply a speech bubble tip to the tooltip at the designated tooltip corner
					border: {
					width: 0,
					radius: 4
				},
				name: 'light' // Use the default light style
			}
		});
	});
	$(".helptext_target").click(function() {return false;})

	$('#multi_action_select').bind('change', function() {
		multi_action_select($(this).find('option:selected').val());
	});
	$('#multi_action_select_service').bind('change', function() {
		multi_action_select($(this).find('option:selected').val(), 'service');
	});

	$('#select_multiple_items').click(function() {
		if (!refresh_is_paused) {
			if (!$('.item_select').is(':visible')) {
				// pausing and un-pausing refresh might be
				// irritating for users that already has selected
				// to pause refresh

				// save previous refresh rate
				// to be able to restore it later
				old_refresh = current_interval;
				$('#ninja_refresh_lable').css('font-weight', 'bold');
				ninja_refresh(0);
				$("#ninja_refresh_control").attr('checked', true);
			} else {
				// restore previous refresh rate
				ninja_refresh(old_refresh);
				$("#ninja_refresh_control").attr('checked', false);
				$('#ninja_refresh_lable').css('font-weight', '');
			}
		}

		if ($('.item_select').is(':hidden'))
			$(	'.item_select').show();
		else
			$(	'.item_select').hide();

		return false;
	});

	$('#select_multiple_service_items').click(function() {
		if (!refresh_is_paused) {
			if (!$('.item_select_service').is(':visible')) {
				// pausing and un-pausing refresh might be
				// irritating for users that already has selected
				// to pause refresh

				// save previous refresh rate
				// to be able to restore it later
				old_refresh = current_interval;
				$('#ninja_refresh_lable').css('font-weight', 'bold');
				ninja_refresh(0);
				$("#ninja_refresh_control").attr('checked', true);
			} else {
				// restore previous refresh rate
				ninja_refresh(old_refresh);
				$("#ninja_refresh_control").attr('checked', false);
				$('#ninja_refresh_lable').css('font-weight', '');
			}
		}

		if ($('.item_select_service').is(':hidden'))
			$(	'.item_select_service').show();
		else
			$(	'.item_select_service').hide();
		return false;
	});

	$('.select_all_items').live('click', function() {
		if ($(this).attr('checked')) {
			$('.select_all_items').attr('checked', true);
			$(".item_select input[type='checkbox']").not('.select_all_items').each(function() {
				if (!$(this).attr('disabled')) {
					$(this).attr('checked', true);
				}
			});
		} else {
			$('.select_all_items').attr('checked', false);
			$(".item_select input[type='checkbox']").not('.select_all_items').each(function() {
				$(this).attr('checked', false);
			});
		}
	});
	$('.select_all_items_service').live('click', function() {
		if ($(this).attr('checked')) {
			$('.select_all_items_service').attr('checked', true);
			$(".item_select_service input[type='checkbox']").not('.select_all_items_service').each(function() {
				if (!$(this).attr('disabled')) {
					$(this).attr('checked', true);
				}
			});
		} else {
			$('.select_all_items_service').attr('checked', false);
			$(".item_select_service input[type='checkbox']").not('.select_all_items_service').each(function() {
				$(this).attr('checked', false);
			});
		}
	});
	// Handle show/hide of settings layer
	$("#settings_icon").click(function() {
		if ($("#page_settings").is(':hidden'))
			$("#page_settings").show();
		else{
			$("#page_settings").hide();
		}
		return false;
	});

	$('#page_settings').click(function(e) {
		e.stopPropagation();
	});

	$(document).click(function() {
		$('#page_settings').hide();
	});

	// are we using keyboard commands or not
	if (_keycommands_active) {
		if (typeof _keycommand_forward != 'undefined' && _keycommand_forward != '') {
			jQuery(document).bind('keydown', _keycommand_forward, function (evt){
				if (typeof $('.nextpage').attr('href') != 'undefined') {
					self.location.href=$('.nextpage').attr('href');
				}
				return false;
			});
		}

		if (typeof _keycommand_back != 'undefined' && _keycommand_back != '') {
			jQuery(document).bind('keydown', _keycommand_back, function (evt){
				if (typeof $('.prevpage').attr('href') != 'undefined') {
					self.location.href=$('.prevpage').attr('href');
				}
				return false;
			});
		}

		if (typeof _keycommand_search != 'undefined' && _keycommand_search != '') {
			jQuery(document).bind('keydown', _keycommand_search, function (evt){$('#query').focus(); return false; });
		}

		if (typeof _keycommand_pause != 'undefined' && _keycommand_pause != '') {
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
			if ($("#ninja_page_refresh").html() == null) {
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

});


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

	if (action == '')
		return false;

	var ACKNOWLEDGED = 1;
	var NOTIFICATIONS_ENABLED = 2;
	var CHECKS_ENABLED = 4;
	var SCHEDULED_DT = 8;

	switch (action) {
		case 'ACKNOWLEDGE_HOST_PROBLEM':
		case 'ACKNOWLEDGE_SVC_PROBLEM':
			$('.' + prop_field).each(function() {
				if ($(this).text() & ACKNOWLEDGED || !($(this).text() & 16)) {
					$(this).closest('tr').find("." + field + " input[type='checkbox']").attr('disabled', true);
				}
			});

			break;
		case 'REMOVE_HOST_ACKNOWLEDGEMENT':
		case 'REMOVE_SVC_ACKNOWLEDGEMENT':
			$('.' + prop_field).each(function() {
				if ( !($(this).text() & ACKNOWLEDGED) ) {
					$(this).closest('tr').find("." + field + " input[type='checkbox']").attr('disabled', true);
				}
			});

			break;
		case 'DISABLE_HOST_NOTIFICATIONS':
		case 'DISABLE_SVC_NOTIFICATIONS':
			$('.' + prop_field).each(function() {
				if ($(this).text() & NOTIFICATIONS_ENABLED) {
					$(this).closest('tr').find("." + field + " input[type='checkbox']").attr('disabled', true);
				}
			});

			break;
		case 'ENABLE_HOST_NOTIFICATIONS':
		case 'ENABLE_SVC_NOTIFICATIONS':
			$('.' + prop_field).each(function() {
				if ( !($(this).text() & NOTIFICATIONS_ENABLED) ) {
					$(this).closest('tr').find("." + field + " input[type='checkbox']").attr('disabled', true);
				}
			});
			break;
		case 'ENABLE_HOST_CHECK':
		case 'ENABLE_SVC_CHECK':
			$('.' + prop_field).each(function() {
				if ( !($(this).text() & CHECKS_ENABLED)) {
					$(this).closest('tr').find("." + field + " input[type='checkbox']").attr('disabled', true);
				}
			});
			break;
		case 'DISABLE_HOST_CHECK':
		case 'DISABLE_SVC_CHECK':
			$('.' + prop_field).each(function() {
				if ($(this).text() & CHECKS_ENABLED) {
					$(this).closest('tr').find("." + field + " input[type='checkbox']").attr('disabled', true);
				}
			});
			break;
	}
}

function create_slider(the_id)
{
	$("#" + the_id + "_slider").slider({
		value: current_interval,
		min: 0,
		max: 500,
		step: 10,
		slide: function(event, ui) {
			$("#" + the_id + "_value").val(ui.value);
			current_interval = ui.value;
			control_save_refreshInterval();
			ninja_refresh(ui.value);
		}
	});
	// set slider position according to current_interval
	$("#" + the_id + "_slider").slider("value", current_interval);
	$('input[name=' + the_id + '_value]').val(current_interval);

}

function control_save_refreshInterval() {
	if (_save_page_interval) {
		clearTimeout(_save_page_interval);
	}
	_save_page_interval = setTimeout("save_refreshInterval()", 5000);
}

function save_refreshInterval()
{
	var url = _site_domain + _index_page + "/ajax/save_page_setting/";
	var data = {page: '*', setting: current_interval, type: _refresh_key};
	$.post(url, data);
	$.jGrowl(sprintf(_page_refresh_msg, current_interval), { header: _success_header });
}

function ninja_refresh(val)
{
	if (_interval) {
		clearInterval(_interval);
	}
	var refresh_val = (val == null) ? _refresh : val;
	current_interval = refresh_val;
	if (val>0) {
		_interval = setInterval( "refresh()", refresh_val*1000 );
	}
}

$(window).resize(function() {
	scroll_control()
});

/**
*	Control if slider should be shown.
*	This function should be called from everywhere
*	we change the menu but with a delay of at least 100msec
*/
function scroll_control()
{
	if ($('#menu').width() < 51) {
		var menuwidth = (parseInt($('#menu ul').height()) > parseInt(document.documentElement.clientHeight-68)) ? 50 : 37;
		$('#menu').css('width', menuwidth+'px');
		$('#content').css('margin-left', (menuwidth+2)+'px');
	}
	$('#menu').css('height', parseInt(document.documentElement.clientHeight - 68)+'px');
}

function handleSliderChange(e, ui){
	var maxScroll = $("#menu-scroll").attr("scrollHeight") - $("#menu-scroll").height();
  $("#menu-scroll").animate({scrollTop: -ui.value * (maxScroll / 100) }, 1000);
}

function handleSliderSlide(e, ui){
	var maxScroll = $("#menu-scroll").attr("scrollHeight") - $("#menu-scroll").height();
	$("#menu-scroll").attr({scrollTop: -ui.value * (maxScroll / 100) });
}

function jgrowl_message(message_str, header_str)
{
	if (message_str!='') {
		$.jGrowl(message_str, { header: header_str });
	}
}

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

function append_quicklink_to_dom(icon, target, href, title) {
	var quicklink = $('<li><a class="image-link"><span class="icon-16 x16-'+icon+'"></span></a></li>');
	quicklink.find('a')
		.attr('target', target)
		.attr('href', href)
		.attr('title', title);

	$('#dojo-add-quicklink').parent().before(quicklink);
};

$(document).on("click", "#dojo-add-quicklink", function(ev) {
	ev.preventDefault();
	var link = $(this);
	LightboxManager.ajax_form_from_href(link.attr("title"), link.attr("href"));
});

$(document).on("submit", ".nj-form[action$='/quicklink/index']", function(ev) {
	var form = $(this);
	ev.preventDefault();
	$.post(form.attr("action"), form.serialize())
		.done(function(data) {
			// render the newly created quicklink in the menu bar
			// to avoid having to reload the page
			var icon = form.find("input[name=icon]");
			var target = form.find("input[name=target]");
			var href = form.find("input[name=href]");
			var title = form.find("input[name=title]");
			append_quicklink_to_dom(
				icon.val(),
				target.val(),
				href.val(),
				title.val()
			);

			// render the newly created quicklink in the lightbox's
			// form
			var ul = form.siblings("ul").first();

			// add rendering
			var li = $("<li>")
				.append($("<span>")
					.append($("<span>")
						.addClass("icon-16 x16-"+icon.val())
					)
					.append($("<a>")
						.attr({
							"target": "_blank",
							"href": href.val()
						})
						.text(title.val())
					)
					.append(" ("+href.val()+")")
				);

			// add remove button
			li.append($("<a>")
				.addClass("remove_quicklink no_uline")
				.attr({
					"href": _site_domain + _index_page + "/quicklink/delete_quicklink",
					"title": "Remove this quicklink"
				})
				.data({
					"title": title.val(),
					"href": href.val()
				})
				.append($("<span>")
					.addClass("icon-cancel error")
			       )
			);

			ul.append(li);

			// reset the form to prepare for another quicklink
			// insertion
			icon.val("");
			href.val("");
			title.val("");
			form.find(".nj-form-icon.active")
				.removeClass("active");

			LightboxManager.alert("Quicklink successfully saved");
			form.siblings(".quicklinks_placeholder").hide();
		})
		.fail(function(data) {
			var msg = JSON.parse(data.responseText).result;
			LightboxManager.alert(msg);
		});
});

$(document).on("click", ".remove_quicklink", function(ev) {
	ev.preventDefault();

	var a = $(this);
	var quicklink_title = a.data("title");
	var url = a.attr("href");
	var data = a.data();
	// since we are using POST, we must not forget to attach the currently
	// valid CSRF token
	data.csrf_token = _csrf_token;
	LightboxManager.confirm(
		"Are you sure you want to remove the quicklink '"+quicklink_title+"'?",
		{
			"yes": {
				"text": "Remove quicklink",
				"cb": function() {
					$.post(url, data)
						.done(function(data) {
							// Remove the quicklink
							// from the "Manage
							// quicklinks" Lightbox
							var ul = a.closest("ul");
							if(ul.find("li").length === 1) {
								// we're removing the last of the list items
								ul.parent().find(".quicklinks_placeholder").show();
							}
							a.closest("li").remove();

							// Remove the rendered
							// quicklink from the
							// main menu bar

							$("#quicklinks a")
								.filter(function() {
									// In order to compare hrefs, we cannot do hrefA == hrefB,
									// since the stored href is relative, but the one accessed
									// through the DOM is absolute. We work around this
									// issue by making the stored href absolute.
									var anchor = document.createElement("a");
									anchor.href = data.result.href;
									return this.href === anchor.href
										&& this.title === data.result.title;
								})
								.closest("li")
								.remove();

							if(ul.find("li").length === 0) {
								ul.siblings(".quicklinks_placeholder").show();
							}
						})
						.fail(function(data) {
							var msg = data.result;
							LightboxManager.alert(msg);
						});
				}
			},
			"no": "Keep quicklink",
			"focus": "yes"
		}
	);
	return false;
});

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

	// ===========================================================
	// code for remembering scroll position between page reloads
	// adapted from http://www.huntingground.freeserve.co.uk/main/mainfram.htm?../scripts/cookies/scrollpos.htm
	// ===========================================================

	function trigger_cb_on_nth_call(cb, n) {
		return function() {
			if (--n <= 0)
				cb();
		};
	}

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

	$.ajax(_site_domain + _index_page + '/ajax/get_setting', {
		data: {
			'type': 'dojo-quicklinks',
			'page': 'tac',
			'csrf_token': _csrf_token
		},
		type: 'POST',
		success: function (obj) {
			if (!obj['dojo-quicklinks']) {
				return;
			}
			var links = obj['dojo-quicklinks'];
			for (var i = 0; i < links.length; i += 1) {
				(function() {
					// make sure that the closure to not
					// get the final link every time
					append_quicklink_to_dom(links[i].icon, links[i].target, links[i].href, links[i].title);
				})();
			}
		}
	});

});

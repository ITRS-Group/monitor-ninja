
var loadimg_sml = new Image(16, 16);
loadimg_sml.src = _site_domain + 'application/media/images/loading_small.gif';

function _(text)
{
	// console.log('To translate: '+ text);
	return text;
}

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

	// listview refresh helper code
	$("#listview_refresh_control").bind('change', function() {
		if ($("#listview_refresh_control").prop("checked")) {
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
	}
	// -- end listview refresh helper code

	$('.select_all_items_service').on('click', function() {
		if ($(this).prop('checked')) {
			$(this).parents('table').find(".item_select_service input[type='checkbox']").not('.select_all_items_service').each(function() {
				if (!$(this).prop('disabled') && !$(this).is(':hidden')) {
					$(this).prop('checked', true);
				}
				else if ($(this).is(':hidden')) {
					$(this).prop('checked', false);
				}
			});
		} else {
			$(this).parents('table').find(".item_select_service input[type='checkbox']").not('.select_all_items_service').each(function() {
				$(this).prop('checked', false);
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
							// Popup message about updated time
							Notify.message(_listview_refresh_msg.replace('{delay}', interval), {type: "success"});
							clearTimeout(lsfilter_storage.list.autorefresh_timer);
							if (interval > 0) {
								lsfilter_storage.list.config.autorefresh_enabled = true;
							} else {
								lsfilter_storage.list.config.autorefresh_enabled = false;
							}
							lsfilter_storage.list.config.autorefresh_delay = interval * 1000;
							lsfilter_storage.list.start_autorefresh_timer();
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

	var global_quicklinks = [];

	function quicklinks_save_all() {
		$.ajax(_site_domain + _index_page + '/ajax/save_page_setting', {
			data: {
				'type': 'dojo-quicklinks',
				'page': 'tac',
				'setting': JSON.stringify(global_quicklinks),
				'csrf_token': _csrf_token
			},
			type: 'POST',
			complete: function () {
				$('#dojo-add-quicklink-href').attr('value', '');
				$('#dojo-add-quicklink-title').attr('value', '');
				$('#dojo-add-quicklink-icon').attr('value', '');
			}
		});
	}

	function quicklink_icon_listener() {
		$("#dojo-icon-container").on('click', 'span', function () {				
			var span = $(this);
			$('#dojo-add-quicklink-icon').val(span.data('icon'));

			// we have to change the background of the td, since the span already
			// has the icon image as its background
			var all_tds = $('#dojo-icon-container td');
			all_tds.removeClass('highlight');
			span.parents('td').addClass('highlight');
		});
	}

	function quicklink_icon_cleanup() {
		var all_tds = $('#dojo-icon-container td');
		all_tds.removeClass('highlight');
	}

	function quicklink_cleanup() {
		$('#dojo-quicklink-remove').html('');
		for (var i = 0; i < global_quicklinks.length; i += 1) {
			var l = global_quicklinks[i];
			var vid = l.title + ':' + l.href;
			var quicklink = $('<li><label></label> (<a target="_blank" class="external"></a>)</li>');
			quicklink
				.find('label')
				.text(l.title)
				.prepend($('<span class="icon-16"></span>').addClass('x16-' + l.icon))
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
		quicklink_clear_errors()
	}

	function quicklink_markup() {
		var markup = document.querySelectorAll("#dojo-add-quicklink-menu");
		return markup[0];
	}

	function quicklink_clear_errors() {
		if ($(".quicklink-error")) {
			$(".quicklink-error").remove();
		}
	}

	function quicklink_error(error) {
		quicklink_clear_errors()
		$("#dojo-add-quicklink-menu").prepend(
			`<span class="quicklink-error info-notice-warning">${error}</span>`
		)
	}

	$('#dojo-add-quicklink').on("click", function () {
		$('#dojo-quicklink-remove').html('');
		for (var i = 0; i < global_quicklinks.length; i += 1) {
			var l = global_quicklinks[i];
			var vid = l.title + ':' + l.href;
			var quicklink = $('<li><label></label> (<a target="_blank" class="external"></a>)</li>');
			quicklink
				.find('label')
				.text(l.title)
				.prepend($('<span class="icon-16"></span>').addClass('x16-' + l.icon))
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

		var lightbox = LightboxManager.create(false);
		var header = document.createElement('h1');
		header.textContent = 'Add new quicklink';

		lightbox.show();

		// Callback to clear the options when you close the window with the X
		lightbox.quicklink_header(header, function () {
			$('#dojo-add-quicklink-href').attr('value', '');
			$('#dojo-add-quicklink-title').attr('value', '');
			$('#dojo-add-quicklink-icon').attr('value', '');
			quicklink_icon_cleanup();
			quicklink_clear_errors();
		});

		// Get the contents of the quicklink markup
		var quicklink_nodelist = quicklink_markup();

		lightbox.content(quicklink_nodelist);

		// Setup listeners for the icons
		quicklink_icon_listener(quicklink_nodelist)

		lightbox.button("Save", function () {
			if(quicklink_save(lightbox)){
				quicklink_cleanup();
			}
		});

	})

	// Saves the quicklink to the user settings
	function quicklink_save(lightbox) {
		var href = $('#dojo-add-quicklink-href').prop('value'),
			title = $('#dojo-add-quicklink-title').prop('value'),
			icon = $('#dojo-add-quicklink-icon').prop('value'),
			target = $('#dojo-add-quicklink-target').prop('value'),
			changed = false;
		var error = '';
		var selected = 0;
		if (href && title && icon) {
			selected += 3;
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
				var remove_script_tags = new RegExp("(javascript:)|<[^>]*script", "gmi");
				if (remove_script_tags.test(href)) {
					href = "/";
				}
				global_quicklinks.push({ 'href': href, 'title': title, 'icon': icon, 'target': target });
				var quicklink = $('<li><a class="image-link"><span class="icon-16 x16-' + icon + '"></span></a></li>');
				quicklink
					.find('a')
					.prop('target', target)
					.prop('href', href)
					.prop('title', title);
				$('#dojo-add-quicklink').parent().before(quicklink);
				changed = true;
			} else {
				quicklink_error(error)
				return false;
			}
		}

		// This removes selected quicklinks
		$('#dojo-quicklink-remove input[type="checkbox"]').each(function () {
			var i = global_quicklinks.length;
			var vid = '';
			if (this.checked) {
				selected++;
				for (i; i--;) {
					vid = global_quicklinks[i].title + ':' + global_quicklinks[i].href;
					if (this.value === vid) {
						$('#quicklinks li a[title="' + this.title + '"]').parent().remove();
						global_quicklinks.splice(i, 1);
						changed = true;
					}
				}
			}

		});
		if (changed) {
			quicklinks_save_all();
		}
		
		if (!selected) {
			error += 'Invalid input';
			quicklink_error(error);
		}

		if (!error) {
			lightbox.hide();
			quicklink_clear_errors();
		}
	};
	
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

});

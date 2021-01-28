
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

$(function() {

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

	$(window).on( "resize", fit_content );
	fit_content();

	// make scroll memory cookie to be reset
	// when actively clicking on a link.
	$('body').on('click', 'a', function() {
		_save_scroll = false;
	});

	if ( content_div ) {
    content_div.on('click');
		content_div.trigger('focus');
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
	$("#listview_refresh_control").on('change', function() {
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
	$("#listview_refresh_value").on('change', function() {
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
	$("#page_settings_icon, #global_notifications_icon").on('click', function() {
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
			jQuery(document).on('keydown', _keycommand_forward, function (evt){
				if (typeof $('.nextpage').attr('href') != 'undefined') {
					// reset scroll memory to start at top for next page
					_save_scroll = false;
					self.location.href=$('.nextpage').attr('href');
				}
				return false;
			});
		}

		if (typeof _keycommand_back !== 'undefined' && _keycommand_back !== '') {
			jQuery(document).on('keydown', _keycommand_back, function (evt){
				if (typeof $('.prevpage').attr('href') != 'undefined') {
					// reset scroll memory to start at top for previous page
					_save_scroll = false;
					self.location.href=$('.prevpage').attr('href');
				}
				return false;
			});
		}

		if (typeof _keycommand_search !== 'undefined' && _keycommand_search !== '') {
			jQuery(document).on('keydown', _keycommand_search, function (evt){$('#query').trigger('focus'); return false; });
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

		$('#dojo-add-quicklink-menu form').on('submit', function (ev) {
			ev.preventDefault();
			var href = $('#dojo-add-quicklink-href').prop('value'),
				title = $('#dojo-add-quicklink-title').prop('value'),
				icon = $('#dojo-add-quicklink-icon').prop('value'),
				target = $('#dojo-add-quicklink-target').prop('value'),
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
					var remove_script_tags = new RegExp("(javascript:)|<[^>]*script", "gmi");
					if(remove_script_tags.test(href)) {
						href = "/";
					}
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

});

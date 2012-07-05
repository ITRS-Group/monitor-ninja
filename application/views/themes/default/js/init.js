var sURL = window.location.pathname + location.search;
var edit_visible = 0;

$(document).ready(function() {
	// make scroll memory cookie to be reset
	// when actively clicking on a link.
	$('a').click(function() {
		_save_scroll = false;
	});

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

	if (_current_uri == 'noc/index') {
		$('#ninja_noc_control').attr('checked', true);
	}
	$('#ninja_noc_control').click(function() {
		var new_url = $.query;
		new_url = $.query.REMOVE('d');

		var noheader_val= $('#noheader_chbx').attr('checked') ? 1 : 0;
		/*
		if (!$.query.get('noc')) {
			new_url = $.query.set('noc', true).set('noheader', noheader_val);
		} else {
			new_url = $.query.set('noc', false).set('noheader', noheader_val);

			// adding dummy param to be able to reload page without ?noc
			new_url = $.query.set('d', true).set('noheader', noheader_val);
		}
		*/
		var noc_val = $('#ninja_noc_control').attr('checked') ? 1 : 0;
		new_url = $.query.set('noc', noc_val).set('noheader', noheader_val);

		// special handling for tac and noc
		if (_current_uri == 'tac/index') {
			if ($('#ninja_noc_control').attr('checked')) {
				new_url = _site_domain + _index_page + '/noc';
			}
		} else if (_current_uri == 'noc/index') {
			new_url = _site_domain + _index_page + '/tac/index' + new_url.toString();
		}

		window.location.href = new_url.toString();
	});

	// stop widgets from trying to reload once user clicked
	// on a menu
	$('#menu a').click(function() {_is_refreshing = true;});

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
		if (typeof section_state != 'undefined' && section_state!='') {
			// hide the sections set to 'hide'
			if (section_state=='hide') {
				// using collapse_section() from
				// collapse_menu.js
				collapse_section(section);
			}
		}
	});

	$('.pnp_graph_icon').each(function() {
			if (typeof _use_popups == 'undefined' || !_use_popups) {
				return;
			}

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
					screen: true, // Keep the tooltip on-screen at all times
					x: 10,
					y: -5
				}
			},
			show: {
				when: 'mouseover',
				solo:true,
				delay:_popup_delay
			},
			hide: {
				effect: 'slide',
				when: {
					event: 'mouseout',
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
			if (typeof _use_popups == 'undefined' || !_use_popups) {
				return;
			}
			var obj_name = $(this).data('obj_name');
			if (!obj_name) {
				return false;
			}

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
				target: 'rightTop', // Position the tooltip
				tooltip: 'bottomLeft'
			},
			adjust: {
					screen: true, // Keep the tooltip on-screen at all times
					x: 10,
					y: -5
				}
			},
			show: {
				when: 'mouseover',
				solo:true,
				delay:_popup_delay
			},
			hide: {
				effect: 'slide',
				when: {
					event: 'mouseout',
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
			$('.item_select_service').show();
		else
			$('.item_select_service').hide();

		return false;
	});

	$('.select_all_items').live('click', function() {
		if ($(this).attr('checked')) {
			$('.select_all_items').attr('checked', true);
			$(".item_select input[type='checkbox']").not('.select_all_items').each(function() {
				if (!$(this).attr('disabled') && !$(this).is(':hidden')) {
					$(this).attr('checked', true);
				}
				else if ($(this).is(':hidden')) {
					$(this).attr('checked', false);
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
				if (!$(this).attr('disabled') && !$(this).is(':hidden')) {
					$(this).attr('checked', true);
				}
				else if ($(this).is(':hidden')) {
					$(this).attr('checked', false);
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
		if ($("#page_settings").is(':hidden')) {
			$("#page_settings").show();
			if ($('#infobar').is(':visible')) {
				var top = 125;
				$('#version_info').css('top', (top + 3) + 'px');
				$('#page_settings').css('top', (top + 3) + 'px');
			}
		} else {
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
					// reset scroll memory to start at top for next page
					_save_scroll = false;
					self.location.href=$('.nextpage').attr('href');
				}
				return false;
			});
		}

		if (typeof _keycommand_back != 'undefined' && _keycommand_back != '') {
			jQuery(document).bind('keydown', _keycommand_back, function (evt){
				if (typeof $('.prevpage').attr('href') != 'undefined') {
					// reset scroll memory to start at top for previous page
					_save_scroll = false;
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

	$('#multi_object_submit_service').click(function() {
		// check that we have any selected items
		if (!$('.item_select_service input[name=object_select\\[\\]]').is(':checked')) {
			show_message("multi_object_submit_progress_service", _nothing_selected_error);
			return false;
		}

		// Check if we actually set an action
		if ($('#multi_action_select_service').val() == "") {
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
		if ($('#multi_action_select').val() == "") {
			show_message("multi_object_submit_progress", _no_action_error);
			return false;
		}

		show_progress("multi_object_submit_progress", _wait_str);
	});

	// ======== Saved search handling ==========
	var name = $("#search_name"),
		query = $('#search_query'),
		allFields = $([]).add(name).add(query),
		tips = $(".validateTips"),
		description = $('#search_description');


	function check_save_search_fields(o, n, min, max)
	{
		if ( o.val.length > max || o.val().length < min ) {
			o.addClass("ui-state-error");
			updateTips(_search_save_error, n, min, max);
			return false;
		} else {
			return true;
		}
	}

	function updateTips(t, n, min, max) {
		tips.text( sprintf(t, n, min, max) ).addClass( "ui-state-highlight" );
		setTimeout(function() {
			tips.removeClass( "ui-state-highlight", 1500 );
		}, 500 );
	}

	$( "#save-search-form" ).dialog({
		autoOpen: false,
		height: 330,
		width: 350,
		closeOnEscape: true,
		modal: true,
		buttons: {
			Cancel: function() {
				$(this).dialog( "close" );
			},
			"Save this search": function() {
				var bValid = true;
				allFields.removeClass( "ui-state-error" );
				bValid = bValid && check_save_search_fields(query, _search_string_field, 1, 100);
				bValid = bValid && check_save_search_fields(name, _search_name_field, 1, 100);

				if (!bValid) {
					$(this).dialog("close");
					return;
				}
				// save form to db
				$.ajax({
					url:_site_domain + _index_page + '/' + '/ajax/save_search',
					type: 'POST',
					data: {name: name.val(), query: query.val(), description: description.val(), search_id: $('#search_id').val()},
					success: function(data) {
						data = parseInt(data);
						if (isNaN(data)) { // return value should be an integer if OK
							jgrowl_message(_search_saved_error, _search_save_error);
							return;
						}
						jgrowl_message(_search_saved_ok, _search_save_ok);

						// update/edit
						if ($('#search_id').val() != 0 && $('#saved_searchrow_' + $('#search_id').val())) {
							// update list of saved searches
							$('#searchname_' + $('#search_id').val()).html(name.val());
							$('#searchquery_' + $('#search_id').val()).html('<a href="' + _site_domain + _index_page + '/' + 'search/lookup?query=' + query.val() + '">' + query.val() + '</a>');
							$('#searchqueryimg_' + $('#search_id').val()).html('<a href="' + _site_domain + _index_page + '/' + 'search/lookup?query=' + query.val() + '"><img src="' + _site_domain + _theme_path + 'icons/16x16/use_search.png" /></a>');
							$('#searchdescription_' + $('#search_id').val()).html(description.val());

						} else if($('#search_id').val() == 0) {
							var previously_saved_searches_for_same_query = $('#saved_searches_table td[id^=searchquery_]:contains("'+query.val()+'")');
							if(previously_saved_searches_for_same_query.length) {
								previously_saved_searches_for_same_query.parent('tr').remove();
							}
							// created new search - add rows
							var new_data = '<td class="edit_search_query" id="searchquery_' + data + '"><a href="' + _site_domain + _index_page + '/' + 'search/lookup?query=' + query.val() + '">' + query.val() + '</a></td>';
							new_data += '<td class="edit_search_name" id="searchname_' + data + '">' + name.val() + '</td>';
							new_data += '<td class="edit_search_description" id="searchdescription_' + data + '">' + description.val() + '</td>'; //_theme_path
							new_data += '<td id="searchqueryimg_' + data + '"><a href="' + _site_domain + _index_page + '/' + 'search/lookup?query=' + query.val() + '"><img src="' + _site_domain + _theme_path + 'icons/16x16/use_search.png" /></a></td>';
							new_data += '<td class="edit_search_item" id="editsearch_' + data + '"><img style="cursor:pointer" src="' + _site_domain + _theme_path + 'icons/16x16/edit.png" id="editsearchimg_' + data + '" /></td>';
							new_data += '<td class="remove_search_item" id="removesearch_' + data + '"><img style="cursor:pointer" src="' + _site_domain + _theme_path + 'icons/16x16/remove.png" id="removesearchimg_' + data + '" /></td>';
							$('#saved_searches_table').append('<tr id="saved_searchrow_' + data + '">' + new_data + '</tr>');
							if (!$('#my_saved_searches').is(':visible')) {
								$('#my_saved_searches').show();
							}

							$('#search_id').attr('value', data);
						}
					}
				});
				$(this).dialog("close");
			}
		}
	});

	$('.edit_search_item').css('cursor', 'pointer');
	$('.remove_search_item').css('cursor', 'pointer');
	$('#save_search').css('cursor', 'pointer');

	$('#save_search').click(function() {
		$( "#save-search-form" ).dialog("open");

		// reset form
		var old_query = query.val(); // stash current query
		allFields.val('');
		query.val(old_query);
		$('#search_id').val(0);
		description.val('');

		// ... unless there's already a saved search for the same query
		$.get(
			_site_domain + _index_page + '/' + '/ajax/fetch_saved_search_by_query',
			{query: old_query},
			function(data) {
				if (data.error == 'Error') {
					jgrowl_message(data.error);
				} else {
					data = data.result;
					// set fetched values to edit dialog
					$('#search_name').attr('value', data['search_name'])
					$('#search_description').attr('value', data['search_description'])
					$('#search_id').attr('value', data['search_id']);
				}
			},
			'json'
		);
	});

	// hide/show layer with saved searches
	$('#my_saved_searches').click(function() {
		// try to position the dialog box
		if ($( "#saved_searches_table" ).is(':visible')) {
			$('#saved_searches_table').dialog('close');
		} else {
			$( "#saved_searches_table" ).dialog( "open" );
		}
	});

	$("#saved_searches_table").dialog({
		dialogClass: 'saved_searches',
		autoOpen: false,
		height: 'auto',
		width: 'auto',
		modal: false,
		buttons: {
			Close: function() {
				$(this).dialog( "close" );
			}
		}
	});

	// handle edit click for saved searches
	$('.edit_search_item').live('click', function() {
		var the_id = $(this).attr('id');
		the_id = the_id.replace('editsearch_', '');
		var original_img_src = $('#editsearchimg_' + the_id).attr('src');
		switch_image('editsearchimg_' + the_id, loadimg_sml.src)

		$.ajax({
			url:_site_domain + _index_page + '/' + '/ajax/fetch_saved_search',
			type: 'POST',
			data: {search_id: the_id},
			success: function(data) {
				if (data == 'Error') {
					jgrowl_message(_search_saved_error, _search_save_error);
				} else {
					data = eval( "(" + data + ")" );

					// set fetched values to edit dialog
					$('#saved_searches_table').dialog('close');
					$( "#save-search-form" ).dialog('open');
					$('#search_name').attr('value', data['search_name'])
					$('#search_query').attr('value', data['search_query'])
					$('#search_description').attr('value', data['search_description'])
					$('#search_id').attr('value', data['search_id']);
				}
			}
		});

		// restore original image with a timeout
		setTimeout(function() {switch_image('editsearchimg_' + the_id, original_img_src)}, 3000);
	});

	// handle remove click for saved searches
	$('.remove_search_item').live('click', function() {
		if (!confirm(_search_remove_confirm)) {
			return false;
		}

		var the_id = $(this).attr('id');
		the_id = the_id.replace('removesearch_', '');
		var original_img_src = $('#removesearchimg_' + the_id).attr('src');
		switch_image('removesearchimg_' + the_id, loadimg_sml.src)

		$.ajax({
			url:_site_domain + _index_page + '/' + '/ajax/remove_search',
			type: 'POST',
			data: {search_id: the_id},
			success: function(data) {
				if (data == 'OK') {
					// remove row
					$('#saved_searchrow_' + the_id).remove();
					if ($('#saved_searches_table tr').length == 1) {
						$('#saved_searches_table').dialog('close');
						$('#my_saved_searches').hide();
					}
				}
			}
		});

		// restore original image with a timeout
		setTimeout(function() {switch_image('removesearchimg_' + the_id, original_img_src)}, 3000);
	});

	$('.notescontainer').qtip({
		style: { name: 'light', tip: false },
		position: {
			corner: {
				target: 'leftTop',
				tooltip: 'bottomRight'
			}
		},
		show: { when: { event: 'click' } },
		hide: {
				effect: 'slide',
				when: {
					event: 'unfocus',
					delay:2000
				}
			}
	});
});

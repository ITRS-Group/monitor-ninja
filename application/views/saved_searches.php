<?php defined('SYSPATH') OR die('No direct access allowed.');
if (isset($searches) && !empty($searches)) { ?>
<script>
$(function() {
	$('<span id="my_saved_searches" style="padding: 4px; vertical-align: text-bottom; cursor: pointer;"><img id="my_saved_searches_img" title="Click to view your saved searches" src="/monitor/application/views/icons/16x16/save_search.png" /></span>').insertBefore('#query');

	var name = $("#search_name"),
		query = $('#query'),
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
						data = parseInt(data, 10);
						if (isNaN(data)) { // return value should be an integer if OK
							jgrowl_message(_search_saved_error, _search_save_error);
							return;
						}
						jgrowl_message(_search_saved_ok, _search_save_ok);

						// update/edit
						if ($('#search_id').val() !== 0 && $('#saved_searchrow_' + $('#search_id').val())) {
							// update list of saved searches
							$('#searchname_' + $('#search_id').val()).html(name.val());
							$('#searchquery_' + $('#search_id').val()).html('<a href="' + _site_domain + _index_page + '/' + 'search/lookup?query=' + query.val() + '">' + query.val() + '</a>');
							$('#searchqueryimg_' + $('#search_id').val()).html('<a href="' + _site_domain + _index_page + '/' + 'search/lookup?query=' + query.val() + '"><img src="' + _site_domain + 'icons/16x16/use_search.png" /></a>');
							$('#searchdescription_' + $('#search_id').val()).html(description.val());

						} else if($('#search_id').val() === 0) {
							var previously_saved_searches_for_same_query = $('#saved_searches_table td[id^=searchquery_]:contains("'+query.val()+'")');
							if(previously_saved_searches_for_same_query.length) {
								previously_saved_searches_for_same_query.parent('tr').remove();
							}
							// created new search - add rows
							var new_data = '<td class="edit_search_query" id="searchquery_' + data + '"><a href="' + _site_domain + _index_page + '/' + 'search/lookup?query=' + query.val() + '">' + query.val() + '</a></td>';
							new_data += '<td class="edit_search_name" id="searchname_' + data + '">' + name.val() + '</td>';
							new_data += '<td class="edit_search_description" id="searchdescription_' + data + '">' + description.val() + '</td>';
							new_data += '<td id="searchqueryimg_' + data + '"><a href="' + _site_domain + _index_page + '/' + 'search/lookup?query=' + query.val() + '"><img src="' + _site_domain + 'icons/16x16/use_search.png" /></a></td>';
							new_data += '<td class="edit_search_item" id="editsearch_' + data + '"><img style="cursor:pointer" src="' + _site_domain + 'icons/16x16/edit.png" id="editsearchimg_' + data + '" /></td>';
							new_data += '<td class="remove_search_item" id="removesearch_' + data + '"><img style="cursor:pointer" src="' + _site_domain + 'icons/16x16/remove.png" id="removesearchimg_' + data + '" /></td>';
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
		$.ajax(
			_site_domain + _index_page + '/ajax/fetch_saved_search_by_query',
			{
				data: {
					query: old_query
				},
				error: function(data) {
					jgrowl_message(data.responseText);
					$('#search_query').attr('value', old_query);
				},
				complete: function(data) {
					// set fetched values to edit dialog
					$('#search_name').attr('value', data['search_name']);
					$('#search_description').attr('value', data['search_description']);
					$('#search_id').attr('value', data['search_id']);
				},
				dataType: 'json'
			}
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
		switch_image('editsearchimg_' + the_id, loadimg_sml.src);

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
					$('#search_name').attr('value', data['search_name']);
					$('#search_query').attr('value', data['search_query']);
					$('#search_description').attr('value', data['search_description']);
					$('#search_id').attr('value', data['search_id']);
				}
			}
		});

		// restore original image with a timeout
		setTimeout(function() {switch_image('editsearchimg_' + the_id, original_img_src);}, 3000);
	});

	// handle remove click for saved searches
	$('.remove_search_item').live('click', function() {
		if (!confirm(_search_remove_confirm)) {
			return false;
		}

		var the_id = $(this).attr('id');
		the_id = the_id.replace('removesearch_', '');
		var original_img_src = $('#removesearchimg_' + the_id).attr('src');
		switch_image('removesearchimg_' + the_id, loadimg_sml.src);

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
		setTimeout(function() {switch_image('removesearchimg_' + the_id, original_img_src);}, 3000);
	});
});
</script>
<div id="save-search-form" title="<?php echo _('Save search') ?>" style="display:none">
	<form>
	<p class="validateTips"></p>
	<fieldset>
		<label for="search_query"><?php echo _('Search string') ?></label>
		<input type="text" name="search_query" id="search_query" value="<?php echo isset($query_str) ? $query_str : '' ?>" class="texts search_query ui-widget-content ui-corner-all" />
		<label for="search_name"><?php echo _('Name') ?></label>
		<input type="text" name="search_name" id="search_name" class="texts ui-widget-content ui-corner-all" />
		<label for="search_description"><?php echo _('Description') ?></label>
		<textarea cols="30" rows="3" name="search_description" id="search_description" class="texts ui-widget-content ui-corner-all"></textarea>
		<input type="hidden" name="search_id" id="search_id" value="0">
	</fieldset>
	</form>
</div>

<table id="saved_searches_table" title="<?php echo _('Saved searches') ?>" style="display:none">
	<tr style="height:20px">
		<td><strong><?php echo _('Search string') ?></strong></td>
		<td><strong><?php echo _('Name') ?></strong></td>
		<td><strong><?php echo _('Description') ?></strong></td>
		<td colspan="3"></td>
	</tr>
<?php foreach ($searches as $s) { ?>
	<tr id="saved_searchrow_<?php echo $s->id ?>">
		<td id="searchquery_<?php echo $s->id ?>"><?php echo html::anchor('search/lookup?query='.$s->search_query, $s->search_query, array('title' => _('Use this search'))) ?></td>
		<td id="searchname_<?php echo $s->id ?>"><?php echo $s->search_name ?></td>
		<td id="searchdescription_<?php echo $s->id ?>"><?php echo $s->search_description ?></td>
		<td id="searchqueryimg_<?php echo $s->id ?>"><?php echo html::anchor('search/lookup?query='.$s->search_query, html::image($this->add_path('icons/16x16/use_search.png'), array('title' => _('Use this search'))) ) ?></td>
		<td class="edit_search_item" id="editsearch_<?php echo $s->id ?>"><?php echo html::image($this->add_path('icons/16x16/edit.png'), array('title' => _('Edit this search'), 'id' => 'editsearchimg_'.$s->id)) ?></td>
		<td class="remove_search_item" id="removesearch_<?php echo $s->id ?>"><?php echo html::image($this->add_path('icons/16x16/remove.png'), array('title' => _('Remove this search'), 'id' => 'removesearchimg_'.$s->id)) ?></td>
	</tr>
<?php } ?>

</table>
<?php } ?>

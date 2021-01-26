function lsfilter_list_attach_events( listview, base_element ) {

	/* link_query, from lists */
	$(base_element)

	.on('click', '.query_link', function(e) {
		var query = $(this).attr('data-query');
		if( lsfilter_main ) {
			if (_controller_name === 'listview') {
				e.preventDefault();
				lsfilter_main.update( query, false, '' );
				return false;
			}
		}
	})

	.on('click', 'a.link_ajax_refresh', function(e) {
		e.preventDefault();
		var url = $(this).attr('href');
		$.ajax(url).done(function() {
			if (lsfilter_main)
				lsfilter_main.refresh();
			if (lsfilter_saved)
				lsfilter_saved.refresh_filter_list();
		});
		return false;
	})

	.on('click', '.link_set_sort', function(e) {
		e.preventDefault();
		var column = $(this).attr('data-column');
		listview.set_sort(column);
		return false;
	})

	.on('click', '.link_load_more_rows', function(e) {
		e.preventDefault();
		listview.load_more_rows($(this));
		return false;
	})

	.on('change', '.listview_multiselect_checkbox', function(e) {
		var tgt = $(this);
		if( lsfilter_multiselect ) {
			lsfilter_multiselect.box_register(tgt.prop('value'), tgt.prop('checked'));
		}
		var tr = tgt.closest('tr');
		var classname = "";
		if (tr.hasClass('odd'))
			classname = 'selected_odd';
		else
			classname = 'selected_even';
		if (tgt.prop('checked')) {
			tr.addClass(classname);
		}
		else {
			tr.removeClass(classname);
			$('#select_all').prop('checked', false);
		}
	})

	.on('change', '.listview_multiselect_checkbox_all', function(e) {
		listview.config.table.find('.listview_multiselect_checkbox').prop('checked', $(this).prop('checked')).trigger('change');
	});
}

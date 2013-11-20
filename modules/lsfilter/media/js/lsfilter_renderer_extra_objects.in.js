listview_renderer_extra_objects.all = [
	function() {
		var menu = $('<div id="filter-query-multi-action"/>').append(
			$('<h2/>').text(_('Multi action')),
			$('<ul id="multi-action-list"/>').append(
				$('<li/>').text(_('Table doesn\'t support multi-select'))
			),
			$('<div id="multi-action-message"/>'));
		lsfilter_multiselect.init(menu.find('#multi-action-list'));
		lsfilter_multiselect.update({
			'source': false,
			'metadata': {'table': lsfilter_storage.list.request_metadata.table}
		});
		return menu;
	},
	function() {
		var menu = $('<div id="filter-query-builder"/>').append(
			$('<div style="margin: 8px 0 0 8px"/>').append(
				$('<input/>').attr('type', 'text').attr('id', 'lsfilter_save_filter_name').attr('placeholder', _('Filter Name')),
				$('<button/>').attr('id', 'lsfilter_save_filter').text(_('Save Filter')),
				// if authorized_for('saved_filters_global')
				$('<input/>').attr('type', 'checkbox').attr('id', 'lsfilter_save_filter_global'),
				_('Make global')
			),
			$('<h2/>').text(_('Manual input')),
			$('<form/>').attr('action', '#').append(
				$('<textarea/>').css('width', '98%').css('height', '30px').attr('name', 'filter_query').attr('id', 'filter_query').val(lsfilter_query),
				$('<input/>').attr('type', 'hidden').attr('name', 'filter_query_order').attr('id', 'filter_query_order').val(lsfilter_query_order)
			),
			$('<div/>').attr('id', 'filter-query-status'),
			$('<h2/>').text(_('Graphical input')),
			$('<form/>').attr('id', 'filter_visual_form').append(
				$('<div/>').attr('id', 'filter_visual')));
		lsfilter_textarea.init(menu.find('#filter_query'), menu.find('#filter_query_order'));
		lsfilter_visual.init(menu.find('#filter_visual'));
		lsfilter_visual.update({
			source: 'textfield',
			query: lsfilter_query,
			order: lsfilter_query_order
		});
		return menu;
	}
];

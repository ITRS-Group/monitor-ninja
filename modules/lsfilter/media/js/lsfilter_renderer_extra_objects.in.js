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
	}
];

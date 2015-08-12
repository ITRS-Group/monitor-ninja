listview_renderer_extra_objects.all = [
	function() {
		var menu;
		if (!lsfilter_multiselect.elem_objtype) {
			menu = $('<div id="filter-query-multi-action"/>').append(
				$('<ul id="multi-action-list"/>').append(
					$('<li/>').text(_('Table doesn\'t support multi-select'))
				));
			lsfilter_multiselect.init(menu.find('#multi-action-list'));
			lsfilter_multiselect.update({
				'source': false,
				'metadata': {'table': lsfilter_storage.list.request_metadata.table}
			});
		} else {
			menu = $('#filter-query-multi-action')
		}
		return menu;
	}
];

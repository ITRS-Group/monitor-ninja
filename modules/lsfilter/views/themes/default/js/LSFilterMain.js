var lsfilter_main = {
	update : function(query, source) {
		console.log('update <' + query + '> from <' + source + '>');
		
		if (source != 'history')
			lsfilter_history.update(query);

		if (source != 'list')
			lsfilter_list.update(query);

		if (source != 'saved')
			lsfilter_saved.update(query);

		if (source != 'textarea')
			lsfilter_textarea.update(query);

		if (source != 'visual')
			lsfilter_visual.update(query);

		return true;
	},
	init : function() {
		lsfilter_history.init();
		lsfilter_list.init();
		lsfilter_saved.init();
		lsfilter_textarea.init();
		lsfilter_visual.init();

		// when frist loaded, the textarea contains the query from the controller
		lsfilter_textarea.load();
	}
};

$().ready(function() {
	lsfilter_main.init();
});
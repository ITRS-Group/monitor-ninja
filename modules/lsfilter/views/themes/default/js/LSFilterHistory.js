var lsfilter_history = {
	update : function(query) {
		window.history.pushState({
			query : query
		}, window.title, '?q='+encodeURIComponent(query));
	},
	init : function() {
		window.onpopstate = function(evt) {
			if (evt.state.query) {
				lsfilter_main.update(evt.state.query, 'history');
			}
		}
	}
}
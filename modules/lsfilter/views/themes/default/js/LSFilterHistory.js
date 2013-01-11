var lsfilter_history = {
	update : function(data) {
		if( data.source == 'history' ) return;
		window.history.pushState(data, window.title, '?q='+encodeURIComponent(data.query)+'&s='+encodeURIComponent(data.order));
	},
	init : function() {
		window.onpopstate = function(evt) {
			if (evt.state && evt.state.query) {
				lsfilter_main.update(evt.state.query, 'history', evt.state.order);
			} else {
				console.log('think about the history');
				console.log(evt);
			}
		}
	}
}
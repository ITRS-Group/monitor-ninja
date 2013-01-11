var lsfilter_history = {
	enabled: false,
		
	update : function(data) {
		if( !this.enabled ) return;
		if( data.source == 'history' ) return;
		var order_query = '';
		if( data.order ) {
			order_query = '&s='+encodeURIComponent(data.order);
		}
		window.history.pushState(data, window.title, '?q='+encodeURIComponent(data.query)+order_query);
	},
	init : function() {
		if(window.history.pushState) {
			this.enabled = true; 
		} else {
			return;
		}
		window.onpopstate = function(evt) {
			if (evt.state && evt.state.query) {
				var order = evt.state.order;
				if( !order ) order = '';
				lsfilter_main.update(evt.state.query, 'history', order);
			} else {
				console.log('think about the history');
				console.log(evt);
			}
		}
	}
}
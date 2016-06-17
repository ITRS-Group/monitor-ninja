var lsfilter_history = {
	on: {
		"update_ok": function(data) {
			if (!window.history.pushState) {
				/* Do update of regular link in toolbar
				 * (TODO) */
				return;
			}
			if (data.source == 'history') return;
			var order_query = '';
			if (data.order) {
				order_query = '&s=' + encodeURIComponent(data.order);
			}
			window.history.pushState(data, window.title, '?q=' + encodeURIComponent(data.query) + order_query);
		}
	},

	init: function()
	{
		if (!window.history.pushState) {
			return;
		}

		window.onpopstate = function(evt) {
			if (evt.state && evt.state.query) {
				var order = evt.state.order;
				if (!order) order = '';
				lsfilter_main.update(evt.state.query, 'history', order);
			}
		};
	}
};

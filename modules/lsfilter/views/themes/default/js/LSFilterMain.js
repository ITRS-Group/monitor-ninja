var lsfilter_main = {
	update_delay : 500,

	/***************************************************************************
	 * Trigger update of ListView
	 **************************************************************************/
	update_delayed : function(query, source) {
		var self = this; // To be able to access it from within handlers
		if (this.update_timer) {
			clearTimeout(this.update_timer);
		}
		this.update_query = query;
		this.update_source = source;
		this.update_timer = setTimeout(function() {
			self.update_run();
		}, this.update_delay);
	},
	
	update : function(query, source) {
		this.update_query = query;
		this.update_source = source;
		this.update_run();
	},

	update_timer : false,
	update_query : false,
	update_source : false,

	update_run : function() {
		var source = this.update_source;
		var query = this.update_query;

		this.update_timer = false;
		console.log('update <' + query + '> from <' + source + '>');
		try {
			var parser = new LSFilter(new LSFilterPreprocessor(),
					new LSFilterMetadataVisitor());
			var metadata = parser.parse(query);

			console.log(metadata);

			lsfilter_history.update(query, source, metadata);
			lsfilter_list.update(query, source, metadata);
			lsfilter_multiselect.update(query, source, metadata);
			lsfilter_saved.update(query, source, metadata);
			lsfilter_textarea.update(query, source, metadata);
			lsfilter_visual.update(query, source, metadata);

		} catch (ex) {
			this.handle_parse_exception(ex);
		}
	},
	/***************************************************************************
	 * Initialize ListView
	 **************************************************************************/
	init : function() {
		this.update_page_links();

		lsfilter_history.init();
		lsfilter_list.init();
		lsfilter_multiselect.init();
		lsfilter_saved.init();
		lsfilter_textarea.init();
		lsfilter_visual.init();

		// when frist loaded, the textarea contains the query from the
		// controller
		lsfilter_textarea.load();
	},
	/***************************************************************************
	 * Handler for parsing exception
	 **************************************************************************/
	handle_parse_exception : function(ex) {
	},

	/***************************************************************************
	 * Update links from rest of the page
	 **************************************************************************/

	update_page_links : function() {
		var self = this; // To be able to access it from within handlers
		$(
				'a[href^="' + _site_domain + _index_page + '/'
						+ _controller_name + '"]').click(function(evt) {
			var href = $(this).attr('href');
			var args = self.parseParams(href);
			if (args.q) {
				lsfilter_main.update(args.q, 'external_link');
				return false;
			}
			return true;
		});
	},

	/***************************************************************************
	 * Helpers for query parsing
	 **************************************************************************/
	re : /([^&=?]+)=([^&]*)/g,
	decodeRE : /\+/g, // Regex for replacing addition symbol with a space
	decode : function(str) {
		return decodeURIComponent(str.replace(this.decodeRE, " "));
	},
	parseParams : function(query) {
		var params = {}, e;
		while (e = this.re.exec(query)) {
			var k = this.decode(e[1]), v = this.decode(e[2]);
			if (k.substring(k.length - 2) === '[]') {
				k = k.substring(0, k.length - 2);
				(params[k] || (params[k] = [])).push(v);
			} else
				params[k] = v;
		}
		return params;
	}
};

$().ready(function() {
	lsfilter_main.init();
});
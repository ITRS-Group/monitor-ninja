var lsfilter_main = {
	update : function(query, source) {
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
	handle_parse_exception : function(ex) {
	},

	update_page_links : function() {
		var self = this; // To be able to access it from within handlers
		$(
				'a[href^="' + _site_domain + _index_page + '/'
						+ _controller_name + '"]').click(function(evt) {
			var href = $(this).attr('href');
			var args = self.parseParams(href);
			if( args.q ) {
				lsfilter_main.update(args.q, 'external_link');
				return false;
			}
			return true;
		});
	},

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
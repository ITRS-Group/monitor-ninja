var lsfilter_storage = {};

var lsfilter_main = {
	update_delay: 300,
	
	/***************************************************************************
	 * Trigger update of ListView
	 **************************************************************************/
	update_delayed: function(query, source, order)
	{
		var self = this; // To be able to access it from within handlers
		if (this.update_timer) {
			clearTimeout(this.update_timer);
		}
		this.update_query = query;
		this.update_source = source;
		this.udpate_order = order;
		this.update_timer = setTimeout(function()
		{
			self.update_run();
		}, this.update_delay);
	},
	
	update: function(query, source, order)
	{
		this.update_query = query;
		this.update_source = source;
		this.update_order = order;
		this.update_run();
	},
	
	update_timer: false,
	update_query: false,
	update_source: false,
	update_order: false,
	
	state: {
		query: false,
		order: false
	},
	
	update_run: function()
	{
		var source = this.update_source;
		var query = this.update_query;
		var order = this.update_order;
		
		this.update_timer = false;
		try {
			
			if( query ) {
				this.state.query = query;
			}
			if( typeof order == 'string' ) {
				this.state.order = order;
			}
			
			var parser = new LSFilter(new LSFilterPreprocessor(),new LSFilterMetadataVisitor());
			var metadata = parser.parse(this.state.query);
			
			var data = $.extend({
				source: source,
				metadata: metadata
			},this.state);
			
			lsfilter_history.update(data);
			lsfilter_storage.list.update(data);
			lsfilter_multiselect.update(data);
			lsfilter_saved.update(data);
			lsfilter_textarea.update(data);
			lsfilter_visual.update(data);
			
		}
		catch (ex) {
			this.handle_parse_exception(ex);
		}
	},
	/***************************************************************************
	 * Initialize ListView
	 **************************************************************************/
	init: function()
	{
		this.update_page_links();

		lsfilter_history.init();
		
		lsfilter_storage.list = new lsfilter_list({
			table: $('#filter_result'),
			totals: $('#filter_result_totals'),
			attach_head: true,
			loading_start: function()
			{
				var loader = $('<span class="lsfilter-loader" />').append(
						$('<span>' + _('Loading...') + '</span>'));
				$('#filter_loading_status').append(loader);
				return loader;
			},
			loading_stop: function(loader)
			{
				loader.remove();
			}
		});
		lsfilter_multiselect.init();
		lsfilter_saved.init();
		lsfilter_textarea.init();
		lsfilter_visual.init();
		
		// when first loaded, the textarea contains the query from the
		// controller
		lsfilter_textarea.load();
		
	},
	/***************************************************************************
	 * Handler for parsing exception
	 **************************************************************************/
	handle_parse_exception: function(ex)
	{
		console.log(ex);
		console.log(ex.stack);
	},
	
	/***************************************************************************
	 * Update links from rest of the page
	 **************************************************************************/
	
	update_page_links: function()
	{
		var self = this; // To be able to access it from within handlers
		$(
				'a[href^="' + _site_domain + _index_page + '/'
						+ _controller_name + '"]').click(function(evt)
		{
			var href = $(this).attr('href');
			var args = self.parseParams(href);
			if (args.q) {
				lsfilter_main.update(args.q, 'external_link', '');
				return false;
			}
			return true;
		});
	},
	
	/***************************************************************************
	 * Helpers for query parsing
	 **************************************************************************/
	re: /([^&=?]+)=([^&]*)/g,
	decodeRE: /\+/g, // Regex for replacing addition symbol with a space
	decode: function(str)
	{
		return decodeURIComponent(str.replace(this.decodeRE, " "));
	},
	parseParams: function(query)
	{
		var params = {}, e;
		while (e = this.re.exec(query)) {
			var k = this.decode(e[1]), v = this.decode(e[2]);
			if (k.substring(k.length - 2) === '[]') {
				k = k.substring(0, k.length - 2);
				(params[k] || (params[k] = [])).push(v);
			}
			else
				params[k] = v;
		}
		return params;
	}
};

$().ready(function()
{
	lsfilter_main.init();
});

var lsfilter_storage = {};

var lsfilter_main = {

	update_delay : 300,

	/***************************************************************************
	 * Trigger update of ListView
	 **************************************************************************/
	update_delayed : function(query, source, order) {
		var self = this; // To be able to access it from within handlers
		if (this.update_timer) {
			clearTimeout(this.update_timer);
		}
		this.update_query = query;
		this.update_source = source;
		this.udpate_order = order;
		this.update_timer = setTimeout(function() {
			self.update_run();
		}, this.update_delay);
	},

	update : function(query, source, order) {
		this.update_query = query;
		this.update_source = source;
		this.update_order = order;
		this.update_run();
	},

	update_timer : false,
	update_query : false,
	update_source : false,
	update_order : false,

	state : {
		query : false,
		order : false
	},

	refresh: function() {
		this.update_run();
	},

	update_run : function() {

		var source = this.update_source;
		var query = this.update_query;
		var order = this.update_order;

		var header_height = $( "body > .container >#header" ).outerHeight() + 4;

		this.update_timer = false;
		try {

			if (query) {
				this.state.query = query;
			}
			if (typeof order == 'string') {
				this.state.order = order;
			}

			var parser = new LSFilter(new LSFilterPreprocessor(),
					new LSFilterMetadataVisitor());
			var metadata = parser.parse(this.state.query);

			var data = $.extend({
				source : source,
				metadata : metadata
			}, this.state);

			this.clear_parse_status();

			$('#filter-query-builder').css( "top", header_height + "px" );

			$('#extra-dropdowns').replaceContent(
				$.map((listview_renderer_extra_objects[metadata.table] || []).concat(listview_renderer_extra_objects.all || []), function(x) {
					if (typeof x == 'function') {
						x = x();
					}
					x.addClass('filter-query-dropdown');
					x.css( "top", header_height + "px" );
					return x.toArray();
				}));
			lsfilter_history.update(data);
			lsfilter_storage.list.update(data);
			lsfilter_multiselect.update(data);
			lsfilter_saved.update(data);
			lsfilter_textarea.update(data);
			lsfilter_visual.update(data);

		} catch (ex) {
			console.log(ex);
			this.set_parse_status(ex);
			$('#filter-query-builder').show();
		}
	},
	/***************************************************************************
	 * Initialize ListView
	 **************************************************************************/
	init : function() {
		this.update_page_links();

		lsfilter_history.init();

		lsfilter_storage.list = new lsfilter_list({
			autorefresh_delay : _lv_refresh_delay * 1000,
			table : $('#filter_result'),
			toolbar: $( '.main-toolbar' ),
			totals : $('#filter_result_totals'),
			attach_head : true,
			loading_start : function() {
				var loader = $('<span class="lsfilter-loader" />').append(
						$('<span>' + _('Loading...') + '</span>'));
				$('#filter_loading_status').append(loader);
				return loader;
			},
			loading_stop : function(loader) {
				loader.remove();
			},
			per_page: lsfilter_per_page
		});
		lsfilter_textarea.init($('#filter_query'), $('#filter_query_order'));
		lsfilter_saved.init();
		lsfilter_visual.init($('#filter_visual'));
		lsfilter_visual.update({
			source: 'textfield',
			query: lsfilter_query,
			order: lsfilter_query_order
		});

	},

	/***************************************************************************
	 * Update status bar
	 **************************************************************************/

	set_parse_status : function(status) {
		$('#filter-query-status').text(status);
	},
	clear_parse_status : function() {
		$('#filter-query-status').html("&nbsp;");
	},

	/***************************************************************************
	 * Update links from rest of the page
	 **************************************************************************/

	update_page_links : function() {
		var self = this; // To be able to access it from within handlers
		$(
				'a[href^="' + _site_domain + _index_page + '/' + _controller_name + '"]').click(function(evt) {
			var href = $(this).attr('href');
			var args = self.parseParams(href);
			if (args.q) {

				lsfilter_main.update(args.q, 'external_link', '');

				var content_div = $( "body > .container > #content" )
				content_div.click();
				content_div.focus();

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
	lsfilter_main.update(lsfilter_query, false, lsfilter_query_order);
});

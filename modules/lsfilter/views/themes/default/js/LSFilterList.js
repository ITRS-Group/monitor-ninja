var lsfilter_list = {
	// Configuration
	request_delay : 500,

	autorefresh_delay : 30000,

	// External methods
	update : function(query) {
		var self = this; // To be able to access it from within handlers
		this.request_query = query;
		if (this.request_timer) {
			clearTimeout(this.request_timer);
		}
		this.request_timer = setTimeout(function() {
			self.resuest_timer = false;
			self.send_request();
		}, this.request_delay);
	},
	init : function() {

	},

	// Internal veriables
	request_query : '',
	resuest_timer : false,

	sort_vis_column : null,
	sort_db_columns : [],
	sort_ascending : true,

	autorefresh_timer : false,

	// Internal methods
	send_request : function() {
		var self = this; // To be able to access it from within handlers
		var parser = new LSFilter(new LSFilterPreprocessor(),
				new LSFilterMetadataVisitor());
		var metadata = parser.parse(this.request_query);

		this.loading_start();

		listview_ajax_active_request = $.ajax({
			url : _site_domain + _index_page + "/" + _controller_name
					+ "/fetch_ajax",
			dataType : 'json',
			data : {
				"query" : this.request_query,
				"sort" : [],
				"sort_asc" : (1 ? 1 : 0),
				"columns" : listview_columns_for_table(metadata['table']),
				"limit" : 100,
				"offset" : 0
			},
			success : function(data) {
				self.handle_ajax_response(data);
			}
		});
	},

	handle_autorefresh : function() {

	},

	handle_ajax_response : function(data) {

		this.loading_stop();

		if (data.status == 'success') {
			listview_current_table = data.table;
			listview_render_totals(data.totals);

			listview_render_table(data.data, data.count);
			multi_select_refresh();
		}

		if (data.status == 'error') {
			$('#filter_result').empty().text("Error: " + data.data);
			$('#filter_result_totals').empty();
		}

		this.autorefresh_timer = setTimeout(this.handle_autorefresh,
				this.autorefresh_delay);
	},

	loading_start : function() {
		// var loader = $('<span class="lsfilter-loader"
		// />').append($('<span>'+_('Loading...')+'</span>'));
	},

	loading_stop : function() {

	}
};
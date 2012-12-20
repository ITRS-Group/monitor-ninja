var lsfilter_list = {
	// Configuration
	request_delay : 500,

	autorefresh_delay : 30000,

	/***************************************************************************
	 * External methods
	 **************************************************************************/
	update : function(query, source, metadata) {
		if (source == 'list')
			return;
		var self = this; // To be able to access it from within handlers
		this.request_query = query;
		this.request_metadata = metadata;
		this.sort_vis_column = null;
		this.sort_db_columns = [];
		this.sort_ascending = true;
		if (this.request_timer) {
			clearTimeout(this.request_timer);
		}
		this.loading_start();
		this.request_timer = setTimeout(function() {
			self.resuest_timer = false;
			self.send_request();
		}, this.request_delay);
	},
	init : function() {

	},
	set_sort : function(vis_column, db_columns) {
		if (this.sort_vis_column == vis_column) {
			this.sort_ascending = !this.sort_ascending;
		} else {
			this.sort_vis_column = vis_column;
			this.sort_db_columns = db_columns;
			this.sort_ascending = true;
		}
		this.send_request();
	},

	/***************************************************************************
	 * Internal veriables
	 **************************************************************************/
	request_query : '',
	request_metadata : {},
	resuest_timer : false,

	sort_vis_column : null,
	sort_db_columns : [],
	sort_ascending : true,

	autorefresh_timer : false,

	/***************************************************************************
	 * Internal methods
	 **************************************************************************/
	send_request : function() {
		var self = this; // To be able to access it from within handlers

		listview_ajax_active_request = $
				.ajax({
					url : _site_domain + _index_page + "/" + _controller_name
							+ "/fetch_ajax",
					dataType : 'json',
					data : {
						"query" : this.request_query,
						"sort" : this.sort_db_columns,
						"sort_asc" : (this.sort_ascending ? 1 : 0),
						"columns" : listview_columns_for_table(this.request_metadata['table']),
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

			listview_render_table(data.data, data.count, this.sort_vis_column, this.sort_ascending);
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
		var loader = $('<span class="lsfilter-loader" />').append(
				$('<span>' + _('Loading...') + '</span>'));
		$('#filter_loading_status').append(loader);
	},

	loading_stop : function() {
		$('.lsfilter-loader').remove();
	}
};
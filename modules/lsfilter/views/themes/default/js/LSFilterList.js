var lsfilter_list = {
	// Configuration
	request_delay : 500,

	autorefresh_delay : 30000,

	config: {
		offset: 0, // watch out, we've got state
		per_page: 100
	},

	/***************************************************************************
	 * External methods
	 **************************************************************************/
	update : function(query, source, metadata) {
		if(this.request_metadata && this.request_metadata !== metadata) {
			// we're switching from one type of view to another,
			// reset unwanted state
			this.config.offset = 0;
		}
		if (source == 'list')
			return;
		var self = this; // To be able to access it from within handlers
		this.request_query = query;
		this.request_metadata = metadata;
		this.sort_vis_column = null;
		this.sort_db_columns = [];
		this.sort_ascending = true;
		self.send_request(this.config);
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
		this.send_request(this.config);
	},

	send_request : function(config) {
		var self = this; // To be able to access it from within handlers

		if(typeof config.increment_items_in_view !== "undefined" && Boolean(config.increment_items_in_view)) {
			delete config.increment_items_in_view;
			self.config.offset += self.config.per_page;
		}

		var options = $.extend(
			self.config,
			{
				callback: self.handle_ajax_response // get the scope right
			}
		);
		$.extend(options, config);


		var loader = $('<span class="lsfilter-loader" />').append(
				$('<span>' + _('Loading...') + '</span>'));
		$('#filter_loading_status').append(loader);
		listview_ajax_active_request = $.ajax({
			url : _site_domain + _index_page + "/" + _controller_name + "/fetch_ajax",
			dataType : 'json',
			data : {
				"query" : this.request_query,
				"sort" : this.sort_db_columns,
				"sort_asc" : (this.sort_ascending ? 1 : 0),
				"columns" : listview_columns_for_table(this.request_metadata['table']),
				"limit" : options.per_page,
				"offset" : options.offset
			},
			success : function(data) {
				options.callback(data);
			},
			complete: function() {
				$('.lsfilter-loader').remove();
			}
		});
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

	handle_autorefresh : function() {

	},

	handle_ajax_response : function(data) {
		if (data.status == 'success') {
			listview_current_table = data.table;
			listview_render_totals(data.table, data.totals);

			listview_render_table(data.data, data.count, this.sort_vis_column, this.sort_ascending);
			multi_select_refresh();
		} else if (data.status == 'error') {
			$('#filter_result').empty().text("Error: " + data.data);
			$('#filter_result_totals').empty();
		}

		this.autorefresh_timer = setTimeout(this.handle_autorefresh,
				this.autorefresh_delay);
	}
};

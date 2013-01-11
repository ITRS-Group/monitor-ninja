function lsfilter_list(config)
{
	// Configuration
	this.defaults = {
		per_page: 100,
		autorefresh_delay: 30000,
		request_url: _site_domain + _index_page + "/" + _controller_name
				+ "/fetch_ajax",
		attach_head: false,
		loading_start: function()
		{
		},
		loading_stop: function()
		{
		}
	};
	this.config = $.extend({}, this.defaults, config);
	
	if($.browser.msie) {
		var parts = $.browser.version.split('.');
		if( parseInt(parts[0]) < 8 ) {
			this.config.attach_head = false; /* Don't support attached head in ie7 */
		}
	}
	
	/***************************************************************************
	 * External methods
	 **************************************************************************/
	this.update = function(data)
	{
		var self = this; // To be able to access it from within handlers
		var metadata = data.metadata;
		
		if (data.source && data.source == 'list') { return; }
		
		if (!metadata) {
			var parser = new LSFilter(new LSFilterPreprocessor(),
					new LSFilterMetadataVisitor());
			metadata = parser.parse(data.query);
		}
		
		if (this.request_metadata && this.request_metadata !== metadata) {
			// we're switching from one type of view to another,
			// reset unwanted state
			this.config.offset = 0;
			this.previous_obj = {};
		}
		
		this.request_query = data.query;
		this.request_metadata = metadata;
		
		this.sort_vis_column = false;
		this.sort_db_columns = [];
		this.sort_ascending = true;
		
		var order_parts = [];
		if (data.order) {
			order_parts = data.order.split(' ');
		}
		if (order_parts.length >= 1 && order_parts.length <= 2) {
			
			this.sort_vis_column = order_parts[0];
			
			this.sort_db_columns = [];
			for ( var key in listview_renderer_table[metadata.table] ) {
				if (key == this.sort_vis_column) {
					this.sort_db_columns = listview_renderer_table[metadata.table][key].sort;
				}
			}
		}
		if (order_parts.length == 2) {
			this.sort_ascending = (order_parts[1].toLowerCase() == 'asc');
		}
		
		this.send_request({
			append: false,
			callback: function(data)
			{
				self.handle_ajax_response(data);
				// get the scope right
			}
		});
	};
	
	this.set_sort = function(table, vis_column)
	{
		var self = this; // To be able to access it from within handlers
		this.config.offset = 0;
		this.previous_obj = 0;
		
		if (this.sort_vis_column == vis_column) {
			this.sort_ascending = !this.sort_ascending;
		}
		else {
			this.sort_db_columns = [];
			for ( var key in listview_renderer_table[table]) {
				if (key == vis_column) {
					this.sort_db_columns = listview_renderer_table[table][key].sort;
				}
			}
			this.sort_vis_column = vis_column;
			this.sort_ascending = true;
		}
		
		lsfilter_main.update(false, 'list', this.sort_vis_column
				+ (this.sort_ascending ? ' asc' : ' desc'));
		
		this.send_request({
			append: false,
			callback: function(data)
			{
				self.handle_ajax_response(data);
				// get the scope right
			}
		});
	};
	
	this.send_request = function(config)
	{
		var self = this; // To be able to access it from within handlers
		
		if (typeof config.increment_items_in_view !== "undefined"
				&& Boolean(config.increment_items_in_view)) {
			delete config.increment_items_in_view;
			self.config.offset += self.config.per_page;
		}
		
		if (this.active_ajax_request) {
			this.active_ajax_request.abort();
		}
		
		var options = $.extend({}, self.config, config);
		
		var db_sort_columns = [];
		for ( var i = 0; i < this.sort_db_columns.length; i++) {
			var col = this.sort_db_columns[i];
			var parts = col.split(' ');
			var col_asc = true;
			if (parts.length == 2) {
				col_asc = parts[1].toLowerCase() == 'asc';
			}
			if (!this.sort_ascending) col_asc = !col_asc;
			db_sort_columns.push(parts[0] + (col_asc ? ' asc' : ' desc'));
		}
		
		var loading_id = self.config.loading_start();
		this.active_ajax_request = $
				.ajax({
					url: this.config.request_url,
					dataType: 'json',
					data: {
						"query": this.request_query,
						"sort": db_sort_columns,
						"columns": listview_columns_for_table(this.request_metadata['table']),
						"limit": options.per_page,
						"offset": options.offset
					},
					success: function(data)
					{
						options.callback(data);
					},
					complete: function()
					{
						this.active_ajax_request = false;
						self.config.loading_stop(loading_id);
						// $('.lsfilter-loader').remove();
					}
				});
	};
	
	/***************************************************************************
	 * Internal veriables
	 **************************************************************************/
	this.request_query = '';
	this.request_metadata = {};
	this.resuest_timer = false;
	
	this.active_ajax_request = false;
	
	this.sort_vis_column = null;
	this.sort_db_columns = [];
	this.sort_ascending = true;
	this.sort_columns_table = {};
	
	this.autorefresh_timer = false;
	
	/***************************************************************************
	 * Internal methods
	 **************************************************************************/
	
	this.handle_autorefresh = function()
	{
		
	};
	
	this.handle_ajax_response = function(data)
	{
		var new_table = $('<span />');
		var new_totals = $('<span />');
		if (data.status == 'success') {
			new_totals = this.render_totals(data.table, data.totals);
			new_table = this.render_table(data, this.sort_vis_column,
					this.sort_ascending);
		}
		else if (data.status == 'error') {
			new_table.text("Error: " + data.data);
		}
		
		if (this.config.table) {
			this.config.table.empty().append(new_table);
			this.attach_header(new_table);
		}
		if (this.config.totals) this.config.totals.empty().append(new_totals);
		
		this.autorefresh_timer = setTimeout(this.handle_autorefresh,
				this.autorefresh_delay);
	};
	
	this.render_totals = function(table, totals)
	{
		var container = $('<ul />');
		container.append($('<li />').text(
				table.charAt(0).toUpperCase() + table.slice(1)).css('float',
				'left').css('font-weight', 'bold'));
		if (totals) {
			for ( var field in listview_renderer_totals) {
				if (field in totals) {
					container.append(listview_renderer_totals[field](
							totals[field][1]).css('float', 'left').wrapInner(
							link_query(totals[field][0])));
				}
			}
		}
		return container;
	};
	
	this.insert_rows = function(columns, data, tbody)
	{
		/*
		 * // #load_more is a tr>td>a node var last_row =
		 * $('#load_more').parent().parent();
		 */
		for ( var i = 0; i < data.data.length; i++) {
			var obj = data.data[i];
			
			var row = $('<tr />');
			row.addClass(i % 2 ? 'odd' : 'even');
			row.data('key', obj.key);
			
			for ( var cur_col = 0; cur_col < columns.length; cur_col++) {
				row.append(columns[cur_col]({
					obj: obj,
					last_obj: this.previous_obj,
					row: row,
					listview: this
				}));
				// .addClass( 'listview-cell-' + cur_col));
			}
			tbody.append(row);
			this.previous_obj = obj;
		}
	};
	
	this.add_fill_bar = function(columns, data, tbody)
	{
		var self = this; // To be able to access it from within handlers
		var more_rows = data.count - tbody.children().length;
		if (more_rows > this.config.per_page) {
			more_rows = this.config.per_page;
		}
		if (more_rows > 0) {
			var loadcell = $('<td/>');
			var loadrow = $('<tr class="table_pagination" />')
			loadrow.append(loadcell)
			tbody.append(loadrow);
			
			loadcell.attr('colspan', columns.length);
			loadcell.append($('<a id="load_more" href="#">'
					+ _('Load ' + more_rows + ' more rows') + '</a>'));
			loadcell.click(function(ev)
			{
				ev.preventDefault();
				var loadingcell = $('<td>Loading...</td>');
				loadingcell.attr('colspan', columns.length);
				loadrow.empty().append(loadingcell);
				self.send_request({
					append: true,
					callback: function(result)
					{
						loadrow.remove();
						self.insert_rows(columns, result, tbody);
						self.add_fill_bar(columns, result, tbody);
						self.refresh_multi_select(tbody);
					},
					increment_items_in_view: true
				});
			});
		}
		/*
		 * } else if ($('#load_more').length && tbody.find('tr').length >=
		 * data.data.length) { $('#load_more').remove(); }
		 */
	}

	this.render_table = function(data, sort_col, sort_asc)
	{
		var self = this; // To be able to access it from within handlers
		
		listview_table_col_index = 0;
		listview_last_host = '';
		
		if (data.length == 0) { return $('<h2 class="lsfilter-noresult">'
				+ _('Empty result set') + '</h2>'); }
		
		/*
		 * Render table
		 */

		var table = $('<table cellspacing="0" cellpadding="0" border="0" />');
		var thead = $('<thead />');
		var tbody = $('<tbody />');
		table.append(thead);
		table.append(tbody);
		
		/*
		 * Render table header
		 */

		var columns = new Array();
		var header = $('<tr />');
		
		for ( var key in listview_renderer_table[data.table]) {
			var col_render = listview_renderer_table[data.table][key];
			
			/*
			 * Check if column is avalible in current view.
			 */
			if (col_render.avalible) {
				if (!col_render.avalible({})) {
					continue;
				}
			}
			
			columns.push(col_render.cell);
			
			var th = $('<th />');
			// .attr('id', listview_table_col_name(col_render.header));
			th.append(col_render.header);
			
			if (col_render.sort) {
				var sort_dir = 0;
				if (sort_col == key) sort_dir = -1;
				if (sort_asc) sort_dir = -sort_dir;
				this.add_sort(data.table, th, key, sort_dir);
			}
			header.append(th);
		}
		thead.append(header);
		
		this.insert_rows(columns, data, tbody);
		this.add_fill_bar(columns, data, tbody);
		this.refresh_multi_select(tbody);
		
		/*
		 * table.find('[id^=listview-col-]').hover( function () { var index =
		 * $(this).attr('id').split('-col-')[1]; table.find('.listview-cell-' +
		 * index).addClass('listview-col-hover'); }, function () { var index =
		 * $(this).attr('id').split('-col-')[1]; table.find('.listview-cell-' +
		 * index).removeClass('listview-col-hover'); } );
		 */
		/*
		 * var header = $("thead", table), clone = header.clone(true);
		 * header.after(clone);
		 */

		return table;
	};
	
	this.add_sort = function(table, element, vis_column, current)
	{
		var self = this; // To be able to access it from within handlers
		
		if (current == 0) { // No sort
		
			element
					.prepend($('<span class="lsfilter-sort-span">&sdot;</span>'));
		}
		else if (current > 0) { // Ascending?
			element.attr('title', 'Sort descending');
			element
					.prepend($('<span class="lsfilter-sort-span">&darr;</span>'));
		}
		else {
			element.attr('title', 'Sort ascending');
			element
					.prepend($('<span class="lsfilter-sort-span">&uarr;</span>'));
		}
		element.click({
			table: table,
			vis_column: vis_column
		}, function(evt)
		{
			self.set_sort(evt.data.table, evt.data.vis_column);
		});
	};
	
	this.refresh_multi_select = function(baseelem)
	{
		baseelem.find('.listview_multiselect_checkbox').createCheckboxRange();
	};
	
	this.attach_header = function(table)
	{
		if (!this.config.attach_head) return;
		var header = $("thead", table);
		var clone = header.clone(true);
		header.after(clone);
		var update_float_header = function()
		{
			table.each(function()
			{
				
				var el = $(this);
				var offset = el.offset();
				var scrollTop = $(window).scrollTop();
				
				if (scrollTop >= 0) {
					
					var head = header.find("tr").children();
					var cloneHead = clone.find("tr").children();
					var index = 0;
					
					clone.css('min-width', header.width());
					
					head.each(function()
					{
						
						if ($.browser.webkit) {
							$(cloneHead[index]).css(
									'width',
									(parseInt($(this).css('width'), 10) + 1)
											+ 'px');
						}
						else {
							$(cloneHead[index]).css('width',
									$(this).css('width'));
						}
						
						$(cloneHead[index]).css('padding',
								$(this).css('padding')).css('margin',
								$(this).css('margin')).css('border',
								$(this).css('border'));
						index++;
					});
					
					clone.addClass('floating-header');
					clone.css('visibility', 'visible');
					
				}
				
			});
		}
		$(window).resize(update_float_header).scroll(update_float_header)
				.trigger("scroll");
	}
}

function lsfilter_list(config)
{
	// Configuration
	this.defaults = {
		per_page: 100,
		autorefresh_delay: 30000,
		autorefresh_enabled: true,
		request_url: _site_domain + _index_page + "/" + _controller_name
				+ "/fetch_ajax",
		columns: false,
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
		
		this.table_desc = new lsfilter_list_table_desc(this.request_metadata, this.config.columns);
		
		this.sort_vis_column = false;
		this.sort_db_columns = [];
		this.sort_ascending = true;
		
		var order_parts = [];
		if (data.order) {
			order_parts = data.order.split(' ');
		}
		if (order_parts.length >= 1 && order_parts.length <= 2) {
			
			this.sort_vis_column = order_parts[0];
			
			this.sort_db_columns = this.table_desc.sort_cols(this.sort_vis_column);
		}
		if (order_parts.length == 2) {
			this.sort_ascending = (order_parts[1].toLowerCase() == 'asc');
		}
		
		this.send_request({
			append: false,
			success: function(data)
			{
				self.handle_ajax_response(data);
			},
			error: function(data)
			{
				self.handle_ajax_error_response(data);
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
			this.sort_db_columns = this.table_desc.sort_cols(vis_column);
			this.sort_vis_column = vis_column;
			this.sort_ascending = true;
		}
		
		if( typeof lsfilter_main != "undefined" )
			lsfilter_main.update(false, 'list', this.sort_vis_column
				+ (this.sort_ascending ? ' asc' : ' desc'));
		
		this.send_request({
			append: false,
			success: function(data)
			{
				self.handle_ajax_response(data);
			},
			error: function(data)
			{
				self.handle_ajax_error_response(data);
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
		var options = $.extend(
			{
				error: function() {},
				success: function() {},
				complete: function() {}
			},
			self.config,
			config
		);
		this.active_ajax_request = $
				.ajax({
					url: this.config.request_url,
					dataType: 'json',
					data: {
						"query": this.request_query,
						"sort": db_sort_columns,
						"columns": this.table_desc.db_columns,
						"limit": options.per_page,
						"offset": options.offset
					},
					success: function(data)
					{
						options.success(data);
					},
					error: function(data)
					{
						options.error('Error reloading');
					},
					complete: function(data)
					{
						options.complete(data);
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
	this.visible_count = 0;
	
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
		var self = this; // To be able to access it from within handlers

		this.send_request({
			append: false,
			offset: 0,
			per_page: this.config.offset + this.config.per_page,
			success: function(data)
			{
				// Don't drop first host line in auto refresh...
				self.previous_obj = {};
				
				self.handle_ajax_response(data);
			},
			error: function(data)
			{
				// Don't drop first host line in auto refresh...
				self.previous_obj = {};

				self.handle_ajax_error_response(data);
			}
		});
	};
	
	this.start_autorefresh_timer = function() {
		var self = this; // To be able to access it from within handlers
		if( this.config.autorefresh_enabled == false )
			return;
		
		if(this.autorefresh_timer != false ) {
			clearTimeout( this.autorefresh_timer );
		}
		this.autorefresh_timer = setTimeout(function() {
			self.handle_autorefresh()
		}, this.config.autorefresh_delay);
	}
	
	this.handle_ajax_response = function(data)
	{
		var new_table;
		var new_totals = $('<span />');
		if(data.data.length) {
			new_table = this.render_table(data, this.sort_vis_column, this.sort_ascending);
		} else {
			var empty_text;
			if(!(empty_text = $(this.config.table).parents('.widget').data('text-if-empty'))) {
				empty_text = _("Nothing found for the filter '"+this.request_query+"'");
			}
			new_table = $('<div class="alert"></div>').text(empty_text);
		}
		new_totals = this.render_totals(data.table, data.totals);

		if (this.config.table) {
			this.config.table.find('*').unbind();
			this.config.table.empty().append(new_table);
			this.attach_header(new_table);
		}
		if (this.config.totals) this.config.totals.empty().append(new_totals);

		this.start_autorefresh_timer();
	};

	this.handle_ajax_error_response = function(data)
	{
		var alert = $('<div class="alert error" />');
		alert.html("<strong>Error:</strong> " + data.data);
		
		if (this.config.table) {
			this.config.table.find('*').unbind();
			this.config.table.empty().append(alert);
		}
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
					var item = listview_renderer_totals[field](totals[field][1])
						.css('float', 'left').wrapInner(
							link_query(totals[field][0]).addClass('no_uline')
						);
					item.find('a').attr('title', item.find('span').attr('title'));
					container.append(item);
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
		if( columns.length <= 0 ) {
			/* Don't bother then...*/
			return;
		}
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
					complete: function(xhr)
					{
						// let's just pass on some json
						var result = JSON.parse(xhr.responseText);
						self.start_autorefresh_timer();
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
		
		for ( var key=0; key<this.table_desc.vis_columns.length; key++ ) {
			var col_name = this.table_desc.vis_columns[key];
			var col_render = this.table_desc.col_renderers[col_name];
			
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
				if (sort_col == col_name) sort_dir = -1;
				if (sort_asc) sort_dir = -sort_dir;
				this.add_sort(data.table, th, col_name, sort_dir);
			}
			header.append(th);
		}
		thead.append(header);
		
		this.insert_rows(columns, data, tbody);
		this.add_fill_bar(columns, data, tbody);
		this.refresh_multi_select(tbody);
		
		return table;
	};
	
	this.add_sort = function(table, element, vis_column, current)
	{
		var self = this; // To be able to access it from within handlers
		
		if (current == 0) { // No sort
		
			element
					.prepend($('<span class="lsfilter-sort-span">&nbsp;</span>'));
		}
		else if (current > 0) { // Ascending?
			element.attr('title', 'Sort descending');
			element
					.prepend($('<span class="lsfilter-sort-span" />').append(
							$('<img />').attr('src',_site_domain+'application/views/icons/arrow-down.png')
							));
		}
		else {
			element.attr('title', 'Sort ascending');
			element
					.prepend($('<span class="lsfilter-sort-span" />').append(
							$('<img />').attr('src',_site_domain+'application/views/icons/arrow-up.png')
							));
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

						$(cloneHead[index]).css('padding-left', $(this).css('padding-left'));
						$(cloneHead[index]).css('padding-right', $(this).css('padding-right'));
						$(cloneHead[index]).css('padding-top', $(this).css('padding-top'));
						$(cloneHead[index]).css('padding-bottom', $(this).css('padding-bottom'));
						$(cloneHead[index]).css('margin', $(this).css('margin'));
						$(cloneHead[index]).css('border', $(this).css('border'));

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

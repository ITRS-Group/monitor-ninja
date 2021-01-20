function lsfilter_list(config)
{
	// Configuration
	this.defaults = {
		per_page: 100,
		autorefresh_delay: 30000,
		autorefresh_enabled: true,
		request_url: _site_domain + _index_page + "/" + _controller_name + "/fetch_ajax",
		columns: false,
		toolbar: false,
		attach_head: false,
		notify: false,
		loading_start: function()
		{
		},
		loading_stop: function()
		{
		}
	};
	this.config = $.extend({}, this.defaults, config);

	if (isNaN(this.config.autorefresh_delay)) {
		// Invalid autorefresh_delay defaults to 30 seconds
		this.config.autorefresh_delay = 30000;
	} else if (this.config.autorefresh_delay === 0) {
		// A delay of 0 means that refresh is disabled.
		this.config.autorefresh_enabled = false;
	}

	if(browser.msie) {
		var parts = browser.version.split('.');
		if( parseInt(parts[0], 10) < 8 ) {
			this.config.attach_head = false; /* Don't support attached head in ie7 */
		}
	}

	if(this.config.table)
		lsfilter_list_attach_events( this, this.config.table );
	if(this.config.totals)
		lsfilter_list_attach_events( this, this.config.totals );

	/* Bind this once and for all globally... Needed in lot of callbacks */
	var self = this;

	if( this.config.attach_head ) {
		$(window).on('resize scroll load', function(e) {
			self.update_float_header();
		});
	}
	/***************************************************************************
	 * External methods
	 **************************************************************************/
	this.on = {
		'update_ok': function(data) {
			var metadata = data.metadata;

			if (data.source && data.source == 'list') { return; }

			if (!metadata) {
				var parser = new LSFilter(new LSFilterPreprocessor(),
						new LSFilterMetadataVisitor());
				metadata = parser.parse(data.query);
			}

			if (self.request_metadata && self.request_metadata !== metadata) {
				// we're switching from one type of view to another,
				// reset unwanted state
				self.config.offset = 0;
				self.previous_obj = {};
			}

			self.request_query = data.query;
			self.request_metadata = metadata;

			self.table_desc = new lsfilter_list_table_desc(self.request_metadata, self.config.columns);

			self.sort_vis_column = false;
			self.sort_db_columns = [];
			self.sort_ascending = true;

			var order_parts = [];
			if (data.order) {
				order_parts = data.order.split(' ');
			}
			if (order_parts.length >= 1 && order_parts.length <= 2) {

				self.sort_vis_column = order_parts[0];

				self.sort_db_columns = self.table_desc.sort_cols(self.sort_vis_column);
			}
			if (order_parts.length == 2) {
				self.sort_ascending = (order_parts[1].toLowerCase() == 'asc');
			}

			self.send_request({});
		}
	};

	this.set_sort = function(vis_column)
	{
		this.config.offset = 0;
		this.previous_obj = {};

		if (this.sort_vis_column == vis_column) {
			this.sort_ascending = !this.sort_ascending;
		}
		else {
			this.sort_db_columns = this.table_desc.sort_cols(vis_column);
			this.sort_vis_column = vis_column;
			this.sort_ascending = true;
		}

		if( typeof lsfilter_main != "undefined" )
			lsfilter_main.update(false, 'list', this.sort_vis_column + (this.sort_ascending ? ' asc' : ' desc'));

		this.send_request({});
	};

	this.send_request = function(config)
	{
		if (typeof config.increment_items_in_view !== "undefined" && Boolean(config.increment_items_in_view)) {
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
				success: false
			},
			this.config,
			config
		);
		this.active_ajax_request = $
				.ajax({
					url: this.config.request_url,
					dataType: 'json',
					type: "POST",
					data: {
						"query": this.request_query,
						"sort": db_sort_columns,
						"columns": this.table_desc.db_columns,
						"limit": options.per_page,
						"offset": options.offset,
						"csrf_token": _csrf_token
					},
					success: function(data)
					{
						if(data == null) {
							self.handle_ajax_error_response({data:_("No valid response from server. Still logged in?")});
						} else if(options.success) {
							options.success(data);
						} else {
							self.handle_ajax_response(data);
						}
					},
					error: function(data, text_status)
					{
						if(!data.getAllResponseHeaders() || text_status === "abort") {
							// getAllResponseHeaders() provided by http://ilikestuffblog.com/2009/11/30/how-to-distinguish-a-user-aborted-ajax-call-from-an-error/
							// since my tests of === "abort" all are negative, keeping it if jQuery decides to follow its API sometime..


							// only continue to display error message if
							// it's an actual error, not just multiple requests
							// stacked or something silly like that
							return;
						}
						var message;
						try {
							message = JSON.parse(data.responseText);
						} catch(not_json) {
							message = _('Error reloading');
						}
						self.handle_ajax_error_response(message);
					},
					complete: function(data)
					{
						self.active_ajax_request = false;
						self.config.loading_stop(loading_id);
						self.banner_in_listview();
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

	this.current_columns = [];

	this.active_ajax_request = false;

	this.sort_vis_column = null;
	this.sort_db_columns = [];
	this.sort_ascending = true;
	this.sort_columns_table = {};

	this.autorefresh_timer = false;

	/***************************************************************************
	 * Internal methods
	 **************************************************************************/


	this.update_float_header = function()
	{

		var thead =  $(this.config.table).find('thead');
		var header = $(thead).filter(function(){return !$(this).hasClass('floating-header');});
		var clone = $(thead).filter(function(){return $(this).hasClass('floating-header');});

		if( !header || !clone ) return;

		var header_div = $("body .container #header"),
			head = header.find("tr").children(),
			cloneHead = clone.find("tr").children(),
			index = 0;

		clone.css( 'min-width', header.outerWidth() );
		clone.css( 'top', header_div.outerHeight() + "px" );

		head.each(function() {

			var clonehead = $(cloneHead[index]);
			var thishead = $(this);

			var w = parseInt( thishead.width(), 10 ) + 1;

			index++;

			clonehead.css( 'width', w + 'px');

			clonehead.css('padding-right', thishead.css('padding-right'));
			clonehead.css('padding-top', thishead.css('padding-top'));
			clonehead.css('padding-bottom', thishead.css('padding-bottom'));
			clonehead.css('margin', thishead.css('margin'));
			clonehead.css('border', thishead.css('border'));
		});

		// Get scrollbar height and width
		var scroll = this.get_browser_scroll_size();
		$('#align_th').remove();
		if ($('.content').get(0).scrollHeight > $('.content').height()) {
			/*
			 *	Append a th to the floating header
			 *	Give it a width that matches the scrollbars width
			 *	It's not a very nice solution but at least we
			 *	don't have to rewrite the floating header at this time
			*/
			clone.find("tr")
				.append(
					$("<th>")
						.attr('id', 'align_th')
						.css('width', (scroll.width - 4) + 'px')
						.css('padding-right', 0)
				);
		}
		this.banner_in_listview();
	};

	this.update_nagbar = function(messages)
	{
		var i;
		if(this.config.notify) {
			this.config.notify.clear('nagbar');
			for(i=0;i<messages.length;i++) {
				if (typeof(messages[i]) === 'object') {
					this.config.notify.message(messages[i].message, {
						type: messages[i].type,
						nag: true
					});
				} else {
					this.config.notify.message(messages[i], {
						type: 'error',
						nag: true
					});
				}
			}
			$(window).trigger('resize');
		}
	}

	this.get_browser_scroll_size = function()
	{
		var css = {
			"border":  "none",
			"height":  "200px",
			"margin":  "0",
			"padding": "0",
			"width":   "200px"
		};

		var inner = $("<div>").css($.extend({}, css));
		var outer = $("<div>").css($.extend({
			"left":       "-2000px",
			"overflow":   "scroll",
			"position":   "absolute",
			"top":        "-2000px"
		}, css)).append(inner).appendTo("body")
		.scrollLeft(2000)
		.scrollTop(2000);

		var scrollSize = {
			"height": (outer.offset().top - inner.offset().top) || 0,
			"width": (outer.offset().left - inner.offset().left) || 0
		};

		outer.remove();
		return scrollSize;
	};

	this.handle_autorefresh = function()
	{
		this.send_request({
			offset: 0,
			per_page: this.config.offset + this.config.per_page
		});
	};

	this.start_autorefresh_timer = function() {
		if( this.config.autorefresh_enabled === false || isNaN(this.config.autorefresh_delay) || this.config.autorefresh_delay < 1 )
			return;

		if(this.autorefresh_timer !== false ) {
			clearTimeout( this.autorefresh_timer );
		}
		this.autorefresh_timer = setTimeout(function() {
			self.handle_autorefresh();
		}, this.config.autorefresh_delay);
	};

	this.handle_ajax_response = function(data)
	{
		var new_table;
		var new_totals = $('<span />');

		if(data.messages) {
			self.update_nagbar(data.messages);
		}

		if(data.data.length) {
			new_table = this.render_table(data, this.sort_vis_column, this.sort_ascending);
		} else {
			var empty_text;
			if(!(empty_text = $(this.config.table).parents('.widget').data('text-if-empty'))) {
				empty_text = _("No entries found using filter");
			}
			new_table = $('<div class="alert notice"></div>').text(empty_text);
		}
		new_totals = this.render_totals(data.table, data.totals);

		if(this.config.toolbar) {
			this.config.toolbar.find('.toolbar-buttons').replaceContent(
				$('<ul/>').append(
					$.map([$('<li id="filter_loading_status"/>')].concat(
							listview_renderer_buttons[data.table] || []
					).concat(
								listview_renderer_buttons.all || []
					),
						function(x) {
							if (typeof x == 'function') {
								x = x();
							}
							return $('<li class="filter-query-button"/>').html(x).toArray();
			})));
		}

		if (this.config.totals) {
			this.config.totals.replaceContent(new_totals);
		}
		if (this.config.table) {
			this.config.table.replaceContent(new_table);
			this.attach_header();
		}

		this.start_autorefresh_timer();
	};

	this.handle_ajax_error_response = function(data)
	{

		var alert = $('<div class="alert error" />');
		if (typeof(data.data) === 'undefined') {
			alert.html("Service Unavailable, you may attempt to refresh your browser");
		} else {
			alert.html("<strong>Error:</strong> " + data.data);
		}

		if (this.config.table) {
			this.config.table.replaceContent(alert);
		}
		this.start_autorefresh_timer();
	};

	this.render_totals = function(table, totals)
	{

		var subtitle = null;
		if ( this.config.toolbar ) {
			subtitle = this.config.toolbar.find( '.main-toolbar-subtitle' );
		}

		var container = $('<ul />');

		if ( subtitle ) {
			subtitle.html("").append( link_query('['+table+'] all')
				.text(table.charAt(0).toUpperCase() + table.slice(1))
				.css( "border", "none" )
			);
		}

		if (totals) {
			for ( var field in listview_renderer_totals) {
				if (field in totals) {

					var item = listview_renderer_totals[field](totals[field][1])
						.wrapInner(
							link_query(totals[field][0])
						);

					/* If the label is the same as the toolbar sub-header, remove it */
					if ( item.hasClass( "extra_toolbar_category" ) ) {
						var label = item.find( 'a[data-query="[' + table + '] all"]' );
						if ( label.length > 0 ) {
							var replacer = table.charAt(0).toUpperCase() + table.slice(1) + ":";
							label.html( label.html().replace( replacer, "" ) );
						}
					}

					item.find('a').attr('title', item.find('span').attr('title'));
					container.append(item);

				}
			}
		}
		return container;
	};

	this.insert_rows = function(data, tbody)
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

			for ( var cur_col = 0; cur_col < this.current_columns.length; cur_col++) {
				row.append(this.current_columns[cur_col]({
					obj: obj,
					last_obj: this.previous_obj,
					row: row,
					listview: this
				}));
				// .addClass( 'listview-cell-' + cur_col));
			}
			row.addClass('listview_row');

			tbody.append(row);
			this.previous_obj = obj;
		}
	};

	this.add_fill_bar = function(data, tbody)
	{
		if( this.current_columns.length <= 0 ) {
			/* Don't bother then...*/
			return;
		}

		var more_rows = data.count - tbody.children().length;
		if (more_rows > this.config.per_page) {
			more_rows = this.config.per_page;
		}
		if (more_rows > 0) {
			var loadcell = $('<td/>');
			var loadrow = $('<tr class="table_pagination" />');
			loadrow.append(loadcell);
			tbody.append(loadrow);

			loadcell.attr('colspan', this.current_columns.length);
			loadcell.append($('<a id="load_more" href="#">' + _('Load ' + more_rows + ' more rows') + '</a>'));
			loadcell.addClass('link_load_more_rows');
		}
	};

	this.load_more_rows = function(loadingcell)
	{
		loadingcell.removeClass('link_load_more_rows');
		loadingcell.text('Loading...');

		var loadrow = loadingcell.parent('tr');
		var tbody = loadrow.parent('tbody');

		this.send_request({
			success: function(data)
			{
				loadrow.remove();
				self.insert_rows(data, tbody);
				self.add_fill_bar(data, tbody);
			},
			increment_items_in_view: true
		});
	};

	this.render_table = function(data, sort_col, sort_asc)
	{
		listview_table_col_index = 0;
		listview_last_host = '';

		if (data.length === 0) {
			return $('<h2 class="lsfilter-noresult">' + _('Empty result set') + '</h2>');
		}

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

		this.current_columns = [];
		var header = $('<tr />');

		for ( var key=0; key<this.table_desc.vis_columns.length; key++ ) {
			var col_name = this.table_desc.vis_columns[key];
			var col_render = this.table_desc.col_renderers[col_name];

			/*
			 * Check if column is available in current view.
			 */
			if (col_render.available) {
				if (!col_render.available({table:this.table_desc.metadata.table})) {
					continue;
				}
			}

			this.current_columns.push(col_render.cell);

			var th = $('<th />');

			if (col_render.sort) {
				var sort_dir = 0;
				if (sort_col == col_name) sort_dir = -1;
				if (sort_asc) sort_dir = -sort_dir;
				this.add_sort(th, col_name, sort_dir);
			}

			if (typeof(col_render.header) === 'function') {
				th.append(col_render.header());
			} else {
				th.append(col_render.header);
			}
			header.append(th);
		}
		thead.append(header);

		this.previous_obj = {};
		this.insert_rows(data, tbody);
		this.add_fill_bar(data, tbody);

		return table;
	};

	this.add_sort = function(element, vis_column, current)
	{
		var img = _site_domain+'application/views/icons/arrow-up-down.png';
		element.attr('title', 'Sort ascending');
		if (current > 0) {
			element.attr('title', 'Sort descending');
			img = _site_domain+'application/views/icons/arrow-up.png';
			element.addClass('current');
		}
		else if(current < 0) {
			img = _site_domain+'application/views/icons/arrow-down.png';
			element.addClass('current');
		}

		element.addClass('sortable');
		element.addClass('link_set_sort');

		element.attr('data-column', vis_column );

		element.append($('<span class="lsfilter-sort-span" />').css('background-image', 'url('+img+')'));
	};

	this.attach_header = function()
	{
		if (!this.config.attach_head) return;

		var thead =  $(this.config.table).find('thead');
		var header = $(thead).filter(function(){return !$(this).hasClass('floating-header');});
		var clone = header.clone(true);
		header.after(clone);

		clone.addClass('floating-header');
		header.find('input').remove();

		this.update_float_header();
	};

	this.banner_in_listview = function()
	{
		var banner = $('div#nachos-page-banners');
		if (banner.length > 0) {
			var thead = $(this.config.table).find('thead');
			var fixed_thead = $(thead).filter(function(){return !$(this).hasClass('floating-header');});
			if (thead.length > fixed_thead.length) {
				banner.css('margin', '25px 0 3px 0');
				fixed_thead.remove();
			}
		}
	}
}

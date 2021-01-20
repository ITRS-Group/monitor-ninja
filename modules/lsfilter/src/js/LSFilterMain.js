var lsfilter_storage = {};

var lsfilter_main = {

	update_delay : 300,

	listeners: [],

	// Modules that listens for events, for example an instance of
	// "lsfilter_textarea"
	add_listener: function(listener) {
		this.listeners.push(listener);
	},

	/***************************************************************************
	 * Trigger update of ListView
	 **************************************************************************/
	// Proxy for update()
	update_delayed : function(query, source, order) {
		var self = this; // To be able to access it from within handlers
		if (this.update_timer) {
			clearTimeout(this.update_timer);
		}
		this.update_timer = setTimeout(function() {
			self.update(query, source, order);
		}, this.update_delay);
	},

	update : function(query, source, order) {
		if(this.update_query &&
			$.trim(query) === $.trim(this.update_query) &&
			$.trim(order) === $.trim(this.update_order)
			) {
			// don't issue multiple requests for the same query,
			// use refresh() if you want a brand new result set
			// of the current query
			return;
		}

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

	emit: function(signal) {
		var args = Array.prototype.slice.call(arguments);
		// remove the named 'signal' argument
		args.splice(0, 1);
		for(var i = 0; i < this.listeners.length; i++) {
			var listener = this.listeners[i];
			if(typeof listener['on'] === "undefined") {
				continue;
			}
			if(typeof listener['on'][signal] === "function") {
				// Using "apply" makes sure that callbacks
				// can refer to their own instance as "this"
				listener['on'][signal].apply(listener, args);
			}
		}
	},

	// Private method, run either update(), update_delayed() or refresh()
	update_run : function() {
		var source = this.update_source;
		var query = this.update_query;
		var order = this.update_order;

		var header_height = $( "body > .container >#header" ).outerHeight() + 4;

		this.update_timer = false;

		if (query) {
			this.state.query = query;
		}
		if (typeof order == 'string') {
			this.state.order = order;
		}

		var parser = new LSFilter(new LSFilterPreprocessor(),
				new LSFilterMetadataVisitor());
		try {
			var metadata = parser.parse(this.state.query);
			//Validate custom variables filter query format
			if(metadata['columns'].indexOf('custom_variables') > -1) {
				var custom_variables_values = this.state.query.split('custom_variables')[1].split('"')[1];
				var valid = new RegExp("[\\w]+\\s[\\w.]+$");
				if(!valid.test(custom_variables_values)) {
					this.set_parse_status("Error: Invalid query, custom variables format will be 'name value', Ex: 'NOMONITORING value'");
					$('#filter-query-builder').show();
					this.emit('update_failed');
					return;
				}
			}
		} catch (ex) {
			console.log(ex);
			this.set_parse_status(ex);
			$('#filter-query-builder').show();
			this.emit('update_failed');
			return;
		}

		var data = $.extend({
			source : source,
			metadata : metadata
		}, this.state);

		this.clear_parse_status();

		$('#filter-query-builder').css( "top", header_height + "px" );

		$('#extra-dropdowns').replaceContent(
			$.map((listview_renderer_extra_objects[metadata.table] || []).concat(listview_renderer_extra_objects_all || []), function(x) {
				if (typeof x == 'function') {
					x = x();
				}
				x.addClass('filter-query-dropdown');
				x.css( "top", header_height + "px" );
				return x.toArray();
			}));
		this.emit('update_ok', data);
	},
	/***************************************************************************
	 * Initialize ListView
	 **************************************************************************/
	init : function() {
		// Add all components that make up the listview. All of them
		// receive events when the global state is updated (e.g. the
		// textarea is notified whenever a change occurs in the
		// graphical filter builder).
		this.update_page_links();

		lsfilter_history.init();
		this.add_listener(lsfilter_history);

		lsfilter_storage.list = new lsfilter_list({
			autorefresh_delay : _lv_refresh_delay * 1000,
			table : $('#filter_result'),
			toolbar: $( '.main-toolbar' ),
			totals : $('#filter_result_totals'),
			attach_head : true,
			notify : Notify,
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
		this.add_listener(lsfilter_storage.list);

		lsfilter_textarea.init($('#filter_query'), $('#filter_query_order'));
		this.add_listener(lsfilter_textarea);

		lsfilter_saved.init();
		this.add_listener(lsfilter_saved);

		lsfilter_visual.init($('#filter_visual'));
		this.add_listener(lsfilter_visual);

		this.update(lsfilter_query, "Sweet Sweetback's Baadasssss Song", lsfilter_query_order);
		/**
		 * Closes the filter builder IF
		 * 1. User clicks the "Close filter builder" button
		 * 2. User clicks outside of the filter builder
		 * 3. User presses ESC
		 */
		var filterBuilder = $('#filter-query-builder');
		$('#close-filter-builder').on({
			click: function() {
				// Button clicked
				filterBuilder.hide();
			}
		});
		$(document).on({
			click: function(e) {
				if ($(e.target).closest(filterBuilder).length === 0) {
					// Clicked outside of the filter builder
					$(filterBuilder).hide();
				}
			},
			keyup: function(e) {
				if (e.keyCode === 27) {
					// Pressed ESC
					$(filterBuilder).hide();
				}
			}
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
				'a[href^="' + _site_domain + _index_page + '/' + _controller_name + '"]').on('click', function(evt) {
			var href = $(this).attr('href');
			var args = self.parseParams(href);
			if (args.q) {

				lsfilter_main.update(args.q, 'external_link', '');

				var content_div = $( "body > .container > #content" )
				content_div.on('click');
				content_div.trigger('focus');

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

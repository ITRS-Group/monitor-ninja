var listview_ajax_timer = null;
var listview_ajax_query = "";
var listview_ajax_active_request = null;

var listview_sort_vis_column = null;
var listview_sort_db_columns = [];
var listview_sort_ascending = true;

var listview_autorefresh_enabled = true;
var listview_autorefresh_timeout = 30000;
var listview_autorefresh_timer = false;

function listview_update_sort(vis_column, db_columns) {
	if (listview_sort_vis_column != vis_column) {
		listview_sort_ascending = true;
	} else {
		listview_sort_ascending = !listview_sort_ascending;
	}
	listview_sort_vis_column = vis_column;
	listview_sort_db_columns = db_columns;
	listview_refresh();
}

function listview_columns_for_table(table) {
	if (!listview_renderer_table[table])
		return false;

	var renderer = listview_renderer_table[table];
	var columns = [];
	var columns_dict = {};

	for ( var tblcol in renderer) {
		var deps = renderer[tblcol].depends;
		for ( var i = 0; i < deps.length; i++) {
			if (!columns_dict[deps[i]]) {
				columns.push(deps[i]);
				columns_dict[deps[i]] = true;
			}
		}
	}

	return columns;
}

var LSFilterMetadataVisitor = function LSFilterMetadataVisitor(){
	// entry: program := * query end
	this.visit_entry = function(query0) {
		return query0;
	};
	
	// query: query := * brace_l table_def brace_r search_query
	this.visit_query = function(table_def1, search_query3) {
		return table_def1;
	};
	
	// table_def_simple: table_def := * name
	this.visit_table_def_simple = function(name0) {
		return {'table':name0};
	};
	
	// search_query: search_query := * filter
	this.visit_search_query = function(filter0) {
	};
	
	// filter_or: filter := * filter or filter2
	this.visit_filter_or = function(filter0, filter2) {
	};
	
	// filter_and: filter2 := * filter2 and filter3
	this.visit_filter_and = function(filter0, filter2) {
	};
	
	// filter_not: filter3 := * not filter4
	this.visit_filter_not = function(filter1) {
	};
	
	// filter_ok: filter4 := * match
	this.visit_filter_ok = function(match0) {
	};
	
	// match_in: match := * in set_descr
	this.visit_match_in = function(set_descr1) {
	};
	
	// match_field_in: match := * field in set_descr
	this.visit_match_field_in = function(field0, set_descr2) {
	};
	
	// match_not_re_ci: match := * field not_re_ci arg_string
	this.visit_match_not_re_ci = function(field0, arg_string2) {
	};
	
	// match_not_re_cs: match := * field not_re_cs arg_string
	this.visit_match_not_re_cs = function(field0, arg_string2) {
	};
	
	// match_re_ci: match := * field re_ci arg_string
	this.visit_match_re_ci = function(field0, arg_string2) {
	};
	
	// match_re_cs: match := * field re_cs arg_string
	this.visit_match_re_cs = function(field0, arg_string2) {
	};
	
	// match_not_eq_ci: match := * field not_eq_ci arg_string
	this.visit_match_not_eq_ci = function(field0, arg_string2) {
	};
	
	// match_eq_ci: match := * field eq_ci arg_string
	this.visit_match_eq_ci = function(field0, arg_string2) {
	};
	
	// match_not_eq: match := * field not_eq arg_num_string
	this.visit_match_not_eq = function(field0, arg_num_string2) {
	};
	
	// match_gt_eq: match := * field gt_eq arg_num
	this.visit_match_gt_eq = function(field0, arg_num2) {
	};
	
	// match_lt_eq: match := * field lt_eq arg_num
	this.visit_match_lt_eq = function(field0, arg_num2) {
	};
	
	// match_gt: match := * field gt arg_num
	this.visit_match_gt = function(field0, arg_num2) {
	};
	
	// match_lt: match := * field lt arg_num
	this.visit_match_lt = function(field0, arg_num2) {
	};
	
	// match_eq: match := * field eq arg_num_string
	this.visit_match_eq = function(field0, arg_num_string2) {
	};
	
	// set_descr_name: set_descr := * string
	this.visit_set_descr_name = function(string0) {
	};
	
	// field_name: field := * name
	this.visit_field_name = function(name0) {
	};
	
	// field_obj: field := * name dot field
	this.visit_field_obj = function(name0, field2) {
	};
	
	this.accept = function(result) {
		return result;
	};
	
};

var listview_autorefresh_handler = function listview_autorefresh_handler() {
	listview_autorefresh_timer = false;
	listview_refresh();
}



function listview_do_request() {
	if( listview_autorefresh_timer ) {
		clearTimeout( listview_autorefresh_timer );
	}
	
	console.log("Query: " + listview_ajax_query);

	var parser = new LSFilter(new LSFilterPreprocessor(), new LSFilterMetadataVisitor());
	var metadata = parser.parse(listview_ajax_query);
	
	console.log(metadata);

	listview_render_start_loading();
	if (listview_ajax_active_request != null) {
		listview_ajax_active_request.abort();
	}

	var loader = $('<span class="lsfilter-loader" />').append($('<span>Loading...</span>'));

	console.log("Query: " + listview_ajax_query);
	
	listview_render_start_loading(loader);
	
	listview_ajax_active_request = $.ajax({
		url : _site_domain + _index_page + "/" + _controller_name
				+ "/fetch_ajax",
		dataType : 'json',
		data : {
			"query" : listview_ajax_query,
			"sort" : listview_sort_db_columns,
			"sort_asc" : (listview_sort_ascending?1:0),
			"columns" : listview_columns_for_table(metadata['table']),
			"limit" : 100,
			"offset" : 0
		},
		success : function(data) {
			listview_render_stop_loading(loader);
			if (data.status == 'success') {
				listview_render_totals(data.totals);
				listview_render_table(data.data);
			}
			if (data.status == 'error') {
				$('#filter_result').empty().text("Error: " + data.data);
				$('#filter_result_totals').empty();
			}
			listview_autorefresh_timer = setTimeout( listview_autorefresh_handler, listview_autorefresh_timeout);
		}
	});
	
	return true;
}

function listview_refresh() {
	listview_do_request();
}

function listview_update(query) {
	if (listview_ajax_timer != null) {
		clearTimeout(listview_ajax_timer);
	}
	listview_ajax_query = query;
	listview_sort_vis_column = null;
	listview_sort_db_columns = [];
	listview_sort_ascending = true;
	listview_ajax_timer = setTimeout(function() {
		listview_refresh();
	}, 500);
}
var listview_ajax_timer = null;
var listview_ajax_query = "";
var listview_ajax_active_request = null;

var listview_sort_vis_column = null;
var listview_sort_db_columns = [];
var listview_sort_ascending = true;

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

function listview_do_request() {
	if (listview_ajax_active_request != null) {
		listview_ajax_active_request.abort();
	}
	console.log("Query: " + listview_ajax_query);
	listview_ajax_active_request = $.ajax({
		url : _site_domain + _index_page + "/" + _controller_name
				+ "/fetch_ajax",
		dataType : 'json',
		data : {
			"query" : listview_ajax_query,
			"sort" : listview_sort_db_columns,
			"sort_asc" : (listview_sort_ascending?1:0)
		},
		success : function(data) {
			if (data.status == 'success') {
				listview_render_totals(data.totals);
				listview_render_table(data.data);
			}
			if (data.status == 'error') {
				$('#filter_result').empty().text("Error: " + data.data);
			}
		}
	});
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
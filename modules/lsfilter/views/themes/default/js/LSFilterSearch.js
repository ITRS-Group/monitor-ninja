var listview_ajax_timer = null;
var listview_ajax_query = "";
var listview_ajax_active_request = null;

var listview_sort_vis_column = null;
var listview_sort_db_columns = null;

function listview_update_sort(vis_column, db_columns) {
	listview_sort_vis_column = vis_column;
	listview_sort_db_columns = db_columns;
	listview_do_request(listview_ajax_query,false);
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

function listview_do_request(query, columns) {
	if (listview_ajax_active_request != null) {
		listview_ajax_active_request.abort();
	}
	console.log("Query: " + query);
	listview_ajax_active_request = $.ajax({
		url : _site_domain + _index_page + "/" + _controller_name
				+ "/fetch_ajax",
		dataType : 'json',
		data : {
			"query" : query,
			"columns" : columns
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

function listview_update(query) {
	if (listview_ajax_timer != null) {
		clearTimeout(listview_ajax_timer);
	}
	listview_ajax_query = query;
	listview_ajax_timer = setTimeout(function() {
		listview_do_request(listview_ajax_query,
				listview_columns_for_table('hosts'));
	}, 500);
}
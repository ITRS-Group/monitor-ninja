var query_timer = null;
var query_string = "";
var current_request = null;

function link_to_sort(element,columns) {
	element.prepend($('<span style="float:right;">v</span>'));
	element.click({
		columns : columns
	}, function(evt) {
		alert("Sort by: " + evt.data.columns);
	});
}

function lsfilter_render_totals(totals) {
	var container = $('<ul />');
	if (totals) {
		for ( var field in lsfilter_totals_renderer) {
			if (field in totals) {
				container.append(lsfilter_totals_renderer[field](totals[field])
						.css('float', 'left'));
			}
		}
	}
	$('#filter_result_totals').empty().append(container);
}

function lsfilter_render_table(data) {
	var tbody = false;
	var last_table = '';
	var container = '';
	var columns = null;
	/*
	 * temporary offline container
	 */
	var output = $('<span />');

	console.log("Got " + data.length + " objects");
	if (data.length == 0) {
		output.append('<h2>Empty result set</h2>');
	} else {

		/*
		 * Render table
		 */
		for ( var i = 0; i < data.length; i++) {
			var obj = data[i];

			if (last_table != obj._table) {
				var table = $('<table />');
				output.append(table);
				console.log(fetch_column_list(obj._table));

				last_table = obj._table;
				columns = new Array();
				var header = $('<tr />');
				for ( var key in lsfilter_result_renderer[obj._table]) {
					var col_render = lsfilter_result_renderer[obj._table][key]
					columns.push(col_render.cell);
					var th = $('<th />');
					th.text(col_render.header);
					if (col_render.sort)
						link_to_sort(th,col_render.sort);
					header.append(th);
				}
				table.append($('<thead />').append(header));

				tbody = $('<tbody />');
				table.append(tbody);
			}

			var row = $('<tr />');
			if (i % 2 == 0)
				row.addClass('even');
			else
				row.addClass('odd');

			for ( var cur_col = 0; cur_col < columns.length; cur_col++) {
				row.append(columns[cur_col](obj));
			}
			tbody.append(row);
		}
	}
	$('#filter_result').empty().append(output);
}

function fetch_column_list(table) {
	if (!lsfilter_result_renderer[table])
		return false;

	var renderer = lsfilter_result_renderer[table];
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

function doAjaxSearch(query, columns) {
	if (current_request != null) {
		current_request.abort();
	}
	console.log("Query: " + query);
	current_request = $.ajax({
		url : _site_domain + _index_page + "/" + _controller_name
				+ "/fetch_ajax",
		dataType : 'json',
		data : {
			"query" : query,
			"columns" : columns
		},
		success : function(data) {
			if (data.status == 'success') {
				lsfilter_render_totals(data.totals);
				lsfilter_render_table(data.data);
			}
			if (data.status == 'error') {
				$('#filter_result').empty().text("Error: " + data.data);
			}
		}
	});
}

function sendAjaxSearch(query) {
	if (query_timer != null) {
		clearTimeout(query_timer);
	}
	query_string = query;
	query_timer = setTimeout(function() {
		doAjaxSearch(query_string, fetch_column_list('hosts'));
	}, 500);
}
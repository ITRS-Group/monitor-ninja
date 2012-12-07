var query_timer = null;
var query_string = "";
var current_request = null;

var render = {
	"hosts" : {
		"name" : {
			"header" : '<th>Name</th>',
			"cell" : function(obj) {
				return $('<td />').text(obj.name);
			}
		}
	},
	"services" : {
		"host_name" : {
			"header" : '<th>Host name</th>',
			"cell" : function(obj) {
				return $('<td />').text(obj.host.name);
			}
		},
		"description" : {
			"header" : '<th>Description</th>',
			"cell" : function(obj) {
				return $('<td />').text(obj.description);
			}
		}
	}
};

var doAjaxSearch = function() {
	if (current_request != null) {
		current_request.abort();
	}
	console.log("Query: " + query_string);
	current_request = $
			.ajax({
				url : _site_domain + _index_page + "/" + _controller_name
						+ "/fetch_ajax",
				dataType : 'json',
				data : {
					"q" : query_string
				},
				success : function(data) {
					if (data.status == 'success') {
						var table = false;
						var last_table = '';
						var container = '';
						var columns = null;
						var output = $('<span />'); /*
													 * temporary offline
													 * container
													 */

						console.log("Got " + data.data.length + " objects");
						if (data.data.length == 0) {
							output.append('<h2>Empty result set</h2>');
						} else {
							for ( var i = 0; i < data.data.length; i++) {
								var obj = data.data[i];

								if (last_table != obj._table) {
									table = $('<table />');
									output.append(table);

									last_table = obj._table;
									columns = new Array();
									var header = $('<tr />');
									for ( var key in render[obj._table]) {
										columns
												.push(render[obj._table][key].cell);
										header
												.append($(render[obj._table][key].header));
									}
									table.append(header);
								}

								var row = $('<tr />');
								for ( var cur_col = 0; cur_col < columns.length; cur_col++) {
									row.append(columns[cur_col](obj));
								}
								table.append(row);
							}
						}
						$('#filter_result').empty().append(output);
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
	query_timer = setTimeout(doAjaxSearch, 500);
}
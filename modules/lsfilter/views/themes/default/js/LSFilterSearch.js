var query_timer = null;
var query_string = "";
var current_request = null;

var render = {
	"hosts" : {
		"status" : {
			"header" : '<th>&nbsp;</th>',
			"cell" : function(obj) {
				return $('<td><span class="icon-16 x16-shield-'
						+ obj.state_text + '"></span></td>');

			}
		},
		"name" : {
			"header" : '<th>Name</th>',
			"cell" : function(obj) {
				return $('<td />').text(obj.name);
			}
		},
		"last_check" : {
			"header" : '<th>Last Checked</th>',
			"cell" : function(obj) {
				return $('<td />').text(
						new Date(obj.last_check * 1000).toString());
			}
		},
		"status_info" : {
			"header" : '<th>Status Information</th>',
			"cell" : function(obj) {
				return $('<td />').text(obj.plugin_output);
			}
		},
		"display_name" : {
			"header" : '<th>Display name</th>',
			"cell" : function(obj) {
				return $('<td />').text(obj.display_name);
			}
		}
	},
	"services" : {
		"host_status" : {
			"header" : '<th>&nbsp;</th>',
			"cell" : function(obj) {
				return $('<td><span class="icon-16 x16-shield-'
						+ obj.host.state_text + '"></span></td>');

			}
		},
		"host_name" : {
			"header" : '<th>Host</th>',
			"cell" : function(obj) {
				return $('<td />').text(obj.host.name);
			}
		},
		"status" : {
			"header" : '<th>&nbsp;</th>',
			"cell" : function(obj) {
				return $('<td><span class="icon-16 x16-shield-'
						+ obj.state_text + '"></span></td>');

			}
		},
		"description" : {
			"header" : '<th>Description</th>',
			"cell" : function(obj) {
				return $('<td />').text(obj.description);
			}
		},
		"last_check" : {
			"header" : '<th>Last Checked</th>',
			"cell" : function(obj) {
				return $('<td />').text(
						new Date(obj.last_check * 1000).toString());
			}
		},
		"attempt" : {
			"header" : '<th>Attempt</th>',
			"cell" : function(obj) {
				return $('<td />').text(
						obj.current_attempt + "/" + obj.max_check_attempts);
			}
		},
		"status_info" : {
			"header" : '<th>Status Information</th>',
			"cell" : function(obj) {
				return $('<td />').text(obj.plugin_output);
			}
		},
		"display_name" : {
			"header" : '<th>Display name</th>',
			"cell" : function(obj) {
				return $('<td />').text(obj.display_name);
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
						var tbody = false;
						var last_table = '';
						var container = '';
						var columns = null;
						/*
						 * temporary offline container
						 */
						var output = $('<span />');

						console.log("Got " + data.data.length + " objects");
						if (data.data.length == 0) {
							output.append('<h2>Empty result set</h2>');
						} else {
							for ( var i = 0; i < data.data.length; i++) {
								var obj = data.data[i];

								if (last_table != obj._table) {
									var table = $('<table />');
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
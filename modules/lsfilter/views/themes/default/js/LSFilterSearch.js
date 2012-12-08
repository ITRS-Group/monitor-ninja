var query_timer = null;
var query_string = "";
var current_request = null;

function icon16(name, title, link) {
	var img = $('<span />');
	img.addClass('icon-16');
	img.addClass('x16-' + name);
	if (title)
		img.attr('title', title);
	if (link) {
		img = link.clone().append(img);
		img.css('border', '0');
	}
	return img;
}
function icon(url, link) {
	var img = $('<img />');
	img.attr('src', '/monitor/images/logos/' + url); // FIXME
	img.css('height', '16px');
	img.css('width', '16px');
	if (link) {
		img = link.clone().append(img);
		img.css('border', '0');
	}
	return img;
}
function link(rel_url, args) {
	var get_data = "";
	var delim = "?";
	for ( var key in args) {
		get_data += delim + key + "=" + encodeURIComponent(args[key]);
		delim = "&";
	}

	var el = $('<a />');
	el.attr('href', _site_domain + _index_page + "/" + rel_url + get_data);
	return el;
}
function extinfo_link(type, name) {
	var args = {};
	args[type] = name;
	return link('extinfo/details', args);
}

var render = {
	"hosts" : {
		"status" : {
			"header" : '',
			"cell" : function(obj) {
				return $('<td />').append(
						icon16('shield-' + obj.state_text, obj.state_text));

			}
		},
		"name" : {
			"header" : 'Name',
			"cell" : function(obj) {
				var cell = $('<td />');
				cell.append(extinfo_link('host', obj.name).text(obj.name));

				if (obj.icon_image)
					cell.append(icon(obj.icon_image,
							extinfo_link('host', obj.name)).css('float',
							'right'));

				return cell;
			}
		},
		"actions" : {
			"header" : 'Actions',
			"cell" : function(obj) {
				var cell = $('<td />');

				// FIXME: icon for service-details
				cell.append(icon16('service-details',
						'View service details for this host', link(
								_current_uri, {
									'filter_query' : '[services] host.name = "'
											+ obj.name + '"' // FIXME: escape
								})));

				if (obj.acknowledged)
					cell.append(icon16('acknowledged', 'Acknowledged'));

				if (!obj.notifications_enabled)
					cell.append(icon16('notify-disabled',
							'Notification disabled'));

				if (obj.checks_disabled)
					cell.append(icon16('active-checks-disabled',
							'Checks Disabled'));

				if (obj.is_flapping) // FIXME: Needs icon in compass
					cell.append(icon16('flapping', 'Flapping'));

				if (obj.scheduled_downtime_depth > 0)
					cell.append(icon16('scheduled-downtime',
							'Scheduled Downtime'));

				if (obj.comments > 0)
					cell.append(icon16('add-comment', 'Comments'));

				// FIXME: Add nacoma link

				if (obj.pnpgraph_present)
					cell.append(icon16('pnp', 'Show performance graph', link(
							'pnp', {
								"srv" : "_HOST_",
								"host" : obj.name
							})));

				if (obj.action_url)
					cell.append(icon16('host-actions',
							'perform extra host actions', $('<a />').attr(
									'href', obj.action_url)));

				if (obj.notes_url)
					cell.append(icon16('host-notes', 'View extra host notes',
							$('<a />').attr('href', obj.notes_url)));

				return cell;
			}
		},
		"last_check" : {
			"header" : 'Last Checked',
			"cell" : function(obj) {
				var last_check = new Date(obj.last_check * 1000);
				return $('<td />').text(last_check.toLocaleTimeString());
			}
		},
		"status_info" : {
			"header" : 'Status Information',
			"cell" : function(obj) {
				return $('<td />').text(obj.plugin_output);
			}
		},
		"display_name" : {
			"header" : 'Display name',
			"cell" : function(obj) {
				return $('<td />').text(obj.display_name);
			}
		}
	},
	"services" : {
		"host_status" : {
			"header" : '',
			"cell" : function(obj) {
				return $('<td><span class="icon-16 x16-shield-'
						+ obj.host.state_text + '"></span></td>');

			}
		},
		"host_name" : {
			"header" : 'Host',
			"cell" : function(obj) {
				return $('<td />').text(obj.host.name);
			}
		},
		"status" : {
			"header" : '',
			"cell" : function(obj) {
				return $('<td><span class="icon-16 x16-shield-'
						+ obj.state_text + '"></span></td>');
			}
		},
		"description" : {
			"header" : 'Description',
			"cell" : function(obj) {
				return $('<td />').text(obj.description);
			}
		},
		"last_check" : {
			"header" : 'Last Checked',
			"cell" : function(obj) {
				var last_check = new Date(obj.last_check * 1000);
				return $('<td />').text(last_check.toLocaleTimeString());
			}
		},
		"attempt" : {
			"header" : 'Attempt',
			"cell" : function(obj) {
				return $('<td />').text(
						obj.current_attempt + "/" + obj.max_check_attempts);
			}
		},
		"status_info" : {
			"header" : 'Status Information',
			"cell" : function(obj) {
				return $('<td />').text(obj.plugin_output);
			}
		},
		"display_name" : {
			"header" : 'Display name',
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
												.append($('<th />')
														.text(
																render[obj._table][key].header));
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
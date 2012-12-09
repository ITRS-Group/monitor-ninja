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
function link_fnc(fnc) {
	return $('<a />').click(fnc);
}
function extinfo_link(host, service) {
	var args = {};
	args['host'] = host;
	if (service)
		args['service'] = service;
	return link('extinfo/details', args);
}

var lsfilter_totals_renderer = {
	"host_all" : function(cnt) {
		var container = $('<li />');
		container.append("Hosts:");
		container.append(icon16('host', "Hosts total"));
		container.append(cnt);
		return container;
	},
	"host_state_up" : function(cnt) {
		var container = $('<li />');
		container.append(icon16('shield-up', "Hosts up"));
		container.append(cnt);
		return container;
	},
	"host_state_down" : function(cnt) {
		var container = $('<li />');
		container.append(icon16('shield-down', "Hosts down"));
		container.append(cnt);
		return container;
	},
	"host_state_unreachable" : function(cnt) {
		var container = $('<li />');
		container.append(icon16('shield-unreachable', "Hosts unreachable"));
		container.append(cnt);
		return container;
	},
	"host_pending" : function(cnt) {
		var container = $('<li />');
		container.append(icon16('shield-pending', "Hosts pending"));
		container.append(cnt);
		return container;
	},

	"service_all" : function(cnt) {
		var container = $('<li />');
		container.append("Services:");
		container.append(icon16('shield-info', "Services total"));
		container.append(cnt);
		return container;
	},
	"service_state_ok" : function(cnt) {
		var container = $('<li />');
		container.append(icon16('shield-ok', "Services ok"));
		container.append(cnt);
		return container;
	},
	"service_state_warning" : function(cnt) {
		var container = $('<li />');
		container.append(icon16('shield-warning', "Services warning"));
		container.append(cnt);
		return container;
	},
	"service_state_critical" : function(cnt) {
		var container = $('<li />');
		container.append(icon16('shield-critical', "Services critical"));
		container.append(cnt);
		return container;
	},
	"service_state_unknown" : function(cnt) {
		var container = $('<li />');
		container.append(icon16('shield-unknown', "Services unknown"));
		container.append(cnt);
		return container;
	},
	"service_pending" : function(cnt) {
		var container = $('<li />');
		container.append(icon16('shield-pending', "Services pending"));
		container.append(cnt);
		return container;
	},
};

var lsfilter_result_renderer = {

	/*
	 * Render Hosts
	 */

	"hosts" : {
		"status" : {
			"header" : '',
			"depends" : [ 'state_text' ],
			"sort" : [ 'state' ],
			"cell" : function(obj) {
				return $('<td />').append(
						icon16('shield-' + obj.state_text, obj.state_text));

			}
		},
		"name" : {
			"header" : 'Name',
			"depends" : [ 'name', 'icon_image' ],
			"sort" : [ 'name' ],
			"cell" : function(obj) {
				var cell = $('<td />');
				cell.append(extinfo_link(obj.name).text(obj.name));

				if (obj.icon_image)
					cell.append(icon(obj.icon_image, extinfo_link(obj.name))
							.css('float', 'right'));

				return cell;
			}
		},
		"actions" : {
			"header" : 'Actions',
			"depends" : [ 'name', 'acknowledged', 'notifications_enabled',
					'checks_disabled', 'is_flapping',
					'scheduled_downtime_depth', 'pnpgraph_present',
					'action_url', 'notes_url', 'comments' ],
			"sort" : false,
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

				if (obj.comments > 0)
					cell.append(icon16('add-comment', 'Comments'));

				return cell;
			}
		},
		"last_check" : {
			"header" : 'Last Checked',
			"depends" : [ 'last_check' ],
			"sort" : [ 'last_check' ],
			"cell" : function(obj) {
				var last_check = new Date(obj.last_check * 1000);
				return $('<td />').text(last_check.toLocaleTimeString());
			}
		},
		"status_info" : {
			"header" : 'Status Information',
			"depends" : [ 'plugin_output' ],
			"sort" : [ 'plugin_output' ],
			"cell" : function(obj) {
				return $('<td />').text(obj.plugin_output);
			}
		},
		"display_name" : {
			"header" : 'Display name',
			"depends" : [ 'display_name' ],
			"sort" : [ 'display_name' ],
			"cell" : function(obj) {
				return $('<td />').text(obj.display_name);
			}
		}
	},

	/*
	 * Render Services
	 */

	"services" : {
		"host_status" : {
			"header" : '',
			"depends" : [ 'host.state_text' ],
			"sort" : [ 'host.state' ],
			"cell" : function(obj) {
				return $('<td><span class="icon-16 x16-shield-'
						+ obj.host.state_text + '"></span></td>');

			}
		},
		"host_name" : {
			"header" : 'Host',
			"depends" : [ 'host.name' ],
			"sort" : [ 'host.name' ],
			"cell" : function(obj) {
				return $('<td />').append(
						extinfo_link(obj.host.name).text(obj.host.name));
			}
		},
		"status" : {
			"header" : '',
			"depends" : [ 'state_text' ],
			"sort" : [ 'state' ],
			"cell" : function(obj) {
				return $('<td><span class="icon-16 x16-shield-'
						+ obj.state_text + '"></span></td>');
			}
		},
		"description" : {
			"header" : 'Description',
			"depends" : [ 'host.name', 'description' ],
			"sort" : [ 'description' ],
			"cell" : function(obj) {
				return $('<td />').append(
						extinfo_link(obj.host.name, obj.description).text(
								obj.description));
			}
		},
		"last_check" : {
			"header" : 'Last Checked',
			"depends" : [ 'last_check' ],
			"sort" : [ 'last_check' ],
			"cell" : function(obj) {
				var last_check = new Date(obj.last_check * 1000);
				return $('<td />').text(last_check.toLocaleTimeString());
			}
		},
		"attempt" : {
			"header" : 'Attempt',
			"depends" : [ 'current_attempt', 'max_check_attempts' ],
			"sort" : [ 'current_attempt' ],
			"cell" : function(obj) {
				return $('<td />').text(
						obj.current_attempt + "/" + obj.max_check_attempts);
			}
		},
		"status_info" : {
			"header" : 'Status Information',
			"depends" : [ 'plugin_output' ],
			"sort" : [ 'plugin_output' ],
			"cell" : function(obj) {
				return $('<td />').text(obj.plugin_output);
			}
		},
		"display_name" : {
			"header" : 'Display name',
			"depends" : [ 'display_name' ],
			"sort" : [ 'display_name' ],
			"cell" : function(obj) {
				return $('<td />').text(obj.display_name);
			}
		}
	}
};
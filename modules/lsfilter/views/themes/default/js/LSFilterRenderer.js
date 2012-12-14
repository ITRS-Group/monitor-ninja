function icon12(name, title, link) {
	var img = $('<span />');
	img.addClass('icon-12');
	img.addClass('x12-' + name);
	if (title)
		img.attr('title', title);
	if (link) {
		img = link.clone().append(img);
		img.css('border', '0');
	}
	return img;
}
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

function listview_add_sort(element, vis_column, db_columns, current) {
	if (current == 0) { // No sort
		
		element.prepend($('<span class="lsfilter-sort-span">&sdot;</span>'));
	} else if (current > 0) { // Ascending?
		element.attr('title', 'Sort descending');
		element.prepend($('<span class="lsfilter-sort-span">&darr;</span>'));
	} else {
		element.attr('title', 'Sort ascending');
		element.prepend($('<span class="lsfilter-sort-span">&uarr;</span>'));
	}
	element.click({
		vis_column : vis_column,
		db_columns : db_columns
	}, function(evt) {
		listview_update_sort(evt.data.vis_column, evt.data.db_columns);
	});
}

/*******************************************************************************
 * Totals renderer
 ******************************************************************************/

var listview_renderer_totals = {
	"count" : function(cnt) {
		var container = $('<li class="extra_toolbar_category" />');
		container.append("Count: &nbsp; ");
		container.append(icon16('shield-info', "Matching"));
		container.append(cnt);
		return container;
	},

	"host_all" : function(cnt) {
		var container = $('<li class="extra_toolbar_category" />');
		container.append("Hosts: &nbsp; ");
		container.append(icon16('host', "Hosts total"));
		container.append(cnt);
		return container;
	},
	"host_state_up" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield') + '-up',
				"Hosts up"));
		container.append(cnt);
		return container;
	},
	"host_state_down" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-down', "Hosts down"));
		container.append(cnt);
		return container;
	},
	"host_state_unreachable" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-unreachable', "Hosts unreachable"));
		container.append(cnt);
		return container;
	},
	"host_pending" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-pending', "Hosts pending"));
		container.append(cnt);
		return container;
	},

	"service_all" : function(cnt) {
		var container = $('<li class="extra_toolbar_category" />');
		container.append("Services: &nbsp; ");
		container.append(icon16('shield-info', "Services total"));
		container.append(cnt);
		return container;
	},
	"service_state_ok" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield') + '-ok',
				"Services ok"));
		container.append(cnt);
		return container;
	},
	"service_state_warning" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-warning', "Services warning"));
		container.append(cnt);
		return container;
	},
	"service_state_critical" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-critical', "Services critical"));
		container.append(cnt);
		return container;
	},
	"service_state_unknown" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-unknown', "Services unknown"));
		container.append(cnt);
		return container;
	},
	"service_pending" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-pending', "Services pending"));
		container.append(cnt);
		return container;
	},
};

/*******************************************************************************
 * Table renderer
 ******************************************************************************/

function render_summary_state (state, stats, type, group) {

	/*
		@FIXME - INTERNET EXPLODER SUCKS - No support for either Object.keys or Array.prototype.filter pre IE 9
	*/

	var li = $('<li />').append(
			$('<a />').attr('href', "?q=query_placeholder")
				.append(icon16('shield-' + state) ).append( $('<span />').html(stats[state]) )
		),
		
		subchecks = Object.keys(stats).filter(function (e, i, a) {
			return (e.indexOf(state + '_') >= 0);
		});

	if (subchecks.length > 0) {

		li.append( $('<span />').html(' ( '));

		for (var check in subchecks) {
			var key = subchecks[check];
			if (stats[key]) {
				li.append(
					$('<a />').attr('href', '?q=query_placeholder').append(
						$('<span style="text-transform: capitalize;" />').text(stats[key] + ' ' + (key.split('_and_')[1]).replace(/_/g, ' '))
					)
				)
			}
		}

		li.append( $('<span />').html(' ) '));

	}

	return li;
}

function render_service_status_summary (stats, group) {
	var ul = $('<ul class="listview-summary" />');

	ul.append( render_summary_state('ok', stats, 'services', group) );
	ul.append( render_summary_state('warning', stats, 'services', group) );
	ul.append( render_summary_state('critical', stats, 'services', group) );
	ul.append( render_summary_state('unknown', stats, 'services', group) );
	ul.append( render_summary_state('pending', stats, 'services', group) );

	return ul;
}

function render_host_status_summary (stats, group) {
	var ul = $('<ul class="listview-summary" />');

	ul.append( render_summary_state('up', stats, 'hosts', group) );
	ul.append( render_summary_state('down', stats, 'hosts', group) );
	ul.append( render_summary_state('unreachable', stats, 'hosts', group) );
	ul.append( render_summary_state('pending', stats, 'hosts', group) );
	

	return ul;
}

var listview_last_host = '';
var listview_renderer_table = {

	/***************************************************************************
	 * Render Hosts
	 **************************************************************************/

	"hosts" : {
		"status" : {
			"header" : '',
			"depends" : [ 'state_text' ],
			"sort" : [ 'state' ],
			"cell" : function(obj) {
				return $('<td class="icon" />').append(
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
				return $('<td style="max-width: 300px;" />').text(obj.plugin_output);
			}
		},
		"services_num_all" : {
			"header" : icon12('shield-info').addClass('header-icon'),
			"depends" : [ 'num_services' ],
			"sort" : false,
			"cell" : function(obj) {
				var cell = $('<td />').css('text-align','center');
				if (obj.num_services > 0) {
					cell.append(obj.num_services);
				}
				return cell;
			}
		},
		"services_num_all" : {
			"header" : icon12('shield-info').addClass('header-icon'),
			"depends" : [ 'num_services' ],
			"sort" : false,
			"cell" : function(obj) {
				var cell = $('<td />').css('text-align','center');
				if (obj.num_services > 0) {
					cell.append(obj.num_services);
				}
				return cell;
			}
		},
		"services_num_ok" : {
			"header" : icon12('shield-ok').addClass('header-icon'),
			"depends" : [ 'num_services_ok' ],
			"sort" : false,
			"cell" : function(obj) {
				var cell = $('<td />').css('text-align','center');
				if (obj.num_services_ok > 0) {
					cell.append(obj.num_services_ok);
				}
				return cell;
			}
		},
		"services_num_warning" : {
			"header" : icon12('shield-warning').addClass('header-icon'),
			"depends" : [ 'num_services_warn' ],
			"sort" : false,
			"cell" : function(obj) {
				var cell = $('<td />').css('text-align','center');
				if (obj.num_services_warn > 0) {
					cell.append(obj.num_services_warn);
				}
				return cell;
			}
		},
		"services_num_critical" : {
			"header" : icon12('shield-critical').addClass('header-icon'),
			"depends" : [ 'num_services_crit' ],
			"sort" : false,
			"cell" : function(obj) {
				var cell = $('<td />').css('text-align','center');
				if (obj.num_services_crit > 0) {
					cell.append(obj.num_services_crit);
				}
				return cell;
			}
		},
		"services_num_unknown" : {
			"header" : icon12('shield-unknown').addClass('header-icon'),
			"depends" : [ 'num_services_unknown' ],
			"sort" : false,
			"cell" : function(obj) {
				var cell = $('<td />').css('text-align','center');
				if (obj.num_services_unknown > 0) {
					cell.append(obj.num_services_unknown);
				}
				return cell;
			}
		},
		"services_num_pending" : {
			"header" : icon12('shield-pending').addClass('header-icon'),
			"depends" : [ 'num_services_pending' ],
			"sort" : false,
			"cell" : function(obj) {
				var cell = $('<td />').css('text-align','center');
				if (obj.num_services_pending > 0) {
					cell.append(obj.num_services_pending);
				}
				return cell;
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

	/***************************************************************************
	 * Render Services
	 **************************************************************************/

	"services" : {
		"host_status" : {
			"header" : '',
			"depends" : [ 'host.state_text' ],
			"sort" : [ 'host.state' ],
			"cell" : function(obj) {
				if (obj.host && obj.host.name != listview_last_host) {
					return $('<td class="icon" />').append(
						icon16('shield-' + obj.host.state_text,
								obj.host.state_text));
				} else {
					return $('<td class="icon" />').addClass('listview-empty-cell');
				}
			}
		},
		"host_name" : {
			"header" : 'Host Name',
			"depends" : [ 'host.name', 'host.icon_image' ],
			"sort" : [ 'host.name' ],
			"cell" : function(obj) {
				var cell = $('<td />');

				if (obj.host && obj.host.name != listview_last_host) {
					cell.append(extinfo_link(obj.host.name).text(obj.host.name));

					if (obj.host.icon_image)
						cell.append(icon(obj.host.icon_image,
							extinfo_link(obj.host.name)).css('float', 'right'));
					listview_last_host = obj.host.name;
				} else {
					cell.addClass('listview-empty-cell');
				}

				return cell;
			}
		},
		"status" : {
			"header" : '',
			"depends" : [ 'state_text' ],
			"sort" : [ 'state' ],
			"cell" : function(obj) {
				return $('<td class="icon"><span class="icon-16 x16-shield-'
						+ obj.state_text + '"></span></td>');
			}
		},
		"description" : {
			"header" : 'Service',
			"depends" : [ 'host.name', 'description' ],
			"sort" : [ 'description' ],
			"cell" : function(obj) {
				return $('<td />').append(
						extinfo_link(obj.host.name, obj.description).text(
								obj.description));
			}
		},
		"actions" : {
			"header" : "Actions",
			"depends" : [ 'acknowledged', 'comments', 'notifications_enabled',
					'checks_disabled', 'is_flapping',
					'scheduled_downtime_depth', 'pnpgraph_present',
					'action_url', 'notes_url' ],
			"sort" : false,
			"cell" : function(obj) {
				var cell = $('<td />');

				if (obj.acknowledged)
					cell.append(icon16('acknowledged', 'Acknowledged'));

				if (obj.comments > 0)
					cell.append(icon16('add-comment', 'Comments'));

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

				/***************************************************************
				 * 
				 */

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
				return $('<td style="max-width: 300px;" />').text(obj.plugin_output);
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
	/***************************************************************************
	 * Render Host groups
	 **************************************************************************/
	"hostgroups" : {
		"name" : {
			"header" : 'Host Group',
			"depends" : [ 'alias', 'name' ],
			"sort" : [ 'alias', 'name' ],
			"cell" : function(obj) {
				var cell = $('<td />');
				cell.append(
					$('<a />').attr('href', '?q=[hosts] in "' + obj.name + '"').text(
						obj.alias + ' (' + obj.name + ')'
					)
				);
				return cell;
			}
		},
		"actions" : {
			"header" : 'Actions',
			"depends" : [],
			"sort" : false,
			"cell" : function(obj) {
				var cell = $('<td />');
				return cell;
			}
		},
		"host_status_summary" : {
			"header" : 'Host Status Summary',
			"depends" : [ 'host_stats' ],
			"sort" : false,
			"cell" : function(obj) {
				var cell = $('<td / >');
				cell.append(render_host_status_summary(obj.host_stats, obj.name));
				return cell;
			}
		},
		"service_status_summary" : {
			"header" : 'Service Status Summary',
			"depends" : [ 'service_stats' ],
			"sort" : false,
			"cell" : function(obj) {
				var cell = $('<td / >');
				cell.append(render_service_status_summary(obj.service_stats, obj.name));
				return cell;
			}
		}
	},
	/***************************************************************************
	 * Render Service groups
	 **************************************************************************/
	"servicegroups" : {
		"name" : {
			"header" : 'Service Group',
			"depends" : [ 'alias', 'name' ],
			"sort" : [ 'alias', 'name' ],
			"cell" : function(obj) {
				var cell = $('<td />');
				cell.text(obj.alias);
				cell.text(' (' + obj.name + ')');
				return cell;
			}
		},
		"actions" : {
			"header" : 'Actions',
			"depends" : [],
			"sort" : false,
			"cell" : function(obj) {
				var cell = $('<td />');
				return cell;
			}
		},
		"service_status_summary" : {
			"header" : 'Service Status Summary',
			"depends" : [ 'service_stats' ],
			"sort" : false,
			"cell" : function(obj) {
				var cell = $('<td / >');
				cell.append(render_service_status_summary(obj.service_stats, obj.name));
				return cell;
			}
		}
	},
	/***************************************************************************
	 * Render Comments
	 **************************************************************************/
	"comments" : {
		"is_service" : {
			"header" : 'Type',
			"depends" : [ 'is_service' ],
			"sort" : false,
			"cell" : function(obj) {
				return $('<td />').text(obj.is_service ? 'Service' : 'Host');
			}
		},
		"host_status" : {
			"header" : '',
			"depends" : [ 'host.state_text' ],
			"sort" : [ 'host.state' ],
			"cell" : function(obj) {
				return $('<td />').append(
						icon16('shield-' + obj.host.state_text,
								obj.host.state_text));

			}
		},
		"host_name" : {
			"header" : 'Host Name',
			"depends" : [ 'host.name', 'host.icon_image' ],
			"sort" : [ 'host.name' ],
			"cell" : function(obj) {
				var cell = $('<td />');
				cell.append(extinfo_link(obj.host.name).text(obj.host.name));

				if (obj.host.icon_image)
					cell.append(icon(obj.host.icon_image,
							extinfo_link(obj.host.name)).css('float', 'right'));

				return cell;
			}
		},
		"service_status" : {
			"header" : '',
			"depends" : [ 'service.state_text', 'service.description' ],
			"sort" : [ 'service.state' ],
			"cell" : function(obj) {
				if (!obj.service.description)
					return $('<td />');

				return $('<td><span class="icon-16 x16-shield-'
						+ obj.service.state_text + '"></span></td>');
			}
		},
		"service_description" : {
			"header" : 'Service',
			"depends" : [ 'host.name', 'service.description' ],
			"sort" : [ 'service.description' ],
			"cell" : function(obj) {
				if (!obj.service.description)
					return $('<td />');
				return $('<td />').append(
						extinfo_link(obj.host.name, obj.service.description)
								.text(obj.service.description));
			}
		},
		"time" : {
			"header" : 'Entry Time',
			"depends" : [ 'entry_time' ],
			"sort" : [ 'entry_time' ],
			"cell" : function(obj) {
				var time = new Date(obj.entry_time * 1000);
				return $('<td />').text(time.toLocaleTimeString());
			}
		},
		"author" : {
			"header" : 'Author',
			"depends" : [ 'author' ],
			"sort" : [ 'author' ],
			"cell" : function(obj) {
				return $('<td />').text(obj.author);
			}
		},
		"comment" : {
			"header" : 'Comment',
			"depends" : [ 'comment' ],
			"sort" : [ 'comment' ],
			"cell" : function(obj) {
				return $('<td />').text(obj.comment);
			}
		},
		"id" : {
			"header" : 'ID',
			"depends" : [ 'id' ],
			"sort" : [ 'id' ],
			"cell" : function(obj) {
				return $('<td />').text(obj.id);
			}
		},
		"persistent" : {
			"header" : 'Persistent',
			"depends" : [ 'persistent' ],
			"sort" : [ 'persistent' ],
			"cell" : function(obj) {
				var cell = $('<td />');
				if (obj.persistent)
					cell.text("Yes");
				else
					cell.text("No");
				return cell;
			}
		},
		"entry_type" : {
			"header" : 'Type',
			"depends" : [ 'entry_type' ],
			"sort" : [ 'entry_type' ],
			"cell" : function(obj) {
				var cell = $('<td />');
				switch (obj.entry_type) {
				case 1:
					cell.text("User comment");
					break;
				case 2:
					cell.text("Scheduled downtime");
					break;
				case 3:
					cell.text("Flapping");
					break;
				case 4:
					cell.text("Acknowledgement");
					break;
				}
				return cell;
			}
		},
		"expires" : {
			"header" : 'Expires',
			"depends" : [ 'expires', 'expire_time' ],
			"sort" : [ 'expires', 'expire_time' ],
			"cell" : function(obj) {
				var cell = $('<td />');
				if (obj.expires)
					cell.text(obj.expire_time);
				else
					cell.text('N/A');
				return cell;
			}
		},
		"actions" : {
			"header" : 'Actions',
			"depends" : [],
			"sort" : false,
			"cell" : function(obj) {
				return $('<td />');
			}
		}
	}
};

/*******************************************************************************
 * Renderer methods
 ******************************************************************************/

function listview_render_start_loading(loader) {
	$('#filter_visual_result').prepend(loader);
}
function listview_render_stop_loading(loader) {
	loader.remove();
}

function listview_render_totals(totals) {
	var container = $('<ul />');
	if (totals) {
		for ( var field in listview_renderer_totals) {
			if (field in totals) {
				container.append(listview_renderer_totals[field](totals[field])
						.css('float', 'left'));
			}
		}
	}
	$('#filter_result_totals').empty().append(container);
}

var listview_table_col_index = 0;

function listview_table_col_name(c) {
	var name = listview_table_col_index;
	listview_table_col_index += 1;
	return 'listview-col-' + name;
}

function listview_render_table(data) {
	var tbody = false;
	var last_table = '';
	var container = '';
	var columns = null;
	/*
	 * temporary offline container
	 */
	var output = $('<span />');

	//console.log("Got " + data.length + " objects");

	listview_table_col_index = 0;
	listview_last_host = '';

	if (data.length == 0) {
		output.append('<h2 class="lsfilter-noresult">Empty result set</h2>');
	} else {

		/*
		 * Render table
		 */
		for ( var i = 0; i < data.length; i++) {
			var obj = data[i];

			if (last_table != obj._table) {
				var table = $('<table cellspacing="0" cellpadding="0" border="0" />');
				output.append(table);
				
				//console.log(listview_columns_for_table(obj._table));

				last_table = obj._table;
				columns = new Array();
				var header = $('<tr />');
				for ( var key in listview_renderer_table[obj._table]) {
					var col_render = listview_renderer_table[obj._table][key];
					columns.push(col_render.cell);
					var th = $('<th />').attr('id', listview_table_col_name(col_render.header));
					th.append(col_render.header);
					if (col_render.sort) {
						var sort_dir = 0;
						if (listview_sort_vis_column == key)
							sort_dir = -1;
						if (listview_sort_ascending)
							sort_dir = -sort_dir;
						listview_add_sort(th, key, col_render.sort, sort_dir);
					}
					header.append(th);
				}
				table.append($('<thead cellspacing="0" cellpadding="0" border="0" />').append(header));

				tbody = $('<tbody />');
				table.append(tbody);
			}

			var row = $('<tr />');
			if (i % 2 == 0)
				row.addClass('even');
			else
				row.addClass('odd');

			for ( var cur_col = 0; cur_col < columns.length; cur_col++) {
				row.append(columns[cur_col](obj).addClass('listview-cell-' + cur_col));
			}
			tbody.append(row);
		}
	}

	$('#filter_result').empty().append(output);
	

	if (table) {
		
		table.find('[id^=listview-col-]').hover(function () {
			
			var self = $(this),
				index = self.attr('id').split('-col-')[1];

			table.find('.listview-cell-' + index).addClass('listview-col-hover');
		}, function () {
			
			var self = $(this),
				index = self.attr('id').split('-col-')[1];

			table.find('.listview-cell-' + index).removeClass('listview-col-hover');
		});

		var header = $("thead", table),
			clone = header.clone(true);

		header.after(clone);

		function update_float_header() {
			table.each(function() {
		   
				var el 			= $(this),
					offset		= el.offset(),
					scrollTop	= $(window).scrollTop();

				if (scrollTop >= 0) {

					var head = header.find("tr").children(),
						cloneHead = clone.find("tr").children(),
						index = 0;

					clone.css('min-width', header.width());

					head.each(function () {

						if ($.browser.webkit) {
							$(cloneHead[index]).css('width', (parseInt($(this).css('width'), 10) + 1) + 'px');
						} else {
							$(cloneHead[index]).css('width', $(this).css('width'));
						}
						
						$(cloneHead[index]).css('padding', $(this).css('padding'));
						$(cloneHead[index]).css('margin', $(this).css('margin'));
						$(cloneHead[index]).css('border', $(this).css('border'));
						index++;
					});

					clone.addClass('floating-header');
					clone.css('visibility', 'visible');

				}

		   });
		}

		$(window)
			.resize(update_float_header)
			.scroll(update_float_header)
			.trigger("scroll");

	}

}
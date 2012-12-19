var listview_sort_vis_column = false;
var listview_sort_ascending = false;

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



/*******************************************************************************
 * Totals renderer
 ******************************************************************************/

var listview_renderer_totals = {
	"count" : function(cnt) {
		var container = $('<li class="extra_toolbar_category" />');
		container.append(_("Count")+": &nbsp; ");
		container.append(icon16('shield-info', _("Matching")));
		container.append(cnt);
		return container;
	},

	"host_all" : function(cnt) {
		var container = $('<li class="extra_toolbar_category" />');
		container.append(_("Hosts")+": &nbsp; ");
		container.append(icon16('host', _("Hosts total")));
		container.append(cnt);
		return container;
	},
	"host_state_up" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield') + '-up',
				_("Hosts up")));
		container.append(cnt);
		return container;
	},
	"host_state_down" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-down', _("Hosts down")));
		container.append(cnt);
		return container;
	},
	"host_state_unreachable" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-unreachable', _("Hosts unreachable")));
		container.append(cnt);
		return container;
	},
	"host_pending" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-pending', _("Hosts pending")));
		container.append(cnt);
		return container;
	},

	"service_all" : function(cnt) {
		var container = $('<li class="extra_toolbar_category" />');
		container.append(_("Services")+": &nbsp; ");
		container.append(icon16('shield-info', _("Services total")));
		container.append(cnt);
		return container;
	},
	"service_state_ok" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield') + '-ok',
				_("Services ok")));
		container.append(cnt);
		return container;
	},
	"service_state_warning" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-warning', _("Services warning")));
		container.append(cnt);
		return container;
	},
	"service_state_critical" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-critical', _("Services critical")));
		container.append(cnt);
		return container;
	},
	"service_state_unknown" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-unknown', _("Services unknown")));
		container.append(cnt);
		return container;
	},
	"service_pending" : function(cnt) {
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-pending', _("Services pending")));
		container.append(cnt);
		return container;
	},
};

/*******************************************************************************
 * Table renderer
 ******************************************************************************/

function render_summary_state ( ul, state, stats, substates ) {
	var li = $('<li />').append(
			link_query(stats.queries[state])
				.append(icon16('shield-' + state) ).append( $('<span />').text(stats.stats[state] + " " + state) )
		);

	var delim = ' ( ';
	var suffix = '';
	
	for (var tag in substates) {
		var key = state + tag;
		var type = substates[tag];

		if (stats.stats[key]) {
			li.append(delim);
			li.append(
				link_query(stats.queries[key]).text(stats.stats[key] + ' ' + type)
			);
			delim = ', ';
			suffix = ' ) ';
		}
	}
	
	li.append(suffix);

	ul.append( li );
}

function render_service_status_summary (stats) {
	var ul = $('<ul class="listview-summary" />');

	render_summary_state(ul, 'ok', stats, {});
	render_summary_state(ul, 'warning', stats, {
		'_and_ack': _('acknowledged'),
		'_and_disabled_active': _('disabled active'),
		'_and_scheduled': _('scheduled'),
		'_and_unhandled': _('unhandled'),
		'_on_down_host': _('on down host')
			});
	render_summary_state(ul, 'critical', stats, {
		'_and_ack': _('acknowledged'),
		'_and_disabled_active': _('disabled active'),
		'_and_scheduled': _('scheduled'),
		'_and_unhandled': _('unhandled'),
		'_on_down_host': _('on down host')
			});
	render_summary_state(ul, 'unknown', stats, {
		'_and_ack': _('acknowledged'),
		'_and_disabled_active': _('disabled active'),
		'_and_scheduled': _('scheduled'),
		'_and_unhandled': _('unhandled'),
		'_on_down_host': _('on down host')
			});
	render_summary_state(ul, 'pending', stats, {});

	return ul;
}

function render_host_status_summary (stats) {
	var ul = $('<ul class="listview-summary" />');

	render_summary_state(ul, 'up', stats, {});
	render_summary_state(ul, 'down', stats, {});
	render_summary_state(ul, 'unreachable', stats, {});
	render_summary_state(ul, 'pending', stats, {});
	

	return ul;
}

var listview_multi_select_cell_renderer = function(obj, tr) {
	var checkbox = $('<input type="checkbox" name="object_select[]" />').attr('value',obj.key);
	if( listview_selection[obj.key] ) {
		checkbox.prop('checked', true);
		
		if( tr.hasClass('odd') )
			tr.addClass( 'selected_odd' );
		else
			tr.addClass( 'selected_even' );
	}
	checkbox.change(function(evt) {
		var tgt = $(evt.target);
		listview_selection[tgt.attr('value')] = tgt.prop('checked');
		
		var tr = tgt.closest('tr');
		var classname = ""
		if( tr.hasClass('odd') )
			classname = 'selected_odd';
		else
			classname = 'selected_even';
		
		if( tgt.prop('checked') ) {
			tr.addClass(classname);
		} else {
			tr.removeClass(classname);
		}
	});
	return $('<td style="width: 1em;" />').append(checkbox);
};

var listview_last_host = '';
var listview_renderer_table = {

	/***************************************************************************
	 * Render Hosts
	 **************************************************************************/
		
	"hosts" : {
		"select" : {
			"header" : '',
			"depends" : [],
			"sort" : false,
			"cell" : listview_multi_select_cell_renderer
		},
		"status" : {
			"header" : '',
			"depends" : [ 'state_text' ],
			"sort" : [ 'state' ],
			"cell" : function(obj, tr) {
				return $('<td class="icon" />').append(
						icon16('shield-' + obj.state_text, obj.state_text));

			}
		},
		"name" : {
			"header" : _('Name'),
			"depends" : [ 'name', 'icon_image' ],
			"sort" : [ 'name' ],
			"cell" : function(obj, tr) {
				var cell = $('<td />');
				cell.append(extinfo_link(obj.name).text(obj.name));
				if (obj.icon_image)
					cell.append(icon(obj.icon_image, extinfo_link(obj.name))
							.css('float', 'right'));

				return cell;
			}
		},
		"actions" : {
			"header" : _('Actions'),
			"depends" : [ 'name', 'acknowledged', 'notifications_enabled',
					'checks_disabled', 'is_flapping',
					'scheduled_downtime_depth', 'pnpgraph_present',
					'action_url', 'notes_url', 'comments' ],
			"sort" : false,
			"cell" : function(obj, tr) {
				var cell = $('<td />');

				// FIXME: icon for service-details
				cell.append(icon16('service-details',
						_('View service details for this host'), link(
								_current_uri, {
									'filter_query' : '[services] host.name = "'
											+ obj.name + '"' // FIXME: escape
								})));

				if (obj.acknowledged)
					cell.append(icon16('acknowledged', _('Acknowledged')));

				if (!obj.notifications_enabled)
					cell.append(icon16('notify-disabled',
							'Notification disabled'));

				if (obj.checks_disabled)
					cell.append(icon16('active-checks-disabled',
							_('Checks Disabled')));

				if (obj.is_flapping) // FIXME: Needs icon in compass
					cell.append(icon16('flapping', _('Flapping')));

				if (obj.scheduled_downtime_depth > 0)
					cell.append(icon16('scheduled-downtime',
							_('Scheduled Downtime')));

				// FIXME: Add nacoma link

				if (obj.pnpgraph_present)
					cell.append(icon16('pnp', _('Show performance graph'), link(
							'pnp', {
								"srv" : "_HOST_",
								"host" : obj.name
							})));

				if (obj.action_url)
					cell.append(icon16('host-actions',
							_('perform extra host actions'), $('<a />').attr(
									'href', obj.action_url)));

				if (obj.notes_url)
					cell.append(icon16('host-notes', _('View extra host notes'),
							$('<a />').attr('href', obj.notes_url)));

				if (obj.comments > 0)
					cell.append(icon16('add-comment', _('Comments')));

				return cell;
			}
		},
		"last_check" : {
			"header" : _('Last Checked'),
			"depends" : [ 'last_check' ],
			"sort" : [ 'last_check' ],
			"cell" : function(obj, tr) {
				var last_check = new Date(obj.last_check * 1000);
				return $('<td />').text(last_check.toLocaleTimeString());
			}
		},
		"duration" : {
			"header" : _('Duration'),
			"depends" : ['duration'],
			"sort" : ['last_state_change'],
			"cell" : function(obj, tr) {
				return $('<td />').text(obj.duration);
			}
		},
		"status_info" : {
			"header" : _('Status Information'),
			"depends" : [ 'plugin_output' ],
			"sort" : [ 'plugin_output' ],
			"cell" : function(obj, tr) {
				return $('<td style="max-width: 300px;" />').text(obj.plugin_output);
			}
		},
		"services_num_all" : {
			"header" : icon12('shield-info').addClass('header-icon'),
			"depends" : [ 'num_services' ],
			"sort" : false,
			"cell" : function(obj, tr) {
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
			"cell" : function(obj, tr) {
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
			"cell" : function(obj, tr) {
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
			"cell" : function(obj, tr) {
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
			"cell" : function(obj, tr) {
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
			"cell" : function(obj, tr) {
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
			"cell" : function(obj, tr) {
				var cell = $('<td />').css('text-align','center');
				if (obj.num_services_pending > 0) {
					cell.append(obj.num_services_pending);
				}
				return cell;
			}
		},
		"display_name" : {
			"header" : _('Display name'),
			"depends" : [ 'display_name' ],
			"sort" : [ 'display_name' ],
			"cell" : function(obj, tr) {
				return $('<td />').text(obj.display_name);
			}
		}
	},

	/***************************************************************************
	 * Render Services
	 **************************************************************************/

	"services" : {
		"select" : {
			"header" : '',
			"depends" : [],
			"sort" : false,
			"cell" : listview_multi_select_cell_renderer
		},
		"host_status" : {
			"header" : '',
			"depends" : [ 'host.state_text' ],
			"sort" : [ 'host.state' ],
			"cell" : function(obj, tr) {
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
			"header" : _('Host Name'),
			"depends" : [ 'host.name', 'host.icon_image' ],
			"sort" : [ 'host.name' ],
			"cell" : function(obj, tr) {
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
			"cell" : function(obj, tr) {
				return $('<td class="icon"><span class="icon-16 x16-shield-'
						+ obj.state_text + '"></span></td>');
			}
		},
		"description" : {
			"header" : _('Service'),
			"depends" : [ 'host.name', 'description' ],
			"sort" : [ 'description' ],
			"cell" : function(obj, tr) {
				return $('<td />').append(
						extinfo_link(obj.host.name, obj.description).text(
								obj.description));
			}
		},
		"actions" : {
			"header" : _('Actions'),
			"depends" : [ 'acknowledged', 'comments', 'notifications_enabled',
					'checks_disabled', 'is_flapping',
					'scheduled_downtime_depth', 'pnpgraph_present',
					'action_url', 'notes_url', 'host.name', 'description' ],
			"sort" : false,
			"cell" : function(obj, tr) {
				var cell = $('<td />');

				if (obj.acknowledged)
					cell.append(icon16('acknowledged', _('Acknowledged')));

				if (obj.comments > 0)
					cell.append(icon16('add-comment', _('Comments')));

				if (!obj.notifications_enabled)
					cell.append(icon16('notify-disabled',
							_('Notification disabled')));

				if (obj.checks_disabled)
					cell.append(icon16('active-checks-disabled',
							_('Checks Disabled')));

				if (obj.is_flapping) // FIXME: Needs icon in compass
					cell.append(icon16('flapping', _('Flapping')));

				if (obj.scheduled_downtime_depth > 0)
					cell.append(icon16('scheduled-downtime',
							_('Scheduled Downtime')));

				/***************************************************************
				 * 
				 */

				// FIXME: Add nacoma link
				if (obj.pnpgraph_present)
					cell.append(icon16('pnp', _('Show performance graph'), link(
							'pnp', {
								"srv" : obj.description,
								"host" : obj.host.name
							})));

				if (obj.action_url)
					cell.append(icon16('host-actions',
							_('perform extra host actions'), $('<a />').attr(
									'href', obj.action_url)));

				if (obj.notes_url)
					cell.append(icon16('host-notes', _('View extra host notes'),
							$('<a />').attr('href', obj.notes_url)));

				return cell;
			}
		},
		"last_check" : {
			"header" : _('Last Checked'),
			"depends" : [ 'last_check' ],
			"sort" : [ 'last_check' ],
			"cell" : function(obj, tr) {
				var last_check = new Date(obj.last_check * 1000);
				return $('<td />').text(last_check.toLocaleTimeString());
			}
		},
		"duration" : {
			"header" : _('Duration'),
			"depends" : ['duration'],
			"sort" : ['last_state_change'],
			"cell" : function(obj, tr) {
				return $('<td />').text(obj.duration);
			}
		},
		"attempt" : {
			"header" : _('Attempt'),
			"depends" : [ 'current_attempt', 'max_check_attempts' ],
			"sort" : [ 'current_attempt' ],
			"cell" : function(obj, tr) {
				return $('<td />').text(
						obj.current_attempt + "/" + obj.max_check_attempts);
			}
		},
		"status_info" : {
			"header" : _('Status Information'),
			"depends" : [ 'plugin_output' ],
			"sort" : [ 'plugin_output' ],
			"cell" : function(obj, tr) {
				return $('<td style="max-width: 300px;" />').text(obj.plugin_output);
			}
		},
		"display_name" : {
			"header" : _('Display name'),
			"depends" : [ 'display_name' ],
			"sort" : [ 'display_name' ],
			"cell" : function(obj, tr) {
				return $('<td />').text(obj.display_name);
			}
		}
	},
	/***************************************************************************
	 * Render Host groups
	 **************************************************************************/
	"hostgroups" : {
		"name" : {
			"header" : _('Host Group'),
			"depends" : [ 'alias', 'name' ],
			"sort" : [ 'alias', 'name' ],
			"cell" : function(obj, tr) {
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
			"header" : _('Actions'),
			"depends" : [],
			"sort" : false,
			"cell" : function(obj, tr) {
				var cell = $('<td />');
				return cell;
			}
		},
		"host_status_summary" : {
			"header" : _('Host Status Summary'),
			"depends" : [ 'host_stats' ],
			"sort" : false,
			"cell" : function(obj, tr) {
				var cell = $('<td / >');
				cell.append(render_host_status_summary(obj.host_stats));
				return cell;
			}
		},
		"service_status_summary" : {
			"header" : _('Service Status Summary'),
			"depends" : [ 'service_stats' ],
			"sort" : false,
			"cell" : function(obj, tr) {
				var cell = $('<td / >');
				cell.append(render_service_status_summary(obj.service_stats));
				return cell;
			}
		}
	},
	/***************************************************************************
	 * Render Service groups
	 **************************************************************************/
	"servicegroups" : {
		"name" : {
			"header" : _('Service Group'),
			"depends" : [ 'alias', 'name' ],
			"sort" : [ 'alias', 'name' ],
			"cell" : function(obj, tr) {
				var cell = $('<td />');
				cell.text(obj.alias);
				cell.text(' (' + obj.name + ')');
				return cell;
			}
		},
		"actions" : {
			"header" : _('Actions'),
			"depends" : [],
			"sort" : false,
			"cell" : function(obj, tr) {
				var cell = $('<td />');
				return cell;
			}
		},
		"service_status_summary" : {
			"header" : _('Service Status Summary'),
			"depends" : [ 'service_stats' ],
			"sort" : false,
			"cell" : function(obj, tr) {
				var cell = $('<td / >');
				cell.append(render_service_status_summary(obj.service_stats));
				return cell;
			}
		}
	},
	/***************************************************************************
	 * Render Comments
	 **************************************************************************/
	"comments" : {
		"is_service" : {
			"header" : _('Type'),
			"depends" : [ 'is_service' ],
			"sort" : false,
			"cell" : function(obj, tr) {
				return $('<td />').text(obj.is_service ? 'Service' : 'Host');
			}
		},
		"host_status" : {
			"header" : '',
			"depends" : [ 'host.state_text' ],
			"sort" : [ 'host.state' ],
			"cell" : function(obj, tr) {
				return $('<td />').append(
						icon16('shield-' + obj.host.state_text,
								obj.host.state_text));

			}
		},
		"host_name" : {
			"header" : _('Host Name'),
			"depends" : [ 'host.name', 'host.icon_image' ],
			"sort" : [ 'host.name' ],
			"cell" : function(obj, tr) {
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
			"cell" : function(obj, tr) {
				if (!obj.service.description)
					return $('<td />');

				return $('<td><span class="icon-16 x16-shield-'
						+ obj.service.state_text + '"></span></td>');
			}
		},
		"service_description" : {
			"header" : _('Service'),
			"depends" : [ 'host.name', 'service.description' ],
			"sort" : [ 'service.description' ],
			"cell" : function(obj, tr) {
				if (!obj.service.description)
					return $('<td />');
				return $('<td />').append(
						extinfo_link(obj.host.name, obj.service.description)
								.text(obj.service.description));
			}
		},
		"time" : {
			"header" : _('Entry Time'),
			"depends" : [ 'entry_time' ],
			"sort" : [ 'entry_time' ],
			"cell" : function(obj, tr) {
				var time = new Date(obj.entry_time * 1000);
				return $('<td />').text(time.toLocaleTimeString());
			}
		},
		"author" : {
			"header" : _('Author'),
			"depends" : [ 'author' ],
			"sort" : [ 'author' ],
			"cell" : function(obj, tr) {
				return $('<td />').text(obj.author);
			}
		},
		"comment" : {
			"header" : _('Comment'),
			"depends" : [ 'comment' ],
			"sort" : [ 'comment' ],
			"cell" : function(obj, tr) {
				return $('<td />').text(obj.comment);
			}
		},
		"id" : {
			"header" : _('ID'),
			"depends" : [ 'id' ],
			"sort" : [ 'id' ],
			"cell" : function(obj, tr) {
				return $('<td />').text(obj.id);
			}
		},
		"persistent" : {
			"header" : _('Persistent'),
			"depends" : [ 'persistent' ],
			"sort" : [ 'persistent' ],
			"cell" : function(obj, tr) {
				var cell = $('<td />');
				if (obj.persistent)
					cell.text(_('Yes'));
				else
					cell.text(_('No'));
				return cell;
			}
		},
		"entry_type" : {
			"header" : _('Type'),
			"depends" : [ 'entry_type' ],
			"sort" : [ 'entry_type' ],
			"cell" : function(obj, tr) {
				var cell = $('<td />');
				switch (obj.entry_type) {
				case 1:
					cell.text(_("User comment"));
					break;
				case 2:
					cell.text(_("Scheduled downtime"));
					break;
				case 3:
					cell.text(_("Flapping"));
					break;
				case 4:
					cell.text(_("Acknowledgement"));
					break;
				}
				return cell;
			}
		},
		"expires" : {
			"header" : _('Expires'),
			"depends" : [ 'expires', 'expire_time' ],
			"sort" : [ 'expires', 'expire_time' ],
			"cell" : function(obj, tr) {
				var cell = $('<td />');
				if (obj.expires)
					cell.text(obj.expire_time);
				else
					cell.text(_('N/A'));
				return cell;
			}
		},
		"actions" : {
			"header" : _('Actions'),
			"depends" : [],
			"sort" : false,
			"cell" : function(obj, tr) {
				return $('<td />');
			}
		}
	},
	/***************************************************************************
	 * Render contacts
	 **************************************************************************/
	"contacts" : {
		"name" : {
			"header" : _('Name'),
			"depends" : [ 'name' ],
			"sort" : [ 'name' ],
			"cell" : function(obj, tr) {
				return $('<td />').text(obj.name);
			}
		},
		"alias" : {
			"header" : _('Alias'),
			"depends" : [ 'alias' ],
			"sort" : [ 'alias' ],
			"cell" : function(obj, tr) {
				return $('<td />').text(obj.alias);
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

function listview_render_table(data, total_count) {
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
		output.append('<h2 class="lsfilter-noresult">'+_('Empty result set')+'</h2>');
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
				row.append(columns[cur_col](obj, row).addClass('listview-cell-' + cur_col));
			}
			tbody.append(row);
		}
	}

	if( data.length < total_count)
		tbody.append(
				$('<tr class="table_pagination"/>')
					.append(
							$('<td />')
							.attr('colspan', columns.length)
							.append(_('Load more rows'))
							.click(listview_increase_length)
						)
					);

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
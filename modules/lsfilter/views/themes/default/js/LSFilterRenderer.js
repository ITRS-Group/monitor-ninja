/*
 * This file exports two objects.
 * 
 * One with methods to render list items (<li>) for status totals
 * One with methods to render columns for different tables.
 * 
 * Methods in this files is internal helpers used by above methods.
 */

function listview_columns_for_table(table)
{
	if (!listview_renderer_table[table]) return false;
	
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
	"count": function(cnt)
	{
		var container = $('<li class="extra_toolbar_category" />');
		container.append(_("Count") + ": &nbsp; ");
		container.append(icon16('shield-info', _("Matching")));
		container.append(cnt);
		return container;
	},
	
	"host_all": function(cnt)
	{
		var container = $('<li class="extra_toolbar_category" />');
		container.append(_("Hosts") + ": &nbsp; ");
		container.append(icon16('host', _("Hosts total")));
		container.append(cnt);
		return container;
	},
	"host_state_up": function(cnt)
	{
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield') + '-up',
				_("Hosts up")));
		container.append(cnt);
		return container;
	},
	"host_state_down": function(cnt)
	{
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-down', _("Hosts down")));
		container.append(cnt);
		return container;
	},
	"host_state_unreachable": function(cnt)
	{
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-unreachable', _("Hosts unreachable")));
		container.append(cnt);
		return container;
	},
	"host_pending": function(cnt)
	{
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-pending', _("Hosts pending")));
		container.append(cnt);
		return container;
	},
	
	"service_all": function(cnt)
	{
		var container = $('<li class="extra_toolbar_category" />');
		container.append(_("Services") + ": &nbsp; ");
		container.append(icon16('shield-info', _("Services total")));
		container.append(cnt);
		return container;
	},
	"service_state_ok": function(cnt)
	{
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield') + '-ok',
				_("Services ok")));
		container.append(cnt);
		return container;
	},
	"service_state_warning": function(cnt)
	{
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-warning', _("Services warning")));
		container.append(cnt);
		return container;
	},
	"service_state_critical": function(cnt)
	{
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-critical', _("Services critical")));
		container.append(cnt);
		return container;
	},
	"service_state_unknown": function(cnt)
	{
		var container = $('<li />');
		container.append(icon16(((cnt == 0) ? 'shield-not' : 'shield')
				+ '-unknown', _("Services unknown")));
		container.append(cnt);
		return container;
	},
	"service_pending": function(cnt)
	{
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

function render_summary_state(ul, state, stats, substates)
{
	var li = $('<li />').append(
			link_query(stats.queries[state]).append(icon16('shield-' + state))
					.append(
							$('<span />')
									.text(stats.stats[state] + " " + state)));
	
	var delim = ' ( ';
	var suffix = '';
	
	for ( var tag in substates) {
		var key = state + tag;
		var type = substates[tag];
		
		if (stats.stats[key]) {
			li.append(delim);
			li.append(link_query(stats.queries[key]).text(
					stats.stats[key] + ' ' + type));
			delim = ', ';
			suffix = ' ) ';
		}
	}
	
	li.append(suffix);
	
	ul.append(li);
}

function render_service_status_summary(stats)
{
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

function render_host_status_summary(stats)
{
	var ul = $('<ul class="listview-summary" />');
	
	render_summary_state(ul, 'up', stats, {});
	render_summary_state(ul, 'down', stats, {});
	render_summary_state(ul, 'unreachable', stats, {});
	render_summary_state(ul, 'pending', stats, {});
	
	return ul;
}

var listview_multi_select_cell_renderer = function(args)
{
	var checkbox = $('<input type="checkbox" name="object_select[]" />').attr(
			'value', args.obj.key);
	if (false /* listview_selection[args.obj.key] */) {
		checkbox.prop('checked', true);
		if (tr.hasClass('odd'))
			tr.addClass('selected_odd');
		else
			tr.addClass('selected_even');
	}
	checkbox.change(function(evt)
	{
		var tgt = $(evt.target);
		// listview_selection[tgt.attr('value')] = tgt.prop('checked');
		var tr = tgt.closest('tr');
		var classname = ""
		if (tr.hasClass('odd'))
			classname = 'selected_odd';
		else
			classname = 'selected_even';
		if (tgt.prop('checked')) {
			tr.addClass(classname);
		}
		else {
			tr.removeClass(classname);
		}
	});
	return $('<td style="width: 1em;" />').append(checkbox);
};

var listview_renderer_table = {
	
	/***************************************************************************
	 * Render Hosts
	 **************************************************************************/
	
	"hosts": {
		"select": {
			"header": '',
			"depends": [],
			"sort": false,
			"avalible": function(args)
			{
				return _controller_name == 'listview';
			},
			"cell": listview_multi_select_cell_renderer
		},
		"status": {
			"header": '',
			"depends": [ 'state_text' ],
			"sort": [ 'state' ],
			"cell": function(args)
			{
				return $('<td class="icon" />').append(
						icon16('shield-' + args.obj.state_text,
								args.obj.state_text));
				
			}
		},
		"name": {
			"header": _('Name'),
			"depends": [ 'name', 'icon_image' ],
			"sort": [ 'name' ],
			"cell": function(args)
			{
				var cell = $('<td />');
				cell.append(extinfo_link(args.obj.name).text(args.obj.name));
				if (args.obj.icon_image)
					cell.append(icon(args.obj.icon_image,
							extinfo_link(args.obj.name)).css('float', 'right'));
				
				return cell;
			}
		},
		"actions": {
			"header": _('Actions'),
			"depends": [ 'name', 'acknowledged', 'notifications_enabled',
					'checks_disabled', 'is_flapping',
					'scheduled_downtime_depth', 'pnpgraph_present',
					'action_url', 'notes_url', 'comments_count' ],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td />');
				
				// FIXME: icon for service-details
				cell.append(icon16('service-details',
						_('View service details for this host'), link(
								_current_uri, {
									'filter_query': '[services] host.name = "'
											+ args.obj.name + '"' // FIXME:
											// escape
								})));
				
				if (args.obj.acknowledged)
					cell.append(icon16('acknowledged', _('Acknowledged')));
				
				if (!args.obj.notifications_enabled)
					cell.append(icon16('notify-disabled',
							'Notification disabled'));
				
				if (args.obj.checks_disabled)
					cell.append(icon16('active-checks-disabled',
							_('Checks Disabled')));
				
				if (args.obj.is_flapping) // FIXME: Needs icon in compass
				cell.append(icon16('flapping', _('Flapping')));
				
				if (args.obj.scheduled_downtime_depth > 0)
					cell.append(icon16('scheduled-downtime',
							_('Scheduled Downtime')));
				
				// FIXME: Add nacoma link
				
				if (args.obj.pnpgraph_present) {
					var pnp_link = icon16('pnp', _('Show performance graph'),
							link('pnp', {
								"srv": "_HOST_",
								"host": args.obj.name
							}));
					pnp_popup( pnp_link, {
								"srv": "_HOST_",
								"host": args.obj.name
							});
					cell.append(pnp_link);
				}
				
				if (args.obj.action_url)
					cell.append(icon16('host-actions',
							_('perform extra host actions'), $('<a />').attr(
									'href', args.obj.action_url)));
				
				if (args.obj.notes_url)
					cell.append(icon16('host-notes',
							_('View extra host notes'), $('<a />').attr('href',
									args.obj.notes_url)));
				
				if (args.obj.comments_count > 0)
					cell.append(icon16('add-comment', _('Comments')));
				
				return cell;
			}
		},
		"last_check": {
			"header": _('Last Checked'),
			"depends": [ 'last_check' ],
			"sort": [ 'last_check' ],
			"cell": function(args)
			{
				return $('<td />').text(format_timestamp(args.obj.last_check));
			}
		},
		"duration": {
			"header": _('Duration'),
			"depends": [ 'duration' ],
			"sort": [ 'last_state_change' ],
			"cell": function(args)
			{
				return $('<td />').text(format_interval(args.obj.duration));
			}
		},
		"status_info": {
			"header": _('Status Information'),
			"depends": [ 'plugin_output' ],
			"sort": [ 'plugin_output' ],
			"cell": function(args)
			{
				return $('<td style="max-width: 300px;" />').text(
						args.obj.plugin_output);
			}
		},
		"services_num_all": {
			"header": icon12('shield-info').addClass('header-icon'),
			"depends": [ 'num_services' ],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td />').css('text-align', 'center');
				if (args.obj.num_services > 0) {
					cell.append(args.obj.num_services);
				}
				return cell;
			}
		},
		"services_num_all": {
			"header": icon12('shield-info').addClass('header-icon'),
			"depends": [ 'num_services' ],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td />').css('text-align', 'center');
				if (args.obj.num_services > 0) {
					cell.append(args.obj.num_services);
				}
				return cell;
			}
		},
		"services_num_ok": {
			"header": icon12('shield-ok').addClass('header-icon'),
			"depends": [ 'num_services_ok' ],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td />').css('text-align', 'center');
				if (args.obj.num_services_ok > 0) {
					cell.append(args.obj.num_services_ok);
				}
				return cell;
			}
		},
		"services_num_warning": {
			"header": icon12('shield-warning').addClass('header-icon'),
			"depends": [ 'num_services_warn' ],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td />').css('text-align', 'center');
				if (args.obj.num_services_warn > 0) {
					cell.append(args.obj.num_services_warn);
				}
				return cell;
			}
		},
		"services_num_critical": {
			"header": icon12('shield-critical').addClass('header-icon'),
			"depends": [ 'num_services_crit' ],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td />').css('text-align', 'center');
				if (args.obj.num_services_crit > 0) {
					cell.append(args.obj.num_services_crit);
				}
				return cell;
			}
		},
		"services_num_unknown": {
			"header": icon12('shield-unknown').addClass('header-icon'),
			"depends": [ 'num_services_unknown' ],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td />').css('text-align', 'center');
				if (args.obj.num_services_unknown > 0) {
					cell.append(args.obj.num_services_unknown);
				}
				return cell;
			}
		},
		"services_num_pending": {
			"header": icon12('shield-pending').addClass('header-icon'),
			"depends": [ 'num_services_pending' ],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td />').css('text-align', 'center');
				if (args.obj.num_services_pending > 0) {
					cell.append(args.obj.num_services_pending);
				}
				return cell;
			}
		},
		"display_name": {
			"header": _('Display name'),
			"depends": [ 'display_name' ],
			"sort": [ 'display_name' ],
			"cell": function(args)
			{
				return $('<td />').text(args.obj.display_name);
			}
		}
	},
	
	/***************************************************************************
	 * Render Services
	 **************************************************************************/
	
	"services": {
		"select": {
			"header": '',
			"depends": [],
			"sort": false,
			"avalible": function(args)
			{
				return _controller_name == 'listview';
			},
			"cell": listview_multi_select_cell_renderer
		},
		"host_status": {
			"header": '',
			"depends": [ 'host.state_text' ],
			"sort": [ 'host.state' ],
			"cell": function(args)
			{
				if (args.obj.host
						&& (!args.last_obj.host || args.obj.host.name != args.last_obj.host.name)) {
					return $('<td class="icon" />').append(
							icon16('shield-' + args.obj.host.state_text,
									args.obj.host.state_text));
					
				}
				else {
					return $('<td class="icon" />').addClass(
							'listview-empty-cell');
				}
			}
		},
		"host_name": {
			"header": _('Host Name'),
			"depends": [ 'host.name', 'host.icon_image' ],
			"sort": [ 'host.name' ],
			"cell": function(args)
			{
				var cell = $('<td />');
				
				if (args.obj.host
						&& (!args.last_obj.host || args.obj.host.name != args.last_obj.host.name)) {
					cell.append(extinfo_link(args.obj.host.name).text(
							args.obj.host.name));
					
					if (args.obj.host.icon_image)
						cell.append(icon(args.obj.host.icon_image,
								extinfo_link(args.obj.host.name)).css('float',
								'right'));
					
				}
				else {
					cell.addClass('listview-empty-cell');
				}
				
				return cell;
			}
		},
		"status": {
			"header": '',
			"depends": [ 'state_text' ],
			"sort": [ 'state' ],
			"cell": function(args)
			{
				return $('<td class="icon"><span class="icon-16 x16-shield-'
						+ args.obj.state_text + '"></span></td>');
			}
		},
		"description": {
			"header": _('Service'),
			"depends": [ 'host.name', 'description' ],
			"sort": [ 'description' ],
			"cell": function(args)
			{
				return $('<td />').append(
						extinfo_link(args.obj.host.name, args.obj.description)
								.text(args.obj.description));
			}
		},
		"actions": {
			"header": _('Actions'),
			"depends": [ 'acknowledged', 'comments_count',
					'notifications_enabled', 'checks_disabled', 'is_flapping',
					'scheduled_downtime_depth', 'pnpgraph_present',
					'action_url', 'notes_url', 'host.name', 'description' ],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td />');
				
				if (args.obj.acknowledged)
					cell.append(icon16('acknowledged', _('Acknowledged')));
				
				if (args.obj.comments_count > 0)
					cell.append(icon16('add-comment', _('Comments')));
				
				if (!args.obj.notifications_enabled)
					cell.append(icon16('notify-disabled',
							_('Notification disabled')));
				
				if (args.obj.checks_disabled)
					cell.append(icon16('active-checks-disabled',
							_('Checks Disabled')));
				
				if (args.obj.is_flapping) // FIXME: Needs icon in compass
				cell.append(icon16('flapping', _('Flapping')));
				
				if (args.obj.scheduled_downtime_depth > 0)
					cell.append(icon16('scheduled-downtime',
							_('Scheduled Downtime')));
				
				/***************************************************************
				 * 
				 */
				
				// FIXME: Add nacoma link
				if (args.obj.pnpgraph_present) {
					var pnp_link = icon16('pnp', _('Show performance graph'),
							link('pnp', {
								"srv": args.obj.description,
								"host": args.obj.host.name
							}));
					pnp_popup( pnp_link, {
								"srv": args.obj.description,
								"host": args.obj.host.name
							});
					cell.append(pnp_link);
				}
				
				if (args.obj.action_url)
					cell.append(icon16('host-actions',
							_('perform extra host actions'), $('<a />').attr(
									'href', args.obj.action_url)));
				
				if (args.obj.notes_url)
					cell.append(icon16('host-notes',
							_('View extra host notes'), $('<a />').attr('href',
									args.obj.notes_url)));
				
				return cell;
			}
		},
		"last_check": {
			"header": _('Last Checked'),
			"depends": [ 'last_check' ],
			"sort": [ 'last_check' ],
			"cell": function(args)
			{
				return $('<td />').text(format_timestamp(args.obj.last_check));
			}
		},
		"duration": {
			"header": _('Duration'),
			"depends": [ 'duration' ],
			"sort": [ 'last_state_change' ],
			"cell": function(args)
			{
				return $('<td />').text(format_interval(args.obj.duration));
			}
		},
		"attempt": {
			"header": _('Attempt'),
			"depends": [ 'current_attempt', 'max_check_attempts' ],
			"sort": [ 'current_attempt' ],
			"cell": function(args)
			{
				return $('<td />').text(
						args.obj.current_attempt + "/"
								+ args.obj.max_check_attempts);
			}
		},
		"status_info": {
			"header": _('Status Information'),
			"depends": [ 'plugin_output' ],
			"sort": [ 'plugin_output' ],
			"cell": function(args)
			{
				return $('<td style="max-width: 300px;" />').text(
						args.obj.plugin_output);
			}
		},
		"display_name": {
			"header": _('Display name'),
			"depends": [ 'display_name' ],
			"sort": [ 'display_name' ],
			"cell": function(args)
			{
				return $('<td />').text(args.obj.display_name);
			}
		}
	},
	/***************************************************************************
	 * Render Host groups
	 **************************************************************************/
	"hostgroups": {
		"name": {
			"header": _('Host Group'),
			"depends": [ 'alias', 'name' ],
			"sort": [ 'alias', 'name' ],
			"cell": function(args)
			{
				var cell = $('<td />');
				cell.append($('<a />').attr('href',
						'?q=[hosts] in "' + args.obj.name + '"').text(
						args.obj.alias + ' (' + args.obj.name + ')'));
				return cell;
			}
		},
		"actions": {
			"header": _('Actions'),
			"depends": [],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td />');
				return cell;
			}
		},
		"host_status_summary": {
			"header": _('Host Status Summary'),
			"depends": [ 'host_stats' ],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td / >');
				cell.append(render_host_status_summary(args.obj.host_stats));
				return cell;
			}
		},
		"service_status_summary": {
			"header": _('Service Status Summary'),
			"depends": [ 'service_stats' ],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td / >');
				cell
						.append(render_service_status_summary(args.obj.service_stats));
				return cell;
			}
		}
	},
	/***************************************************************************
	 * Render Service groups
	 **************************************************************************/
	"servicegroups": {
		"name": {
			"header": _('Service Group'),
			"depends": [ 'alias', 'name' ],
			"sort": [ 'alias', 'name' ],
			"cell": function(args)
			{
				var cell = $('<td />');
				cell.text(args.obj.alias);
				cell.text(' (' + args.obj.name + ')');
				return cell;
			}
		},
		"actions": {
			"header": _('Actions'),
			"depends": [],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td />');
				return cell;
			}
		},
		"service_status_summary": {
			"header": _('Service Status Summary'),
			"depends": [ 'service_stats' ],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td / >');
				cell
						.append(render_service_status_summary(args.obj.service_stats));
				return cell;
			}
		}
	},
	/***************************************************************************
	 * Render Comments
	 **************************************************************************/
	"comments": {
		"select": {
			"header": '',
			"depends": [],
			"sort": false,
			"avalible": function(args)
			{
				return _controller_name == 'listview';
			},
			"cell": listview_multi_select_cell_renderer
		},
		"id": {
			"header": _('ID'),
			"depends": [ 'id' ],
			"sort": [ 'id' ],
			"cell": function(args)
			{
				return $('<td />').text(args.obj.id);
			}
		},
		"is_service": {
			"header": _('Type'),
			"depends": [ 'is_service' ],
			"sort": false,
			"cell": function(args)
			{
				return $('<td />').text(
						args.obj.is_service ? 'Service' : 'Host');
			}
		},
		"host_status": {
			"header": '',
			"depends": [ 'host.state_text' ],
			"sort": [ 'host.state' ],
			"cell": function(args)
			{
				return $('<td />').append(
						icon16('shield-' + args.obj.host.state_text,
								args.obj.host.state_text));
				
			}
		},
		"host_name": {
			"header": _('Host Name'),
			"depends": [ 'host.name', 'host.icon_image' ],
			"sort": [ 'host.name' ],
			"cell": function(args)
			{
				var cell = $('<td />');
				cell.append(extinfo_link(args.obj.host.name).text(
						args.obj.host.name));
				
				if (args.obj.host.icon_image)
					cell.append(icon(args.obj.host.icon_image,
							extinfo_link(args.obj.host.name)).css('float',
							'right'));
				
				return cell;
			}
		},
		"service_status": {
			"header": '',
			"depends": [ 'service.state_text', 'service.description' ],
			"sort": [ 'service.state' ],
			"cell": function(args)
			{
				if (!args.obj.service.description) return $('<td />');
				
				return $('<td><span class="icon-16 x16-shield-'
						+ args.obj.service.state_text + '"></span></td>');
			}
		},
		"service_description": {
			"header": _('Service'),
			"depends": [ 'host.name', 'service.description' ],
			"sort": [ 'service.description' ],
			"cell": function(args)
			{
				if (!args.obj.service.description) return $('<td />');
				return $('<td />').append(
						extinfo_link(args.obj.host.name,
								args.obj.service.description).text(
								args.obj.service.description));
			}
		},
		"time": {
			"header": _('Entry Time'),
			"depends": [ 'entry_time' ],
			"sort": [ 'entry_time' ],
			"cell": function(args)
			{
				return $('<td />').text(format_timestamp(args.obj.entry_time));
			}
		},
		"author": {
			"header": _('Author'),
			"depends": [ 'author' ],
			"sort": [ 'author' ],
			"cell": function(args)
			{
				return $('<td />').text(args.obj.author);
			}
		},
		"comment": {
			"header": _('Comment'),
			"depends": [ 'comment' ],
			"sort": [ 'comment' ],
			"cell": function(args)
			{
				return $('<td />').text(args.obj.comment);
			}
		},
		"id": {
			"header": _('ID'),
			"depends": [ 'id' ],
			"sort": [ 'id' ],
			"cell": function(args)
			{
				return $('<td />').text(args.obj.id);
			}
		},
		"persistent": {
			"header": _('Persistent'),
			"depends": [ 'persistent' ],
			"sort": [ 'persistent' ],
			"cell": function(args)
			{
				var cell = $('<td />');
				if (args.obj.persistent)
					cell.text(_('Yes'));
				else
					cell.text(_('No'));
				return cell;
			}
		},
		"entry_type": {
			"header": _('Type'),
			"depends": [ 'entry_type' ],
			"sort": [ 'entry_type' ],
			"cell": function(args)
			{
				var cell = $('<td />');
				switch (args.obj.entry_type) {
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
		"expires": {
			"header": _('Expires'),
			"depends": [ 'expires', 'expire_time' ],
			"sort": [ 'expires', 'expire_time' ],
			"cell": function(args)
			{
				var cell = $('<td />');
				if (args.obj.expires)
					cell.text(args.obj.expire_time);
				else
					cell.text(_('N/A'));
				return cell;
			}
		},
		"actions": {
			"header": _('Actions'),
			"depends": [ 'id', 'entry_type', 'is_service' ],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td />');
				
				var del_icon = icon16('delete-comment');
				if (args.obj.entry_type == 2) { /* Scheduled downtime */
					del_icon = icon16('delete-downtime');
				}
				
				var del_command = 'DEL_HOST_COMMENT';
				if (args.obj.is_service) {
					del_command = 'DEL_SVC_COMMENT';
				}
				
				cell.append(link('command/submit', {
					cmd_typ: del_command,
					com_id: args.obj.id
				}).append(del_icon));
				
				return cell;
			}
		}
	},
	/***************************************************************************
	 * Render Downtimes
	 **************************************************************************/
	"downtimes": {
		"select": {
			"header": '',
			"depends": [],
			"sort": false,
			"avalible": function(args)
			{
				return _controller_name == 'listview';
			},
			"cell": listview_multi_select_cell_renderer
		},
		"is_service": {
			"header": _('Type'),
			"depends": [ 'is_service' ],
			"sort": false,
			"cell": function(args)
			{
				return $('<td />').text(
						args.obj.is_service ? 'Service' : 'Host');
			}
		},
		"host_status": {
			"header": '',
			"depends": [ 'host.state_text' ],
			"sort": [ 'host.state' ],
			"cell": function(args)
			{
				return $('<td />').append(
						icon16('shield-' + args.obj.host.state_text,
								args.obj.host.state_text));
				
			}
		},
		"host_name": {
			"header": _('Host Name'),
			"depends": [ 'host.name', 'host.icon_image' ],
			"sort": [ 'host.name' ],
			"cell": function(args)
			{
				var cell = $('<td />');
				cell.append(extinfo_link(args.obj.host.name).text(
						args.obj.host.name));
				
				if (args.obj.host.icon_image)
					cell.append(icon(args.obj.host.icon_image,
							extinfo_link(args.obj.host.name)).css('float',
							'right'));
				
				return cell;
			}
		},
		"service_status": {
			"header": '',
			"depends": [ 'service.state_text', 'service.description' ],
			"sort": [ 'service.state' ],
			"cell": function(args)
			{
				if (!args.obj.service.description) return $('<td />');
				
				return $('<td><span class="icon-16 x16-shield-'
						+ args.obj.service.state_text + '"></span></td>');
			}
		},
		"service_description": {
			"header": _('Service'),
			"depends": [ 'host.name', 'service.description' ],
			"sort": [ 'service.description' ],
			"cell": function(args)
			{
				if (!args.obj.service.description) return $('<td />');
				return $('<td />').append(
						extinfo_link(args.obj.host.name,
								args.obj.service.description).text(
								args.obj.service.description));
			}
		},
		"time": {
			"header": _('Entry Time'),
			"depends": [ 'entry_time' ],
			"sort": [ 'entry_time' ],
			"cell": function(args)
			{
				return $('<td />').text(format_timestamp(args.obj.entry_time));
			}
		},
		"author": {
			"header": _('Author'),
			"depends": [ 'author' ],
			"sort": [ 'author' ],
			"cell": function(args)
			{
				return $('<td />').text(args.obj.author);
			}
		},
		"comment": {
			"header": _('Comment'),
			"depends": [ 'comment' ],
			"sort": [ 'comment' ],
			"cell": function(args)
			{
				return $('<td />').text(args.obj.comment);
			}
		},
		"start_time": {
			"header": _("Start Time"),
			"depends": [ 'start_time' ],
			"sort": [ 'start_time' ],
			"cell": function(args)
			{
				return $('<td />').text(format_timestamp(args.obj.start_time));
			}
		},
		"end_time": {
			"header": _("End Time"),
			"depends": [ 'end_time' ],
			"sort": [ 'end_time' ],
			"cell": function(args)
			{
				return $('<td />').text(format_timestamp(args.obj.end_time));
			}
		},
		"type": {
			"header": _("Type"),
			"depends": [ 'fixed' ],
			"sort": [ 'fixed' ],
			"cell": function(args)
			{
				return $('<td />').text(
						args.obj.fixed ? _("Fixed") : _("Flexible"));
			}
		},
		"duration": {
			"header": _("Duration"),
			"depends": [ 'start_time', 'end_time' ],
			"sort": false,
			"cell": function(args)
			{
				return $('<td />')
						.text(
								format_interval(args.obj.end_time
										- args.obj.start_time));
			}
		},
		"triggered_by": {
			"header": _("Triggered by"),
			"depends": [ 'triggered_by_text' ],
			"sort": [ 'triggered_by' ],
			"cell": function(args)
			{
				return $('<td />').text(args.obj.triggered_by_text);
			}
		},
		"actions": {
			"header": _('Actions'),
			"depends": [ 'id', 'is_service', 'host.name', 'service.description' ],
			"sort": false,
			"cell": function(args)
			{
				var cell = $('<td />');
				
				// Delete
				var del_icon = icon16('delete-downtime',
						_("Delete/cancel this scheduled downtime entry"));
				
				var del_command = 'DEL_HOST_DOWNTIME';
				if (args.obj.is_service) {
					del_command = 'DEL_SVC_DOWNTIME';
				}
				
				cell.append(link('command/submit', {
					cmd_typ: del_command,
					downtime_id: args.obj.id
				}).append(del_icon));
				
				// Schedule recurring
				
				var recurring_args = {
					host: args.obj.host.name
				};
				if (args.obj.service.description) {
					recurring_args.service = args.obj.service.description;
				}
				cell.append(link('recurring_downtime', recurring_args).append(
						icon16('recurring-downtime')));
				
				return cell;
			}
		}
	},
	/***************************************************************************
	 * Render contacts
	 **************************************************************************/
	"contacts": {
		"name": {
			"header": _('Name'),
			"depends": [ 'name' ],
			"sort": [ 'name' ],
			"cell": function(args)
			{
				return $('<td />').text(args.obj.name);
			}
		},
		"alias": {
			"header": _('Alias'),
			"depends": [ 'alias' ],
			"sort": [ 'alias' ],
			"cell": function(args)
			{
				return $('<td />').text(args.obj.alias);
			}
		}
	},
	/***************************************************************************
	 * Render notifications
	 **************************************************************************/
	"notifications": {
		"status": {
			"header": '',
			"depends": [ 'state_text' ],
			"sort": [ 'notification_type', 'state' ],
			"cell": function(args)
			{
				return $('<td class="icon" />').append(
						icon16('shield-' + args.obj.state_text,
								args.obj.state_text));
				
			}
		},
		"host_name": {
			"header": _('Host'),
			"depends": [ 'host.name' ],
			"sort": [ 'host.name', 'service.description' ],
			"cell": function(args)
			{
				var cell = $('<td />');
				cell.append(extinfo_link(args.obj.host_name).text(
						args.obj.host_name));
				
				return cell;
			}
		},
		"service_description": {
			"header": _('Service'),
			"depends": [ 'host.name', 'service.description' ],
			"sort": [ 'service.description' ],
			"cell": function(args)
			{
				var cell = $('<td />');
				cell.append(extinfo_link(args.obj.host_name,
						args.obj.service_description).text(
						args.obj.service_description));
				
				return cell;
			}
		},
		"time": {
			"header": _('Time'),
			"depends": [ 'start_time' ],
			"sort": [ 'start_time' ],
			"cell": function(args)
			{
				return $('<td />').text(format_timestamp(args.obj.start_time));
			}
		},
		"contact": {
			"header": _('Contact'),
			"depends": [ 'contact_name' ],
			"sort": [ 'contact_name' ],
			"cell": function(args)
			{
				return $('<td />').text(args.obj.contact_name);
			}
		},
		"notification_command": {
			"header": _('Notification Command'),
			"depends": [ 'command_name' ],
			"sort": [ 'command_name' ],
			"cell": function(args)
			{
				return $('<td />').text(args.obj.command_name);
			}
		},
		"information": {
			"header": _('Information'),
			"depends": [ 'output' ],
			"sort": [ 'output' ],
			"cell": function(args)
			{
				return $('<td />').text(args.obj.output);
			}
		}
	}
};

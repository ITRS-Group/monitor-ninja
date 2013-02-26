/*******************************************************************************
 * Table renderer
 ******************************************************************************/

listview_renderer_table.hosts = {
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
		"sort": [ 'has_been_checked', 'state' ],
		"cell": function(args)
		{
			return $('<td class="icon" />')
					.append(
							icon16('shield-' + args.obj.state_text,
									args.obj.state_text));
			
		}
	},
	"name": {
		"header": _('Name'),
		"depends": [ 'display_name', 'name', 'icon_image' ],
		"sort": [ 'display_name' ],
		"cell": function(args)
		{
			var cell = $('<td />');
			cell.append(extinfo_link({
				host: args.obj.name
			}).text(args.obj.display_name));
			if (args.obj.icon_image)
				cell.append(icon(args.obj.icon_image, extinfo_link({
					host: args.obj.name
				})).css('float', 'right'));
			
			return cell;
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
	},
	"actions": {
		"header": _('Actions'),
		"depends": [ 'name', 'acknowledged', 'notifications_enabled',
				'checks_disabled', 'is_flapping', 'scheduled_downtime_depth',
				'pnpgraph_present', 'action_url', 'notes_url', 'config_url',
				'comments_count' ],
		"sort": false,
		"cell": function(args)
		{
			var cell = $('<td />');
			
			// FIXME: icon for service-details
			cell.append(icon16('service-details',
					_('View service details for this host'),
					link_query('[services] host.name = "' + args.obj.name + '"' // FIXME:
					// escape
					)));
			
			if (args.obj.acknowledged)
				cell.append(icon16('acknowledged', _('Acknowledged')));
			
			if (!args.obj.notifications_enabled)
				cell.append(icon16('notify-disabled', 'Notification disabled'));
			
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
				var pnp_link = icon16('pnp', _('Show performance graph'), link(
						'pnp', {
							"srv": "_HOST_",
							"host": args.obj.name
						}));
				pnp_popup(pnp_link, {
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
				cell.append(icon16('host-notes', _('View extra host notes'), $(
						'<a />').attr('href', args.obj.notes_url)));
			
			if (args.obj.config_url)
				cell.append(icon16('nacoma', _('Configure this host'), $(
						'<a />').attr('href', args.obj.config_url)));
			
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
		"sort": [ 'last_state_change desc' ],
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
				cell.addClass('cell_svccnt_all');
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
				cell.addClass('cell_svccnt_ok');
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
				cell.addClass('cell_svccnt_warning');
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
				cell.addClass('cell_svccnt_critical');
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
				cell.addClass('cell_svccnt_unknown');
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
				cell.addClass('cell_svccnt_pending');
			}
			return cell;
		}
	}
};

listview_renderer_table.services = {
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
				return $('<td class="icon" />').addClass('listview-empty-cell');
			}
		}
	},
	"host_name": {
		"header": _('Host Name'),
		"depends": [ 'host.display_name', 'host.name', 'host.icon_image' ],
		"sort": [ 'host.display_name' ],
		"cell": function(args)
		{
			var cell = $('<td />');
			
			if (args.obj.host
					&& (!args.last_obj.host || args.obj.host.name != args.last_obj.host.name)) {
				cell.append(extinfo_link({
					host: args.obj.host.name
				}).text(args.obj.host.display_name));
				
				if (args.obj.host.icon_image)
					cell.append(icon(args.obj.host.icon_image, extinfo_link({
						host: args.obj.host.name
					})).css('float', 'right'));
				
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
		"depends": [ 'host.name', 'description', 'display_name' ],
		"sort": [ 'display_name' ],
		"cell": function(args)
		{
			return $('<td />').append(extinfo_link({
				host: args.obj.host.name,
				service: args.obj.description
			}).text(args.obj.display_name));
		}
	},
	"actions": {
		"header": _('Actions'),
		"depends": [ 'acknowledged', 'comments_count', 'notifications_enabled',
				'checks_disabled', 'is_flapping', 'scheduled_downtime_depth',
				'pnpgraph_present', 'action_url', 'notes_url', 'config_url',
				'host.name', 'description' ],
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
				var pnp_link = icon16('pnp', _('Show performance graph'), link(
						'pnp', {
							"srv": args.obj.description,
							"host": args.obj.host.name
						}));
				pnp_popup(pnp_link, {
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
				cell.append(icon16('host-notes', _('View extra host notes'), $(
						'<a />').attr('href', args.obj.notes_url)));
			
			if (args.obj.config_url)
				cell.append(icon16('nacoma', _('Configure this service'), $(
						'<a />').attr('href', args.obj.config_url)));
			
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
		"sort": [ 'last_state_change desc' ],
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
	}
};

listview_renderer_table.hostgroups = {
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
			cell.append(icon16('extended-information', _('Actions'),
					extinfo_link({
						hostgroup: args.obj.name
					})));
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
			cell.append(render_service_status_summary(args.obj.service_stats));
			return cell;
		}
	}
};

listview_renderer_table.servicegroups = {
	"name": {
		"header": _('Service Group'),
		"depends": [ 'alias', 'name' ],
		"sort": [ 'alias', 'name' ],
		"cell": function(args)
		{
			var cell = $('<td />');
			cell.append($('<a />').attr('href',
					'?q=[services] in "' + args.obj.name + '"').text(
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
			cell.append(icon16('extended-information', _('Actions'),
					extinfo_link({
						servicegroup: args.obj.name
					})));
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
			cell.append(render_service_status_summary(args.obj.service_stats));
			return cell;
		}
	}
};

listview_renderer_table.comments = {
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
			return $('<td />').text(args.obj.is_service ? 'Service' : 'Host');
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
			cell.append(extinfo_link({
				host: args.obj.host.name
			}).text(args.obj.host.name));
			
			if (args.obj.host.icon_image)
				cell.append(icon(args.obj.host.icon_image, extinfo_link({
					host: args.obj.host.name
				})).css('float', 'right'));
			
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
			return $('<td />').append(extinfo_link({
				host: args.obj.host.name,
				service: args.obj.service.description
			}).text(args.obj.service.description));
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
			
			var del_command = 'DEL_HOST_COMMENT';
			if (args.obj.is_service) del_command = 'DEL_SVC_COMMENT';
			
			cell.append(link('command/submit', {
				cmd_typ: del_command,
				'comment_id': args.obj.id
			}).append(icon16('delete-comment')));
			
			return cell;
		}
	}
};
listview_renderer_table.downtimes = {
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
			return $('<td />').text(args.obj.is_service ? 'Service' : 'Host');
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
				cell
						.append(icon(args.obj.host.icon_image,
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
			return $('<td />').append(extinfo_link({
				host: args.obj.host.name,
				service: args.obj.service.description
			}).text(args.obj.service.description));
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
			return $('<td />')
					.text(args.obj.fixed ? _("Fixed") : _("Flexible"));
		}
	},
	"duration": {
		"header": _("Duration"),
		"depends": [ 'start_time', 'end_time' ],
		"sort": false,
		"cell": function(args)
		{
			return $('<td />').text(
					format_interval(args.obj.end_time - args.obj.start_time));
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
};

listview_renderer_table.contacts = {
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
};

listview_renderer_table.notifications = {
	"status": {
		"header": '',
		"depends": [ 'state_text' ],
		"sort": [ 'notification_type', 'state' ],
		"cell": function(args)
		{
			return $('<td class="icon" />')
					.append(
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
			cell.append(extinfo_link({
				host: args.obj.host_name
			}).text(args.obj.host_name));
			
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
			cell.append(extinfo_link({
				host: args.obj.host_name,
				service: args.obj.service_description
			}).text(args.obj.service_description));
			
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
};

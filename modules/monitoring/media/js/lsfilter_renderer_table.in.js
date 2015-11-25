/*******************************************************************************
 * Table renderer
 ******************************************************************************/

listview_renderer_table.hosts = {
	"state" : {
		"header" : '',
		"depends" : [ 'state_text', 'name' ],
		"sort" : [ 'has_been_checked desc', 'state desc' ],
		"cell" : function(args) {
			return $('<td class="icon obj_properties" />')
					.append(
							icon16('shield-' + args.obj.state_text, args.obj.state_text)
					)
					.addClass(args.obj.state_text)
					.attr('data-table', 'hosts')
					.attr('data-object', args.obj.key);
		}
	},
	"name" : {
		"header" : _('Name'),
		"depends" : [ 'name', 'icon_image', 'address' ],
		"sort" : [ 'name' ],
		"cell" : function(args) {
			var cell = $('<td />');
			cell.append(extinfo_link({
				host : args.obj.name
			}).attr('title', args.obj.address).update_text(args.obj.name));
			if (args.obj.icon_image)
				cell.append(icon(args.obj.icon_image, extinfo_link({
					host : args.obj.name
				})).css('float', 'right'));

			return cell;
		}
	},
	"alias" : {
		"header" : _('Alias'),
		"depends" : [ 'alias' ],
		"sort" : [ 'alias' ],
		"cell" : function(args) {
			return $('<td />').update_text(args.obj.alias);
		}
	},
	"status" : {
		"header" : _('Status'),
		"depends" : [ 'name', 'acknowledged', 'notifications_enabled',
				'checks_disabled', 'is_flapping', 'scheduled_downtime_depth',
				'pnpgraph_present', 'comments_count' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />');

			if (args.obj.pnpgraph_present > 0) {

				var pnp_link = icon16('pnp', _('Show performance graph'), link(
						'pnp', {
							"srv" : "_HOST_",
							"host" : args.obj.name
						}));

				pnp_link.attr('data-popover',
					'pnp:' + args.obj.name
				);

				cell.append(pnp_link);

			}

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

			if (args.obj.comments_count > 0)
				cell.append(comment_icon(args.obj.name, null));

			return cell;
		}
	},
	"actions" : {
		"header" : _('Actions'),
		"depends" : [ 'name', 'action_url', 'config_url', 'notes_url',
				'config_allowed' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />');

			// FIXME: icon for service-details
			cell.append(icon16('service-details',
					_('View service details for this host'),
					link_query('[services] host.name = "' + args.obj.name + '"' // FIXME:
					// escape
					)));

			if (args.obj.action_url) {
				cell.append(icon16('host-actions',
						_('Perform extra host actions'), $('<a />').attr(
								'href', args.obj.action_url).attr('target',
								_action_url_target)));
			}

			if (args.obj.config_url && args.obj.config_allowed)
				cell.append(icon16('nacoma', _('Configure this host'), $(
						'<a />').attr('href', args.obj.config_url)));

			if (args.obj.notes_url) {
				cell.append(icon16('host-notes', _('View extra host notes'), $(
						'<a />').attr('href', args.obj.notes_url).attr(
						'target', _notes_url_target)));
			}

			return cell;
		}
	},
	"last_check" : {
		"header" : _('Last Checked'),
		"depends" : [ 'last_check' ],
		"sort" : [ 'last_check' ],
		"cell" : function(args) {
			return $('<td />').text(format_timestamp(args.obj.last_check));
		}
	},
	"duration" : {
		"header" : _('Duration'),
		"depends" : [ 'duration' ],
		"sort" : [ 'last_state_change desc' ],
		"cell" : function(args) {
			return $('<td />').text(format_interval(args.obj.duration));
		}
	},
	"status_information" : {
		"header" : _('Status Information'),
		"depends" : [ 'plugin_output' ],
		"sort" : [ 'plugin_output' ],
		"cell" : function(args) {
			return $('<td class="restricted-output" />').update_text(
					args.obj.plugin_output);
		}
	},
	"services_num_all" : {
		"header" : icon12('shield-info').addClass('header-icon'),
		"depends" : [ 'num_services', 'name' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />').css('text-align', 'center');
			if (args.obj.num_services > 0) {
				cell.append(link_query(
						'[services] host.name = "' + args.obj.name + '"')
						.append(args.obj.num_services));
				cell.addClass('state-info');
			}
			return cell;
		}
	},
	"services_num_ok" : {
		"header" : icon12('shield-ok').addClass('header-icon'),
		"depends" : [ 'num_services_ok' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />').css('text-align', 'center');
			if (args.obj.num_services_ok > 0) {
				cell.append(link_query(
						'[services] host.name = "' + args.obj.name
								+ '" and state=0 and has_been_checked!=0')
						.append(args.obj.num_services_ok));
				cell.addClass('state-ok');
			}
			return cell;
		}
	},
	"services_num_warning" : {
		"header" : icon12('shield-warning').addClass('header-icon'),
		"depends" : [ 'num_services_warn' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />').css('text-align', 'center');
			if (args.obj.num_services_warn > 0) {
				cell.append(link_query(
						'[services] host.name = "' + args.obj.name
								+ '" and state=1 and has_been_checked!=0')
						.append(args.obj.num_services_warn));
				cell.addClass('state-warning');
			}
			return cell;
		}
	},
	"services_num_critical" : {
		"header" : icon12('shield-critical').addClass('header-icon'),
		"depends" : [ 'num_services_crit' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />').css('text-align', 'center');
			if (args.obj.num_services_crit > 0) {
				cell.append(link_query(
						'[services] host.name = "' + args.obj.name
								+ '" and state=2 and has_been_checked!=0')
						.append(args.obj.num_services_crit));
				cell.addClass('state-critical');
			}
			return cell;
		}
	},
	"services_num_unknown" : {
		"header" : icon12('shield-unknown').addClass('header-icon'),
		"depends" : [ 'num_services_unknown' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />').css('text-align', 'center');
			if (args.obj.num_services_unknown > 0) {
				cell.append(link_query(
						'[services] host.name = "' + args.obj.name
								+ '" and state=3 and has_been_checked!=0')
						.append(args.obj.num_services_unknown));
				cell.addClass('state-unknown');
			}
			return cell;
		}
	},
	"services_num_pending" : {
		"header" : icon12('shield-pending').addClass('header-icon'),
		"depends" : [ 'num_services_pending' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />').css('text-align', 'center');
			if (args.obj.num_services_pending > 0) {
				cell.append(link_query(
						'[services] host.name = "' + args.obj.name
								+ '" and has_been_checked=0').append(
						args.obj.num_services_pending));
				cell.addClass('state-pending');
			}
			return cell;
		}
	}
};

listview_renderer_table.services = {
	"host_state" : {
		"header" : '',
		"depends" : [ 'host.state_text', 'host.name' ],
		"sort" : [ 'host.state' ],
		"cell" : function(args) {
			if (args.obj.host
					&& (!args.last_obj.host || args.obj.host.name != args.last_obj.host.name)) {
				return $('<td class="icon obj_properties" />')
					.append(
						icon16('shield-' + args.obj.host.state_text, args.obj.host.state_text)
					)
					.addClass(args.obj.host.state_text)
					.attr('data-table', 'hosts')
					.attr('data-object', args.obj.host.name);

			} else {
				return $('<td class="icon" />').addClass('listview-empty-cell');
			}
		}
	},
	"host_name" : {
		"header" : _('Host Name'),
		"depends" : [ 'host.name', 'host.icon_image', 'host.address' ],
		"sort" : [ 'host.name', 'description' ],
		"cell" : function(args) {
			var cell = $('<td />');

			if (args.obj.host
					&& (!args.last_obj.host || args.obj.host.name != args.last_obj.host.name)) {
				cell.append(extinfo_link({
					host : args.obj.host.name
				}).attr('title', args.obj.host.address).update_text(
						args.obj.host.name));

				if (args.obj.host.icon_image)
					cell.append(icon(args.obj.host.icon_image, extinfo_link({
						host : args.obj.host.name
					})).css('float', 'right'));

			} else {
				cell.addClass('listview-empty-cell');
			}

			return cell;
		}
	},
	"host_actions" : {
		"header" : _('Host Actions'),
		"depends" : [ 'host.name', 'host.action_url', 'host.config_url',
				'host.notes_url', 'host.config_allowed' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />');

			if (args.obj.host
					&& (!args.last_obj.host || args.obj.host.name != args.last_obj.host.name)) {
				cell.append(icon16('service-details',
						_('View service details for this host'),
						link_query('[services] host.name = "'
								+ args.obj.host.name + '"' // FIXME:
								// escape
						)));

				if (args.obj.host.action_url)
					cell.append(icon16('host-actions',
							_('Perform extra host actions'), $('<a />').attr(
									'href', args.obj.host.action_url)));

				if (args.obj.host.config_url && args.obj.host.config_allowed)
					cell.append(icon16('nacoma', _('Configure this host'), $(
							'<a />').attr('href', args.obj.host.config_url)));

				if (args.obj.host.notes_url)
					cell.append(icon16('host-notes',
							_('View extra host notes'), $('<a />').attr('href',
									args.obj.host.notes_url)));
			} else {
				cell.addClass('listview-empty-cell');
			}

			return cell;
		}
	},
	"state" : {
		"header" : '',
		"depends" : [ 'state_text', 'description', 'host.name' ],
		"sort" : [ 'has_been_checked desc', 'state desc' ],
		"cell" : function(args) {
			return $(
					'<td class="icon svc_obj_properties"><span class="icon-16 x16-shield-'
							+ args.obj.state_text + '"></span></td>').addClass(
					args.obj.state_text)
					.attr('data-table', 'services')
					.attr('data-object', args.obj.key);
		}
	},
	"description" : {
		"header" : _('Service'),
		"depends" : [ 'host.name', 'description', 'icon_image' ],
		"sort" : [ 'description' ],
		"cell" : function(args) {

			var cell = $('<td />');

			cell.append(extinfo_link({
				host : args.obj.host.name,
				service : args.obj.description
			}).update_text(args.obj.description));

			if (args.obj.icon_image) {
				cell.append(icon(args.obj.icon_image, extinfo_link({
					host : args.obj.host.name,
					service : args.obj.description
				})).css('float', 'right'));
			}

			return cell;

		}
	},
	"status" : {
		"header" : _('Status'),
		"depends" : [ 'host.name', 'description', 'pnpgraph_present',
				'acknowledged', 'comments_count', 'notifications_enabled',
				'checks_disabled', 'is_flapping', 'scheduled_downtime_depth',
				'host.scheduled_downtime_depth' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />');

			if (args.obj.pnpgraph_present > 0) {

				var pnp_link = icon16('pnp', _('Show performance graph'), link(
						'pnp', {
							"srv" : args.obj.description,
							"host" : args.obj.host.name
						}));

				pnp_link.attr('data-popover',
					'image:/monitor/op5/pnp/image?host=' + args.obj.host.name + '&srv=' + args.obj.description + '&view=0&source=0'
				);

				cell.append(pnp_link);

			}

			if (args.obj.acknowledged)
				cell.append(icon16('acknowledged', _('Acknowledged')));

			if (args.obj.comments_count > 0)
				cell.append(comment_icon(args.obj.host.name,
						args.obj.description));

			if (!args.obj.notifications_enabled)
				cell.append(icon16('notify-disabled',
						_('Notification disabled')));

			if (args.obj.checks_disabled)
				cell.append(icon16('active-checks-disabled',
						_('Checks Disabled')));

			if (args.obj.is_flapping) // FIXME: Needs icon in compass
				cell.append(icon16('flapping', _('Flapping')));

			if ((args.obj.scheduled_downtime_depth > 0)
					|| (args.obj.host.scheduled_downtime_depth > 0))
				cell.append(icon16('scheduled-downtime',
						_('Scheduled Downtime')));

			return cell;
		}
	},
	"actions" : {
		"header" : _('Actions'),
		"depends" : [ 'action_url', 'config_url', 'notes_url', 'config_allowed' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />');

			if (args.obj.action_url) {
				cell.append(icon16('host-actions',
						_('Perform extra service actions'), $('<a />').attr({
							'href': args.obj.action_url,
							'target': _action_url_target
						})));
			}

			if (args.obj.config_url && args.obj.config_allowed)
				cell.append(icon16('nacoma', _('Configure this service'), $(
						'<a />').attr('href', args.obj.config_url)));

			if (args.obj.notes_url)
				cell.append(icon16('host-notes', _('View extra service notes'),
						$('<a />').attr('href', args.obj.notes_url)));

			return cell;
		}
	},
	"last_check" : {
		"header" : _('Last Checked'),
		"depends" : [ 'last_check' ],
		"sort" : [ 'last_check' ],
		"cell" : function(args) {
			return $('<td />').text(format_timestamp(args.obj.last_check));
		}
	},
	"duration" : {
		"header" : _('Duration'),
		"depends" : [ 'duration' ],
		"sort" : [ 'last_state_change desc' ],
		"cell" : function(args) {
			return $('<td />').text(format_interval(args.obj.duration));
		}
	},
	"attempt" : {
		"header" : _('Attempt'),
		"depends" : [ 'current_attempt', 'max_check_attempts' ],
		"sort" : [ 'current_attempt' ],
		"cell" : function(args) {
			return $('<td />').text(
					args.obj.current_attempt + "/"
							+ args.obj.max_check_attempts);
		}
	},
	"status_information" : {
		"header" : _('Status Information'),
		"depends" : [ 'plugin_output' ],
		"sort" : [ 'plugin_output' ],
		"cell" : function(args) {
			return $('<td class="restricted-output" />').update_text(
					args.obj.plugin_output);
		}
	}
};

listview_renderer_table.hostgroups = {
	"name" : {
		"header" : _('Host Group'),
		"depends" : [ 'alias', 'name' ],
		"sort" : [ 'alias', 'name' ],
		"cell" : function(args) {
			var cell = $('<td />');
			cell.append(link_query('[hosts] groups >= "' + args.obj.name + '"')
					.update_text(args.obj.alias + ' (' + args.obj.name + ')'));
			return cell;
		}
	},
	"actions" : {
		"header" : _('Actions'),
		"depends" : [],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />');
			cell.append(icon16('extended-information', _('Actions'),
					extinfo_link({
						hostgroup : args.obj.name
					})));
			return cell;
		}
	},
	"host_status_summary" : {
		"header" : _('Host Status Summary'),
		"depends" : [ 'host_stats' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td / >');
			cell.append(render_host_status_summary(args.obj.host_stats));
			return cell;
		}
	},
	"service_status_summary" : {
		"header" : _('Service Status Summary'),
		"depends" : [ 'service_stats' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td / >');
			cell.append(render_service_status_summary(args.obj.service_stats));
			return cell;
		}
	}
};

listview_renderer_table.servicegroups = {
	"name" : {
		"header" : _('Service Group'),
		"depends" : [ 'alias', 'name' ],
		"sort" : [ 'alias', 'name' ],
		"cell" : function(args) {
			var cell = $('<td />');
			cell.append(link_query('[services] groups >= "' + args.obj.name + '"')
					.update_text(args.obj.alias + ' (' + args.obj.name + ')'));
			return cell;
		}
	},
	"actions" : {
		"header" : _('Actions'),
		"depends" : [],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />');
			cell.append(icon16('extended-information', _('Actions'),
					extinfo_link({
						servicegroup : args.obj.name
					})));
			return cell;
		}
	},
	"service_status_summary" : {
		"header" : _('Service Status Summary'),
		"depends" : [ 'service_stats' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td / >');
			cell.append(render_service_status_summary(args.obj.service_stats));
			return cell;
		}
	}
};

listview_renderer_table.comments = {
	"id" : {
		"header" : _('ID'),
		"depends" : [ 'id' ],
		"sort" : [ 'id' ],
		"cell" : function(args) {
			return $('<td />').text(args.obj.id);
		}
	},
	"object_type" : {
		"header" : _('Type'),
		"depends" : [ 'is_service' ],
		"sort" : false,
		"cell" : function(args) {
			return $('<td />').text(args.obj.is_service ? 'Service' : 'Host');
		}
	},
	"host_state" : {
		"header" : '',
		"depends" : [ 'host.state_text' ],
		"sort" : [ 'host.state' ],
		"cell" : function(args) {
			return $('<td />').append(
					icon16('shield-' + args.obj.host.state_text,
							args.obj.host.state_text));

		}
	},
	"host_name" : {
		"header" : _('Host Name'),
		"depends" : [ 'host.name', 'host.icon_image' ],
		"sort" : [ 'host.name' ],
		"cell" : function(args) {
			var cell = $('<td />');
			cell.append(extinfo_link({
				host : args.obj.host.name
			}).update_text(args.obj.host.name));

			if (args.obj.host.icon_image)
				cell.append(icon(args.obj.host.icon_image, extinfo_link({
					host : args.obj.host.name
				})).css('float', 'right'));

			return cell;
		}
	},
	"service_state" : {
		"header" : '',
		"depends" : [ 'service.state_text', 'service.description' ],
		"sort" : [ 'service.state' ],
		"cell" : function(args) {
			if (!args.obj.service.description)
				return $('<td />');

			return $('<td><span class="icon-16 x16-shield-'
					+ args.obj.service.state_text + '"></span></td>');
		}
	},
	"service_description" : {
		"header" : _('Service'),
		"depends" : [ 'host.name', 'service.description' ],
		"sort" : [ 'service.description' ],
		"cell" : function(args) {
			if (!args.obj.service.description)
				return $('<td />');
			return $('<td />').append(extinfo_link({
				host : args.obj.host.name,
				service : args.obj.service.description
			}).update_text(args.obj.service.description));
		}
	},
	"entry_time" : {
		"header" : _('Entry Time'),
		"depends" : [ 'entry_time' ],
		"sort" : [ 'entry_time' ],
		"cell" : function(args) {
			return $('<td />').text(format_timestamp(args.obj.entry_time));
		}
	},
	"author" : {
		"header" : _('Author'),
		"depends" : [ 'author' ],
		"sort" : [ 'author' ],
		"cell" : function(args) {
			return $('<td />').text(args.obj.author);
		}
	},
	"comment" : {
		"header" : _('Comment'),
		"depends" : [ 'comment' ],
		"sort" : [ 'comment' ],
		"cell" : function(args) {
			return $('<td class="restricted-output"/>').update_text(args.obj.comment);
		}
	},
	"id" : {
		"header" : _('ID'),
		"depends" : [ 'id' ],
		"sort" : [ 'id' ],
		"cell" : function(args) {
			return $('<td />').text(args.obj.id);
		}
	},
	"persistent" : {
		"header" : _('Persistent'),
		"depends" : [ 'persistent' ],
		"sort" : [ 'persistent' ],
		"cell" : function(args) {
			var cell = $('<td />');
			if (args.obj.persistent)
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
		"cell" : function(args) {
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
	"expires" : {
		"header" : _('Expires'),
		"depends" : [ 'expires', 'expire_time' ],
		"sort" : [ 'expires', 'expire_time' ],
		"cell" : function(args) {
			var cell = $('<td />');
			if (args.obj.expires)
				cell.text(args.obj.expire_time);
			else
				cell.text(_('N/A'));
			return cell;
		}
	},
	"actions" : {
		"header" : _('Actions'),
		"depends" : [ 'id', 'entry_type', 'is_service' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />');
			cell.append(
				icon16(
					'delete-comment',
					_('Delete comment'),
					link(
						'cmd',
						{
							'command': 'delete',
							'table': 'comments',
							'object': args.obj.id+";"+Number(args.obj.is_service)
						}
					)
						.attr('title', 'Delete comment')
						.attr('class', 'action_delete_comment')
				)
			);
			return cell;
		}
	}
};
listview_renderer_table.downtimes = {
	"id" : {
		"header" : _('ID'),
		"depends" : [ 'id' ],
		"sort" : [ 'id' ],
		"cell" : function(args) {
			return $('<td />').text(args.obj.id);
		}
	},
	"object_type" : {
		"header" : _('Type'),
		"depends" : [ 'is_service' ],
		"sort" : false,
		"cell" : function(args) {
			return $('<td />').text(args.obj.is_service ? 'Service' : 'Host');
		}
	},
	"host_state" : {
		"header" : '',
		"depends" : [ 'host.state_text' ],
		"sort" : [ 'host.state' ],
		"cell" : function(args) {
			return $('<td />').append(
					icon16('shield-' + args.obj.host.state_text,
							args.obj.host.state_text));

		}
	},
	"host_name" : {
		"header" : _('Host Name'),
		"depends" : [ 'host.name', 'host.icon_image' ],
		"sort" : [ 'host.name' ],
		"cell" : function(args) {
			var cell = $('<td />');
			cell.append(extinfo_link({
				host : args.obj.host.name
			}).update_text(args.obj.host.name));

			if (args.obj.host.icon_image)
				cell.append(icon(args.obj.host.icon_image, extinfo_link({
					host : args.obj.host.name
				})).css('float', 'right'));

			return cell;
		}
	},
	"service_state" : {
		"header" : '',
		"depends" : [ 'service.state_text', 'service.description' ],
		"sort" : [ 'service.state' ],
		"cell" : function(args) {
			if (!args.obj.service.description)
				return $('<td />');

			return $('<td><span class="icon-16 x16-shield-'
					+ args.obj.service.state_text + '"></span></td>');
		}
	},
	"service_description" : {
		"header" : _('Service'),
		"depends" : [ 'host.name', 'service.description' ],
		"sort" : [ 'service.description' ],
		"cell" : function(args) {
			if (!args.obj.service.description)
				return $('<td />');
			return $('<td />').append(extinfo_link({
				host : args.obj.host.name,
				service : args.obj.service.description
			}).update_text(args.obj.service.description));
		}
	},
	"entry_time" : {
		"header" : _('Entry Time'),
		"depends" : [ 'entry_time' ],
		"sort" : [ 'entry_time' ],
		"cell" : function(args) {
			return $('<td />').text(format_timestamp(args.obj.entry_time));
		}
	},
	"author" : {
		"header" : _('Author'),
		"depends" : [ 'author' ],
		"sort" : [ 'author' ],
		"cell" : function(args) {
			return $('<td />').text(args.obj.author);
		}
	},
	"comment" : {
		"header" : _('Comment'),
		"depends" : [ 'comment' ],
		"sort" : [ 'comment' ],
		"cell" : function(args) {
			return $('<td />').update_text(args.obj.comment);
		}
	},
	"start_time" : {
		"header" : _("Start Time"),
		"depends" : [ 'start_time' ],
		"sort" : [ 'start_time' ],
		"cell" : function(args) {
			return $('<td />').text(format_timestamp(args.obj.start_time));
		}
	},
	"end_time" : {
		"header" : _("End Time"),
		"depends" : [ 'end_time' ],
		"sort" : [ 'end_time' ],
		"cell" : function(args) {
			return $('<td />').text(format_timestamp(args.obj.end_time));
		}
	},
	"type" : {
		"header" : _("Type"),
		"depends" : [ 'fixed' ],
		"sort" : [ 'fixed' ],
		"cell" : function(args) {
			return $('<td />')
					.text(args.obj.fixed ? _("Fixed") : _("Flexible"));
		}
	},
	"duration" : {
		"header" : _("Duration"),
		"depends" : [ 'start_time', 'end_time' ],
		"sort" : false,
		"cell" : function(args) {
			return $('<td />').text(
					format_interval(args.obj.end_time - args.obj.start_time));
		}
	},
	"triggered_by" : {
		"header" : _("Triggered by"),
		"depends" : [ 'triggered_by_text' ],
		"sort" : [ 'triggered_by' ],
		"cell" : function(args) {
			return $('<td />').text(args.obj.triggered_by_text);
		}
	},
	"actions" : {
		"header" : _('Actions'),
		"depends" : [ 'id', 'is_service', 'host.name', 'service.description' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />');

			// Delete
			cell.append(
				icon16(
					'delete-downtime',
					_("Delete/cancel this scheduled downtime entry"),
					link(
						'cmd',
						{
							'command': 'delete',
							'table': 'downtimes',
							'object': args.obj.id+";"+Number(args.obj.is_service)
						}
					)
				)
			);

			// Schedule recurring
			var recurring_args = {
				host : args.obj.host.name
			};
			if (args.obj.service.description) {
				recurring_args.service = args.obj.service.description;
			}
			cell.append(icon16('recurring-downtime', _('Recurring downtime'),
					link('recurring_downtime', recurring_args)));

			return cell;
		}
	}
};

listview_renderer_table.contacts = {
	"name" : {
		"header" : _('Name'),
		"depends" : [ 'name' ],
		"sort" : [ 'name' ],
		"cell" : function(args) {
			return $('<td />').update_text(args.obj.name);
		}
	},
	"alias" : {
		"header" : _('Alias'),
		"depends" : [ 'alias' ],
		"sort" : [ 'alias' ],
		"cell" : function(args) {
			return $('<td />').update_text(args.obj.alias);
		}
	}
};

listview_renderer_table.notifications = {
	"state" : {
		"header" : '',
		"depends" : [ 'state_text' ],
		"sort" : [ 'notification_type', 'state' ],
		"cell" : function(args) {
			return $('<td class="icon" />')
					.append(
							icon16('shield-' + args.obj.state_text,
									args.obj.state_text));

		}
	},
	"host_name" : {
		"header" : _('Host'),
		"depends" : [ 'host_name' ],
		"sort" : [ 'host_name', 'service_description' ],
		"cell" : function(args) {
			var cell = $('<td />');
			cell.append(extinfo_link({
				host : args.obj.host_name
			}).update_text(args.obj.host_name));

			return cell;
		}
	},
	"service_description" : {
		"header" : _('Service'),
		"depends" : [ 'host_name', 'service_description' ],
		"sort" : [ 'service_description' ],
		"cell" : function(args) {
			var cell = $('<td />');
			cell.append(extinfo_link({
				host : args.obj.host_name,
				service : args.obj.service_description
			}).update_text(args.obj.service_description));

			return cell;
		}
	},
	"time" : {
		"header" : _('Time'),
		"depends" : [ 'start_time' ],
		"sort" : [ 'start_time' ],
		"cell" : function(args) {
			return $('<td />').text(format_timestamp(args.obj.start_time));
		}
	},
	"contact" : {
		"header" : _('Contact'),
		"depends" : [ 'contact_name' ],
		"sort" : [ 'contact_name' ],
		"cell" : function(args) {
			return $('<td />').text(args.obj.contact_name);
		}
	},
	"notification_command" : {
		"header" : _('Notification Command'),
		"depends" : [ 'command_name' ],
		"sort" : [ 'command_name' ],
		"cell" : function(args) {
			return $('<td />').text(args.obj.command_name);
		}
	},
	"status_information" : {
		"header" : _('Status Information'),
		"depends" : [ 'output' ],
		"sort" : [ 'output' ],
		"cell" : function(args) {
			return $('<td class="restricted-output" />').update_text(args.obj.output);
		}
	}
};

listview_renderer_table.recurring_downtimes = {
	"downtime_type": {
		"header": _('Downtime type'),
		"depends": ['downtime_type'],
		"sort": ['downtime_type'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.downtime_type);
		}
	},
	"objects": {
		"header": _('Objects'),
		"depends": ['objects'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.objects.join(', '));
		}
	},
	"author": {
		"header": _('Author'),
		"depends": ['author'],
		"sort": ['author'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.author);
		}
	},
	"comment": {
		"header": _('Comment'),
		"depends": ['comment'],
		"sort": ['comment'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.comment);
		}
	},
	"start_time": {
		"header": _('Start time'),
		"depends": ['start_time_string'],
		"sort": ['start_time'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.start_time_string);
		}
	},
	"end_time": {
		"header": _('End time'),
		"depends": ['end_time_string'],
		"sort": ['end_time'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.end_time_string);
		}
	},
	"duration": {
		"header": _('Duration'),
		"depends": ['duration_string'],
		"sort": ['duration'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.duration_string);
		}
	},
	"fixed": {
		"header": _('Fixed?'),
		"depends": ['fixed'],
		"sort": ['fixed'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.fixed ? _("Fixed") : _("Flexible"));
		}
	},
	"weekdays": {
		"header": _('Weekdays'),
		"depends": ['weekdays'],
		"cell": function(args) {
			return $('<td />').update_text($.map(args.obj.weekdays, function (x) {return Date.dayNames[x];}).join(', '));
		}
	},
	"months": {
		"header": _('Months'),
		"depends": ['months'],
		"cell": function(args) {
			return $('<td />').update_text($.map(args.obj.months, function (x) {return Date.monthNames[x-1];}).join(', '));
		}
	},
	"actions": {
		"header": _('Actions'),
		"depends": ['id'],
		"cell": function(args) {
			var cell = $('<td />');
			cell.append(icon16('edit',
				_('Edit schedule'),
				link('recurring_downtime/index/' + args.obj.id)
					.attr('title', 'Edit schedule')
			));
			cell.append(icon16('delete-doc',
				_('Delete schedule'),
				link('recurring_downtime/delete/')
					.data('recurring-id', args.obj.id)
					.addClass('recurring_delete')
					.attr('title', 'Delete schedule')
			));
			return cell;
		}
	},
};

listview_renderer_table.commands = {
	"name": {
		"header": _('Name'),
		"depends": ['name'],
		"sort": ['name'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.name);
		}
	},
	"line": {
		"header": _('Shell command line'),
		"depends": ['line'],
		"sort": ['line'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.line);
		}
	}
};

listview_renderer_table.timeperiods = {
	"name": {
		"header": _('Name'),
		"depends": ['name'],
		"sort": ['name'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.name);
		}
	},
	"alias": {
		"header": _('Alias'),
		"depends": ['alias'],
		"sort": ['alias'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.alias);
		}
	},
	"days": {
		"header": _('Day schedule'),
		"depends": ['days'],
		"sort": false,
		"cell": function(args) {
			var cell = $('<td />');
			var i;
			var sub_list = $('<table />');
			var days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
			for(i=0;i<7;i++) {
				var day = args.obj.days[i];

				/* Build timerange definition */
				var range_def = "";
				var tr_i;
				var have_range = false;
				for(tr_i=0;tr_i<day.length;tr_i+=2) {
					if(range_def)
						range_def += ", ";
					range_def += "" + (day[tr_i]/3600.0) + "-" + (day[tr_i+1]/3600.0);
					have_range = true;
				}

				if( have_range ) {
					sub_list.append(
							$('<tr />')
							.append($('<td />').text(days[i]))
							.append($('<td />').text(range_def))
						);
				}
			}
			cell.append(sub_list);
			return cell;
		}
	},
	"exceptions": {
		"header": _('Exceptions'),
		"depends": ['exceptions'],
		"sort": false,
		"cell": function(args) {
			var cell = $('<td />');
			var i;
			var sub_list = $('<table />');
			var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
			var days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
			var format_timerange = function(tr) {
			};
			for (i = 0; i < args.obj.exceptions.length; i++) {
				var ex = args.obj.exceptions[i];

				/* Build date definition */
				var date_def = "";
				switch (ex.type) {
				case 'calendar_date':
					date_def += ex.syear + "-" + (ex.smon + 1) + "-"
							+ ex.smday;
					if ((ex.syear != ex.eyear) || (ex.smon != ex.emon)
							|| (ex.smday != ex.emday))
						date_def += " - " + ex.eyear + "-" + (ex.emon + 1)
								+ "-" + ex.emday;
					break;
				case 'month_date':
					date_def += months[ex.smon] + " " + ex.smday;
					if ((ex.smon != ex.emon) || (ex.smday != ex.emday))
						date_def += " - " + months[ex.emon + 1] + " "
								+ ex.emday;
					break;
				case 'month_day':
					date_def += "day " + ex.smday;
					if ((ex.smday != ex.emday))
						date_def += " - " + ex.emday;
					break;
				case 'month_week_day':
					date_def += days[ex.swday] + " " + ex.swday_offset + " "
							+ months[ex.smon];
					if ((ex.swday != ex.ewday)
							|| (ex.swday_offset != ex.ewday_offset)
							|| (ex.smon != ex.emon))
						date_def += " - " + days[ex.ewday] + " " + ex.ewday_offset
								+ " " + months[ex.emon];
					break;
				case 'week_day':
					date_def += days[ex.swday] + " " + ex.swday_offset;
					if ((ex.swday != ex.ewday)
							|| (ex.swday_offset != ex.ewday_offset))
						date_def += " - " + days[ex.ewday] + " " + ex.ewday_offset;
					break;
				}
				if (ex.skip_interval > 1) {
					date_def += " / " + ex.skip_interval;
				}

				/* Build timerange definition */
				var range_def = "";
				var tr_i;
				for(tr_i=0;tr_i<ex.times.length;tr_i+=2) {
					if(range_def)
						range_def += ", ";
					range_def += "" + (ex.times[tr_i]/3600.0) + "-" + (ex.times[tr_i+1]/3600.0);
				}

				sub_list.append(
						$('<tr />')
						.append($('<td />').text(date_def))
						.append($('<td />').text(range_def))
					);
			}
			cell.append(sub_list);
			return cell;
		}
	},
	"exclusions": {
		"header": _('Exclusions'),
		"depends": ['exclusions'],
		"sort": false,
		"cell": function(args) {
			var cell = $('<td />');
			var i;
			var sub_list = $('<ul />');
			for(i=0;i<args.obj.exclusions.length;i++) {
				sub_list.append($('<li />').text(args.obj.exclusions[i]));
			}
			cell.append(sub_list);
			return cell;
		}
	},
	"in": {
		"header": _('Currently active'),
		"depends": ['is_active'],
		"sort": ['is_active'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.is_active ? _('Yes') : _('No'));
		}
	}
};

listview_renderer_table.contactgroups = {
	"name": {
		"header": _('Name'),
		"depends": ['name'],
		"sort": ['name'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.name);
		}
	},
	"alias": {
		"header": _('Alias'),
		"depends": ['alias'],
		"sort": ['alias'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.alias);
		}
	},
	"members": {
		"header": _('Members'),
		"depends": ['members'],
		"sort": ['members'],
		"cell": function(args) {
			return $('<td />').update_text(args.obj.members.join(', '));
		}
	}
};

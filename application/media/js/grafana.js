var grafana_hosts = {
	"status" : {
                "depends" : [ 'name', 'acknowledged', 'notifications_enabled',
                                'checks_disabled', 'is_flapping', 'scheduled_downtime_depth',
                                'pnpgraph_present', 'comments_count', 'custom_variable_names', 'custom_variable_values' ],
                "cell" : function(args) {
                        var cell = $('<td />');

                        if (args.obj.custom_variable_names.indexOf('GRAPHITEPREFIX') > -1) {


                                var currentDate = new Date();
                                var timeStamp = currentDate.getTime();
                                var timeStamp_back = currentDate.setHours(currentDate.getHours() - 6);

                                var grafana_link = icon16('grafana', _('Show grafana graph'), link('grafana', { "host" : args.obj.name }));

                                grafana_link.attr('data-popover','image:https://<HOSTNAME>:3000/render/dashboard-solo/db/host-dashboard?panelId=1&from=' + timeStamp_back + '&to=' + timeStamp + 'var-Host=' + args.obj.name + '&theme=light&width=700&height=250');

                                cell.append(grafana_link);

                        }

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
        }
};

var grafana_services = {
	"status" : {
		"depends" : [ 'host.name', 'description', 'pnpgraph_present',
                                'acknowledged', 'comments_count', 'notifications_enabled',
                                'checks_disabled', 'is_flapping', 'scheduled_downtime_depth',
                                'host.scheduled_downtime_depth', 'custom_variable_names', 'custom_variable_values' ],
		"cell" : function(args) {
                        var cell = $('<td />');

			if (args.obj.custom_variable_names.indexOf('GRAPHITEPREFIX') > -1) {

                                String.prototype.beginsWith = function (string) {
                                        return(this.indexOf(string) === 0);
                                };

                                var currentDate = new Date();
                                var timeStamp = currentDate.getTime();
                                var timeStamp_6h = currentDate.setHours(currentDate.getHours() - 6);

                                var grafana_extra_attr = "";
                                var grafana_available = 0;
                                var desc = args.obj.description;
                                desc = desc.replace(/[\/]/gi, "_");
                                desc = desc.replace(/\s+/g, '');

                                var panelId_array = { "Memory": 3, "CPU": 2, "Filesystem": 7, "TRAFFIC": 5 };

                                for (var key in panelId_array) {
                                        if (args.obj.description.beginsWith(key)) {
                                                if (key=="Filesystem")
                                                        grafana_extra_attr = "&var-Disk=" + desc;
                                                var panelId = panelId_array[key];
                                                var grafana_available = 1;
                                        }
                                }

                                var grafana_link = icon16('grafana', _('Show grafana graph'), link(
                                        'grafana', {
                                                    "srv" : args.obj.description,
                                                    "host" : args.obj.host.name
                                        }));

                                if (grafana_available) {

                                        grafana_link.attr('data-popover',
                                                'image:https://<HOSTNAME>:3000/render/dashboard-solo/db/host-dashboard?panelId=' + panelId + '&from=' + timeStamp_6h +'&to=' + timeStamp +'var-Host=' + args.obj.host.name + grafana_extra_attr + '&theme=light&width=700&height=300'
                                        );
                                };
                                cell.append(grafana_link);

                        }	

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
	}
};

jQuery.extend(listview_renderer_table.hosts, grafana_hosts);
jQuery.extend(listview_renderer_table.services, grafana_services);

/*******************************************************************************
 * Table renderer
 ******************************************************************************/

var listview_renderer_table_all = {
	"select" : {
		"order" : 35,
		"header" : $('<input type="checkbox" id="select_all" class="listview_multiselect_checkbox_all" />'),
		"depends" : [ 'key' ],
		"sort" : false,
		"available" : function(args) {
			if(_controller_name != 'listview')
				return false;
			if(!listview_commands[args.table])
				return false;

			var cmd_count = 0;
			for ( var cmdname in listview_commands[args.table]) {
				// Redirect commands can't be applied in multi aciton
				if(!listview_commands[args.table][cmdname]['redirect']) {
					cmd_count++;
				}
			}
			if(cmd_count == 0)
				return false;
			return true;
		},
		"cell" : function(args)
		{
			var checkbox = $(
					'<input type="checkbox" name="object[]" class="listview_multiselect_checkbox" />')
					.attr('value', args.obj.key);
			if ( lsfilter_multiselect.box_selected(args.obj.key) ) {
				checkbox.prop('checked', true);
				if (args.row.hasClass('odd'))
					args.row.addClass('selected_odd');
				else
					args.row.addClass('selected_even');
			}
			return $('<td style="width: 1em; padding: 0 3px" />').append(checkbox);
		}
	},
}

listview_renderer_table.saved_filters = {
	"icon" : {
		"header" : '',
		"depends" : [ 'filter_table' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td class="icon" />');

			var icon = false;
			var base = 'menu';
			switch (args.obj.filter_table) {
			case 'hosts':
				icon = 'host';
				break;
			case 'services':
				icon = 'service';
				break;
			case 'hostgroups':
				icon = 'hostgroup';
				break;
			case 'servicegroups':
				icon = 'servicegroup';
				break;
			case 'comments':
				icon = 'comments';
				break;
			case 'notifications':
				icon = 'notifications';
				break;
			default:
				icon = 'eventlog';
			}
			if (icon) {
				cell.append(icon16(icon, false, false, base));
			}
			return cell;

		}
	},
	"scope" : {
		"header" : _('Scope'),
		"depends" : [ 'scope' ],
		"sort" : false,
		"cell" : function(args) {
			return $('<td />').text(args.obj.scope);
		}
	},
	"name" : {
		"header" : _('Name'),
		"depends" : [ 'filter_name', 'filter' ],
		"sort" : [ 'filter_name' ],
		"cell" : function(args) {
			return $('<td />').append(
					link_query(args.obj.filter).text(args.obj.filter_name));

		}
	},
	"filter" : {
		"header" : _('filter string'),
		"depends" : [ 'filter' ],
		"sort" : [ 'filter' ],
		"cell" : function(args) {
			return $('<td />').append(
					link_query(args.obj.filter).text(args.obj.filter));
		}
	},
	"owner" : {
		"header" : _('Owner'),
		"depends" : [ 'username' ],
		"sort" : [ 'username' ],
		"cell" : function(args) {
			var cell = $('<td />');
			if (args.obj.username) {
				cell.text(args.obj.username);
			}
			return cell;

		}
	},
	"actions" : {
		"header" : _('Actions'),
		"depends" : [ 'id', 'deletable' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />');

			if (args.obj.deletable) {
				// Delete
				var del_icon = icon16('delete',
						_("Delete/cancel this saved filter"));
				var del_link = _site_domain + _index_page
						+ '/listview/delete_saved_filter?id=' + args.obj.id;

				var del_elem = $('<a />')
						.attr('href', del_link)
						.addClass('link_ajax_refresh')
						.on('click', 
								function(ev) {
									if (!confirm(_("Are you sure you want to delete the filter '")
											+ args.obj.filter_name + "' ?")) {
										return false;
									}
								}).append(del_icon);

				cell.append(del_elem);
			}
			return cell;
		}
	}
};
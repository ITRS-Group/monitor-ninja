/*******************************************************************************
 * Table renderer
 ******************************************************************************/

listview_renderer_table.saved_reports = {
	"type" : {
		"header" : _('Type'),
		"depends" : [ 'type' ],
		"sort" : [ 'type asc' ],
		"cell" : function(args) {
			return $('<td />').text(args.obj.type);

		}
	},
	"name" : {
		"header" : _('Name'),
		"depends" : [ 'report_name', 'type', 'id' ],
		"sort" : [ 'report_name asc' ],
		"cell" : function(args) {
			var cell = $('<td />').append(link(args.obj.type + '/generate', {
				report_id : args.obj.id
			}).text(args.obj.report_name));
			return cell;
		}
	},
	"created_by" : {
		"header" : _('Created by'),
		"depends" : [ 'created_by' ],
		"sort" : [ 'created_by asc' ],
		"cell" : function(args) {
			var cell = $('<td />');
			if (args.obj.created_by !== undefined) {
				cell.text(args.obj.created_by);
			} else {
				cell.text(_('N/A'));
			}
			return cell;
		}
	},
	"created_at" : {
		"header" : _('Created at'),
		"depends" : [ 'created_at' ],
		"sort" : [ 'created_at asc' ],
		"cell" : function(args) {
			var cell = $('<td />');
			if (args.obj.created_at !== undefined) {
				cell.text(format_timestamp(args.obj.created_at));
			} else {
				cell.text(_('N/A'));
			}
			return cell;
		}
	},
	"modified_by" : {
		"header" : _('Modified by'),
		"depends" : [ 'modified_by' ],
		"sort" : [ 'modified_by asc' ],
		"cell" : function(args) {
			var cell = $('<td />');
			if (args.obj.modified_by !== undefined) {
				cell.text(args.obj.modified_by);
			} else {
				cell.text(_('N/A'));
			}
			return cell;
		}
	},
	"modified_at" : {
		"header" : _('Modified at'),
		"depends" : [ 'modified_at' ],
		"sort" : [ 'modified_at asc' ],
		"cell" : function(args) {
			var cell = $('<td />');
			if (args.obj.modified_at !== undefined) {
				cell.text(format_timestamp(args.obj.modified_at));
			} else {
				cell.text(_('N/A'));
			}
			return cell;
		}
	},
	"actions" : {
		"header" : _('Actions'),
		"depends" : [ 'type', 'id', 'report_name' ],
		"sort" : false,
		"cell" : function(args) {
			var cell = $('<td />');

			cell.append(icon16('delete', 'Delete ' + args.obj.report_name,
					link(args.obj.type + '/delete', {
						report_id : args.obj.id
					}).addClass('link_ajax_refresh')));
			return cell;
		}
	},
};

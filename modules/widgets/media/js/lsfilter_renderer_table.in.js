/*******************************************************************************
 * Table renderer
 ******************************************************************************/

listview_renderer_table.ninja_widgets = {
	"id" : {
		"header" : _('id'),
		"depends" : [ 'id' ],
		"sort" : [ 'id' ],
		"cell" : function(args) {
			var cell = $('<td />');
			cell.text(args.obj.id);
			return cell;
		}
	},
	"username" : {
		"header" : _('username'),
		"depends" : [ 'username' ],
		"sort" : [ 'username' ],
		"cell" : function(args) {
			var cell = $('<td />');
			cell.text(args.obj.username);
			return cell;
		}
	},
	"page" : {
		"header" : _('page'),
		"depends" : [ 'page' ],
		"sort" : [ 'page' ],
		"cell" : function(args) {
			var cell = $('<td />');
			cell.text(args.obj.page);
			return cell;
		}
	},
	"name" : {
		"header" : _('name'),
		"depends" : [ 'name' ],
		"sort" : [ 'name' ],
		"cell" : function(args) {
			var cell = $('<td />');
			cell.text(args.obj.name);
			return cell;
		}
	},
	"friendly_name" : {
		"header" : _('friendly_name'),
		"depends" : [ 'friendly_name' ],
		"sort" : [ 'friendly_name' ],
		"cell" : function(args) {
			var cell = $('<td />');
			cell.text(args.obj.friendly_name);
			return cell;
		}
	},
	"setting" : {
		"header" : _('setting'),
		"depends" : [ 'setting' ],
		"sort" : [ 'setting' ],
		"cell" : function(args) {
			var cell = $('<td />');
			if(args.obj.setting) {
				var list = $('<ul />');
				$.each(args.obj.setting, function(name, value) {
					list.append($('<li />').text(value).prepend($('<b />').text(name + " = ")));
				});
				cell.append(list);
			} else {
				cell.text("No settings");
			}
			cell.text(args.obj.setting);
			return cell;
		}
	},
	"instance_id" : {
		"header" : _('instance_id'),
		"depends" : [ 'instance_id' ],
		"sort" : [ 'instance_id' ],
		"cell" : function(args) {
			var cell = $('<td />');
			cell.text(args.obj.instance_id);
			return cell;
		}
	}
};

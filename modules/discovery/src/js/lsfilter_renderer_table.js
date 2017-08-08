/*******************************************************************************
 * Table renderer
 ******************************************************************************/

listview_renderer_table.discovery_hosts = {
	"name" : {
		"header" : _('Name'),
		"depends" : [ 'name' ],
		"sort" : [ 'name' ],
		"cell" : function(args) {
			return $('<td />').append(args.obj.name);
		}
	},
	"ip" : {
		"header" : _('IP'),
		"depends" : [ 'ip' ],
		"sort" : [ 'ip' ],
		"cell" : function(args) {
			return $('<td />').append(args.obj.ip);
		}
	},
	"protocol" : {
		"header" : _('Protocol'),
		"depends" : [ 'protocol' ],
		"sort" : [ 'protocol' ],
		"cell" : function(args) {
			return $('<td />').append(args.obj.protocol);
		}
	},
	"parent" : {
		"header" : _('Parent'),
		"depends" : [ 'parent' ],
		"sort" : [ 'parent' ],
		"cell" : function(args) {
			return $('<td />').append(args.obj.parent);
		}
	}
};

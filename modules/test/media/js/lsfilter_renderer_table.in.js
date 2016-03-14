/*******************************************************************************
 * Table renderer
 ******************************************************************************/

listview_renderer_table.hosts = {
	"name" : {
		"header" : _('Name'),
		"depends" : [ 'name' ],
		"sort" : [ 'name' ],
		"cell" : function(args) {
			return $('<td />').append(args.obj.name);
		}
	}
};
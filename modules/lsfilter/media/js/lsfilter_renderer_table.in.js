/*******************************************************************************
 * Table renderer
 ******************************************************************************/

var listview_renderer_table_all = {
	"select" : {
		"header" : $('<input type="checkbox" id="select_all" class="listview_multiselect_checkbox_all" />'),
		"depends" : [ 'key' ],
		"sort" : false,
		"available" : function(args) {
			if(_controller_name != 'listview')
				return false;
			if(!listview_commands[args.table] || listview_commands[args.table].length==0)
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
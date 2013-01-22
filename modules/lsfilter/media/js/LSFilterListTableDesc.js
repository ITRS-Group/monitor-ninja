
var LSColumnsPP = function(){
	this.parent = LSColumnsPreprocessor;
	this.parent();
	
	this.preprocess_string = function(value) {
		return value;
	};
	
};

var LSColumnsFilterListVisitor = function(all_columns, all_db_columns){
	
	this.custom_cols = {};
	
	// entry: definition := * column_list end
	this.visit_entry = function(column_list0, end1) {
	};
	
	// column_list_single: column_list := * column
	this.visit_column_list_single = function(column0) {
		if( column0.op == 'add' ) {
			return column0.cols;
		}
		return [];
	};
	
	// column_list_multi: column_list := * column_list comma column
	this.visit_column_list_multi = function(column_list0, column2) {
		if( column2.op == 'add' ) {
			return column_list0.concat(column2.cols).filter(function(el,i,a){return i==a.indexOf(el);});
		}
		if( column2.op == 'sub' ) {
			return column_list0.filter(function(el,i,a){return column2.cols.indexOf(el) < 0;});
		}
		return column_list0;
	};
	
	// column_all: column := * all
	this.visit_column_all = function() {
		return {
			op: 'add',
			cols: all_columns
		};
	};
	
	// column_default: column := * name
	this.visit_column_default = function(name0) {
		if( all_columns.indexOf(name0) < 0 ) {
			return {op: 'nop'};
		}
		return {
			op: 'add',
			cols: [name0]
		};
	};
	
	// column_disable: column := * minus name
	this.visit_column_disable = function(name1) {
		if( all_columns.indexOf(name1) < 0 ) {
			return {op: 'nop'};
		}
		return {
			op: 'sub',
			cols: [name1]
		};
	};
	
	// column_custom: column := * name eq expr
	this.visit_column_custom = function(name0, expr2) {
		this.custom_cols[name0] = expr2;
		return {
			op: 'add',
			cols: [name0]
		};
	};
	
	// expr_var: expr := * name
	this.visit_expr_var = function(name0) {
		return function(args) {
			if( args.obj[name0] )
				return args.obj[name0];
			return '';
			};
	};
	
	this.accept = function(result) {
		return result;
	};
	
};


function lsfilter_list_table_desc(metadata, columndesc)
{
	this.metadata = metadata;
	this.vis_columns = [];
	this.col_renderers = {};
	this.db_columns = [];
	
	if (!listview_renderer_table[metadata.table]) return;
	
	var all_col_renderers = listview_renderer_table[metadata.table];
	var all_columns = [];
	for ( var col in all_col_renderers) {
		all_columns.push(col);
	}
	var all_db_columns = livestatus_structure[metadata.table];
	var custom_columns = {};
	
	if (columndesc) {
		// TODO: handling of column slection description

		var columns_line_visitor = new LSColumnsFilterListVisitor(all_columns, all_db_columns);
		var parser = new LSColumns(new LSColumnsPP(), columns_line_visitor);
		try {
			this.vis_columns = parser.parse(columndesc);
			custom_columns = columns_line_visitor.custom_cols;
		} catch(e) {
			console.log(parser);
			console.log(columndesc);
			console.log(e);
		}
	}
	else {
		this.vis_columns = all_columns;
	}
	
	/* Add custom column renderers */
	for( var name in custom_columns ) {
		var content = custom_columns[name];
		this.col_renderers[name] = {
			"header": name,
			"depends": [],
			"sort": false,
			"cell": function(args)
			{
				return $('<td />').append(content(args));
			}
		};
	}
	console.log(this.col_renderers);
	
	for ( var i = 0; i < this.vis_columns.length; i++) {
		/* Fetch column renderers */
		var column_obj = this.col_renderers[this.vis_columns[i]];
		if( !column_obj ) {
			column_obj = all_col_renderers[this.vis_columns[i]];
			this.col_renderers[this.vis_columns[i]] = column_obj;
		}
		/* Fetch database column dependencies */
		for ( var j = 0; j < column_obj.depends.length; j++) {
			this.db_columns.push(column_obj.depends[j]);
		}
	}
	
	/* Build fetch sort columns method */
	this.sort_cols = function(vis_col)
	{
		var sort = this.col_renderers[vis_col].sort;
		if (sort) return sort;
		return [];
	}
}

var LSColumnsPP = function()
{
	/* add preprocessor as parent */
	this.parent = LSColumnsPreprocessor;
	this.parent();
	
	this.preprocess_string = function(value)
	{
		return value.substring(1, value.length - 1).replace(/\\(.)/g, '$1');
	};
	
	this.preprocess_float = function(value)
	{
		return parseFloat(value);
	};
	
	this.preprocess_integer = function(value)
	{
		return parseInt(value, 10);
	};
	
};

var LSColumnsFilterListVisitor = function(all_columns, all_db_columns, metadata)
{
	var column_exists = function(col_name)
	{
		/* Could have used indexOf if IE7 wasn't crappy... */
		var i;
		for (i = 0; i < all_columns.length; i++) {
			if (all_columns[i] == col_name) { return true; }
		}
		return false;
	};
	var db_column_exists = function(col_name)
	{
		if (all_db_columns[col_name]) { return true; }
		return false;
	};
	
	this.custom_cols = {};
	this.custom_deps = [];
	
	// entry: definition := * column_list end
	this.visit_entry = function(column_list0, end1)
	{
	};
	
	// column_list_single: column_list := * column
	this.visit_column_list_single = function(column0)
	{
		if (column0.op == 'add') { return column0.cols; }
		return [];
	};
	
	// column_list_multi: column_list := * column_list comma column
	this.visit_column_list_multi = function(column_list0, column2)
	{
		var result = [];
		if (column2.op == 'add') {
			var tmpresult = column_list0.concat(column2.cols);
			/*
			 * Only save unique columns. If this wasn't IE7-compatible, this
			 * would have been a simple filter...
			 */
			for ( var i = 0; i < tmpresult.length; i++) {
				var to_add = true;
				for ( var j = 0; to_add && j < result.length; j++) {
					if (result[j] == tmpresult[i]) {
						to_add = false;
					}
				}
				if (to_add) {
					result.push(tmpresult[i]);
				}
			}
		}
		else if (column2.op == 'sub') {
			/*
			 * If we didn't have to care about IE7, this would be a simple
			 * Array.filter: result =
			 * column_list0.filter(function(el,i,a){return
			 * column2.cols.indexOf(el) < 0;});
			 */
			for ( var i = 0; i < column_list0.length; i++) {
				var to_add = true;
				for ( var j = 0; to_add && j < column2.cols.length; j++) {
					if (column2.cols[j] == column_list0[i]) {
						to_add = false;
					}
				}
				if (to_add) {
					result.push(column_list0[i]);
				}
			}
		}
		else {
			// Do nothing, pass previous list through...
			result = column_list0;
		}
		return result;
	};
	
	// column_all: column := * all
	this.visit_column_all = function()
	{
		return {
			op: 'add',
			cols: all_columns
		};
	};
	
	// column_default: column := * name
	this.visit_column_default = function(name0)
	{
		if (!column_exists(name0)) { return {
			op: 'nop'
		}; }
		return {
			op: 'add',
			cols: [ name0 ]
		};
	};
	
	// column_disable: column := * minus name
	this.visit_column_disable = function(name1)
	{
		if (!column_exists(name1)) { return {
			op: 'nop'
		}; }
		return {
			op: 'sub',
			cols: [ name1 ]
		};
	};
	
	// column_custom: column := * custom_name eq expr
	this.visit_column_custom = function(custom_name0, expr2)
	{
		this.custom_cols[custom_name0] = function(args) {
			var value = expr2(args);

			/* If an array, join the values */
			if( typeof value == "object" ) {
				value = value.join(", ");
			}

			return value;
		}
		return {
			op: 'add',
			cols: [ custom_name0 ]
		};
	};
	
	// expr_add: expr := * expr op_add expr2
	this.visit_expr_add = function(expr0, expr2)
	{
		return function(args)
		{
			return expr0(args) + expr2(args);
		};
	};
	
	// expr_sub: expr := * expr op_sub expr2
	this.visit_expr_sub = function(expr0, expr2)
	{
		return function(args)
		{
			return expr0(args) - expr2(args);
		};
	};
	
	// expr_mult: expr2 := * expr2 op_mult expr3
	this.visit_expr_mult = function(expr0, expr2)
	{
		return function(args)
		{
			return expr0(args) * expr2(args);
		};
	};
	
	// expr_div: expr2 := * expr2 op_div expr3
	this.visit_expr_div = function(expr0, expr2)
	{
		return function(args)
		{
			return expr0(args) / expr2(args);
		};
	};
	
	// expr_neg: expr3 := * op_sub expr4
	this.visit_expr_neg = function(expr1)
	{
		return function(args)
		{
			return -expr1(args);
		};
	};
	
	// expr_var: expr3 := * var
	this.visit_expr_var = function(var0)
	{
		/* Fetch table column dependencies */
		var name = var0.name;
		var curtbl = metadata.table;
		var type = '';
		for ( var i = 0; i < name.length; i++) {
			var curname = name[i];
			if (orm_structure[curtbl][curname]) {
				var type = orm_structure[curtbl][curname];
				if (type[0] == 'object') {
					curtbl = type[1];
				}
				else {
					this.custom_deps.push(name.slice(0, i + 1).join('.'));
					type = type[0];
					break;
				}
			}
			else {
				return function(variable)
				{
					/* We might not know the variable from strucutre, but it
					 * can be a local, as in loop variable in list compherension
					 */
					var value = var0.fetch(variable);
					if(typeof value != "undefined")
						return value;
					/* In this case, it's really unknown */
					return 'Unknown variable ' + name.slice(0, i + 1).join('.');
				};
			}
		}
		var defval = '';
		switch (type) {
			case 'int':
				defval = 0;
				break;
			case 'list':
				defval = [];
				break;
		}
		
		return function(args)
		{
			return var0.fetch(args.obj, defval);
		};
	};
	
	// var_var: var := * name
	this.visit_var_var = function(name0)
	{
		var name = name0;
		return {
			name: [ name ],
			fetch: function(variable, defval)
			{
				/*
				 * Should always exists, otherwise a missmatch between
				 * livstatus_structure javascript and generated code, or invalid
				 * dependency-lookup code in this class
				 */
				return variable[name];
			}
		};
	};

	// var_index: var := * var sq_l integer sq_r
	this.visit_var_index = function(var0, integer2)
	{
		var idx = integer2;
		var name_list = var0.name;
		return {
			name: name_list,
			fetch: function(variable, defval)
			{
				var variable = var0.fetch(variable);
				if (variable && variable[idx])
					return variable[idx];
				return defval;
			}
		};
	};
	
	// var_attr: var := * var dot name
	this.visit_var_attr = function(var0, name2)
	{
		var name = name2;
		var name_list = var0.name;
		name_list.push(name);
		return {
			name: name_list,
			fetch: function(variable, defval)
			{
				var variable = var0.fetch(variable);
				if (variable && variable[name])
					return variable[name];
				return defval;
			}
		};
	};
	
	// expr_string: expr2 := * string
	this.visit_expr_string = function(string0)
	{
		return function(args)
		{
			return string0;
		};
	};
	
	// expr_int: expr4 := * integer
	this.visit_expr_int = function(integer0)
	{
		return function(args)
		{
			return integer0;
		};
	};
	
	// expr_float: expr4 := * float
	this.visit_expr_float = function(float0)
	{
		return function(args)
		{
			return float0;
		};
	};
	
	// expr_list_comp: expr4 := * sq_l expr for name in expr sq_r
	this.visit_expr_list_comp = function(expr1, name3, expr5) {
		return function(args)
		{
			var list = expr5(args);
			var result = [];
			var subargs = $.extend({},args);
			for( var i=0; i<list.length; i++ ) {
				subargs[name3] = list[i];
				result.push(expr1(subargs));
			}
			return result;
		};
	};
	
	// expr_func: expr4 := * name par_l expr_list par_r
	this.visit_expr_func = function(name0, expr_list2) {
		switch(name0) {
		case "implode":
			return function(args) {
				var fargs = expr_list2(args);
				/* FIXME: test variable types */
				return fargs[1].join(fargs[0]);
			};
		case "time":
			return function(args) {
				var fargs = expr_list2(args);
				/* FIXME: test variable types */
				return format_timestamp(fargs[0]);
			};
		}
		return function(args) {
			return "Unknown function "+name0;
		}
	};
	
	// expr_list: expr_list := * expr comma expr_list
	this.visit_expr_list = function(expr0, expr_list2) {
		return function(args) {
			var arr = expr_list2(args);
			arr.unshift(expr0(args));
			return arr;
		}
	};
	
	// expr_list_end: expr_list := * expr
	this.visit_expr_list_end = function(expr0) {
		return function(args) {
			return [expr0(args)];
		}
	};
	
	this.accept = function(result)
	{
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
	var all_db_columns = orm_structure[metadata.table];
	var custom_columns = {};
	
	if (!columndesc) {
		// If not having a column desc, does a user-config exist?
		if (lsfilter_list_columns && lsfilter_list_columns[metadata.table]) {
			columndesc = lsfilter_list_columns[metadata.table];
		}
	}
	
	if (columndesc) {
		// TODO: handling of column slection description
		
		var columns_line_visitor = new LSColumnsFilterListVisitor(all_columns,
				all_db_columns, metadata);
		var parser = new LSColumns(new LSColumnsPP(), columns_line_visitor);
		try {
			this.vis_columns = parser.parse(columndesc);
			custom_columns = columns_line_visitor.custom_cols;
			this.db_columns = this.db_columns
					.concat(columns_line_visitor.custom_deps);
		}
		catch (e) {
			this.vis_columns = all_columns;
			this.vis_columns.push('message');
			custom_columns['message'] = function(args)
			{
				return e.message;
			};
			
			console.log(parser);
			console.log(columndesc);
			console.log(e);
			console.log(e.stack);
		}
	}
	else {
		this.vis_columns = all_columns;
	}
	
	/* Add custom column renderers */
	for ( var name in custom_columns) {
		var content = custom_columns[name];
		/* Some ugly way to bind variables... there must be a better way? */
		this.col_renderers[name] = (function(in_content)
		{
			return {
				"header": name,
				"depends": [],
				"sort": false,
				"cell": function(args)
				{
					return $('<td />').append(in_content(args));
				}
			};
		})(content);
	}
	
	for ( var i = 0; i < this.vis_columns.length; i++) {
		/* Fetch column renderers */
		var column_obj = this.col_renderers[this.vis_columns[i]];
		if (!column_obj) {
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

/*******************************************************************************
 * Visitor to add extra and/or blocks around innermost objects so all objects is
 * expandable
 ******************************************************************************/
var lsfilter_extra_andor = {
	last_op: false,
	
	visit_query: function(obj)
	{
		var result = {
			'obj': 'query',
			'table': obj.table
		};
		var query = this.visit(obj.query, 'or');
		var newop = 'or';
		if (query.obj == 'or') {
			newop = 'and';
		}
		result['query'] = {
			'obj': newop,
			'sub': [ query ]
		};
		return result;
	},
	
	visit_and: function(obj)
	{
		return this.visit_andor(obj, 'and');
	},
	visit_or: function(obj)
	{
		return this.visit_andor(obj, 'or');
	},
	visit_andor: function(obj, op)
	{
		var list = [];
		for ( var i in obj.sub) {
			list.push(this.visit(obj.sub[i], op));
		}
		return {
			'obj': op,
			'sub': list
		};
	},
	visit_match: function(obj)
	{
		var op = (this.last_op == 'and') ? ('or') : ('and');
		return {
			'obj': op,
			'sub': [ obj ]
		};
	},
	
	visit: function(obj, last_op)
	{
		this.last_op = last_op;
		return LSFilterASTVisit(obj, this);
	}
};

/*******************************************************************************
 * Convert a lsfilter AST to html
 ******************************************************************************/
var lsfilter_graphics_visitor = {
	fields: null,
	operators: {
		"string": {
			'not_re_ci': '!~~',
			'not_re_cs': '!~',
			're_ci': '~~',
			're_cs': '~',
			'not_eq_ci': '!=~',
			'eq_ci': '=~',
			'not_eq': '!=',
			'eq': '='
		},
		"int": {
			'not_eq': '!=',
			'gt_eq': '>=',
			'lt_eq': '<=',
			'gt': '>',
			'lt': '<',
			'eq': '='
		},
		"float": {
			'not_eq': '!=',
			'gt_eq': '>=',
			'lt_eq': '<=',
			'gt': '>',
			'lt': '<',
			'eq': '='
		},
		"object": {
			'in': 'in'
		}
	},
	
	visit_query: function(obj)
	{
		this.fields = livestatus_structure[obj.table];
		this.fields['this'] = [ 'object', obj.table ];
		var list = $('<ul class="lsfilter-query" />');
		
		var table_select = $('<select class="lsfilter-table-select" />');
		
		/* Only accept tables we can render, otherwise livestatus_structure would be used as list */
		for ( var table in listview_renderer_table) {
			if (table == obj.table) {
				table_select.append($('<option value="' + table
						+ '" selected="true">' + table + '</option>'));
			}
			else {
				table_select.append($('<option value="' + table + '">' + table
						+ '</option>'));
			}
		}
		
		table_select.change(function(evt)
		{
			var table = $(evt.target).val();
			console.log( table );
			var query = '[' + table + '] all';
			console.log( query );
			lsfilter_main.update(query, false);
			evt.preventDefault();
			return false;
		});
		
		list.append($('<li class="lsfilter-expr lsfilter-table-expr" />')
				.append(table_select));
		list.append($('<li class="lsfilter-expr lsfilter-query-expr" />')
				.append(this.visit(obj.query)));
		return list;
	},
	
	visit_and: function(obj)
	{
		return this.visit_andor(obj, 'and');
	},
	
	visit_or: function(obj)
	{
		return this.visit_andor(obj, 'or');
	},
	
	visit_andor: function(obj, op)
	{
		var self = this; // To be able to access it from within handlers
		var list = $('<ul class="lsfilter-' + op + '">');
		for ( var i in obj.sub) {
			list.append($(
					'<li class="lsfilter-expr lsfilter-' + op + '-expr"/>')
					.append(this.visit(obj.sub[i])));
		}
		var button = $('<button class="lsfilter-add-' + op + '" />')
				.text(_(op));
		button.click(function(e)
		{
			self.gui_stmnt_button(e, op, $(this));
		});
		list
				.append($('<li class="lsfilter-' + op + '-expr" />').append(
						button));
		return list;
		
	},
	
	visit_not: function(obj)
	{
		return this.visit(obj.sub);
	},
	
	visit_match: function(obj)
	{
		var self = this;
		var result = $('<ul class="lsfilter-expr lsfilter-comp" />');
		
		var fields = $('<select class="lsfilter-field-select" />');
		var ops = $('<select class="lsfilter-operator-select" />');
		var val = $('<input type="text" value="' + obj.value + '" />');
		
		for ( var f in this.fields) {
			if (f == obj.field || (f == 'this' && !obj.field)) {
				fields.append($('<option value="' + f + '" selected="true">'
						+ f + '</option>'));
			}
			else {
				fields
						.append($('<option value="' + f + '">' + f
								+ '</option>'));
			}
		}
		
		result.append($('<li />').append(fields));
		result.append($('<li />').append(ops));
		
		self.field_change(fields.val(), obj.op, val, ops);
		fields.change(function()
		{
			self.field_change($(this).val(), obj.op, val, ops);
			lsfilter_visual.update_query();
		});
		
		val.removeClass().addClass('lsfilter-value-field').addClass(
				'lsfilter-type-' + this.fields[fields.val()].join(''));
		
		this.add_delayed_update(ops);
		this.add_delayed_update(val);
		
		result.append($('<li />').append(val));
		
		return result;
	},
	
	field_change: function(field, op, val, ops)
	{
		console.log(this.fields[field]);
		var operators = this.operators[this.fields[field][0]];
		val.removeClass().addClass('lsfilter-value-field').addClass(
				'lsfilter-type-' + this.fields[field][0]);
		ops.empty();
		
		for ( var operator in operators) {
			if (operator == op) {
				ops.append($('<option selected="true" value="'
						+ operators[operator] + '">' + operators[operator]
						+ '</option>'));
			}
			else {
				ops.append($('<option value="' + operators[operator] + '">'
						+ operators[operator] + '</option>'));
			}
		}
	},
	
	gui_stmnt_button: function(evt, op, btn)
	{
		var self = this;
		var newop = (op == 'and') ? ('or') : ('and');
		evt.preventDefault();
		
		var clone = this.visit({
			'obj': newop,
			'sub': [ {
				'obj': 'match',
				'op': 'in',
				'field': 'this',
				'value': ''
			} ]
		});
		var tmp = null;
		
		var match_field = btn.closest('li').siblings('.lsfilter-expr')
				.children('.lsfilter-comp');
		
		match_field.wrap('<ul class="lsfilter-' + newop
				+ '"><li class="lsfilter-expr lsfilter-' + newop
				+ '-expr"/></ul>');
		
		var button = $('<button class="lsfilter-add-' + newop + '" />').text(
				_(newop));
		button.click(function(e)
		{
			self.gui_stmnt_button(e, newop, $(this));
		});
		
		match_field.parent().parent().append(
				$('<li class="lsfilter-' + newop + '-expr" />').append(button));
		
		$('<li class="lsfilter-expr lsfilter-' + op + '-expr" />')
				.append(clone).insertBefore(btn.closest('li'));
		
		lsfilter_visual.update_query();
	},
	
	visit: function(obj)
	{
		return LSFilterASTVisit(obj, this);
	},
	
	add_delayed_update: function(node)
	{
		node.bind('change', function()
		{
			lsfilter_visual.update_query_delayed();
		});
	}
};

var lsfilter_dom_to_query = {
	visit: function(node, prio)
	{
		node = $(node);
		if (node.hasClass('lsfilter-and')) {
			return this.visit_binary(' and ', 2, node
					.children('.lsfilter-expr').children(), prio);
		}
		else if (node.hasClass('lsfilter-or')) {
			return this.visit_binary(' or ', 1, node.children('.lsfilter-expr')
					.children(), prio);
		}
		else if (node.hasClass('lsfilter-query')) {
			return this.visit_query(node, prio);
		}
		else if (node.hasClass('lsfilter-comp')) { return this.visit_comp(node,
				prio); }
		
	},
	visit_all: function(nodes, prio)
	{
		var self = this;
		var result = $.map(nodes, function(elem, idx)
		{
			return self.visit(elem, prio);
		});
		return result;
	},
	visit_query: function(node, prio)
	{
		var table = node.find('.lsfilter-table-select').val();
		return "[" + table + "] "
				+ this.visit(node.children('.lsfilter-query-expr').children());
	},
	visit_binary: function(op, op_prio, nodes, prio)
	{
		if (nodes.length == 1) { return this.visit_all(nodes, prio).join(); }
		var result = this.visit_all(nodes, op_prio).join(op);
		if (prio > op_prio) {
			result = "(" + result + ")";
		}
		return result;
		
	},
	visit_comp: function(node, prio)
	{
		var field = node.find('.lsfilter-field-select').val();
		var op = node.find('.lsfilter-operator-select').val();
		var value_el = node.find('.lsfilter-value-field');
		var value = value_el.val();
		if (value_el.hasClass('lsfilter-type-int')) {
			value = parseInt(value);
		}
		else if (value_el.hasClass('lsfilter-type-float')) {
			value = parseFloat(value);
		}
		else {
			value = '"' + value.replace(/([\\"'])/g, "\\$1") + '"';
		}
		
		if (field == 'this') field = "";
		return field + " " + op + " " + value;
	}
};

var lsfilter_visual = {
	
	update: function(query, source, metadata)
	{
		if (source == 'visual') return;
		var parser = new LSFilter(new LSFilterPP(), new LSFilterASTVisitor());
		try {
			var ast = parser.parse(query);
			ast = lsfilter_extra_andor.visit(ast);
			var result = lsfilter_graphics_visitor.visit(ast);
			$('#filter_visual').empty().append(result);
		}
		catch (ex) {
			console.log(ex.stack);
			console.log(query);
		}
	},
	init: function()
	{
	},
	
	fields: null,
	
	update_query: function()
	{
		var query = lsfilter_dom_to_query.visit($('#filter_visual').children(),
				0);
		lsfilter_main.update(query, 'visual');
	},
	
	update_query_delayed: function()
	{
		this.update_query();
	}
};

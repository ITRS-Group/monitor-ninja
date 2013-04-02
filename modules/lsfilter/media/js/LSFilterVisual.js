/*******************************************************************************
 * Visitor to add extra and/or blocks around innermost objects so all objects is
 * expandable
 ******************************************************************************/
var lsfilter_extra_andor = {
	last_op : false,

	visit_query : function(obj) {
		var query = obj.query;
		if (query.obj != 'and') {
			query = {
				'obj' : 'and',
				'sub' : [ query ]
			};
		}
		return {
			'obj' : 'query',
			'table' : obj.table,
			'query' : this.visit(query, 'query'),
		};
	},

	visit_and : function(obj) {
		return this.visit_andor(obj, 'and');
	},
	visit_or : function(obj) {
		return this.visit_andor(obj, 'or');
	},
	visit_andor : function(obj, op) {
		var list = [];

		for ( var i in obj.sub) {
			var sub = this.visit(obj.sub[i], op);
			console.log(sub);
			if (!(sub.obj == 'match' && sub.op == 'all'))
				list.push(sub);
		}
		return {
			'obj' : op,
			'sub' : list
		};
	},
	visit_match : function(obj) {
		return obj;
	},
	visit_not : function(obj) {
		return {
			'obj' : 'not',
			'sub' : this.visit(obj.sub)
		};
	},

	visit : function(obj, last_op) {
		this.last_op = last_op;
		return LSFilterASTVisit(obj, this);
	}
};

/*******************************************************************************
 * Convert a lsfilter AST to html
 ******************************************************************************/
var lsfilter_graphics_visitor = {
	fields : null,
	operators : {
		"string" : {
			'not_re_ci' : 'not matching regexp, case insensitive',
			'not_re_cs' : 'not matching regexp',
			're_ci' : 'matching regexp, case insensitive',
			're_cs' : 'matching regexp',
			'not_eq_ci' : 'not equals, case insensitive',
			'eq_ci' : 'equals, case insensitive',
			'not_eq' : 'not equals',
			'eq' : 'equals'
		},
		"int" : {
			'not_eq' : 'not equals',
			'gt_eq' : 'greater than or equals',
			'lt_eq' : 'less than or equal',
			'gt' : 'greater than',
			'lt' : 'less than',
			'eq' : 'equal'
		},
		"float" : {
			'not_eq' : 'not equals',
			'gt_eq' : 'greater than or equals',
			'lt_eq' : 'less than or equals',
			'gt' : 'greater than',
			'lt' : 'less than',
			'eq' : 'equals'
		},
		"time" : {
			'not_eq' : 'not equals',
			'gt_eq' : 'greater than or equals',
			'lt_eq' : 'less than or equals',
			'gt' : 'greater than',
			'lt' : 'less than',
			'eq' : 'equals'
		},
		"object" : {
			'in' : 'in named set',
			'all' : 'all'
		},
		"list" : {
			'gt_eq' : 'contains'
		},
		"dict" : { /*
					 * FIXME: This is ugly... better grammar for
					 * custom_variables
					 */
			'not_re_ci' : 'not matching regexp, case insensitive',
			'not_re_cs' : 'not matching regexp',
			're_ci' : 'matching regexp, case insensitive',
			're_cs' : 'matching regexp',
			'not_eq_ci' : 'not equals, case insensitive',
			'eq_ci' : 'equals, case insensitive',
			'not_eq' : 'not equals',
			'eq' : 'equals'
		}
	},

	visit_query : function(obj) {
		this.fields = lsfilter_visual.fields_for_table(obj.table);
		var container = $('<div />');

		var tablesel = $('<select />');
		tablesel.addClass('lsfilter_visual_table_select');
		for (table in listview_renderer_table) {
			var opt = $('<option />');
			opt.attr('value', table);
			opt.text(table);
			tablesel.append(opt);
		}
		tablesel.val(obj.table);
		container.append(tablesel)

		container.append(this.visit(obj.query));
		return container;
	},

	visit_and : function(obj) {
		return this.visit_andor(obj, 'and');
	},

	visit_or : function(obj) {
		return this.visit_andor(obj, 'or');
	},

	visit_andor : function(obj, op) {
		var list = $('<div />');

		var cmplop = (op == 'and') ? ('or') : ('and');

		list.addClass('lsfilter_visual_node');
		list.addClass('lsfilter_visual_group');
		list.addClass('lsfilter_visual_group_' + op);

		list.attr('data-op', op);

		var header = $("<div />");
		header.addClass('lsfilter_visual_group_header')

		list.append(header);

		header.append($('<a class="lsfilter_visual_node_remove" />').append(
				icon12('cross')));
		header.append($('<span />').text(_(op + " filter group")));

		for ( var i in obj.sub) {
			list.append(this.visit(obj.sub[i]));
		}

		var footer = $('<div />');
		footer.addClass('lsfilter_visual_newmarker');
		footer.addClass('lsfilter_visual_group_footer');

		list.append(footer);

		var link;

		link = $('<a />');
		link.addClass('lsfilter_visual_node_addrule');
		link.text(_('Add rule'));
		footer.append(link);

		link = $('<a />');
		link.addClass('lsfilter_visual_node_addgroup');
		link.attr('data-op', 'and');
		link.text(_('Add and group'));
		footer.append(link);

		link = $('<a />');
		link.addClass('lsfilter_visual_node_addgroup');
		link.attr('data-op', 'or');
		link.text(_('Add or group'));
		footer.append(link);

		link = $('<a />');
		link.addClass('lsfilter_visual_node_negate');
		link.text(_('Negate group'));
		footer.append(link);

		return list;

	},

	visit_not : function(obj) {
		var result = this.visit(obj.sub);
		result.toggleClass('lsfilter_visual_not');
		return result;
	},

	visit_match : function(obj) {
		var result = $('<div />');

		result.addClass('lsfilter_visual_node');
		result.addClass('lsfilter_visual_match');

		result.append($('<a class="lsfilter_visual_node_remove" />').append(
				icon12('cross')));

		/* Attribute select box */
		var attrsel = $('<select />');
		attrsel.addClass('lsfilter_visual_field_select');
		for ( var f in this.fields) {
			var opt = $('<option />');
			opt.attr('value', f);
			opt.text(f);
			opt.attr('data-type', this.fields[f].join(";"));
			attrsel.append(opt);
		}
		if (!this.fields[obj.field]) {
			console.log("Unknown field " + obj.field);
			var opt = $('<option />');
			opt.attr('value', obj.field);
			opt.text(_("Unknown column: ") + obj.field);
			opt.attr('data-type', "string");
			attrsel.append(opt);
		}
		attrsel.val(obj.field);
		result.append(attrsel);

		/* Operator select box */
		var opsel = $('<select />');
		opsel.addClass('lsfilter_visual_operator_select');
		this.fill_operator_select(opsel,
				this.fields[obj.field] ? this.fields[obj.field] : [ "string" ]);
		opsel.val(obj.op);
		result.append(opsel);

		/* Input text field */
		var valueinp = $('<input />');
		valueinp.addClass('lsfilter_visual_value_field');
		valueinp.val(obj.value);
		result.append(valueinp);

		return result;
	},

	visit : function(obj) {
		return LSFilterASTVisit(obj, this);
	},

	fill_operator_select : function(select, type) {
		select.empty();
		var ops = [];
		if (type[0]) {
			ops = this.operators[type[0]];
		}
		for ( var op in ops) {
			var opt = $('<option />');
			opt.attr('value', op);
			opt.text(_(ops[op]));
			select.append(opt);
		}
	}
};

/*******************************************************************************
 * Parse DOM structure down to a query
 ******************************************************************************/
var lsfilter_dom_to_query = {
	op_prio : {
		'or' : 1,
		'and' : 2
	},
	operators : {
		'not_re_ci' : '!~~',
		'not_re_cs' : '!~',
		'not_eq_ci' : '!=~',
		'not_eq' : '!=',
		're_ci' : '~~',
		're_cs' : '~',
		'eq_ci' : '=~',
		'gt_eq' : '>=',
		'lt_eq' : '<=',
		'eq' : '=',
		'gt' : '>',
		'lt' : '<',
		'in' : 'in',
		'all' : 'all'
	},

	visit : function(node, prio) {
		node = $(node);
		var result;
		if (node.attr('id') == 'filter_visual') {
			result = this.visit_query(node, prio);
		} else if (node.hasClass('lsfilter_visual_group')) {
			result = this.visit_binary(node, prio);
		} else if (node.hasClass('lsfilter_visual_match')) {
			result = this.visit_match(node, prio);
		}
		if (node.hasClass('lsfilter_visual_not'))
			result = "not (" + result + ")";
		return result;
	},

	/**
	 * Visit all children nodes, either direct or indirect, with class
	 * lsfilter_visual_node. Might be nodes between visited node and root, but
	 * never a node with class lsfilter_visual_node.
	 */
	visit_children : function(root, prio) {
		var self = this;
		var result = [];
		root.children().each(function() {
			if ($(this).hasClass('lsfilter_visual_node')) {
				result.push(self.visit($(this), prio));
			} else {
				result = result.concat(self.visit_children($(this), prio));
			}
		});
		return result;
	},
	visit_query : function(node, prio) {
		var table = node.find('.lsfilter_visual_table_select').val();
		var subq = this.visit_children(node, prio);
		if (subq.length != 1) {
			return "";
		}
		return "[" + table + "] " + subq[0];
	},
	visit_binary : function(node, prio) {
		var op = node.attr('data-op');
		var op_prio = this.op_prio[op];

		var result = this.visit_children(node, op_prio);

		if (prio > op_prio && result.length > 1) {
			return "(" + result.join(" " + op + " ") + ")";
		} else if (result.length == 0) {
			if (op == "and") {
				return "all";
			} else {
				return "not all";
			}
		}
		return result.join(" " + op + " ");

	},
	visit_match : function(node, prio) {
		var field_el = node.find('.lsfilter_visual_field_select');
		var field = field_el.val();

		var opt = field_el.children().filter(function(i) {
			return $(this).attr('value') == field;
		}).last();
		var type = opt.attr('data-type').split(';');

		var op = node.find('.lsfilter_visual_operator_select').val();
		var op_query = this.operators[op];

		var value_el = node.find('.lsfilter_visual_value_field');
		var value = value_el.val();

		if (type[0] == 'int') {
			value = parseInt(value);
			if (isNaN(value))
				value = 0;
		} else if (type[0] == 'float') {
			value = parseFloat(value);
		} else {
			value = '"' + value.replace(/([\\"'])/g, "\\$1") + '"';
		}

		if (field == 'this')
			field = "";
		else
			field = field + " ";

		if (op == 'all')
			return 'all';

		return field + op_query + " " + value;
	}
};

/*******************************************************************************
 * Main object for graphical visualization
 ******************************************************************************/
var lsfilter_visual = {

	update : function(data) {
		if (data.source == 'visual')
			return;
		var parser = new LSFilter(new LSFilterPP(), new LSFilterASTVisitor());
		try {
			var ast = parser.parse(data.query);
			ast = lsfilter_extra_andor.visit(ast);
			var result = lsfilter_graphics_visitor.visit(ast);
			$('#filter_visual').empty().append(result);
		} catch (ex) {
			console.log(ex.stack);
			console.log(data.query);
		}
	},
	init : function() {
		var onnode = function(fnc) {
			return function(e) {
				e.preventDefault();
				fnc($(this).closest('.lsfilter_visual_node'), $(this));

				/* Make sure that there always exist a top node */
				lsfilter_visual.validate_top_integrity();

				/* Something changed. Trigger an update */
				lsfilter_visual.update_query_delayed();
				return false;
			};
		};

		$('#filter_visual')

		.on('click', '.lsfilter_visual_node_addrule', onnode(function(n, el) {
			var marker = n.children('.lsfilter_visual_newmarker');
			var newobj = lsfilter_graphics_visitor.visit({
				'obj' : 'match',
				'field' : 'this',
				'value' : '',
				'op' : 'all'
			});
			marker.before(newobj);
		}))

		.on('click', '.lsfilter_visual_node_addgroup', onnode(function(n, el) {
			var marker = n.children('.lsfilter_visual_newmarker');
			var op = el.attr('data-op');
			var newobj = lsfilter_graphics_visitor.visit({
				obj : op,
				sub : []
			});
			marker.before(newobj);
		}))

		.on('click', '.lsfilter_visual_node_remove', onnode(function(n, el) {
			/* Never remove outermost group */
			if (n.closest('.lsfilter_visual_node')) {
				n.remove();
			}
		}))

		.on('click', '.lsfilter_visual_node_negate', onnode(function(n, el) {
			n.toggleClass('lsfilter_visual_not');
		}))

		.on('change', '.lsfilter_visual_field_select', onnode(function(n, el) {
			var opt = el.children().filter(function(i) {
				return $(this).attr('value') == el.val();
			}).last();
			var type = opt.attr('data-type').split(';');
			var opselect = n.find('.lsfilter_visual_operator_select');
			if (type)
				lsfilter_graphics_visitor.fill_operator_select(opselect, type);
		}))

		.on('change', '.lsfilter_visual_operator_select',
				onnode(function(n, el) {
					/* Well... don't do anything here. onnode does what's needed */
				}))

		.on('change', '.lsfilter_visual_value_field', onnode(function(n, el) {
			/* Well... don't do anything here. onnode does what's needed */
		}))

		.on('change', '.lsfilter_visual_table_select', function(e) {
			e.preventDefault();
			var n = $(this);
			var table = n.val();

			var ast = {
				'obj' : 'query',
				'table' : table,
				'query' : {
					'obj' : 'match',
					'field' : 'this',
					'value' : '',
					'op' : 'all'
				}
			};
			ast = lsfilter_extra_andor.visit(ast);
			var result = lsfilter_graphics_visitor.visit(ast);
			$('#filter_visual').empty().append(result);

			lsfilter_visual.update_query_delayed();
			return false;
		});
	},

	fields : null,

	update_query : function() {
		var query = lsfilter_dom_to_query.visit($('#filter_visual'), 0);

		lsfilter_main.update(query, 'visual', false);
	},

	update_query_delayed : function() {
		this.update_query();
	},

	/* Validate that there exist at least one top object */
	validate_top_integrity : function() {
		if ($('#filter_visual').find('.lsfilter_visual_node').length == 0) {
			var table = $('#filter_visual').find(
					'.lsfilter_visual_table_select').val();
			var ast = {
				'obj' : 'query',
				'table' : table,
				'query' : {
					'obj' : 'and',
					'sub' : []
				}
			};
			var result = lsfilter_graphics_visitor.visit(ast);
			$('#filter_visual').empty().append(result);
		}
	},

	fields_for_table : function(table) {
		/* Clone to not modify original structure */
		var fields = $.extend({}, orm_structure[table]);

		var subtables = [];
		var key;

		for (key in fields) {
			if (fields[key][0] == 'object') {
				subtables.push({
					field : key,
					table : fields[key][1]
				});
			}
		}

		for (key in subtables) {
			var j;
			var ref = subtables[key];
			for (j in orm_structure[ref.table]) {
				if (orm_structure[ref.table][j][0] != 'object') {
					fields[ref.field + '.' + j] = orm_structure[ref.table][j];
				}
			}
		}

		fields['this'] = [ 'object', table ];
		return fields;
	}
};

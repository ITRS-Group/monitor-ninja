/*******************************************************************************
 * Visitor to add extra and/or blocks around innermost objects so all objects is
 * expandable
 ******************************************************************************/
var lsfilter_visual_ast_preproc = {
	visit_query : function(obj) {
		var query = obj.query;

		if (query.obj != 'and') {
			/* If the outermost query isn't an and statement, enforce it */
			query = {
				'obj' : 'and',
				'sub' : [ query ]
			};
		} else {
			/*
			 * If it is an and statement, we want to wrap all
			 * non-group-statements in an and-clause, so it won't become a huge
			 * amount of and-clauses later. To make it easier to look at, keep
			 * the order, and just group following clauses
			 */

			var clauses = [];
			var current_and = false;
			for ( var i in query.sub) {
				var nextobj = query.sub[i];
				while (nextobj.obj == 'not') {
					nextobj = nextobj.sub;
				}
				if (nextobj.obj != 'and' && nextobj.obj != 'or') {
					if (current_and) {
						current_and.push(query.sub[i]);
					} else {
						current_and = [ query.sub[i] ];
						clauses.push({
							'obj' : 'and',
							'sub' : current_and
						});
					}
				} else {
					current_and = false;
					clauses.push(query.sub[i]);
				}
			}
			query = {
				'obj' : 'and',
				'sub' : clauses
			};
		}

		/*
		 * Make sure second level objects is group statmenets. No single rules
		 * should be floating around, and outermost and group isn't a real and
		 * group
		 */
		for ( var i in query.sub) {
			var nextobj = query.sub[i];

			/* Skip over not-nodes, because negated groups are still groups */
			while (nextobj.obj == 'not') {
				nextobj = nextobj.sub;
			}
			if (nextobj.obj != 'and' && nextobj.obj != 'or') {
				query.sub[i] = {
					'obj' : 'and',
					'sub' : [ query.sub[i] ]
				};
			}
		}

		/*
		 * Reduce the tree. This shouldn't remove operator levels, but just
		 * unused operators, like stray "all" and empty groups
		 */
		query = this.visit(query, 'query');

		/*
		 * If everything was reduced, just create a simple empty and statment as
		 * outermost container, to ensure a "Add * group" link exists, mark it
		 * outermost
		 */
		if (!query) {
			query = {
				'obj' : 'and',
				'sub' : []
			};
		}

		/*
		 * We now know that query root is an and statement. Mark it as
		 * outermost, to remove headers, "Add rule" and "Negate"
		 */
		query.outermost = true;

		return {
			'obj' : 'query',
			'table' : obj.table,
			'query' : query,
		};
	},

	visit_and : function(obj) {
		var list = [];

		for ( var i in obj.sub) {
			var sub = this.visit(obj.sub[i]);

			if (sub && !(sub.obj == 'match' && sub.op == 'all'))
				list.push(sub);
		}
		if (list.length == 0)
			return false;
		return {
			'obj' : 'and',
			'sub' : list
		};
	},
	visit_or : function(obj) {
		var list = [];

		for ( var i in obj.sub) {
			var sub = this.visit(obj.sub[i]);
			if (sub)
				list.push(sub);
		}
		if (list.length == 0)
			return {
				'obj' : 'match',
				'op' : 'all',
				'value' : ''
			};
		return {
			'obj' : 'or',
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

	visit : function(obj) {
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
			'eq' : 'equals',
			'not_eq' : 'not equals',
			'eq_ci' : 'equals, case insensitive',
			'not_eq_ci' : 'not equals, case insensitive',
			're_cs' : 'matching regexp',
			're_ci' : 'matching regexp, case insensitive',
			'not_re_cs' : 'not matching regexp',
			'not_re_ci' : 'not matching regexp, case insensitive'
		},
		"int" : {
			'eq' : 'equal',
			'not_eq' : 'not equals',
			'lt' : 'less than',
			'lt_eq' : 'less than or equal',
			'gt' : 'greater than',
			'gt_eq' : 'greater than or equals'
		},
		"float" : {
			'eq' : 'equals',
			'not_eq' : 'not equals',
			'lt' : 'less than',
			'lt_eq' : 'less than or equals',
			'gt' : 'greater than',
			'gt_eq' : 'greater than or equals'
		},
		"time" : {
			'eq' : 'at',
			'not_eq' : 'not at',
			'lt' : 'earlier than',
			'lt_eq' : 'earlier than or at',
			'gt' : 'later than',
			'gt_eq' : 'later than or at'
		},
		"object" : {
			'all' : 'all',
			'in' : 'in named set'
		},
		"list" : {
			'eq' : 'equals',
			'not_eq' : 'not equals',
			'gt_eq' : 'contains'
		},
		/* FIXME: This is ugly... better grammar for custom_variables */
		"dict" : {
			'eq' : 'equals',
			'not_eq' : 'not equals',
			'eq_ci' : 'equals, case insensitive',
			'not_eq_ci' : 'not equals, case insensitive',
			're_cs' : 'matching regexp',
			'not_re_cs' : 'not matching regexp',
			're_ci' : 'matching regexp, case insensitive',
			'not_re_ci' : 'not matching regexp, case insensitive'
		}
	},

	visit_query : function(obj) {
		this.fields = lsfilter_visual.fields_for_table(obj.table);
		var container = $('<div />');

		var tablesel = $('<select />');
		tablesel.addClass('lsfilter_visual_table_select');
		var tables = Object.keys(listview_renderer_table).sort();
		var table;
		for (var i = 0; i < tables.length; i++) {
			table = tables[i];
			var opt = $('<option />');
			opt.attr('value', table);
			opt.text(table);
			tablesel.append(opt);
		}
		tablesel.val(obj.table);
		container.append(tablesel);

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
		header.addClass('lsfilter_visual_group_header');

		list.append(header);

		if (!obj.outermost) {
			header.append($('<a class="lsfilter_visual_node_remove" />')
					.append(icon12('cross')));

			header.append($('<span />').text(_(op + " filter group")));
		}

		for ( var i in obj.sub) {
			list.append(this.visit(obj.sub[i]));
		}

		var footer = $('<div />');
		footer.addClass('lsfilter_visual_newmarker');
		footer.addClass('lsfilter_visual_group_footer');

		list.append(footer);

		var link;

		if (!obj.outermost) {
			link = $('<a />');
			link.addClass('lsfilter_visual_node_addrule');
			link.text(_('Add rule'));
			footer.append(link);
		}

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

		if (!obj.outermost) {
			link = $('<a />');
			link.addClass('lsfilter_visual_node_negate');
			link.text(_('Negate group'));
			footer.append(link);
		}

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

		if (obj.op == 'all' || (obj.op == 'in' && !obj.field)) {
			obj.field = 'this';
		}

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

		var link = $('<a />');
		link.addClass('lsfilter_visual_node_negate');
		link.text(_('Negate'));
		result.append(link);

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
			return $(this).prop('value') == field;
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
		} else if (type[0] == 'time') {
			value = 'date("'+value+'")'
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

	type_timeout : 500,
	current_timeout : false,

	on: {
		'update_ok': function(data) {
			if (data.source == 'visual')
				return;
			if (!this.filter_visual)
				return;
			var parser = new LSFilter(new LSFilterPP(), new LSFilterASTVisitor());
			try {
				var ast = parser.parse(data.query);
				ast = lsfilter_visual_ast_preproc.visit(ast);
				var result = lsfilter_graphics_visitor.visit(ast);
				this.filter_visual.empty().append(result);
				this.update_depths();
				this.update_binary_delimiters();
			} catch (ex) {
				console.log(ex.stack);
				console.log(data.query);
			}
		}
	},
	init : function(filter_visual) {
		this.filter_visual = filter_visual;
		var onnode = function(fnc) {
			return function(e) {
				e.preventDefault();
				fnc($(this).closest('.lsfilter_visual_node'), $(this));

				lsfilter_visual.validate_top_integrity();
				lsfilter_visual.update_depths();
				lsfilter_visual.update_binary_delimiters();
				lsfilter_visual.update_query_delayed();
				return false;
			};
		};

		this.filter_visual

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
				return $(this).prop('value') == el.val();
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

		.on('change keyup', '.lsfilter_visual_value_field',
				onnode(function(n, el) {
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
			ast = lsfilter_visual_ast_preproc.visit(ast);
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
		var self = this;
		if (this.current_timeout) {
			clearTimeout(this.current_timeout);
		}
		this.current_timeout = setTimeout(function() {
			self.current_timeout = false;
			self.update_query();
		}, this.type_timeout);
	},

	/* Color nodes after depth */
	update_depths : function() {
		$('#filter_visual').find('.lsfilter_visual_node').each(function() {
			var depth = $(this).parents('.lsfilter_visual_node').length;
			$(this).addClass('lsfilter_visual_node_' + depth);
		});
	},

	update_binary_delimiters : function() {
		var do_update = function(delimiter, operator) {
			var cls = '.lsfilter_visual_group_' + operator;
			delimiter.addClass('lsfilter_visual_delimiter');
			$('#filter_visual')
					.find(cls + ' > .lsfilter_visual_node')
					.filter(
							function(i) {
								return $(this).prev('.lsfilter_visual_node').length > 0;
							}).before(delimiter);
		};
		$('#filter_visual').find('.lsfilter_visual_delimiter').remove();
		do_update($('<div>OR</div>'), 'or');
		do_update($('<div>AND</div>'), 'and');
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
			ast = lsfilter_visual_ast_preproc.visit(ast);
			var result = lsfilter_graphics_visitor.visit(ast);
			$('#filter_visual').empty().append(result);
		}
	},

	fields_for_table : function(table) {
		/* Clone to not modify original structure */
		var fields = $.extend({}, ninja_manifest.orm_structure[table]);

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
			for (j in ninja_manifest.orm_structure[ref.table]) {
				if (ninja_manifest.orm_structure[ref.table][j][0] != 'object') {
					fields[ref.field + '.' + j] = ninja_manifest.orm_structure[ref.table][j];
				}
			}
		}

		fields['this'] = [ 'object', table ];
		return fields;
	}
};

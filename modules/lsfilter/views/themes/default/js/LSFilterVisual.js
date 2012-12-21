/*******************************************************************************
 * Visitor to add extra and/or blocks around innermost objects so all objects is
 * expandable
 ******************************************************************************/
var lsfilter_extra_andor = {
	last_op : false,

	visit_query : function(obj) {
		var result = {
			'obj' : 'query',
			'table' : obj.table
		};
		var query = this.visit(obj.query, 'or');
		var newop = 'or';
		if (query.obj == 'or') {
			newop = 'and';
		}
		result['query'] = {
			'obj' : newop,
			'sub' : [ query ]
		};
		return result;
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
			list.push(this.visit(obj.sub[i], op));
		}
		return {
			'obj' : op,
			'sub' : list
		};
	},
	visit_match : function(obj) {
		var op = (this.last_op == 'and') ? ('or') : ('and');
		return {
			'obj' : op,
			'sub' : [ obj ]
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
			'in' : 'in',
			'not_re_ci' : '!~~',
			'not_re_cs' : '!~',
			're_ci' : '~~',
			're_cs' : '~',
			'not_eq_ci' : '!=~',
			'eq_ci' : '=~',
			'not_eq' : '!=',
			'eq' : '='
		},
		"int" : {
			'not_eq' : '!=',
			'gt_eq' : '>=',
			'lt_eq' : '<=',
			'gt' : '>',
			'lt' : '<',
			'eq' : '='
		}
	},

	visit_query : function(obj) {
		this.fields = livestatus_structure[obj.table];
		this.fields['this'] = [ 'string' ];
		return this.visit(obj.query);
	},

	visit_and : function(obj) {
		return this.visit_andor(obj, 'and');
	},

	visit_or : function(obj) {
		return this.visit_andor(obj, 'or');
	},

	visit_andor : function(obj, op) {
		var self = this; // To be able to access it from within handlers
		var list = $('<ul class="lsfilter-' + op + '">');
		for ( var i in obj.sub) {
			list.append($(
					'<li class="lsfilter-expr lsfilter-' + op + '-expr"/>')
					.append(this.visit(obj.sub[i])));
		}
		var button = $('<button class="lsfilter-add-' + op + '" />')
				.text(_(op));
		button.click(function(e) {
			self.gui_stmnt_button(e, op, $(this));
		});
		list
				.append($('<li class="lsfilter-' + op + '-expr" />').append(
						button));
		return list;

	},

	visit_not : function(obj) {
		return this.visit(obj.sub);
	},

	visit_match : function(obj) {
		var self = this;
		var result = $('<ul class="lsfilter-comp" />');

		var fields = $('<select />');
		var ops = $('<select class="lsfilter-operator-select" />');
		var val = $('<input type="text" value="' + obj.value + '" />');

		for ( var f in this.fields) {
			if (f == obj.field || (f=='this' && !obj.field)) {
				fields.append($('<option value="' + f + '" selected="true">'
						+ f + '</option>'));
			} else {
				fields
						.append($('<option value="' + f + '">' + f
								+ '</option>'));
			}
		}

		result.append($('<li />').append(fields));
		result.append($('<li />').append(ops));

		self.field_change(fields.val(), obj.op, val, ops);
		fields.change(function() {
			self.field_change($(this).val(), obj.op, val, ops);
		});

		val.removeClass().addClass(
				'lsfilter-type-' + this.fields[fields.val()].join(''));

		result.append($('<li />').append(val));

		return result;
	},

	field_change : function(field, op, val, ops) {
		var operators = this.operators[this.fields[field]];
		val.removeClass().addClass(
				'lsfilter-type-' + this.fields[field].join(''));
		ops.empty();

		for ( var operator in operators) {
			if (operator == op) {
				ops.append($('<option selected="true" value="'
						+ operators[operator] + '">' + operators[operator]
						+ '</option>'));
			} else {
				ops.append($('<option value="' + operators[operator] + '">'
						+ operators[operator] + '</option>'));
			}
		}
	},

	gui_stmnt_button : function(evt, op, btn) {
		var self = this;
		var newop = (op == 'and') ? ('or') : ('and');
		evt.preventDefault();

		var clone = this.visit({
			'obj' : newop,
			'sub' : [ {
				'obj' : 'match',
				'op' : 'in',
				'field' : 'this',
				'value' : ''
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
		button.click(function(e) {
			self.gui_stmnt_button(e, newop, $(this));
		});

		match_field.parent().parent().append($(
				'<li class="lsfilter-' + newop + '-expr" />').append(button));

		$('<li class="lsfilter-expr lsfilter-' + op + '-expr" />')
				.append(clone).insertBefore(btn.closest('li'));
	},

	visit : function(obj) {
		return LSFilterASTVisit(obj, this);
	}
};

var lsfilter_visual = {

	update : function(query, source, metadata) {
		if (source == 'visual')
			return;
		var parser = new LSFilter(new LSFilterPP(),
				new LSFilterASTVisitor());
		try {
			var ast = parser.parse(query);
			ast = lsfilter_extra_andor.visit(ast);
			var result = lsfilter_graphics_visitor.visit(ast);
			$('#filter_visual').empty().append(result);
		} catch (ex) {
			console.log(ex.stack);
			console.log(query);
		}
	},
	init : function() {
		/*
		 * $('#filter_visual_form').bind('change', dotraverse); dotraverse();
		 */
	},

	fields : null,

	/*
	 * 
	 * var filter_string = ['[', $('#lsfilter-query-object').attr('value') , ']
	 * '];
	 * 
	 * filter_string.push( traverse($('#filter_visual .lsfilter-root'), 0) );
	 */
	traverse : function(dom, priority) {

		var seg = [];
		var tmp = null;
		var result = "";
		var out_priority;

		if (dom.hasClass('lsfilter-comp')) {

			dom.children().each(function() {
				if (this.value != 'this') {

					if ($(this).hasClass('lsfilter-type-string')) {
						seg.push('"' + this.value + '"');
					} else if ($(this).hasClass('lsfilter-operator-select')) {
						seg.push(' ' + this.value + ' ');
					} else {
						seg.push(this.value);
					}

				}
			});

			result = seg.join('');
			out_priority = 3;

		} else if (dom.is('.lsfilter-not')) {
			// FIXME
			result = ' not ';
			out_priority = priority;

		} else if (dom.hasClass('lsfilter-and') || dom.hasClass('lsfilter-or')) {

			if (dom.hasClass('lsfilter-and')) {
				out_priority = 2;
			} else if (dom.hasClass('lsfilter-or')) {
				out_priority = 1;
			}

			dom.children().each(function() {
				tmp = traverse($(this), out_priority);
				if (tmp)
					seg.push(tmp);
			});

			if (dom.hasClass('lsfilter-and')) {
				result = seg.join(' and ');
			} else if (dom.hasClass('lsfilter-or')) {
				result = seg.join(' or ');
			}

		} else {

			dom.children().each(function() {
				seg.push(traverse($(this), priority));
			});

			result = seg.join('');
			out_priority = priority;

		}
		if (out_priority < priority)
			result = "(" + result + ")";
		return result;
	}
};
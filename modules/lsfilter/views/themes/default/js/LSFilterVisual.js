var LSFilterVisualizerVisitor = function LSFilterVisualizerVisitor() {

	// Just some demo data

	this.fields = null;

	this.operators = {

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

	};

	// End of just some demo data

	this.accept = function(result) {
		return result;
	};

	this.visit_entry = function(query0) {
		return query0;
	};

	this.visit_query = function(table_def1, search_query3) {
		var result = $('<ul />');
		result.append($('<li style="margin: 3px 0"><strong>' + _('Query')
				+ '</strong></li>'));
		result.append($('<li class="resultvisual" />').append(table_def1));
		result.append($('<li class="resultvisual" />').append(search_query3));
		return result;
	};

	this.visit_table_def_simple = function(name0) {

		var that = this, result = $('<ul />'), groups = $('<select id="lsfilter-query-object" />');

		for ( var type in livestatus_structure) {
			if (type == name0) {
				groups.append($('<option selected="true" value="' + type + '">'
						+ type + '</option>'));
				this.fields = livestatus_structure[type];
				this.fields['this'] = [ 'string' ];
			} else {
				groups.append($('<option value="' + type + '">' + type
						+ '</option>'))
			}
		}

		groups.change(function() {
			that.fields = livestatus_structure[$(this).val()];
		});

		result.append($('<li class="resultvisual" />').append(groups));
		return result;

	};

	this.visit_search_query = function(filter0) {

		var result = $('<ul />');
		filter0.addClass('lsfilter-root');

		result.append($('<li style="margin: 3px 0"><strong>' + _('With filter')
				+ ': </strong></li>'));
		result.append($('<li class="resultvisual" />').append(filter0));

		return result;
	};

	this.visit_filter_or = function(filter0, filter2) {

		var that = this;

		if (filter0.is('.lsfilter-or')) {
			var result = filter0;
		} else {
			var result = $('<ul class="lsfilter-or" />');
			result.append($('<li class="resultvisual lsfilter-or-expr" />')
					.append(filter0));
		}
		result
				.append($('<li class="lsfilter-or-text"><strong>- OR -</strong></li>'));
		result.append($('<li class="resultvisual lsfilter-or-expr" />').append(
				filter2));

		result.append($('<button class="lsfilter-add-and" />').text(_('And'))
				.click(function(e) {
					that.gui_stmnt_button('and', result, e, $(this));
				}));

		result.append($('<button class="lsfilter-add-or" />').text(_('Or'))
				.click(function(e) {
					that.gui_stmnt_button('or', result, e, $(this));
				}));

		return result;
	};

	this.visit_filter_and = function(filter0, filter2) {

		var that = this;

		if (filter0.is('.lsfilter-and')) {
			var result = filter0;
			result.append($('<li class="resultvisual lsfilter-and-expr" />')
					.append(filter2));
		} else {
			var result = $('<ul class="lsfilter-and" />');
			result.append($('<li class="resultvisual lsfilter-and-expr" />')
					.append(filter0));
			result.append($('<li class="resultvisual lsfilter-and-expr" />')
					.append(filter2));
		}

		result.append($('<button class="lsfilter-add-and" />').text(_('And'))
				.click(function(e) {
					that.gui_stmnt_button('and', result, e, $(this));
				}));

		result.append($('<button class="lsfilter-add-or" />').text(_('Or'))
				.click(function(e) {
					that.gui_stmnt_button('or', result, e, $(this));
				}));

		return result;
	};

	this.visit_filter_ok = function(match) {
		var result = $('<ul class="lsfilter-peak-true" />');
		result.append($('<li class="resultvisual" />').append(match));
		return result;
	};

	this.visit_filter_not = function(match) {
		var result = $('<ul  class="lsfilter-peak-false" />');
		result.append($('<li class="resultvisual" />').append(match));
		return result;
	};

	this.visit_match_all = function() {
		return this.match('all', "this", "");
	}
	this.visit_match_in = function(set_descr1) {
		return this.match('in', "this", set_descr1);
	};
	this.visit_match_field_in = function(field0, expr2) {
		return this.match('field_in', field0, expr2);
	};
	this.visit_match_not_re_ci = function(field0, expr2) {
		return this.match('not_re_ci', field0, expr2);
	};
	this.visit_match_not_re_cs = function(field0, expr2) {
		return this.match('not_re_cs', field0, expr2);
	};
	this.visit_match_re_ci = function(field0, expr2) {
		return this.match('re_ci', field0, expr2);
	};
	this.visit_match_re_cs = function(field0, expr2) {
		return this.match('re_cs', field0, expr2);
	};
	this.visit_match_not_eq_ci = function(field0, expr2) {
		return this.match('not_eq_ci', field0, expr2);
	};
	this.visit_match_eq_ci = function(field0, expr2) {
		return this.match('eq_ci', field0, expr2);
	};
	this.visit_match_not_eq = function(field0, expr2) {
		return this.match('not_eq', field0, expr2);
	};
	this.visit_match_gt_eq = function(field0, expr2) {
		return this.match('gt_eq', field0, expr2);
	};
	this.visit_match_lt_eq = function(field0, expr2) {
		return this.match('lt_eq', field0, expr2);
	};
	this.visit_match_gt = function(field0, expr2) {
		return this.match('gt', field0, expr2);
	};
	this.visit_match_lt = function(field0, expr2) {
		return this.match('lt', field0, expr2);
	};
	this.visit_match_eq = function(field0, expr2) {
		return this.match('eq', field0, expr2);
	};

	this.field_change = function(field, op, val, ops) {

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
	};

	this.find_root_node = function(node) {
		if (node.hasClass('lsfilter-and') || node.hasClass('lsfilter-or')
				|| node.hasClass('lsfilter-root')) {
			return node;
		} else {
			return this.find_root_node(node.parent());
		}
	};

	this.gui_stmnt_button = function(stmnt, enclosing, evt, btn) {

		evt.preventDefault();

		var stmnt_block = this.find_root_node(btn), clone = this.match('eq',
				'state', '0'), tmp = null;

		if (!stmnt_block.is('.lsfilter-' + stmnt)) {

			tmp = $('<div />');
			enclosing.after(tmp);

			if (stmnt == 'and') {
				stmnt_block = this.visit_filter_and(enclosing, clone);
			} else if (stmnt == 'or') {
				stmnt_block = this.visit_filter_or(enclosing, clone);
			}

			tmp.replaceWith(stmnt_block);

		} else {
			if (stmnt == 'and') {
				stmnt_block.children('button').remove();
				stmnt_block = this.visit_filter_and(stmnt_block, clone);
			} else if (stmnt == 'or') {
				stmnt_block.children('button').remove();
				stmnt_block = this.visit_filter_or(stmnt_block, clone);
			}
		}
	};

	this.match = function(op, name, expr) {

		var that = this, val = $('<input type="text" value="'
				+ expr.replace(/['"]/g, '') + '" />'), result = $('<ul class="lsfilter-comp" />'), fields = $('<select />'), ops = $('<select class="lsfilter-operator-select" />');

		for ( var f in this.fields) {
			if (f == name) {
				fields.append($('<option value="' + f + '" selected="true">'
						+ f + '</option>'));
			} else {
				fields
						.append($('<option value="' + f + '">' + f
								+ '</option>'));
			}
		}

		result.append(fields);
		result.append(ops);

		that.field_change(fields.val(), op, val, ops);
		fields.change(function() {
			that.field_change($(this).val(), op, val, ops);
		});

		if (name == "this") {
			fields.empty();
			fields.append($('<option />').val('this').text('this'));
			fields.attr('disabled', true);
		}

		val.removeClass().addClass(
				'lsfilter-type-' + this.fields[fields.val()].join(''));

		// console.log(this.fields);
		result.append(val);

		result.append($('<button class="lsfilter-add-and" />').text(_('And'))
				.click(function(e) {
					that.gui_stmnt_button('and', result, e, $(this));
				}));

		result.append($('<button class="lsfilter-add-or" />').text(_('Or'))
				.click(function(e) {
					that.gui_stmnt_button('or', result, e, $(this));
				}));

		return result;
	}

	// set_descr_name: Êset_descr := * string
	this.visit_set_descr_name = function(string0) {
		return string0;
	};

	// field_name: field := * name
	this.visit_field_name = function(name0) {
		return name0;
	};

	// field_obj: field := * name dot field
	this.visit_field_obj = function(name0, field2) {
		return name0 + "." + field2;
	};
};

var lsfilter_visual = {
	update : function(query) {
		var parser = new LSFilter(new LSFilterPreprocessor(),
				new LSFilterVisualizerVisitor());
		try {
			var result = parser.parse(query);
			$('#filter_visual').empty().append(result);
		} catch (ex) {
			console.log(ex);
			console.log(query);
		}
	},
	init : function() {
		/*
		 * $('#filter_visual_form').bind('change', dotraverse); dotraverse();
		 */
	},

	/*
	 * 
	var filter_string = ['[', $('#lsfilter-query-object').attr('value') , '] '];

	filter_string.push(
		traverse($('#filter_visual .lsfilter-root'), 0)
	);
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
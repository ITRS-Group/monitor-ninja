
var saved_filters = {};

function resolve_type (query) {
	query = query.split('[')[1];
	query = query.split(']')[0];
	query = query.replace(/^\s+/, '');
	
	for (var i = query.length - 1; i >= 0; i--) {
		if (/\S/.test(query.charAt(i))) {
			query = query.substring(0, i + 1);
			break;
		}
	}

	return query;
};

function add_saved_filter_list ( list, save ) {

	var type = resolve_type(save['query']),
		icon = "";

	switch (type) {
		case "hosts":
			icon = '<span class="icon-menu menu-host"></span>'
			break;
		case "services":
			icon = '<span class="icon-menu menu-service"></span>'
			break;
		case "hostgroups":
			icon = '<span class="icon-menu menu-hostgroupsummary"></span>'
			break;
		case "servicegroups":
			icon = '<span class="icon-menu menu-servicegroupsummary"></span>'
			break;
		default:
			icon = '<span class="icon-menu menu-eventlog"></span>'
			break;
	}

	list.append( 
		
		$('<li class="saved-filter-'+save['scope']+'" />').html(
			icon + '<a href="?filter_query=' + save['query'] + '">' + save['scope'].toUpperCase() + ' - ' + save['name'] + '</a>'
		).hover(function () {
			$('#filter-query-saved-preview').html( save['query'] );
		}, function () {
			$('#filter-query-saved-preview').empty();
		})

	);
}

function listview_load_filters () {
	
	var basepath = _site_domain + _index_page;

	$.ajax(basepath + '/listview/fetch_saved_queries', {
		data: {
			'type': 'lsfilters_saved',
			'page': 'listview'
		},
		type: 'POST',
		complete: function (xhr) {

			saved_filters = JSON.parse(xhr.responseText);

			$('#filter-query-saved-hide-static, #filter-query-saved-hide-global, #filter-query-saved-hide-user').removeAttr('checked');
			var list = $("#filter-query-saved-filters").empty();

			for (var filter in saved_filters.data) {
				add_saved_filter_list(list, saved_filters.data[filter])						
			}

		}
	});
}

function listview_save_filter (filter) {
	
	var basepath = _site_domain + _index_page,
		save = {"query": filter, "scope": "user"},
		name = $('#lsfilter_save_filter_name').val();

	if (name) {

		if ($('#lsfilter_save_filter_global').attr('checked')) {
			save["scope"] = "global";
		}

		save['name'] = name;
		
		$.ajax(basepath + '/listview/save_query', {
			data: save,
			type: 'GET',
			complete: function (xhr) {
				$('#lsfilter_save_filter').removeClass().text('Save');
				listview_load_filters();
			}
		});
	
	} else {
		$.jGrowl('You must give the filter a name!');
		$('#lsfilter_save_filter').removeClass().text('Save');
	}
}

var LSFilterVisualizerVisitor = function LSFilterVisualizerVisitor(){

	// Just some demo data

	this.fields = null;

	this.operators = {

		"string": {
			'in': 'in',
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
		result.append($('<li style="margin: 3px 0"><strong>Query</strong></li>'));
		result.append($('<li class="resultvisual" />').append(table_def1));
		result.append($('<li class="resultvisual" />').append(search_query3));
		return result;
	};
	
	this.visit_table_def_simple = function(name0) {

		var that = this,
			result = $('<ul />'),
			groups = $('<select id="lsfilter-query-object" />');

		for (var type in livestatus_structure) {
			if (type == name0) {
				groups.append($('<option selected="true" value="' + type + '">' + type + '</option>'));
				this.fields = livestatus_structure[type];
				this.fields['this'] = ['string'];
			} else {
				groups.append($('<option value="' + type + '">' + type + '</option>'))
			}
		}

		groups.change(function () {
			that.fields = livestatus_structure[$(this).val()];
		});

		result.append($('<li class="resultvisual" />').append(groups));
		return result;

	};
	
	this.visit_table_def_columns = function(name0, column_list2) {
		var result = $('<ul />');
		result.append($('<li><strong>table_def_columns</strong></li>'));
		result.append($('<li class="resultvisual" />').append(name0));
		result.append($('<li class="resultvisual" />').append(column_list2));
		return result;
	};
	
	this.visit_column_list_end = function(name0) {
		var result = $('<ul />');
		result.append($('<li><strong>column_list_end</strong></li>'));
		result.append($('<li class="resultvisual" />').append(name0));
		return result;
	};
	
	this.visit_column_list_cont = function(column_list0, name2) {
		var result = $('<ul />');
		result.append($('<li><strong>column_list_cont</strong></li>'));
		result.append($('<li class="resultvisual" />').append(column_list0));
		result.append($('<li class="resultvisual" />').append(name2));
		return result;
	};
	
	this.visit_search_query = function(filter0) {
		var result = $('<ul />');
		filter0.addClass('lsfilter-root');
		result.append($('<li style="margin: 3px 0"><strong>With filter: </strong></li>'));
		result.append($('<li class="resultvisual" />').append(filter0));
		return result;
	};
	
	this.visit_filter_or = function(filter0, filter2) {

		if (filter0.is('.lsfilter-or')) {
			var result = filter0;
		} else {
			var result = $('<ul class="lsfilter-or" />');
			result.append($('<li class="resultvisual lsfilter-or-expr" />').append(filter0));	
		}
		result.append($('<li class="lsfilter-or-text"><strong>- OR -</strong></li>'));
		result.append($('<li class="resultvisual lsfilter-or-expr" />').append(filter2));

		return result;
	};
	
	this.visit_filter_and = function(filter0, filter2) {

		if (filter0.is('.lsfilter-and')) {
			var result = filter0;
			result.append($('<li class="resultvisual lsfilter-and-expr" />').append(filter2));
		} else {
			var result = $('<ul class="lsfilter-and" />');
			result.append($('<li class="resultvisual lsfilter-and-expr" />').append(filter0));
			result.append($('<li class="resultvisual lsfilter-and-expr" />').append(filter2));
		}
		
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
	
	this.visit_match_in        = function(set_descr1)    { return this.match('in',       "this", set_descr1); };
	this.visit_match_field_in  = function(field0, expr2) { return this.match('field_in', field0,expr2); };
	this.visit_match_not_re_ci = function(field0, expr2) { return this.match('not_re_ci',field0,expr2); };
	this.visit_match_not_re_cs = function(field0, expr2) { return this.match('not_re_cs',field0,expr2); };
	this.visit_match_re_ci     = function(field0, expr2) { return this.match('re_ci',    field0,expr2); };
	this.visit_match_re_cs     = function(field0, expr2) { return this.match('re_cs',    field0,expr2); };
	this.visit_match_not_eq_ci = function(field0, expr2) { return this.match('not_eq_ci',field0,expr2); };
	this.visit_match_eq_ci     = function(field0, expr2) { return this.match('eq_ci',    field0,expr2); };
	this.visit_match_not_eq    = function(field0, expr2) { return this.match('not_eq',   field0,expr2); };
	this.visit_match_gt_eq     = function(field0, expr2) { return this.match('gt_eq',    field0,expr2); };
	this.visit_match_lt_eq     = function(field0, expr2) { return this.match('lt_eq',    field0,expr2); };
	this.visit_match_gt        = function(field0, expr2) { return this.match('gt',       field0,expr2); };
	this.visit_match_lt        = function(field0, expr2) { return this.match('lt',       field0,expr2); };
	this.visit_match_eq        = function(field0, expr2) { return this.match('eq',       field0,expr2); };
	
	this.match = function(op,name,expr) {

		console.log(arguments);

		name = name.find('.resultvisual').html();

		var that = this,
			val = $('<input type="text" value="' + expr.replace(/['"]/g,'') + '" />'),
			result = $('<ul class="lsfilter-comp" />'),
			fields = $('<select />'),
			ops = $('<select class="lsfilter-operator-select" />');

		for (var f in this.fields) {
			console.log(name);
			if (f == name) {
				fields.append($('<option value="' + f + '" selected="true">' + f + '</option>'));
			} else {
				fields.append($('<option value="' + f + '">' + f + '</option>'));
			}
		}
		
		result.append(fields);

		for (var operator in this.operators[that.fields[fields.val()]]) {
			var possibles = this.operators[that.fields[fields.val()].join('')];
			if (operator == op) {
				ops.append($('<option selected="true" value="' + possibles[operator] + '">' + possibles[operator] + '</option>'));
			} else {
				ops.append($('<option value="' + possibles[operator] + '">' + possibles[operator] + '</option>'));
			}
		}
		
		result.append(ops);

		fields.change(function () {
			val.removeClass().addClass('lsfilter-type-' + that.fields[this.value].join(''));

			ops.empty();
			for (var operator in that.operators[that.fields[fields.val()]]) {
				var possibles = that.operators[that.fields[fields.val()].join('')];

				if (operator == op) {
					ops.append($('<option selected="true" value="' + possibles[operator] + '">' + possibles[operator] + '</option>'));
				} else {
					ops.append($('<option value="' + possibles[operator] + '">' + possibles[operator] + '</option>'));
				}
			}

		});

		if (name == "this") {
			fields.empty();
			fields.append( $('<option />').val('this').text('this') );
			fields.attr('disabled', true);
		}

		

		val.removeClass().addClass('lsfilter-type-' + this.fields[fields.val()].join(''));

		//console.log(this.fields);
		result.append(val);

		result.append($('<button class="lsfilter-add-and" />').text('And').click(function (e) {

			var and_block = $(this).parent().parent().parent().parent().parent(),
				clone = that.match('eq', 'state', '0');

			if (!and_block.is('.lsfilter-and')) {

				var tmp = $('<div />');
				result.after(tmp);

				var and_block = that.visit_filter_and(result, clone);
				tmp.replaceWith(and_block);

			} else {
				that.visit_filter_and(and_block, clone);
			}

			e.preventDefault();

		}));

		result.append($('<button class="lsfilter-add-or" />').text('Or').click(function (e) {
			
			var or_block = $(this).parent().parent().parent().parent().parent(),
				clone = that.match('eq', 'state', '0');
			
			if (!or_block.is('.lsfilter-or')) {
				
				var tmp = $('<div />');
				result.after(tmp);

				var or_block = that.visit_filter_or(result, clone);
				tmp.replaceWith(or_block);

			} else {
				that.visit_filter_or(or_block, clone);
			}
			

			e.preventDefault();
		}));

		return result;
	}
	
	// set_descr_name: Êset_descr := * string
	this.visit_set_descr_name = function(string0) {
		return string0;
	};
	
	// field_name: field := * name
	this.visit_field_name = function(name0) {
		var result = $('<ul />');
		result.append($('<li><strong>field_name</strong></li>'));
		result.append($('<li class="resultvisual" />').append(name0));
		return result;
	};
	
	// field_obj: field := * name dot field
	this.visit_field_obj = function(name0, field2) {
		var result = $('<ul />');
		result.append($('<li><strong>field_obj</strong></li>'));
		result.append($('<li class="resultvisual" />').append(name0));
		result.append($('<li class="resultvisual" />').append(field2));
		return result;
	};
};

var visualizeSearchFilter = function(evt) {
	
	var filter_string = [];

	var traverse = function (dom, priority) {

		var seg = [];
		var tmp = null;
		var result = "";
		var out_priority;

		if (dom.hasClass('lsfilter-comp')) {

			dom.children().each(function () {
				if (this.value != 'this') {
					

					if ($(this).hasClass('lsfilter-type-string')) {
						seg.push('"' + this.value + '"');	
					} else if( $(this).hasClass('lsfilter-operator-select') ) {
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
			
			dom.children().each(function () {
				tmp = traverse($(this), out_priority);
				if (tmp) seg.push(tmp);
			});

			if (dom.hasClass('lsfilter-and')) {
				result = seg.join(' and ');
			} else if (dom.hasClass('lsfilter-or')) {
				result = seg.join(' or ');
			}

		} else {

			dom.children().each(function () {
				seg.push(traverse($(this), priority));
			});

			result = seg.join('');
			out_priority = priority;

		}
		if( out_priority < priority )
			result = "(" + result + ")";
		return result;
	};

	var string = $('#filter_query').val();
	var parser = new LSFilter(new LSFilterPreprocessor(), new LSFilterVisualizerVisitor());

	var dotraverse = function () {
		
		filter_string = ['[', $('#lsfilter-query-object').attr('value') , '] '];

		filter_string.push(traverse($('#filter_visual .lsfilter-root'),0));

		if ($(document.activeElement).attr('id') != 'filter_query') {
			$('#filter_query').val(filter_string.join(''));
		}

		$('#filter_visual_result').html(
			'<strong>URI: </strong><input type="text" onclick="this.select()" value="' + $('#server_name').val() + '/ninja/index.php/listview?filter_query='+ filter_string.join('') +'">'
		);
	}

	$('#filter_visual_form').bind('change', dotraverse);

	try {

		listview_update($('#filter_query').val());
		var result = parser.parse(string);

		$('#filter_visual').empty().append(result);
		$('#filter_query').css("border", "2px solid #5d2");

		dotraverse();

	} catch( ex ) {
		$('#filter_query').css("border", "2px solid #f40")
		console.log(ex);
	}
}

$().ready(function() {

	listview_load_filters();

	var hide_main_box = function () {

		if ($('#filter-query-builder-manual').css('display') == 'none' && 
			$('#filter-query-builder-graphical').css('display') == 'none' &&
			$('#filter-query-saved').css('display') == 'none') {

			$('#filter-query-builder').hide();

		}
	}

	$('#lsfilter_save_filter').click(function () {
		$(this).addClass('saving').text('Saving...');
		listview_save_filter($('#filter_query').val());	
	})

	$('#show-filter-query-builder-manual-button').click(function () {

		$('#filter-query-builder-manual').toggle(100, function () {
			switch ($(this).css('display')) {
				case "block":
					$('#filter-query-builder').show(200);
					break;
				case "none":
					hide_main_box();
					break;
			}
		});
	});

	function toggle_filter_type (type) {
		$('#filter-query-saved-filters .saved-filter-' + type).toggle(200);
	}

	$('#filter-query-saved-hide-static, #filter-query-saved-hide-global, #filter-query-saved-hide-user').click(function () {
		var type = $(this).attr('id').split('-');
		type = type[type.length - 1];
		toggle_filter_type(type);
	});

	$('#show-filter-query-saved').click(function () {
		$('#filter-query-saved').toggle(100, function () {
			switch ($(this).css('display')) {
				case "block":
					$('#filter-query-builder').show(200);
					break;
				case "none":
					hide_main_box();
					break;
			}
		});
	});

	$('#show-filter-query-builder-graphical-button').click(function () {
		$('#filter-query-builder-graphical').toggle(100, function () {
			switch ($(this).css('display')) {
				case "block":
					$('#filter-query-builder').show(200);
					break;
				case "none":
					hide_main_box();
					break;
			}
		});
	});

	visualizeSearchFilter(false);
	$('#filter_query').bind('input propertychange',visualizeSearchFilter);
});

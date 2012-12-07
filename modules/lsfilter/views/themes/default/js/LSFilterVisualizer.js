var LSFilterVisualizerVisitor = function LSFilterVisualizerVisitor(){

	// Just some demo data

	this.fields = null;

	this.demo_fields = {

		'hosts': {

			'state': {'-2': 'EXCLUDED', '-1': 'PENDING', 0: 'UP', 1: 'DOWN', 2: 'UNREACHABLE', 7: 'ALL'},
			'name': "name",

			'lastcheck': (new Date()),
			'duration': 0

		},

		'services': {

			'name': "name",
			'state': {'-2': 'EXCLUDED', '-1': 'PENDING', 0: 'OK', 1: 'WARNING', 2: 'CRITICAL', 3: 'UNKNOWN', 15: 'ALL'},

			'hostname': "name",
			'hoststatus': {'-2': 'EXCLUDED', '-1': 'PENDING', 0: 'UP', 1: 'DOWN', 2: 'UNREACHABLE', 7: 'ALL'},
			
			'lastcheck': (new Date()),
			'duration': 0

		}

	};

	this.op_replacements = {
		'in': 'in',
		'not_re_ci': '!~~',
		'not_re_cs': '!~',
		're_ci': '~~',
		're_cs': '~',
		'not_eq_ci': '!=~',
		'eq_ci': '=~',
		'not_eq': '!=',
		'gt_eq': '>=',
		'lt_eq': '<=',
		'gt': '>',
		'lt': '<',
		'eq': '='
	};

	this.swapinput = function (select, field) {
		
		var that = this,
			new_field = null;

		switch(
			typeof(this.fields[select.attr('value')])
		) {
			case 'object':

				if (this.fields[select.attr('value')] instanceof Date) {
					new_field = $('<input class="lsfilter-type-string" type="text" value="' + this.fields[select.attr('value')].toString() + '">');
				} else {
					new_field = $('<select />');
					for (var v in this.fields[select.attr('value')]) {
						if (v == field.attr('value')) {
							new_field.append($('<option selected="true" value="'+ v +'">'+ this.fields[select.attr('value')][v] +'</option>'));
						} else {
							new_field.append($('<option value="'+ v +'">'+ this.fields[select.attr('value')][v] +'</option>'));
						}
					}
				}

				field.replaceWith(new_field);

				break;
			case 'string': 
				new_field = $('<input class="lsfilter-type-string" type="text" value="' + field.attr('value') + '">');
				field.replaceWith(new_field);
				break;
			case 'number': 
				new_field = $('<input type="text" value="' + field.attr('value') + '">');
				field.replaceWith(new_field);
				break;
		}

		if (new_field)
			select.change(function () {that.swapinput(select, new_field);});

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

		var result = $('<ul />'),
			groups = $('<select id="lsfilter-query-object" />');

		for (var type in this.demo_fields) {
			if (type == name0) {
				groups.append($('<option selected="true" value="' + type + '">' + type + '</option>'));
				this.fields = this.demo_fields[type];
			} else {
				groups.append($('<option value="' + type + '">' + type + '</option>'))
			}
		}

		//result.append($('<li><strong>table_def_simple</strong></li>'));
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
			result.append($('<li class="resultvisual lsfilter-leaf" />').append(filter0));	
		}

		result.append($('<li class="lsfilter-or-text"><strong>- OR -</strong></li>'));
		result.append($('<li class="resultvisual lsfilter-leaf" />').append(filter2));

		return result;
	};
	
	this.visit_filter_and = function(filter0, filter2) {

		if (filter0.is('.lsfilter-and')) {
			var result = filter0;
		} else {
			var result = $('<ul class="lsfilter-and" />');
			result.append($('<li class="resultvisual lsfilter-and-expr" />').append(filter0));
		}

		result.append($('<li style="margin: 3px 6px"><strong>|<br />AND<br />|</strong></li>'));
		result.append($('<li class="resultvisual lsfilter-and-expr" />').append(filter2));
		
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
	
	this.visit_match_in        = function(set_descr1)    { return this.match('in',       "FIXME",  set_descr1); };
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

		var that = this,
			val = $('<input type="text" value="' + expr.replace(/['"]/g,'') + '" />'),
			result = $('<ul class="lsfilter-comp" />'),
			fields = $('<select />'),
			ops = $('<select />');

		for (var f in this.fields) {
			if (f == name) {
				fields.append($('<option value="' + f + '" selected="true">' + f + '</option>'));
			} else {
				fields.append($('<option value="' + f + '">' + f + '</option>'));
			}
		}

		result.append(fields);
		
		for (var operator in this.op_replacements) {
			if (operator == op) {
				ops.append($('<option selected="true" value="' + this.op_replacements[operator] + '">' + this.op_replacements[operator] + '</option>'));
			} else {
				ops.append($('<option value="' + this.op_replacements[operator] + '">' + this.op_replacements[operator] + '</option>'));
			}
		}
		
		result.append(ops);
		result.append(val);

		result.append($('<button class="lsfilter-add-and" />').text('And').click(function (e) {

			var or_block = $(this).parent().parent().parent().parent(),
				clone = or_block.clone(true);

			or_block.after(clone);
			clone.before($('<li style="margin: 3px 6px"><strong>|<br />AND<br />|</strong></li>'));

			e.preventDefault();

		}));

		result.append($('<button class="lsfilter-add-or" />').text('Or').click(function (e) {
			
			var or_block = $(this).parent().parent().parent().parent(),
				clone = or_block.clone(true);

			or_block.after(clone);
			clone.before($('<li class="lsfilter-or-text"><strong>- OR -</strong></li>'));

			e.preventDefault();
		}));

		fields.change(function () {that.swapinput(fields, val);})
		that.swapinput(fields, val);
		return result;
	}
	
	// set_descr_name: Êset_descr := * string
	this.visit_set_descr_name = function(string0) {
		var result = $('<ul />');
		result.append($('<li><strong>set_descr_name</strong></li>'));
		result.append($('<li class="resultvisual" />').append(string0));
		return result;
	};
	
	// set_descr_query: Êset_descr := * query
	this.visit_set_descr_query = function(query0) {
		console.log("call visit_set_descr_query");
		var result = $('<ul />');
		result.append($('<li><strong>set_descr_query</strong></li>'));
		result.append($('<li class="resultvisual" />').append(query0));
		return result;
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

var render = {
		"hosts": function(obj) {
			console.log(obj);
			var row = $('<tr />');
			return row.append($('<td />').text(obj.name));
		},
		"services": function(obj) {
			console.log(obj);
			var row = $('<tr />');
			return row.append($('<td />').text(obj.host.name+";"+obj.description));
		}
};

var query_timer = null;
var query_string = "";
var current_request = null;

var doAjaxSearch = function() {
	if( current_request != null ) {
		current_request.abort();
	}
	console.log( "Query: "+query_string );
	current_request = $.ajax({
		url: _site_domain + _index_page + "/" + _controller_name + "/fetch_ajax",
		dataType: 'json',
		data: {
			"q": query_string
		},
		success: function(data) {
			if( data.status == 'success' ) {
				console.log( "Got "+data.data.length+" objects" );
				var tbl = $('<table />');
				for( var i=0; i<data.data.length; i++ ) {
					var obj = data.data[i];
					tbl.append( render[obj._table](obj) );
				}
				$('#filter_result').empty().append(tbl);
			}
			if( data.status == 'error' ) {
				$('#filter_result').empty().text( "Error: "+data.data );
			}
		}
	});
}

function sendAjaxSearch(query) {
	if (query_timer != null) {
		clearTimeout( query_timer );
	}
	query_string = query;
	query_timer = setTimeout( doAjaxSearch, 500 );
}

var visualizeSearchFilter = function(evt) {
	
	var filter_string = [];

	var traverse = function (dom, priority) {

		var seg = [];
		var tmp = null;
		var result = "";
		var out_priority;

		if (dom.hasClass('lsfilter-comp')) {

			dom.children().each(function () {
				if ($(this).hasClass('lsfilter-type-string')) {
					seg.push('"' + this.value + '"');	
				} else {
					seg.push(this.value);	
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
		
		sendAjaxSearch(filter_string.join(''));

		$('#filter_visual_result').html(
			'<strong>URI: </strong><input type="text" onclick="this.select()" value="' + $('#server_name').val() + '/ninja/index.php/listview?filter_query='+ encodeURIComponent(filter_string.join('')) +'">'
		);
	}

	$('#filter_visual_form').bind('change', dotraverse);

	try {
		var result = parser.parse(string);

		$('#filter_visual').empty().append(result);
		$('#filter_query').css("border", "2px solid #5d2");

		dotraverse();

	} catch( ex ) {
		$('#filter_query').css("border", "2px solid #f40")
		//console.log(ex);
	}
}

$().ready(function() {

	$('#show-filter-query-builder-manual-button').click(function () {
		$('#filter-query-builder-manual').toggle(300, function () {
			switch ($(this).css('display')) {
				case "block":
					$('#filter-query-builder-graphical').hide(200);
					break;
				case "none":
					break;
			}
		});
	});

	$('#show-filter-query-builder-graphical-button').click(function () {
		$('#filter-query-builder-graphical').toggle(300, function () {
			switch ($(this).css('display')) {
				case "block":
					$('#filter-query-builder-manual').hide(200);
					break;
				case "none":
					break;
			}
		});
	});

	visualizeSearchFilter(false);
	$('#filter_query').bind('input propertychange',visualizeSearchFilter);
});

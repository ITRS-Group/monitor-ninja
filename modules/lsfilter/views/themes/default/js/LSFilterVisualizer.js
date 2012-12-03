var LSFilterVisualizerVisitor = function LSFilterVisualizerVisitor(){
	this.accept = function(result) {
		return result;
	};
	
	this.visit_entry = function(query0) {
		return $('<form action="#" />').append(query0);
	};
	
	this.visit_query = function(table_def1, search_query3) {
		var result = $('<ul />');
		result.append($('<li><strong>query</strong></li>'));
		result.append($('<li class="resultvisual" />').append(table_def1));
		result.append($('<li class="resultvisual" />').append(search_query3));
		return result;
	};
	
	this.visit_table_def_simple = function(name0) {
		var result = $('<ul />');
		result.append($('<li><strong>table_def_simple</strong></li>'));
		result.append($('<li class="resultvisual" />').append(name0));
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
		result.append($('<li><strong>search_query</strong></li>'));
		result.append($('<li class="resultvisual" />').append(filter0));
		return result;
	};
	
	this.visit_filter_or = function(filter0, filter2) {
		var result = $('<ul />');
		result.append($('<li><strong>filter_or</strong></li>'));
		result.append($('<li class="resultvisual" />').append(filter0));
		result.append($('<li class="resultvisual" />').append(filter2));
		return result;
	};
	
	this.visit_filter_and = function(filter0, filter2) {
		var result = $('<ul />');
		result.append($('<li><strong>filter_and</strong></li>'));
		result.append($('<li class="resultvisual" />').append(filter0));
		result.append($('<li class="resultvisual" />').append(filter2));
		return result;
	};
	
	this.visit_filter_ok = function(match) {
		var result = $('<ul />');
		result.append($('<li><strong>filter_ok</strong></li>'));
		result.append($('<li class="resultvisual" />').append(match));
		return result;
	};
	
	this.visit_filter_not = function(match) {
		var result = $('<ul />');
		result.append($('<li><strong>filter_not</strong></li>'));
		result.append($('<li class="resultvisual" />').append(match));
		return result;
	};
	
	this.visit_match_in        = function(name0, expr2) { return this.match('in',       name0,expr2); };
	this.visit_match_field_in  = function(name0, expr2) { return this.match('field_in', name0,expr2); };
	this.visit_match_not_re_ci = function(name0, expr2) { return this.match('not_re_ci',name0,expr2); };
	this.visit_match_not_re_cs = function(name0, expr2) { return this.match('not_re_cs',name0,expr2); };
	this.visit_match_re_ci     = function(name0, expr2) { return this.match('re_ci',    name0,expr2); };
	this.visit_match_re_cs     = function(name0, expr2) { return this.match('re_cs',    name0,expr2); };
	this.visit_match_not_eq_ci = function(name0, expr2) { return this.match('not_eq_ci',name0,expr2); };
	this.visit_match_eq_ci     = function(name0, expr2) { return this.match('eq_ci',    name0,expr2); };
	this.visit_match_not_eq    = function(name0, expr2) { return this.match('not_eq',   name0,expr2); };
	this.visit_match_gt_eq     = function(name0, expr2) { return this.match('gt_eq',    name0,expr2); };
	this.visit_match_lt_eq     = function(name0, expr2) { return this.match('lt_eq',    name0,expr2); };
	this.visit_match_gt        = function(name0, expr2) { return this.match('gt',       name0,expr2); };
	this.visit_match_lt        = function(name0, expr2) { return this.match('lt',       name0,expr2); };
	this.visit_match_eq        = function(name0, expr2) { return this.match('eq',       name0,expr2); };
	
	this.match = function(op,name,expr) {
		var result = $('<ul />');
		result.append($('<li><strong>'+op+'</strong></li>'));
		result.append($('<li class="resultvisual" />').append(name));
		result.append($('<li class="resultvisual" />').append(expr));
		return result;
	}
};

var visualizeSearchFilter = function(evt) {
	var string = $('#filter_query').val();
	var parser = new LSFilter(new LSFilterPreprocessor(), new LSFilterVisualizerVisitor());
	try {
		var result = parser.parse(string);
		$('#filter_visual').empty().append(result);
	} catch( ex ) {
		console.log(ex);
	}
}

$().ready(function() {
	visualizeSearchFilter(false);
	$('#filter_query').bind('input propertychange',visualizeSearchFilter);
});

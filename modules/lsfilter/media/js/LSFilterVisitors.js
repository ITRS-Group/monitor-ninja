var LSFilterPP = function LSFilterPP() {
	this.parent = LSFilterPreprocessor;
	this.parent();

	this.preprocess_string = function(value) {
		return value.substring(1,value.length-1).replace(/\\(.)/g, '$1');
	};
}

var LSFilterMetadataVisitor = function LSFilterMetadataVisitor(){
	this.visit_entry            = function(query0)                    { return query0; };
	this.visit_query            = function(table_def1, search_query3) {
		var metadata = table_def1;
		metadata['columns'] = search_query3;
		return metadata;
		};
	this.visit_table_def_simple = function(name0)                     { return {'table':name0}; };
	this.visit_search_query     = function(filter0)                   { return filter0; };
	this.visit_filter_or        = function(filter0, filter2)          { return filter0.concat(filter2); };
	this.visit_filter_and       = function(filter0, filter2)          { return filter0.concat(filter2); };
	this.visit_filter_not       = function(filter1)                   { return filter0; };
	this.visit_filter_ok        = function(match0)                    { return match0; };
	this.visit_match_all        = function()                          { return []; };
	this.visit_match_in         = function(set_descr1)                { return []; };
	this.visit_match_field_in   = function(field0, set_descr2)        { return [field0]; };
	this.visit_match_not_re_ci  = function(field0, arg_string2)       { return [field0]; };
	this.visit_match_not_re_cs  = function(field0, arg_string2)       { return [field0]; };
	this.visit_match_re_ci      = function(field0, arg_string2)       { return [field0]; };
	this.visit_match_re_cs      = function(field0, arg_string2)       { return [field0]; };
	this.visit_match_not_eq_ci  = function(field0, arg_string2)       { return [field0]; };
	this.visit_match_eq_ci      = function(field0, arg_string2)       { return [field0]; };
	this.visit_match_not_eq     = function(field0, arg_num_string2)   { return [field0]; };
	this.visit_match_gt_eq      = function(field0, arg_num2)          { return [field0]; };
	this.visit_match_lt_eq      = function(field0, arg_num2)          { return [field0]; };
	this.visit_match_gt         = function(field0, arg_num2)          { return [field0]; };
	this.visit_match_lt         = function(field0, arg_num2)          { return [field0]; };
	this.visit_match_eq         = function(field0, arg_num_string2)   { return [field0]; };
	this.visit_set_descr_name   = function(string0)                   { return null; };
	this.visit_field_name       = function(name0)                     { return name0; };
	this.visit_field_obj        = function(name0, field2)             { return name0+"."+field2; };
	this.accept                 = function(result)                    { return result; };
};


var LSFilterASTVisitor = function LSFilterASTVisit() {
	this.visit_entry            = function(query0)                    { return query0; };
	this.visit_query            = function(table_def1, search_query3) { return {'obj':'query', 'table':table_def1, 'query':search_query3}; };
	this.visit_table_def_simple = function(name0)                     { return name0; };
	this.visit_search_query     = function(filter0)                   { return filter0; };
	this.visit_filter_or        = function(filter0, filter2)          { return this.addto('or',[filter0,filter2]); };
	this.visit_filter_and       = function(filter0, filter2)          { return this.addto('and',[filter0,filter2]); };
	this.visit_filter_not       = function(filter1)                   { if(filter1.obj=='not') return filter1.sub; return {'obj':'not','sub':filter1}; };
	this.visit_filter_ok        = function(match0)                    { return match0; };
	this.visit_match_all        = function()                          { return {'obj':'match',                 'value':'',              'op':'all'};       };
	this.visit_match_in         = function(set_descr1)                { return {'obj':'match',                 'value':set_descr1,      'op':'in'};        };
	this.visit_match_field_in   = function(field0, set_descr2)        { return {'obj':'match', 'field':field0, 'value':set_descr2,      'op':'in'};        };
	this.visit_match_not_re_ci  = function(field0, arg_string2)       { return {'obj':'match', 'field':field0, 'value':arg_string2,     'op':'not_re_ci'}; };
	this.visit_match_not_re_cs  = function(field0, arg_string2)       { return {'obj':'match', 'field':field0, 'value':arg_string2,     'op':'not_re_cs'}; };
	this.visit_match_re_ci      = function(field0, arg_string2)       { return {'obj':'match', 'field':field0, 'value':arg_string2,     'op':'re_ci'};     };
	this.visit_match_re_cs      = function(field0, arg_string2)       { return {'obj':'match', 'field':field0, 'value':arg_string2,     'op':'re_cs'};     };
	this.visit_match_not_eq_ci  = function(field0, arg_string2)       { return {'obj':'match', 'field':field0, 'value':arg_string2,     'op':'not_eq_ci'}; };
	this.visit_match_eq_ci      = function(field0, arg_string2)       { return {'obj':'match', 'field':field0, 'value':arg_string2,     'op':'eq_ci'};     };
	this.visit_match_not_eq     = function(field0, arg_num_string2)   { return {'obj':'match', 'field':field0, 'value':arg_num_string2, 'op':'not_eq'};    };
	this.visit_match_gt_eq      = function(field0, arg_num2)          { return {'obj':'match', 'field':field0, 'value':arg_num2,        'op':'gt_eq'};     };
	this.visit_match_lt_eq      = function(field0, arg_num2)          { return {'obj':'match', 'field':field0, 'value':arg_num2,        'op':'lt_eq'};     };
	this.visit_match_gt         = function(field0, arg_num2)          { return {'obj':'match', 'field':field0, 'value':arg_num2,        'op':'gt'};        };
	this.visit_match_lt         = function(field0, arg_num2)          { return {'obj':'match', 'field':field0, 'value':arg_num2,        'op':'lt'};        };
	this.visit_match_eq         = function(field0, arg_num_string2)   { return {'obj':'match', 'field':field0, 'value':arg_num_string2, 'op':'eq'};        };
	this.visit_set_descr_name   = function(string0)                   { return string0; };
	this.visit_field_name       = function(name0)                     { return name0; };
	this.visit_field_obj        = function(name0, field2)             { return name0+"."+field2; };
	this.accept                 = function(result)                    { return result; };
	
	this.addto = function( obj, fields ) {
		var list = [];
		for( var i in fields ) {
			if( fields[i].obj == obj ) {
				list = list.concat(fields[i].sub);
			} else {
				list.push(fields[i]);
			}
		}
		return {'obj':obj, 'sub':list};
	};
};

function LSFilterASTVisit( obj, visitor ) {
	var method_name = 'visit_'+obj.obj;
	return visitor[method_name](obj);
}